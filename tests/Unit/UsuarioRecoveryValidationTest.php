<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UsuarioRecoveryValidationTest extends TestCase
{
    public function testSalvarRecuperacaoRejeitaMetodoInvalidoAntesDoBanco(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Metodo de recuperacao invalido');

        Usuario::salvarRecuperacao(1, 'telefone', 'Pergunta', 'Resposta');
    }

    public function testRedefinirSenhaComRespostaRejeitaSenhaFracaAntesDoBanco(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Senha deve ter minimo 8 caracteres, uma maiuscula, uma minuscula e um numero');

        Usuario::redefinirSenhaComResposta('teste@teste.com', 'Resposta', 'fraca');
    }

    public function testTextoParaCopiaDecodificaEntidadesHtml(): void
    {
        $reflexao = new ReflectionMethod(Questao::class, 'textoParaCopia');
        $reflexao->setAccessible(true);

        $resultado = $reflexao->invoke(null, '&lt;b&gt;Olá&lt;/b&gt; &amp; Teste');

        $this->assertSame('<b>Olá</b> & Teste', $resultado);
    }
}