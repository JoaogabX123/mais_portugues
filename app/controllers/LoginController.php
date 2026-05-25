<?php
/**
 * CONTROLLER: LoginController
 * Gerencia autenticação de usuários
 */

class LoginController {
    /**
     * Fazer login
     * POST /routes/login.php
     */
    public static function fazer_login() {
        try {
            $dados = obterDadosJSON();
            
            if (empty($dados['email']) || empty($dados['senha'])) {
                resposta_erro('Email e senha são obrigatórios', 400);
            }
            
            $email = sanitizarTexto($dados['email']);
            $senha = $dados['senha'];
            
            // Buscar usuário
            $usuario = Usuario::buscarPorEmail($email);
            
            if (!$usuario) {
                resposta_erro('Email ou senha inválidos', 401);
            }
            
            // Verificar senha
            if (!password_verify($senha, $usuario['senha'])) {
                resposta_erro('Email ou senha inválidos', 401);
            }
            
            // Iniciar sessão (já iniciada em config.php)
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['login_time'] = time();
            
            // Atualizar último login
            Usuario::atualizarUltimoLogin($usuario['id']);
            
            if (!empty($dados['lembrar'])) {
                $token = bin2hex(random_bytes(32));
                Usuario::definirTokenLembrar($usuario['id'], $token);
                setcookie('lembrar_login', $token, [
                    'expires' => time() + (30 * 86400),
                    'path' => '/',
                    'httponly' => true,
                    'secure' => false,
                    'samesite' => 'Lax'
                ]);
            } else {
                Usuario::limparTokenLembrar($_COOKIE['lembrar_login'] ?? '');
                setcookie('lembrar_login', '', time() - 3600, '/', '', false, true);
            }

            resposta_sucesso(null, 'Login realizado com sucesso');
            
        } catch (Exception $e) {
            resposta_erro('Erro no servidor: ' . $e->getMessage(), 500);
        }
    }
}
?>
