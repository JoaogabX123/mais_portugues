<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class QuestaoValidationTest extends TestCase
{
    public function testCriarRejeitaCamposObrigatoriosAusentes(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dados obrigatórios faltando: titulo, genero, enunciado, usuario');

        Questao::criar([
            'tipo' => 'objetiva',
            'titulo' => '',
            'genero' => '',
            'enunciado' => '',
            'id_usuario_criador' => 0,
        ]);
    }

    public function testAtualizarRejeitaCamposObrigatoriosAusentes(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dados obrigatórios faltando ou ID inválido');

        Questao::atualizar(0, [
            'tipo' => 'objetiva',
            'titulo' => '',
            'genero' => '',
            'enunciado' => '',
        ]);
    }

    public function testAtualizarRejeitaTituloVazioMesmoComIdValido(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dados obrigatórios faltando ou ID inválido');

        Questao::atualizar(10, [
            'tipo' => 'objetiva',
            'titulo' => '',
            'genero' => 'Narrativo',
            'enunciado' => 'Enunciado',
        ]);
    }

    public function testCriarRejeitaGeneroVazioMesmoComDemaisCamposValidos(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dados obrigatórios faltando: titulo, genero, enunciado, usuario');

        Questao::criar([
            'tipo' => 'dissertativa',
            'titulo' => 'Titulo',
            'genero' => '',
            'enunciado' => 'Texto do enunciado',
            'id_usuario_criador' => 1,
        ]);
    }
}