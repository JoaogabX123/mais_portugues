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
    <title>Dashboard - +Português</title>
    <link rel="stylesheet" href="./css/style.css">
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const API_URL = '<?php echo API_URL; ?>';
        const UPLOAD_URL = '<?php echo UPLOAD_URL; ?>';
    </script>
</head>
<body>
    <header class="topbar">
        <nav class="topbar-inner">
            <a href="./?page=home" class="logo">+Português</a>

            <div class="nav-actions">
                <button class="btn btn-success" type="button" onclick="abrirModalTipo()">+ Nova Questão</button>

                <div class="user-menu">
                    <button class="user-btn" type="button" onclick="toggleDropdownUsuario()">
                        👨‍🏫 <span id="nomeUsuario">Professor</span>
                    </button>

                    <div class="dropdown-usuario" id="dropdownUsuario">
                        <a href="./?page=configuracoes">Configurações</a>
                        <a href="./?page=info">Info</a>
                        <a href="./?page=termos">Termos de Uso</a>
                        <hr>
                        <a href="javascript:void(0);" onclick="fazerLogout()" class="logout-option">Sair</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="app-shell">
        <div class="container">
            <div class="page-header">
                <h1>Bem-vindo de volta!</h1>
                <p>Gerencie e organize sua biblioteca de questões.</p>
            </div>

            <div class="aviso" id="aviso"></div>

            <section class="stats-grid" aria-label="Resumo das questões">
                <article class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-number" id="statTotal">0</div>
                    <div class="stat-label">Total de questões</div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number" id="statObjetivas">0</div>
                    <div class="stat-label">Objetivas</div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon">✍️</div>
                    <div class="stat-number" id="statDissertativas">0</div>
                    <div class="stat-label">Dissertativas</div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number" id="statPublicadas">0</div>
                    <div class="stat-label">Publicadas</div>
                </article>
            </section>

            <section class="toolbar" aria-label="Filtros">
                <input type="text" id="campo_busca" placeholder="Pesquisar questão..." oninput="carregarQuestoes()">
                <select id="filtro_tipo" onchange="carregarQuestoes()">
                    <option value="">Todos os tipos</option>
                    <option value="objetiva">Objetivas</option>
                    <option value="dissertativa">Dissertativas</option>
                </select>
                <input type="text" id="filtro_genero" placeholder="Filtrar por gênero" oninput="carregarQuestoes()">
                <input type="text" id="filtro_subgenero" placeholder="Filtrar por subgênero" oninput="carregarQuestoes()">
            </section>

            <section>
                <div class="section-title">
                    <span>📂 Categorias</span>
                    <button class="btn btn-ghost" type="button" onclick="abrirModalTipo()">Criar questão</button>
                </div>
                <div class="groups-grid" id="categoriesGrid">
                    <div class="loading-state">Carregando categorias...</div>
                </div>
            </section>

            <section>
                <div class="section-title">
                    <span>Questões</span>
                    <span class="badge" id="contadorLista">0 itens</span>
                </div>
                <div class="question-list" id="lista">
                    <div class="vazio">Carregando...</div>
                </div>
            </section>
        </div>
    </main>

    <button class="btn-add" type="button" onclick="abrirModalTipo()">+ Adicionar questão</button>
    <button id="topo" type="button" onclick="window.scrollTo({ top:0, behavior:'smooth' })">↑</button>

    <div class="modal-overlay" id="modal_tipo">
        <div class="modal">
            <h2>Qual tipo de questão?</h2>
            <div class="modal-botoes modal-botoes-tipo">
                <a class="btn btn-primary" href="./?page=criacao_objetiva">Objetiva</a>
                <a class="btn btn-warning" href="./?page=criacao_dissertativa">Dissertativa</a>
            </div>
            <div class="modal-botoes modal-botoes-cancelar">
                <button class="btn btn-secondary" type="button" onclick="fecharModalTipo()">Cancelar</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal_envio">
        <div class="modal modal-envio">
            <h2>Enviar questão</h2>
            <div class="campo">
                <label for="destinatario_envio">Email ou nome do professor</label>
                <input type="text" id="destinatario_envio" placeholder="email@exemplo.com ou nome">
            </div>
            <div class="campo">
                <label for="descricao_envio">Descrição do envio</label>
                <textarea id="descricao_envio" placeholder="Descreva o motivo do envio..."></textarea>
            </div>
            <div class="campo">
                <label for="questao_envio">Questão</label>
                <textarea id="questao_envio" disabled></textarea>
            </div>
            <div class="modal-botoes">
                <button id="btn_enviar_questao" class="btn btn-success" type="button" onclick="enviarQuestao()">Enviar</button>
                <button class="btn btn-secondary" type="button" onclick="fecharModalEnvio()">Cancelar</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal_confirmacao_excluir">
        <div class="modal">
            <h2>Confirmar exclusão</h2>
            <p>Tem certeza que deseja excluir esta questão? Esta ação não pode ser desfeita.</p>
            <div class="modal-botoes">
                <button class="btn btn-danger" type="button" onclick="confirmarExcluir()">Excluir</button>
                <button class="btn btn-secondary" type="button" onclick="fecharModalConfirmacao()">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let carregandoQuestoes = false;
        let questaoAtualParaExcluir = null;
        let questaoAtualParaEnviar = null;
        let questoesCarregadas = [];
        let verificandoRecebidas = false;

        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const sessao = await fetch(`${API_URL}usuarios&acao=verificar_sessao`, { credentials: 'include' });
                if (!sessao.ok) {
                    window.location.href = './?page=login';
                    return;
                }

                const dadosSessao = await sessao.json();
                const nome = dadosSessao.dados?.usuario?.nome || dadosSessao.dados?.usuario_email || 'Professor';
                document.getElementById('nomeUsuario').textContent = String(nome).split(' ')[0];

                const params = new URLSearchParams(window.location.search);
                const msg = params.get('msg');
                const aviso = document.getElementById('aviso');
                if (msg === 'sucesso') {
                    mostrarAviso('Questão salva com sucesso!', 'sucesso');
                } else if (msg === 'excluida') {
                    mostrarAviso('Questão excluída.', 'excluida');
                }

                await carregarQuestoes();
                verificarQuestoesRecebidas();
                setInterval(verificarQuestoesRecebidas, 30000);

                document.addEventListener('click', (e) => {
                    const dropdown = document.getElementById('dropdownUsuario');
                    const btnUsuario = document.querySelector('.user-btn');
                    if (!dropdown.contains(e.target) && !btnUsuario.contains(e.target)) {
                        dropdown.classList.remove('ativo');
                    }
                });
            } catch (erro) {
                console.error(erro);
                window.location.href = './?page=login';
            }
        });

        async function carregarQuestoes() {
            if (carregandoQuestoes) return;
            carregandoQuestoes = true;

            try {
                const params = new URLSearchParams();
                params.set('acao', 'listar');

                const busca = document.getElementById('campo_busca').value.trim();
                const tipo = document.getElementById('filtro_tipo').value.trim();
                const genero = document.getElementById('filtro_genero').value.trim();
                const subgenero = document.getElementById('filtro_subgenero').value.trim();

                if (busca) params.set('busca', busca);
                if (tipo) params.set('tipo', tipo);
                if (genero) params.set('genero', genero);
                if (subgenero) params.set('subgenero', subgenero);

                const res = await fetch(`${API_URL}questoes&${params.toString()}`, { credentials: 'include' });
                if (!res.ok) {
                    window.location.href = './?page=login';
                    return;
                }

                const resposta = await res.json();
                if (!resposta.ok) return;

                questoesCarregadas = resposta.dados?.questoes || [];
                renderStats(questoesCarregadas);
                renderCategorias(questoesCarregadas);
                renderLista(questoesCarregadas);
            } catch (erro) {
                console.error('Erro ao carregar questões:', erro);
            } finally {
                carregandoQuestoes = false;
            }
        }

        function renderStats(questoes) {
            document.getElementById('statTotal').textContent = questoes.length;
            document.getElementById('statObjetivas').textContent = questoes.filter(q => q.tipo === 'objetiva').length;
            document.getElementById('statDissertativas').textContent = questoes.filter(q => q.tipo === 'dissertativa').length;
            document.getElementById('statPublicadas').textContent = questoes.filter(q => q.status === 'publicada').length;
        }

        function renderCategorias(questoes) {
            const grid = document.getElementById('categoriesGrid');
            const categorias = {};

            questoes.forEach(q => {
                const nome = q.genero || 'Sem gênero';
                if (!categorias[nome]) {
                    categorias[nome] = { total: 0, objetivas: 0, dissertativas: 0 };
                }
                categorias[nome].total++;
                if (q.tipo === 'objetiva') categorias[nome].objetivas++;
                if (q.tipo === 'dissertativa') categorias[nome].dissertativas++;
            });

            const itens = Object.entries(categorias);
            if (!itens.length) {
                grid.innerHTML = '<div class="vazio">Nenhuma categoria encontrada ainda.</div>';
                return;
            }

            grid.innerHTML = itens.map(([nome, stats], index) => `
                <article class="group-card alt-${(index % 3) + 1}">
                    <div class="group-icon">📂</div>
                    <h3 class="group-title">${escapeHTML(nome)}</h3>
                    <div class="group-stats">
                        <div class="stat-item"><strong>${stats.total}</strong><span>Total</span></div>
                        <div class="stat-item"><strong>${stats.objetivas}</strong><span>Objetivas</span></div>
                        <div class="stat-item"><strong>${stats.dissertativas}</strong><span>Dissert.</span></div>
                    </div>
                    <button class="btn btn-ghost" type="button" onclick="filtrarGenero('${encodeURIComponent(nome)}')">Visualizar questões</button>
                </article>
            `).join('');
        }

        function renderLista(questoes) {
            const lista = document.getElementById('lista');
            document.getElementById('contadorLista').textContent = `${questoes.length} ${questoes.length === 1 ? 'item' : 'itens'}`;

            if (!questoes.length) {
                lista.innerHTML = '<div class="vazio">Nenhuma questão encontrada. Adicione a primeira!</div>';
                return;
            }

            lista.innerHTML = questoes.map(q => `
                <article class="questao-card">
                    <div class="questao-info" onclick="window.location='./?page=questao_${encodeURIComponent(q.tipo)}&id=${encodeURIComponent(q.id)}'">
                        <span class="questao-titulo">${escapeHTML(q.titulo || '(sem título)')}</span>
                        <div class="meta-row">
                            <span class="tipo-badge">${escapeHTML(q.tipo)}</span>
                            ${q.status === 'rascunho' ? '<span class="badge-rascunho">rascunho</span>' : '<span class="badge">publicada</span>'}
                            <span class="genero-btn">${escapeHTML(q.genero || '-')}</span>
                            ${q.subgenero ? `<span class="subgenero-text">${escapeHTML(q.subgenero)}</span>` : ''}
                        </div>
                    </div>
                    <div class="questao-acoes">
                        <a class="btn btn-warning btn-acoes" href="./?page=editar_questao&id=${encodeURIComponent(q.id)}">Editar</a>
                        <button class="btn btn-acoes" type="button" onclick="abrirModalEnvio('${encodeURIComponent(q.id)}')">Enviar</button>
                        <button class="btn btn-danger btn-acoes" type="button" onclick="abrirModalConfirmacaoExcluir('${encodeURIComponent(q.id)}')">Excluir</button>
                    </div>
                </article>
            `).join('');
        }

        function filtrarGenero(nomeEncoded) {
            document.getElementById('filtro_genero').value = decodeURIComponent(nomeEncoded);
            carregarQuestoes();
            document.getElementById('lista').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function toggleDropdownUsuario() {
            document.getElementById('dropdownUsuario').classList.toggle('ativo');
        }

        function abrirModalTipo() {
            document.getElementById('modal_tipo').classList.add('ativo');
        }

        function fecharModalTipo() {
            document.getElementById('modal_tipo').classList.remove('ativo');
        }

        function abrirModalEnvio(questaoId) {
            const id = decodeURIComponent(questaoId);
            const questao = questoesCarregadas.find(q => String(q.id) === String(id));
            questaoAtualParaEnviar = id;
            document.getElementById('questao_envio').value = montarTextoQuestaoEnvio(questao);
            document.getElementById('modal_envio').classList.add('ativo');
        }

        function fecharModalEnvio() {
            document.getElementById('modal_envio').classList.remove('ativo');
            document.getElementById('destinatario_envio').value = '';
            document.getElementById('descricao_envio').value = '';
            document.getElementById('questao_envio').value = '';
            questaoAtualParaEnviar = null;
        }

        function abrirModalConfirmacaoExcluir(questaoId) {
            questaoAtualParaExcluir = decodeURIComponent(questaoId);
            document.getElementById('modal_confirmacao_excluir').classList.add('ativo');
        }

        function fecharModalConfirmacao() {
            document.getElementById('modal_confirmacao_excluir').classList.remove('ativo');
            questaoAtualParaExcluir = null;
        }

        async function confirmarExcluir() {
            if (!questaoAtualParaExcluir) return;

            try {
                const res = await fetch(`${API_URL}questoes&acao=deletar`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: questaoAtualParaExcluir })
                });

                const resultado = await res.json();
                if (!resultado.ok) {
                    alert(resultado.erro || resultado.mensagem || 'Erro ao excluir questão.');
                    return;
                }

                fecharModalConfirmacao();
                mostrarAviso('Questão excluída com sucesso!', 'excluida');
                carregarQuestoes();
            } catch (erro) {
                console.error(erro);
                alert('Erro ao excluir questão.');
            }
        }

        async function enviarQuestao() {
            const destinatario = document.getElementById('destinatario_envio').value.trim();
            const descricao = document.getElementById('descricao_envio').value.trim();

            if (!destinatario) return alert('Digite o email ou nome do professor.');
            if (!descricao) return alert('Digite uma descrição.');

            const botao = document.getElementById('btn_enviar_questao');
            let destinatarioConfirmado = destinatario;

            try {
                botao.disabled = true;
                botao.textContent = 'Enviando...';

                const res = await fetch(`${API_URL}questoes&acao=enviar`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: questaoAtualParaEnviar,
                        destinatario,
                        descricao
                    })
                });

                const resposta = await res.json();
                if (!res.ok || !resposta.ok) {
                    alert(resposta.erros?.join('\n') || resposta.erro || 'Erro ao enviar questão.');
                    return;
                }

                destinatarioConfirmado = resposta.dados?.destinatario?.nome || resposta.dados?.destinatario?.email || destinatario;
            } catch (erro) {
                console.error(erro);
                alert('Erro ao enviar questão.');
                return;
            } finally {
                botao.disabled = false;
                botao.textContent = 'Enviar';
            }

            fecharModalEnvio();
            mostrarAviso('Questão enviada localmente para ' + destinatarioConfirmado + '!', 'sucesso');
        }

        async function verificarQuestoesRecebidas() {
            if (verificandoRecebidas) return;
            verificandoRecebidas = true;

            try {
                const res = await fetch(`${API_URL}questoes&acao=recebidas`, { credentials: 'include' });
                if (!res.ok) return;

                const resposta = await res.json();
                const envios = resposta.dados?.envios || [];
                if (!resposta.ok || !envios.length) return;

                const primeiro = envios[0];
                const titulo = escapeHTML(primeiro.titulo || 'questão recebida');
                const remetente = escapeHTML(primeiro.remetente_nome || primeiro.remetente_email || 'um professor');
                const aviso = document.getElementById('aviso');
                aviso.innerHTML = `Você recebeu a questão <a href="./?page=questao_${encodeURIComponent(primeiro.tipo)}&id=${encodeURIComponent(primeiro.id_questao_recebida)}">${titulo}</a> de ${remetente}.`;
                aviso.className = 'aviso sucesso aviso-recebida';

                await fetch(`${API_URL}questoes&acao=marcar_recebidas_notificadas`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: envios.map(envio => envio.id_envio) })
                });

                carregarQuestoes();
            } catch (erro) {
                console.error('Erro ao verificar questões recebidas:', erro);
            } finally {
                verificandoRecebidas = false;
            }
        }

        async function fazerLogout() {
            try {
                await fetch(`${API_URL}logout`, { method: 'GET', credentials: 'include' });
            } finally {
                window.location.href = './?page=login';
            }
        }

        function montarTextoQuestaoEnvio(q) {
            if (!q) return 'Questão selecionada.';

            const linhas = [
                `ID: ${q.id}`,
                `Título: ${limparTexto(q.titulo || '(sem título)')}`,
                `Tipo: ${limparTexto(q.tipo || '')}`,
                `Gênero: ${limparTexto(q.genero || '-')}`
            ];

            if (q.subgenero) linhas.push(`Subgênero: ${limparTexto(q.subgenero)}`);
            if (q.especificacao) linhas.push(`Especificação: ${limparTexto(q.especificacao)}`);
            linhas.push('', 'Enunciado:', limparTexto(q.enunciado || ''));

            if (q.tipo === 'objetiva' && q.alternativas) {
                linhas.push('', 'Alternativas:');
                Object.keys(q.alternativas).sort().forEach(letra => {
                    linhas.push(`${letra}) ${limparTexto(q.alternativas[letra] || '')}`);
                });
                if (q.correta) linhas.push('', `Resposta correta: ${limparTexto(q.correta)}`);
            }

            if (q.explicacao) linhas.push('', 'Explicação:', limparTexto(q.explicacao));
            return linhas.join('\n');
        }

        function mostrarAviso(texto, tipo) {
            const aviso = document.getElementById('aviso');
            aviso.textContent = texto;
            aviso.className = `aviso ${tipo}`;
        }

        function limparTexto(valor) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = String(valor || '');
            return textarea.value.replace(/<[^>]*>/g, '').trim();
        }

        function escapeHTML(valor) {
            return String(valor ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        }
    </script>
</body>
</html>
