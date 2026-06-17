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
    <title>Criar Questão Dissertativa - +Português</title>
    <link rel="stylesheet" href="./css/style.css">
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const API_URL = '<?php echo API_URL; ?>';
        const UPLOAD_URL = '<?php echo UPLOAD_URL; ?>';
    </script>
</head>
<body>
    <div class="container-form">
        <div class="header-mini">
            <a href="./?page=home" class="back-btn">← Voltar ao Dashboard</a>
            <a href="./?page=home" class="logo">+Português</a>
        </div>

        <main class="form-page">
            <div class="success-message" id="successMessage"></div>
            <div class="error-message" id="errorMessage"></div>

            <section class="form-container">
                <div class="form-header">
                    <h1>Criar Questão Dissertativa</h1>
                    <p>Crie uma questão aberta com enunciado, critérios de resposta e organização por categoria.</p>
                </div>

                <div class="type-switch">
                    <a href="./?page=criacao_objetiva">Questão Objetiva</a>
                    <a class="active" href="./?page=criacao_dissertativa">Questão Dissertativa</a>
                </div>

                <form id="questionForm">
                    <div class="form-row">
                        <div class="campo">
                            <label for="titulo">Título da Questão</label>
                            <input type="text" id="titulo" placeholder="Ex: Análise de texto argumentativo" required>
                        </div>
                        <div class="campo">
                            <label for="genero">Gênero / Categoria</label>
                            <input type="text" id="genero" placeholder="Ex: Redação, Literatura, Interpretação" required>
                        </div>
                    </div>

                    <section class="form-section">
                        <h3>📝 Enunciado</h3>
                        <div class="campo">
                            <label for="enunciado">Texto do enunciado</label>
                            <textarea id="enunciado" maxlength="20000" placeholder="Escreva aqui o enunciado da questão..." required></textarea>
                            <small><span id="charCount">0</span>/20000 caracteres</small>
                        </div>
                    </section>

                    <section class="form-section section-answer">
                        <h3>💡 Resposta esperada</h3>
                        <div class="campo">
                            <label for="explicacao">Resposta ou critérios de avaliação</label>
                            <textarea id="explicacao" placeholder="Descreva os pontos principais que devem aparecer na resposta."></textarea>
                        </div>
                    </section>

                    <section class="form-section section-meta">
                        <h3>🏷️ Filtros e categorias</h3>
                        <div class="form-row">
                            <div class="campo">
                                <label for="subgenero">Subgênero</label>
                                <input type="text" id="subgenero" placeholder="Ex: Crônica, Artigo de opinião, Poema">
                            </div>
                            <div class="campo">
                                <label for="especificacao">Especificação</label>
                                <input type="text" id="especificacao" placeholder="Ex: Coesão textual">
                            </div>
                        </div>
                    </section>

                    <section class="form-section section-image">
                        <h3>🖼️ Imagem da questão</h3>
                        <label class="upload-area" for="imagem">
                            <input type="file" id="imagem" accept="image/*">
                            <div id="placeholder-upload">
                                <div style="font-size:2rem;">📤</div>
                                <p>Clique ou arraste uma imagem aqui</p>
                                <small>Máximo 5MB. JPG, PNG, WebP ou GIF.</small>
                            </div>
                            <img id="preview-img" class="preview-img" alt="Preview" style="display:none;">
                        </label>
                    </section>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
                        <button type="button" class="btn btn-warning" onclick="enviar('salvar')">Salvar rascunho</button>
                        <button type="submit" class="btn btn-primary">Postar questão</button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script>
        const form = document.getElementById('questionForm');
        const enunciado = document.getElementById('enunciado');
        const charCount = document.getElementById('charCount');
        const inputImagem = document.getElementById('imagem');
        const previewImg = document.getElementById('preview-img');
        const placeholder = document.getElementById('placeholder-upload');

        enunciado.addEventListener('input', () => {
            charCount.textContent = enunciado.value.length;
        });

        inputImagem.addEventListener('change', () => {
            const file = inputImagem.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });

        form.addEventListener('submit', event => {
            event.preventDefault();
            enviar('postar');
        });

        async function enviar(acao) {
            esconderMensagens();

            const fd = new FormData();
            fd.append('tipo', 'dissertativa');
            fd.append('acao', acao);
            fd.append('titulo', document.getElementById('titulo').value.trim());
            fd.append('genero', document.getElementById('genero').value.trim());
            fd.append('enunciado', enunciado.value.trim());
            fd.append('explicacao', document.getElementById('explicacao').value.trim());
            fd.append('especificacao', document.getElementById('especificacao').value.trim());
            fd.append('subgenero', document.getElementById('subgenero').value.trim());

            if (inputImagem.files[0]) {
                fd.append('imagem', inputImagem.files[0]);
            }

            try {
                const res = await fetch(`${API_URL}questoes&acao=salvar`, {
                    method: 'POST',
                    credentials: 'include',
                    body: fd
                });
                const data = await res.json();

                if (!data.ok) {
                    mostrarErro(data.erros?.join('\n') || data.erro || 'Erro ao salvar questão.');
                    return;
                }

                mostrarSucesso('Questão salva com sucesso!');
                setTimeout(() => window.location.href = './?page=home&msg=sucesso', 800);
            } catch (erro) {
                mostrarErro('Erro de conexão: ' + erro.message);
            }
        }

        function mostrarSucesso(msg) {
            const el = document.getElementById('successMessage');
            el.textContent = msg;
            el.classList.add('show');
        }

        function mostrarErro(msg) {
            const el = document.getElementById('errorMessage');
            el.textContent = msg;
            el.classList.add('show');
        }

        function esconderMensagens() {
            document.getElementById('successMessage').classList.remove('show');
            document.getElementById('errorMessage').classList.remove('show');
        }
    </script>
</body>
</html>
