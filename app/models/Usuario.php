<?php
/**
 * MODEL: Usuario
 * Encapsula a lógica de acesso à tabela usuarios
 */

class Usuario {
    private static $conexao = null;
    
    public static function setConexao($conn) {
        self::$conexao = $conn;
    }

    private static function garantirColunasSeguranca() {
        global $conexao;

        $colunas = [
            'recuperacao_metodo' => "ALTER TABLE usuarios ADD COLUMN recuperacao_metodo ENUM('email', 'perguntas') NOT NULL DEFAULT 'email'",
            'recuperacao_pergunta' => "ALTER TABLE usuarios ADD COLUMN recuperacao_pergunta VARCHAR(50) NULL",
            'recuperacao_resposta_hash' => "ALTER TABLE usuarios ADD COLUMN recuperacao_resposta_hash VARCHAR(255) NULL",
            'reset_token_hash' => "ALTER TABLE usuarios ADD COLUMN reset_token_hash VARCHAR(255) NULL",
            'reset_token_expira_em' => "ALTER TABLE usuarios ADD COLUMN reset_token_expira_em DATETIME NULL",
            'lembrar_token_hash' => "ALTER TABLE usuarios ADD COLUMN lembrar_token_hash VARCHAR(255) NULL",
            'lembrar_expira_em' => "ALTER TABLE usuarios ADD COLUMN lembrar_expira_em DATETIME NULL"
        ];

        foreach ($colunas as $coluna => $sql) {
            $stmt = $conexao->prepare(
                "SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = ?"
            );
            if (!$stmt) {
                throw new Exception('Erro ao preparar verificacao de coluna: ' . $conexao->error);
            }

            $stmt->bind_param("s", $coluna);
            $stmt->execute();
            $resultado = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ((int) ($resultado['total'] ?? 0) === 0 && !$conexao->query($sql)) {
                throw new Exception('Erro ao atualizar tabela usuarios: ' . $conexao->error);
            }
        }
    }
    
    /**
     * Buscar usuário por email
     */
    public static function buscarPorEmail($email) {
        global $conexao;
        
        $stmt = $conexao->prepare("SELECT id, email, senha, nome, tipo FROM usuarios WHERE email = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        return $usuario;
    }

    /**
     * Buscar professor ativo por email ou nome.
     */
    public static function buscarPorEmailOuNome($identificador) {
        global $conexao;

        $identificador = trim((string)$identificador);
        if ($identificador === '') {
            return ['usuario' => null, 'ambiguo' => false];
        }

        if (filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conexao->prepare(
                "SELECT id, email, nome, tipo, status
                 FROM usuarios
                 WHERE email = ? AND status = 1
                 LIMIT 1"
            );

            if (!$stmt) {
                throw new Exception('Erro ao preparar query: ' . $conexao->error);
            }

            $stmt->bind_param("s", $identificador);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc() ?: null;
            $stmt->close();

            return ['usuario' => $usuario, 'ambiguo' => false];
        }

        $stmt = $conexao->prepare(
            "SELECT id, email, nome, tipo, status
             FROM usuarios
             WHERE nome = ? AND status = 1
             ORDER BY id ASC
             LIMIT 2"
        );

        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("s", $identificador);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $usuarios = [];
        while ($row = $resultado->fetch_assoc()) {
            $usuarios[] = $row;
        }

        $stmt->close();

        return [
            'usuario' => count($usuarios) === 1 ? $usuarios[0] : null,
            'ambiguo' => count($usuarios) > 1
        ];
    }
    
    /**
     * Buscar usuário por ID
     */
    public static function buscarPorId($id) {
        global $conexao;
        
        $stmt = $conexao->prepare("SELECT id, email, nome, tipo, status FROM usuarios WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        return $usuario;
    }
    
    /**
     * Verificar se email já existe
     */
    public static function emailExiste($email) {
        global $conexao;
        
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $existe = $resultado->num_rows > 0;
        $stmt->close();
        
        return $existe;
    }
    
    /**
     * Criar novo usuário
     */
    public static function criar($dados) {
        global $conexao;
        
        // Validar dados
        $erros = [];
        
        if (empty($dados['nome'])) {
            $erros[] = 'Nome é obrigatório';
        }
        
        if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Email válido é obrigatório';
        }
        
        if (empty($dados['senha'])) {
            $erros[] = 'Senha é obrigatória';
        } elseif (strlen($dados['senha']) < 8 || !preg_match('/[A-Z]/', $dados['senha']) || 
                  !preg_match('/[a-z]/', $dados['senha']) || !preg_match('/\d/', $dados['senha'])) {
            $erros[] = 'Senha deve ter mínimo 8 caracteres, uma maiúscula, uma minúscula e um número';
        }
        
        if (!empty($erros)) {
            return ['sucesso' => false, 'erros' => $erros];
        }
        
        // Verificar se email já existe
        if (self::emailExiste($dados['email'])) {
            return ['sucesso' => false, 'erro' => 'Email já cadastrado'];
        }
        
        // Hash da senha
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $nome = sanitizarTexto($dados['nome']);
        $email = sanitizarTexto($dados['email']);
        $tipo = $dados['tipo'] ?? 'professor';
        
        // Inserir usuário
        $stmt = $conexao->prepare(
            "INSERT INTO usuarios (email, senha, nome, tipo, status, criado_em) VALUES (?, ?, ?, ?, 1, NOW())"
        );
        
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }
        
        $stmt->bind_param("ssss", $email, $senha_hash, $nome, $tipo);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            return ['sucesso' => false, 'erro' => 'Erro ao inserir usuário'];
        }
        
        $novo_id = $conexao->insert_id;
        $stmt->close();
        
        return ['sucesso' => true, 'id' => $novo_id, 'email' => $email, 'nome' => $nome];
    }
    
    /**
     * Atualizar último login
     */
    public static function atualizarUltimoLogin($id) {
        global $conexao;
        
        $stmt = $conexao->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Atualizar perfil do usuario logado
     */
    public static function atualizarPerfil($id, $nome, $email) {
        global $conexao;

        $id = (int) $id;
        $nome = sanitizarTexto($nome);
        $email = sanitizarTexto($email);

        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $emailEmUso = $resultado->num_rows > 0;
        $stmt->close();

        if ($emailEmUso) {
            throw new Exception('Email ja cadastrado por outro usuario');
        }

        $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("ssi", $nome, $email, $id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar perfil: ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    /**
     * Alterar senha do usuario logado
     */
    public static function alterarSenha($id, $senhaAtual, $novaSenha) {
        global $conexao;

        $id = (int) $id;

        if ($senhaAtual === '' || $novaSenha === '') {
            throw new Exception('Senha atual e nova senha sao obrigatorias');
        }

        if (strlen($novaSenha) < 8 || !preg_match('/[A-Z]/', $novaSenha) ||
            !preg_match('/[a-z]/', $novaSenha) || !preg_match('/\d/', $novaSenha)) {
            throw new Exception('Senha deve ter minimo 8 caracteres, uma maiuscula, uma minuscula e um numero');
        }

        $stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) {
            throw new Exception('Senha atual invalida');
        }

        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("si", $hash, $id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao alterar senha: ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public static function definirTokenLembrar($id, $token, $dias = 30) {
        global $conexao;
        self::garantirColunasSeguranca();

        $id = (int) $id;
        $hash = hash('sha256', $token);
        $expiraEm = date('Y-m-d H:i:s', time() + ($dias * 86400));

        $stmt = $conexao->prepare(
            "UPDATE usuarios SET lembrar_token_hash = ?, lembrar_expira_em = ? WHERE id = ?"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("ssi", $hash, $expiraEm, $id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao salvar token de lembrar: ' . $stmt->error);
        }

        $stmt->close();
        return $expiraEm;
    }

    public static function limparTokenLembrar($token) {
        global $conexao;
        self::garantirColunasSeguranca();

        if ($token === '') {
            return true;
        }

        $hash = hash('sha256', $token);
        $stmt = $conexao->prepare(
            "UPDATE usuarios SET lembrar_token_hash = NULL, lembrar_expira_em = NULL WHERE lembrar_token_hash = ?"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("s", $hash);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public static function buscarPorTokenLembrar($token) {
        global $conexao;
        self::garantirColunasSeguranca();

        $hash = hash('sha256', (string) $token);
        $stmt = $conexao->prepare(
            "SELECT id, email, nome, tipo FROM usuarios
             WHERE lembrar_token_hash = ? AND lembrar_expira_em > NOW() AND status = 1 LIMIT 1"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("s", $hash);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->num_rows ? $resultado->fetch_assoc() : null;
        $stmt->close();

        return $usuario;
    }

    public static function salvarRecuperacao($id, $metodo, $pergunta = '', $resposta = '') {
        global $conexao;
        self::garantirColunasSeguranca();

        $id = (int) $id;
        if (!in_array($metodo, ['email', 'perguntas'], true)) {
            throw new Exception('Metodo de recuperacao invalido');
        }

        $pergunta = $metodo === 'perguntas' ? sanitizarTexto($pergunta) : null;
        $respostaHash = $metodo === 'perguntas' ? password_hash(strtolower(trim($resposta)), PASSWORD_DEFAULT) : null;

        $stmt = $conexao->prepare(
            "UPDATE usuarios
             SET recuperacao_metodo = ?, recuperacao_pergunta = ?, recuperacao_resposta_hash = ?
             WHERE id = ?"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("sssi", $metodo, $pergunta, $respostaHash, $id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao salvar recuperacao: ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public static function obterRecuperacaoUsuario($id) {
        global $conexao;
        self::garantirColunasSeguranca();

        $id = (int) $id;
        $stmt = $conexao->prepare(
            "SELECT recuperacao_metodo, recuperacao_pergunta FROM usuarios WHERE id = ? LIMIT 1"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $dados = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $dados ?: ['recuperacao_metodo' => 'email', 'recuperacao_pergunta' => null];
    }

    public static function obterRecuperacaoPorEmail($email) {
        global $conexao;
        self::garantirColunasSeguranca();

        $stmt = $conexao->prepare(
            "SELECT recuperacao_metodo, recuperacao_pergunta, recuperacao_resposta_hash
             FROM usuarios WHERE email = ? AND status = 1 LIMIT 1"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $dados = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$dados) {
            return null;
        }

        return [
            'metodo' => !empty($dados['recuperacao_resposta_hash']) ? 'perguntas' : ($dados['recuperacao_metodo'] ?: 'email'),
            'pergunta' => $dados['recuperacao_pergunta'],
            'pergunta_configurada' => !empty($dados['recuperacao_resposta_hash'])
        ];
    }

    public static function validarRespostaRecuperacao($email, $resposta) {
        global $conexao;
        self::garantirColunasSeguranca();

        $stmt = $conexao->prepare(
            "SELECT id, email, nome, recuperacao_resposta_hash
             FROM usuarios
             WHERE email = ? AND recuperacao_resposta_hash IS NOT NULL AND status = 1 LIMIT 1"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $respostaNormalizada = strtolower(trim($resposta));
        if (!$usuario || empty($usuario['recuperacao_resposta_hash']) ||
            !password_verify($respostaNormalizada, $usuario['recuperacao_resposta_hash'])) {
            return null;
        }

        return $usuario;
    }

    public static function redefinirSenhaComResposta($email, $resposta, $novaSenha) {
        global $conexao;
        self::garantirColunasSeguranca();

        if (strlen($novaSenha) < 8 || !preg_match('/[A-Z]/', $novaSenha) ||
            !preg_match('/[a-z]/', $novaSenha) || !preg_match('/\d/', $novaSenha)) {
            throw new Exception('Senha deve ter minimo 8 caracteres, uma maiuscula, uma minuscula e um numero');
        }

        $usuario = self::validarRespostaRecuperacao($email, $resposta);
        if (!$usuario) {
            throw new Exception('Resposta incorreta');
        }

        $hashSenha = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $conexao->prepare(
            "UPDATE usuarios
             SET senha = ?, reset_token_hash = NULL, reset_token_expira_em = NULL,
                 lembrar_token_hash = NULL, lembrar_expira_em = NULL
             WHERE id = ?"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $id = (int) $usuario['id'];
        $stmt->bind_param("si", $hashSenha, $id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao redefinir senha: ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public static function criarTokenReset($id) {
        global $conexao;
        self::garantirColunasSeguranca();

        $id = (int) $id;
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expiraEm = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $conexao->prepare(
            "UPDATE usuarios SET reset_token_hash = ?, reset_token_expira_em = ? WHERE id = ?"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("ssi", $hash, $expiraEm, $id);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao gerar token: ' . $stmt->error);
        }

        $stmt->close();
        return $token;
    }

    public static function redefinirSenhaComToken($token, $novaSenha) {
        global $conexao;
        self::garantirColunasSeguranca();

        if (strlen($novaSenha) < 8 || !preg_match('/[A-Z]/', $novaSenha) ||
            !preg_match('/[a-z]/', $novaSenha) || !preg_match('/\d/', $novaSenha)) {
            throw new Exception('Senha deve ter minimo 8 caracteres, uma maiuscula, uma minuscula e um numero');
        }

        $hashToken = hash('sha256', $token);
        $hashSenha = password_hash($novaSenha, PASSWORD_DEFAULT);

        $stmt = $conexao->prepare(
            "UPDATE usuarios
             SET senha = ?, reset_token_hash = NULL, reset_token_expira_em = NULL,
                 lembrar_token_hash = NULL, lembrar_expira_em = NULL
             WHERE reset_token_hash = ? AND reset_token_expira_em > NOW()"
        );
        if (!$stmt) {
            throw new Exception('Erro ao preparar query: ' . $conexao->error);
        }

        $stmt->bind_param("ss", $hashSenha, $hashToken);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao redefinir senha: ' . $stmt->error);
        }

        $alterado = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$alterado) {
            throw new Exception('Token invalido ou expirado');
        }

        return true;
    }
}
?>
