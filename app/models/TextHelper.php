<?php

class TextHelper
{
    public static function sanitizarTexto($texto)
    {
        if ($texto === null || $texto === '') {
            return '';
        }

        return trim(htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'));
    }

    public static function textoParaCopia($texto)
    {
        return html_entity_decode((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}
