<?php

final class UsuarioValidationTest extends TestCase
{
    public function testValidarDadosParaCriacaoRetornaErrosQuandoFaltamCamposObrigatorios()
    {
        // Arrange
        $dados = [
            'nome' => '',
            'email' => 'nao-email',
            'senha' => 'abc'
        ];

        // Act
        $erros = Usuario::validarDadosParaCriacao($dados);

        // Assert
        $this->assertIsArray($erros);
        $this->assertContains('Nome é obrigatório', $erros);
        $this->assertContains('Email válido é obrigatório', $erros);
        $this->assertContains(
            'Senha deve ter mínimo 8 caracteres, uma maiúscula, uma minúscula e um número',
            $erros
        );
    }

    public function testValidarDadosParaCriacaoRetornaNenhumErroComDadosValidos()
    {
        // Arrange
        $dados = [
            'nome' => 'Usuário Teste',
            'email' => 'teste@teste.com',
            'senha' => 'Senha123'
        ];

        // Act
        $erros = Usuario::validarDadosParaCriacao($dados);

        // Assert
        $this->assertEmpty($erros);
    }
}
