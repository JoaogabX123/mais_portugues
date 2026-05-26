<?php
$logado = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso - +Português</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <header class="topbar">
        <nav class="topbar-inner">
            <a href="./" class="logo">+Português</a>
            <div class="nav-actions">
                <?php if ($logado): ?>
                    <a href="./?page=home" class="btn btn-ghost">Voltar</a>
                <?php else: ?>
                    <a href="./?page=login" class="btn btn-ghost">Entrar</a>
                    <a href="./?page=signup" class="btn btn-primary">Cadastrar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="app-shell">
        <div class="container doc-page doc-narrow">
            <div class="page-header">
                <h1>Termos de Uso</h1>
                <p>Leia as condições de uso da plataforma +Português.</p>
            </div>

            <section class="doc-panel">
                <h2>1. Sobre a Plataforma</h2>
                <p>O +Português é um sistema de banco de questões voltado para professores de Língua Portuguesa.</p>
                <p>A plataforma permite criar, armazenar, organizar, editar e compartilhar questões dissertativas e objetivas.</p>

                <h2>2. Cadastro e Responsabilidades</h2>
                <ul>
                    <li>O acesso requer cadastro com nome, e-mail válido e senha.</li>
                    <li>O usuário é responsável pela veracidade dos dados cadastrados.</li>
                    <li>É proibido compartilhar credenciais de acesso com terceiros.</li>
                </ul>

                <h2>3. Conteúdo Criado</h2>
                <p>Questões, enunciados, imagens, explicações e alternativas são responsabilidade do usuário que os criou.</p>
                <ul>
                    <li>Não publique conteúdo ofensivo, ilegal ou que viole direitos de terceiros.</li>
                    <li>Use imagens apenas quando tiver direito de uso.</li>
                    <li>Conteúdos que violem estes termos podem ser removidos.</li>
                </ul>

                <h2>4. Privacidade e Segurança</h2>
                <p>O sistema coleta apenas os dados necessários para autenticação e funcionamento da plataforma.</p>
                <ul>
                    <li>Senhas são armazenadas com criptografia adequada.</li>
                    <li>Cookies são usados para manter sessão autenticada.</li>
                    <li>Cada professor acessa apenas os dados vinculados à própria conta.</li>
                </ul>

                <h2>5. Uso Indevido</h2>
                <p>São proibidas tentativas de acesso não autorizado, automações abusivas e ações que comprometam a integridade do sistema.</p>

                <h2>6. Alterações</h2>
                <p>Estes termos podem ser atualizados. O uso continuado da plataforma implica aceitação da versão vigente.</p>

                <p class="doc-date">Última atualização: maio de 2026.</p>
            </section>

            <?php if (!$logado): ?>
                <div class="landing-actions doc-actions">
                    <a href="./?page=signup" class="btn btn-primary">Aceitar e criar conta</a>
                    <a href="./" class="btn btn-ghost">Voltar ao início</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
