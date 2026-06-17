<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function testSanitizarTextoTrimsAndEscapesHtml(): void
    {
        $resultado = sanitizarTexto('  <strong>Olá</strong> & "teste"  ');

        $this->assertSame('&lt;strong&gt;Olá&lt;/strong&gt; &amp; &quot;teste&quot;', $resultado);
    }

    public function testSanitizarTextoRetornaVazioParaNull(): void
    {
        $this->assertSame('', sanitizarTexto(null));
    }

    public function testSanitizarTextoRetornaVazioParaStringVazia(): void
    {
        $this->assertSame('', sanitizarTexto(''));
    }

    public function testBuscarPorEmailOuNomeVazioRetornaEstruturaPadrao(): void
    {
        $resultado = Usuario::buscarPorEmailOuNome('   ');

        $this->assertSame(['usuario' => null, 'ambiguo' => false], $resultado);
    }
}