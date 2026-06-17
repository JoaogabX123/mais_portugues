<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ./?page=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações - +Português</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <header class="topbar">
        <nav class="topbar-inner">
            <a href="./?page=home" class="logo">+Português</a>
            <div class="nav-actions">
                <a href="./?page=home" class="btn btn-ghost">Voltar</a>
            </div>
        </nav>
    </header>

    <main class="app-shell">
        <div class="container doc-page">
            <div class="page-header">
                <h1>Sobre o +Português</h1>
                <p>Uma visão rápida da plataforma e dos recursos principais.</p>
            </div>

            <section class="doc-grid">
                <article class="doc-card">
                    <strong>📝 Questões Dissertativas</strong>
                    <p>Crie questões abertas com enunciado, imagem de apoio e resposta esperada.</p>
                </article>
                <article class="doc-card">
                    <strong>☑️ Questões Objetivas</strong>
                    <p>Monte alternativas A-E, marque o gabarito e salve uma explicação para revisão.</p>
                </article>
                <article class="doc-card">
                    <strong>🗂️ Organização</strong>
                    <p>Use gênero, subgênero e especificação para encontrar materiais com rapidez.</p>
                </article>
                <article class="doc-card">
                    <strong>📤 Compartilhamento</strong>
                    <p>Envie questões para outros professores cadastrados diretamente pelo sistema.</p>
                </article>
                <article class="doc-card">
                    <strong>✏️ Edição Completa</strong>
                    <p>Atualize tipo, conteúdo, imagem, alternativas e status sempre que precisar.</p>
                </article>
                <article class="doc-card">
                    <strong>🔒 Acesso Seguro</strong>
                    <p>Cada professor acessa apenas as próprias questões dentro da área autenticada.</p>
                </article>
            </section>

            <section class="doc-panel">
                <h2>O que é o +Português?</h2>
                <p>
                    O <strong>+Português</strong> é um banco de questões digital feito para professores de Língua
                    Portuguesa. Ele centraliza criação, organização, edição e reutilização de questões objetivas e
                    dissertativas.
                </p>
                <p>
                    A ideia é reduzir o tempo gasto na montagem de provas e atividades, mantendo o material salvo,
                    filtrável e fácil de revisar.
                </p>
                <p>
                    Ao utilizar o sistema, você concorda com os <a href="./?page=termos">Termos de Uso</a>.
                </p>
            </section>
        </div>
    </main>
</body>
</html>
