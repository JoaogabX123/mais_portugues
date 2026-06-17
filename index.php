<?php
/**
 * ROOT INDEX - Redirecionador para /public/index.php
 * Mantém a organização do projeto e protege arquivos sensíveis
 */

header('Location: ./public/index.php' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
exit;
