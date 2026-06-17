<?php

declare(strict_types=1);

if (!defined('APP_TESTING')) {
    define('APP_TESTING', true);
}

$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/mais_portugues/public/index.php';

require_once __DIR__ . '/../app/config/config.php';

final class TestConnectionStub
{
    public string $error = '';

    public function begin_transaction(): void
    {
    }

    public function rollback(): void
    {
    }

    public function commit(): void
    {
    }

    public function prepare($sql)
    {
        return false;
    }

    public function set_charset($charset): bool
    {
        return true;
    }

    public function autocommit($mode): void
    {
    }

    public function query($sql)
    {
        return false;
    }

    public function real_escape_string($value)
    {
        return addslashes((string) $value);
    }
}

global $conexao;
$conexao = new TestConnectionStub();