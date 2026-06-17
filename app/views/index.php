<?php
if (isset($_SESSION['usuario_id'])) {
    header('Location: ./?page=home');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>+Português</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body class="landing-page">
    <header class="topbar landing-topbar">
        <nav class="topbar-inner">
            <a href="./" class="logo">+Português</a>
            <div class="nav-actions">
                <a href="./?page=login" class="btn btn-ghost">Entrar</a>
                <a href="./?page=signup" class="btn btn-primary">Cadastrar</a>
            </div>
        </nav>
    </header>

    <main class="landing-main">
        <section class="landing-hero">
            <div class="landing-copy">
                <span class="landing-kicker">Banco de questões para professores</span>
                <h1>+Português</h1>
                <p>
                    Crie, organize e reutilize questões objetivas e dissertativas em uma área simples,
                    visual e feita para o dia a dia de quem ensina Português.
                </p>
                <div class="landing-actions">
                    <a href="./?page=signup" class="btn btn-primary">Começar agora</a>
                    <a href="./?page=login" class="btn btn-ghost">Já tenho conta</a>
                </div>
            </div>

            <div class="landing-preview" aria-label="Prévia do painel">
                <div class="preview-bar">
                    <span></span><span></span><span></span>
                </div>
                <div class="preview-stat-row">
                    <div>
                        <strong>24</strong>
                        <small>Questões</small>
                    </div>
                    <div>
                        <strong>15</strong>
                        <small>Objetivas</small>
                    </div>
                    <div>
                        <strong>9</strong>
                        <small>Dissertativas</small>
                    </div>
                </div>
                <div class="preview-question">
                    <span class="badge">Literatura</span>
                    <h3>Interpretação de texto</h3>
                    <p>Leia o trecho e identifique a alternativa que melhor resume a ideia central.</p>
                </div>
                <div class="preview-lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </section>

        <section class="landing-features" aria-label="Recursos principais">
            <article>
                <strong>📂 Organização</strong>
                <p>Use gêneros, subgêneros e filtros para encontrar rapidamente o que já foi criado.</p>
            </article>
            <article>
                <strong>📝 Dois tipos</strong>
                <p>Monte questões objetivas com alternativas ou propostas dissertativas com resposta esperada.</p>
            </article>
            <article>
                <strong>🔒 Área privada</strong>
                <p>Cada professor acessa apenas as próprias questões, com sessão e autenticação.</p>
            </article>
        </section>
    </main>

    <footer class="landing-footer">
        <span>&copy; 2026 +Português.</span>
        <nav>
            <a href="./?page=info">Info</a>
            <a href="./?page=termos">Termos</a>
            <a href="./?page=login">Entrar</a>
        </nav>
    </footer>
</body>
</html>
