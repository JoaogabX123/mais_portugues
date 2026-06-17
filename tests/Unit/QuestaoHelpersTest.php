<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class QuestaoHelpersTest extends TestCase
{
    private string $sourceFile = '';
    private string $copiedFile = '';

    protected function tearDown(): void
    {
        if ($this->sourceFile !== '' && file_exists($this->sourceFile)) {
            @unlink($this->sourceFile);
        }

        if ($this->copiedFile !== '' && file_exists($this->copiedFile)) {
            @unlink($this->copiedFile);
        }

        $this->sourceFile = '';
        $this->copiedFile = '';
    }

    public function testCopiarImagemLocalRetornaMesmoCaminhoQuandoArquivoNaoExiste(): void
    {
        $reflexao = new ReflectionMethod(Questao::class, 'copiarImagemLocal');
        $reflexao->setAccessible(true);

        $resultado = $reflexao->invoke(null, 'uploads/nao-existe.jpg');

        $this->assertSame('uploads/nao-existe.jpg', $resultado);
    }

    public function testCopiarImagemLocalRetornaMesmoCaminhoParaExtensaoNaoPermitida(): void
    {
        $this->sourceFile = PUBLIC_PATH . '/teste_copia_imagem.txt';
        file_put_contents($this->sourceFile, 'conteudo');

        $reflexao = new ReflectionMethod(Questao::class, 'copiarImagemLocal');
        $reflexao->setAccessible(true);

        $resultado = $reflexao->invoke(null, 'teste_copia_imagem.txt');

        $this->assertSame('teste_copia_imagem.txt', $resultado);
    }

    public function testCopiarImagemLocalCopiaArquivoPermitidoParaUploads(): void
    {
        $this->sourceFile = PUBLIC_PATH . '/teste_copia_imagem.jpg';
        file_put_contents($this->sourceFile, 'conteudo-imagem');

        $reflexao = new ReflectionMethod(Questao::class, 'copiarImagemLocal');
        $reflexao->setAccessible(true);

        $resultado = $reflexao->invoke(null, 'teste_copia_imagem.jpg');

        $this->assertStringStartsWith('uploads/img_envio_', $resultado);
        $this->assertStringEndsWith('.jpg', $resultado);

        $this->copiedFile = PUBLIC_PATH . '/' . $resultado;
        $this->assertFileExists($this->copiedFile);
        $this->assertSame('conteudo-imagem', file_get_contents($this->copiedFile));
    }
}