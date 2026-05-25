<?php
/**
 * CONTROLLER: RecuperacaoController
 * Gerencia fluxos publicos de recuperacao de senha.
 */

class RecuperacaoController {
    public static function consultar() {
        $dados = obterDadosJSON();
        $email = trim($dados['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            resposta_validacao(['Informe um email valido']);
        }

        $recuperacao = Usuario::obterRecuperacaoPorEmail($email);
        if (!$recuperacao) {
            resposta_erro('Email nao encontrado', 404);
        }

        resposta_sucesso($recuperacao, 'Dados de recuperacao carregados');
    }

    public static function validar_pergunta() {
        $dados = obterDadosJSON();
        $email = trim($dados['email'] ?? '');
        $resposta = trim($dados['resposta'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $resposta === '') {
            resposta_validacao(['Informe email e resposta']);
        }

        $usuario = Usuario::validarRespostaRecuperacao($email, $resposta);
        if (!$usuario) {
            resposta_erro('Resposta incorreta', 401);
        }

        resposta_sucesso(null, 'Resposta confirmada');
    }

    public static function redefinir() {
        $dados = obterDadosJSON();
        $email = trim($dados['email'] ?? '');
        $resposta = trim($dados['resposta'] ?? '');
        $novaSenha = $dados['nova_senha'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $resposta === '' || $novaSenha === '') {
            resposta_validacao(['Informe email, resposta e nova senha']);
        }

        Usuario::redefinirSenhaComResposta($email, $resposta, $novaSenha);
        resposta_sucesso(null, 'Senha redefinida com sucesso');
    }
}
?>
