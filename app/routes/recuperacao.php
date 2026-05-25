<?php
/**
 * ROTA: Recuperacao de senha
 * Endpoints publicos para solicitar e concluir redefinicao.
 */

require_once __DIR__ . '/../config/config.php';

header(HEADER_JSON);

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

try {
    switch ($acao) {
        case 'consultar':
            RecuperacaoController::consultar();
            break;

        case 'validar_pergunta':
            RecuperacaoController::validar_pergunta();
            break;

        case 'redefinir':
            RecuperacaoController::redefinir();
            break;

        default:
            resposta_erro('Acao nao reconhecida', 400);
    }
} catch (Exception $e) {
    resposta_erro('Erro: ' . $e->getMessage(), 500);
}
?>
