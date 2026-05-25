<?php
/**
 * MODEL: EnvioQuestao
 * Registra envios locais de questoes entre usuarios.
 */

class EnvioQuestao {
    public static function garantirTabela() {
        global $conexao;

        $sql = "CREATE TABLE IF NOT EXISTS envios_questoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_questao INT NOT NULL,
            id_questao_copia INT NULL,
            id_usuario_remetente INT NOT NULL,
            id_usuario_destinatario INT NULL,
            email_destinatario VARCHAR(255) NOT NULL,
            nome_destinatario VARCHAR(100) NULL,
            descricao TEXT NOT NULL,
            status ENUM('pendente', 'enviado', 'falha') NOT NULL DEFAULT 'enviado',
            erro TEXT NULL,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            enviado_em DATETIME NULL,
            notificado_em DATETIME NULL,
            INDEX idx_envios_questao (id_questao),
            INDEX idx_envios_questao_copia (id_questao_copia),
            INDEX idx_envios_remetente (id_usuario_remetente),
            INDEX idx_envios_destinatario (id_usuario_destinatario),
            CONSTRAINT fk_envios_questao_original
                FOREIGN KEY (id_questao) REFERENCES questoes(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_envios_questao_copia
                FOREIGN KEY (id_questao_copia) REFERENCES questoes(id)
                ON DELETE SET NULL,
            CONSTRAINT fk_envios_usuario_remetente
                FOREIGN KEY (id_usuario_remetente) REFERENCES usuarios(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_envios_usuario_destinatario
                FOREIGN KEY (id_usuario_destinatario) REFERENCES usuarios(id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$conexao->query($sql)) {
            throw new Exception('Erro ao preparar tabela de envios: ' . $conexao->error);
        }

        self::garantirColuna('id_questao_copia', "ALTER TABLE envios_questoes ADD COLUMN id_questao_copia INT NULL AFTER id_questao");
        self::garantirColuna('id_usuario_destinatario', "ALTER TABLE envios_questoes ADD COLUMN id_usuario_destinatario INT NULL AFTER id_usuario_remetente");
        self::garantirColuna('nome_destinatario', "ALTER TABLE envios_questoes ADD COLUMN nome_destinatario VARCHAR(100) NULL AFTER email_destinatario");
        self::garantirColuna('notificado_em', "ALTER TABLE envios_questoes ADD COLUMN notificado_em DATETIME NULL AFTER enviado_em");
    }

    private static function garantirColuna($coluna, $alterSql) {
        if (self::colunaExiste($coluna)) {
            return;
        }

        global $conexao;
        if (!$conexao->query($alterSql)) {
            throw new Exception('Erro ao ajustar tabela de envios: ' . $conexao->error);
        }
    }

    private static function colunaExiste($coluna) {
        global $conexao;

        $coluna = $conexao->real_escape_string($coluna);
        $resultado = $conexao->query("SHOW COLUMNS FROM envios_questoes LIKE '{$coluna}'");
        if (!$resultado) {
            throw new Exception('Erro ao verificar tabela de envios: ' . $conexao->error);
        }

        $existe = $resultado->num_rows > 0;
        $resultado->free();

        return $existe;
    }

    public static function registrar($idQuestao, $idUsuarioRemetente, $usuarioDestinatario, $descricao, $idQuestaoCopia = null) {
        global $conexao;

        self::garantirTabela();

        $idQuestao = (int)$idQuestao;
        $idQuestaoCopia = $idQuestaoCopia !== null ? (int)$idQuestaoCopia : null;
        $idUsuarioRemetente = (int)$idUsuarioRemetente;
        $idUsuarioDestinatario = (int)($usuarioDestinatario['id'] ?? 0);
        $emailDestinatario = sanitizarTexto($usuarioDestinatario['email'] ?? '');
        $nomeDestinatario = sanitizarTexto($usuarioDestinatario['nome'] ?? '');
        $descricao = sanitizarTexto($descricao);

        if (!$idQuestao || !$idUsuarioRemetente || !$idUsuarioDestinatario || $emailDestinatario === '') {
            throw new Exception('Dados obrigatorios do envio local estao incompletos');
        }

        $stmt = $conexao->prepare(
            "INSERT INTO envios_questoes
            (id_questao, id_questao_copia, id_usuario_remetente, id_usuario_destinatario,
             email_destinatario, nome_destinatario, descricao, status, enviado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'enviado', NOW())"
        );

        if (!$stmt) {
            throw new Exception('Erro ao preparar envio: ' . $conexao->error);
        }

        $stmt->bind_param(
            "iiiisss",
            $idQuestao,
            $idQuestaoCopia,
            $idUsuarioRemetente,
            $idUsuarioDestinatario,
            $emailDestinatario,
            $nomeDestinatario,
            $descricao
        );

        if (!$stmt->execute()) {
            throw new Exception('Erro ao registrar envio: ' . $stmt->error);
        }

        $idEnvio = $stmt->insert_id;
        $stmt->close();

        return $idEnvio;
    }

    public static function listarRecebidasNaoNotificadas($idUsuarioDestinatario) {
        global $conexao;

        self::garantirTabela();

        $idUsuarioDestinatario = (int)$idUsuarioDestinatario;

        $stmt = $conexao->prepare(
            "SELECT
                e.id AS id_envio,
                e.id_questao_copia AS id_questao_recebida,
                e.descricao,
                e.enviado_em,
                q.titulo,
                q.tipo,
                u.nome AS remetente_nome,
                u.email AS remetente_email
             FROM envios_questoes e
             INNER JOIN questoes q ON q.id = e.id_questao_copia
             INNER JOIN usuarios u ON u.id = e.id_usuario_remetente
             WHERE e.id_usuario_destinatario = ?
               AND e.status = 'enviado'
               AND e.notificado_em IS NULL
             ORDER BY e.enviado_em DESC, e.id DESC"
        );

        if (!$stmt) {
            throw new Exception('Erro ao preparar consulta de recebidas: ' . $conexao->error);
        }

        $stmt->bind_param("i", $idUsuarioDestinatario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $envios = [];
        while ($row = $resultado->fetch_assoc()) {
            $envios[] = $row;
        }

        $stmt->close();

        return $envios;
    }

    public static function marcarComoNotificadas($idUsuarioDestinatario, $idsEnvio) {
        global $conexao;

        self::garantirTabela();

        $idUsuarioDestinatario = (int)$idUsuarioDestinatario;
        $idsEnvio = array_values(array_filter(array_map('intval', (array)$idsEnvio)));

        if (!$idUsuarioDestinatario || empty($idsEnvio)) {
            return true;
        }

        $stmt = $conexao->prepare(
            "UPDATE envios_questoes
             SET notificado_em = NOW()
             WHERE id = ?
               AND id_usuario_destinatario = ?
               AND notificado_em IS NULL"
        );

        if (!$stmt) {
            throw new Exception('Erro ao preparar confirmacao de recebimento: ' . $conexao->error);
        }

        foreach ($idsEnvio as $idEnvio) {
            $stmt->bind_param("ii", $idEnvio, $idUsuarioDestinatario);

            if (!$stmt->execute()) {
                throw new Exception('Erro ao confirmar recebimento: ' . $stmt->error);
            }
        }

        $stmt->close();

        return true;
    }
}
?>
