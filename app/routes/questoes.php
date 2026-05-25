<?php
/**
 * ROTA: Questões
 * Endpoints para CRUD de questões
 * GET  /app/routes/questoes.php?acao=listar|buscar
 * POST /app/routes/questoes.php?acao=salvar|deletar|enviar
 * enviar usa JSON {id, destinatario, descricao}
 */

require_once __DIR__ . '/../config/config.php';

header(HEADER_JSON);

$acao = $_GET['acao'] ?? $_POST['acao'] ?? 'listar';

try {
    switch ($acao) {
        case 'listar':
            QuestaoController::listar();
            break;
        
        case 'buscar':
            QuestaoController::buscar();
            break;
        
        case 'salvar':
            QuestaoController::salvar();
            break;
        
        case 'deletar':
        case 'excluir':
            QuestaoController::deletar();
            break;

        case 'enviar':
            QuestaoController::enviar();
            break;

        case 'recebidas':
            QuestaoController::recebidas();
            break;

        case 'marcar_recebidas_notificadas':
            QuestaoController::marcarRecebidasNotificadas();
            break;
        
        default:
            resposta_erro('Ação não reconhecida', 400);
    }
} catch (Exception $e) {
    resposta_erro('Erro: ' . $e->getMessage(), 500);
}
?>
