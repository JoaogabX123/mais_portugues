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
    <title>Editar Questão - +Português</title>
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
                    <h1>Editar Questão</h1>
                    <p>Atualize os campos necessários e salve as alterações.</p>
                </div>

                <div id="loadingState" class="loading-state">Carregando questão...</div>

                <form id="questionForm" style="display:none;">
                    <div class="type-switch">
                        <button type="button" id="btn_objetiva" onclick="alternarTipo('objetiva')">Questão Objetiva</button>
                        <button type="button" id="btn_dissertativa" onclick="alternarTipo('dissertativa')">Questão Dissertativa</button>
                    </div>

                    <div class="form-row">
                        <div class="campo">
                            <label for="titulo">Título da Questão</label>
                            <input type="text" id="titulo" required>
                        </div>
                        <div class="campo">
                            <label for="genero">Gênero / Categoria</label>
                            <input type="text" id="genero" required>
                        </div>
                    </div>

                    <section class="form-section">
                        <h3>📝 Enunciado</h3>
                        <div class="campo">
                            <label for="enunciado">Texto do enunciado</label>
                            <textarea id="enunciado" maxlength="20000" required></textarea>
                            <small><span id="charCount">0</span>/20000 caracteres</small>
                        </div>
                    </section>

                    <section class="form-section section-alt" id="alternativasSection">
                        <h3>📋 Alternativas</h3>
                        <div class="alternativas-list">
                            <?php foreach (['A','B','C','D','E'] as $letra): ?>
                                <div class="alternativa-item">
                                    <div class="alternativa-letra"><?php echo $letra; ?></div>
                                    <input type="text" id="alt_<?php echo $letra; ?>" placeholder="Alternativa <?php echo $letra; ?>">
                                    <input type="radio" name="correta" value="<?php echo $letra; ?>" aria-label="Alternativa correta <?php echo $letra; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="form-section section-meta">
                        <h3>🏷️ Filtros e categorias</h3>
                        <div class="form-row">
                            <div class="campo">
                                <label for="subgenero">Subgênero</label>
                                <input type="text" id="subgenero">
                            </div>
                            <div class="campo">
                                <label for="especificacao">Especificação</label>
                                <input type="text" id="especificacao">
                            </div>
                        </div>
                    </section>

                    <section class="form-section section-answer">
                        <h3>💡 Explicação</h3>
                        <div class="campo">
                            <label for="explicacao">Explicação ou resposta esperada</label>
                            <textarea id="explicacao"></textarea>
                        </div>
                    </section>

                    <section class="form-section section-image">
                        <h3>🖼️ Imagem da questão</h3>
                        <label class="upload-area" for="imagem">
                            <input type="file" id="imagem" accept="image/*">
                            <div id="placeholder-upload">
                                <div style="font-size:2rem;">📤</div>
                                <p>Clique ou arraste uma imagem aqui</p>
                                <small>Se não escolher outra, a imagem atual será mantida.</small>
                            </div>
                            <img id="preview-img" class="preview-img" alt="Preview" style="display:none;">
                        </label>
                    </section>

                    <div class="form-actions">
                        <button type="button" class="btn btn-danger" onclick="abrirModalConfirmacaoExcluir()">Excluir</button>
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
                        <button type="button" class="btn btn-warning" onclick="enviar('salvar')">Salvar rascunho</button>
                        <button type="submit" class="btn btn-primary">Postar questão</button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <div class="modal-overlay" id="modal_confirmacao_editar">
        <div class="modal">
            <h2>Confirmar exclusão</h2>
            <p>Tem certeza que deseja excluir esta questão? Esta ação não pode ser desfeita.</p>
            <div class="modal-botoes">
                <button class="btn btn-danger" type="button" onclick="confirmarExcluirEditar()">Excluir</button>
                <button class="btn btn-secondary" type="button" onclick="fecharModalConfirmacaoEditar()">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        const id = new URLSearchParams(window.location.search).get('id');
        const form = document.getElementById('questionForm');
        const loadingState = document.getElementById('loadingState');
        const inputImagem = document.getElementById('imagem');
        const previewImg = document.getElementById('preview-img');
        const placeholder = document.getElementById('placeholder-upload');
        const enunciado = document.getElementById('enunciado');
        const charCount = document.getElementById('charCount');

        let questao = null;
        let tipoAtual = 'objetiva';

        window.addEventListener('DOMContentLoaded', carregarQuestao);

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

        async function carregarQuestao() {
            if (!id) {
                window.location.href = './?page=home';
                return;
            }

            try {
                const res = await fetch(`${API_URL}questoes&acao=buscar&id=${encodeURIComponent(id)}`, {
                    credentials: 'include'
                });

                if (!res.ok) {
                    window.location.href = './?page=home';
                    return;
                }

                const resposta = await res.json();
                questao = resposta.dados || resposta;
                preencherFormulario(questao);
                loadingState.style.display = 'none';
                form.style.display = 'block';
            } catch (erro) {
                console.error(erro);
                window.location.href = './?page=home';
            }
        }

        function preencherFormulario(q) {
            tipoAtual = q.tipo || 'objetiva';

            document.getElementById('titulo').value = decodeHTML(q.titulo || '');
            document.getElementById('genero').value = decodeHTML(q.genero || '');
            document.getElementById('enunciado').value = decodeHTML(q.enunciado || '');
            document.getElementById('explicacao').value = decodeHTML(q.explicacao || '');
            document.getElementById('subgenero').value = decodeHTML(q.subgenero || '');
            document.getElementById('especificacao').value = decodeHTML(q.especificacao || '');
            charCount.textContent = (q.enunciado || '').length;

            const alternativas = q.alternativas || {};
            ['A','B','C','D','E'].forEach(letra => {
                document.getElementById('alt_' + letra).value = decodeHTML(alternativas[letra] || '');
                const radio = document.querySelector(`input[name="correta"][value="${letra}"]`);
                radio.checked = q.correta === letra || q.resposta_correta === letra;
            });

            if (q.imagem) {
                previewImg.src = `${UPLOAD_URL}${String(q.imagem).replace(/^uploads\//, '')}`;
                previewImg.style.display = 'block';
                placeholder.style.display = 'none';
            }

            alternarTipo(tipoAtual);
        }

        function alternarTipo(tipo) {
            tipoAtual = tipo;
            document.getElementById('alternativasSection').style.display = tipo === 'objetiva' ? 'block' : 'none';
            document.getElementById('btn_objetiva').classList.toggle('active', tipo === 'objetiva');
            document.getElementById('btn_dissertativa').classList.toggle('active', tipo === 'dissertativa');
        }

        async function enviar(acao) {
            esconderMensagens();

            const fd = new FormData();
            fd.append('id', id);
            fd.append('tipo', tipoAtual);
            fd.append('acao', acao);
            fd.append('titulo', document.getElementById('titulo').value.trim());
            fd.append('genero', document.getElementById('genero').value.trim());
            fd.append('enunciado', document.getElementById('enunciado').value.trim());
            fd.append('explicacao', document.getElementById('explicacao').value.trim());
            fd.append('especificacao', document.getElementById('especificacao').value.trim());
            fd.append('subgenero', document.getElementById('subgenero').value.trim());
            fd.append('imagem_atual', questao?.imagem || '');

            if (tipoAtual === 'objetiva') {
                const correta = document.querySelector('input[name="correta"]:checked');
                if (!correta) {
                    mostrarErro('Selecione a alternativa correta.');
                    return;
                }

                fd.append('correta', correta.value);
                ['A','B','C','D','E'].forEach(letra => {
                    fd.append('alt_' + letra, document.getElementById('alt_' + letra).value.trim());
                });
            }

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

                mostrarSucesso('Questão atualizada com sucesso!');
                setTimeout(() => window.location.href = './?page=home&msg=sucesso', 800);
            } catch (erro) {
                mostrarErro('Erro de conexão: ' + erro.message);
            }
        }

        function abrirModalConfirmacaoExcluir() {
            document.getElementById('modal_confirmacao_editar').classList.add('ativo');
        }

        function fecharModalConfirmacaoEditar() {
            document.getElementById('modal_confirmacao_editar').classList.remove('ativo');
        }

        async function confirmarExcluirEditar() {
            try {
                const res = await fetch(`${API_URL}questoes&acao=deletar`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const data = await res.json();

                if (!data.ok) {
                    mostrarErro(data.erro || 'Erro ao excluir questão.');
                    fecharModalConfirmacaoEditar();
                    return;
                }

                window.location.href = './?page=home&msg=excluida';
            } catch (erro) {
                mostrarErro('Erro de conexão: ' + erro.message);
                fecharModalConfirmacaoEditar();
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

        function decodeHTML(valor) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = String(valor || '');
            return textarea.value;
        }
    </script>
</body>
</html>
