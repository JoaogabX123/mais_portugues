<?php

final class TextHelperTest extends TestCase
{
    public function testSanitizarTextoEscapaTagsETrim()
    {
        // Arrange
        $input = "  <script>alert('xss')</script> Olá  ";

        // Act
        $output = TextHelper::sanitizarTexto($input);

        // Assert
        $this->assertSame("&lt;script&gt;alert('xss')&lt;/script&gt; Olá", $output);
    }

    public function testTextoParaCopiaDecodesEntities()
    {
        // Arrange
        $input = 'Caf&eacute; &amp; Idioma';

        // Act
        $output = TextHelper::textoParaCopia($input);

        // Assert
        $this->assertSame('Café & Idioma', $output);
    }
}
