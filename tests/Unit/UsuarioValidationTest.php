<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UsuarioValidationTest extends TestCase
{
    public function testCriarRetornaErrosDeValidacaoParaDadosInvalidos(): void
    {
        $resultado = Usuario::criar([
            'nome' => '',
            'email' => 'email-invalido',
            'senha' => '123',
        ]);

        $this->assertFalse($resultado['sucesso']);
        $this->assertContains('Nome é obrigatório', $resultado['erros']);
        $this->assertContains('Email válido é obrigatório', $resultado['erros']);
        $this->assertContains('Senha deve ter mínimo 8 caracteres, uma maiúscula, uma minúscula e um número', $resultado['erros']);
    }

    public function testCriarRejeitaEmailInvalidoMesmoComNomeESenhaValidos(): void
    {
        $resultado = Usuario::criar([
            'nome' => 'Professor Teste',
            'email' => 'email-invalido',
            'senha' => 'SenhaForte1',
        ]);

        $this->assertFalse($resultado['sucesso']);
        $this->assertContains('Email válido é obrigatório', $resultado['erros']);
    }

    public function testCriarRejeitaSenhaFracaMesmoComNomeEEmailValidos(): void
    {
        $resultado = Usuario::criar([
            'nome' => 'Professor Teste',
            'email' => 'teste@teste.com',
            'senha' => 'senha-fraca',
        ]);

        $this->assertFalse($resultado['sucesso']);
        $this->assertContains('Senha deve ter mínimo 8 caracteres, uma maiúscula, uma minúscula e um número', $resultado['erros']);
    }

    public function testAlterarSenhaRejeitaNovaSenhaFracaAntesDoBanco(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Senha deve ter minimo 8 caracteres, uma maiuscula, uma minuscula e um numero');

        Usuario::alterarSenha(1, 'SenhaAtual1', 'fraca');
    }

    public function testRedefinirSenhaComTokenRejeitaNovaSenhaFracaAntesDoBanco(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Senha deve ter minimo 8 caracteres, uma maiuscula, uma minuscula e um numero');

        Usuario::redefinirSenhaComToken('token', 'fraca');
    }

    public function testAlterarSenhaRejeitaNovaSenhaVaziaAntesDoBanco(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Senha atual e nova senha sao obrigatorias');

        Usuario::alterarSenha(1, 'SenhaAtual1', '');
    }
}