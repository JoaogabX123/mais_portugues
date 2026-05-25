<?php
/**
 * PUBLIC ENTRY POINT - /public/index.php
 * Ponto único de acesso - Router central
 * Renderiza views sem redirecionar HTTP
 */

// Requer configuração global e autoloader (com session_start())
require_once __DIR__ . '/../app/config/config.php';

if (!isset($_SESSION['usuario_id']) && !empty($_COOKIE['lembrar_login'])) {
    try {
        $usuarioLembrado = Usuario::buscarPorTokenLembrar($_COOKIE['lembrar_login']);
        if ($usuarioLembrado) {
            $_SESSION['usuario_id'] = $usuarioLembrado['id'];
            $_SESSION['usuario_email'] = $usuarioLembrado['email'];
            $_SESSION['usuario_nome'] = $usuarioLembrado['nome'];
            $_SESSION['login_time'] = time();
            Usuario::atualizarUltimoLogin($usuarioLembrado['id']);
        }
    } catch (Exception $e) {
        setcookie('lembrar_login', '', time() - 3600, '/', '', false, true);
    }
}

// Determinar view a renderizar
$page = isset($_GET['page']) ? sanitizarTexto($_GET['page']) : null;
$authenticated = isset($_SESSION['usuario_id']);

// Se tenta acessar página protegida sem autenticação
if ($page && !$authenticated && !in_array($page, ['login', 'signup', 'recuperar_senha'])) {
    $page = 'login';
}

// Definir view padrão baseado no status de autenticação
if (!$page) {
    $page = $authenticated ? 'home' : 'index';
}

// Se logado e tenta acessar login, redireciona para home
if ($page === 'login' && $authenticated) {
    $page = 'home';
}

// Caminho do arquivo view
$view_path = APP_PATH . '/views/' . $page . '.php';

// Verificar se arquivo existe
if (!file_exists($view_path)) {
    http_response_code(404);
    die('Página não encontrada: ' . htmlspecialchars($page));
}

// Renderizar view
require $view_path;
?>
