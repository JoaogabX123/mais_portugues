<?php
/**
 * VIEW: Home (Dashboard)
 * Página principal com lista de questões
 */

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
    <title>+Português - Dashboard</title>

    <link rel="stylesheet" href="./css/style.css">

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const API_URL = '<?php echo API_URL; ?>';
        const UPLOAD_URL = '<?php echo UPLOAD_URL; ?>';
    </script>
</head>

<body>

    <header>
        <div class="header-titulo">
            <h1>+Português</h1>
        </div>

        <div class="filtros">
            <input 
                type="text" 
                id="campo_busca" 
                placeholder="Pesquisar questão..."
                oninput="carregarQuestoes()"
            >

            <input 
                type="text" 
                id="filtro_genero" 
                placeholder="Filtrar por gênero"
                oninput="carregarQuestoes()"
            >

            <input 
                type="text" 
                id="filtro_subgenero" 
                placeholder="Filtrar por subgênero"
                oninput="carregarQuestoes()"
            >

            <div class="sugestoes-container" id="sugestoesContainer" style="display:none;">
                <div class="sugestoes-titulo">Sugestões de gênero / subgênero</div>
                <div class="sugestoes-lista" id="sugestoesLista"></div>
            </div>
        </div>

        <div class="usuario-menu">
            <button class="btn-usuario" type="button" onclick="toggleDropdownUsuario()">
                ☰
            </button>

            <div class="dropdown-usuario" id="dropdownUsuario">
                <a href="./?page=configuracoes">⚙️ Configurações</a>
                <a href="./?page=info">ℹ️ Info</a>
                <a href="./?page=termos">📄 Termos de Uso</a>
                <hr>
                <a href="javascript:void(0);" onclick="fazerLogout()" class="logout-option">🚪 Sair</a>
            </div>
        </div>
    </header>

    <main>

        <div class="aviso" id="aviso"></div>

        <div class="lista_de_questoes" id="lista">
            <div class="vazio">
                Carregando...
            </div>
        </div>

    </main>

    <!-- BOTÃO ADICIONAR -->
    <button class="btn-add" onclick="abrirModalTipo()">
        + Adicionar questão
    </button>

    <!-- BOTÃO TOPO -->
    <button 
        id="topo"
        onclick="window.scrollTo({ top:0, behavior:'smooth' })"
    >
        ↑
    </button>

    <!-- MODAL TIPO -->
    <div class="modal-overlay" id="modal_tipo">

        <div class="modal">

            <h2>Qual tipo de questão?</h2>

            <div class="modal-botoes">

                <a href="./?page=criacao_objetiva">
                    Objetiva
                </a>

                <a href="./?page=criacao_dissertativa">
                    Dissertativa
                </a>

            </div>

            <button 
                class="modal-fechar"
                onclick="fecharModalTipo()"
            >
                Cancelar
            </button>

        </div>
    </div>

    <!-- MODAL ENVIO -->
    <div class="modal-overlay" id="modal_envio">

        <div class="modal modal-envio">

            <h2>Enviar Questão</h2>

            <div class="formulario">

                <div class="campo">
                    <label for="destinatario_envio">
                        Email ou nome de usuario do professor:
                    </label>

                    <input 
                        type="text"
                        id="destinatario_envio"
                        placeholder="email@exemplo.com ou nome"
                        autocomplete="off"
                    >
                </div>

                <div class="campo">

                    <label for="descricao_envio">
                        Descrição do envio:
                    </label>

                    <textarea 
                        id="descricao_envio"
                        placeholder="Descreva o motivo do envio..."
                        style="height:80px;"
                    ></textarea>

                </div>

                <div class="campo">

                    <label for="questao_envio">
                        Questão:
                    </label>

                    <textarea 
                        id="questao_envio"
                        disabled
                        style="height:180px;"
                    ></textarea>

                </div>

            </div>

            <div 
                class="modal-botoes"
                style="margin-top:20px; gap:10px;"
            >

                <button 
                    id="btn_enviar_questao"
                    class="btn salvar"
                    onclick="enviarQuestao()"
                >
                    Enviar
                </button>

                <button 
                    class="btn cancelar"
                    onclick="fecharModalEnvio()"
                >
                    Cancelar
                </button>

            </div>

        </div>
    </div>

    <!-- MODAL EXCLUSÃO -->
    <div class="modal-overlay" id="modal_confirmacao_excluir">

        <div class="modal">

            <h2>Confirmar Exclusão</h2>

            <p style="margin-bottom:20px; color:#666;">
                Tem certeza que deseja excluir esta questão?
                Esta ação não pode ser desfeita.
            </p>

            <div class="modal-botoes" style="gap:10px;">

                <button
                    class="btn excluir"
                    onclick="confirmarExcluir()"
                    style="flex:1;"
                >
                    Excluir
                </button>

                <button
                    class="btn cancelar"
                    onclick="fecharModalConfirmacao()"
                    style="flex:1;"
                >
                    Cancelar
                </button>

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

                console.log('🔍 Verificando sessão...');

                const res = await fetch(
                    `${API_URL}usuarios&acao=verificar_sessao`,
                    {
                        credentials: 'include'
                    }
                );

                if (!res.ok) {

                    window.location.href = './?page=login';
                    return;
                }

                const params = new URLSearchParams(window.location.search);

                const msg = params.get('msg');

                const aviso = document.getElementById('aviso');


                if (msg === 'sucesso') {

                    aviso.textContent = '✔ Questão salva com sucesso!';
                    aviso.className = 'aviso sucesso';

                } else if (msg === 'excluida') {

                    aviso.textContent = '🗑 Questão excluída.';
                    aviso.className = 'aviso excluida';
                }

                await carregarQuestoes();
                verificarQuestoesRecebidas();
                setInterval(verificarQuestoesRecebidas, 30000);

                document.addEventListener('click', (e) => {

                    const dropdown =
                        document.getElementById('dropdownUsuario');

                    const btnUsuario =
                        document.querySelector('.btn-usuario');

                    if (
                        !dropdown.contains(e.target) &&
                        !btnUsuario.contains(e.target)
                    ) {
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

                const busca =
                    document.getElementById('campo_busca')
                    .value.trim();

                const genero =
                    document.getElementById('filtro_genero')
                    .value.trim();

                const subgenero =
                    document.getElementById('filtro_subgenero')
                    .value.trim();


                const params = new URLSearchParams();

                params.set('acao', 'listar');

                params.set('busca', busca);

                if (genero) {
                    params.set('genero', genero);
                }

                if (subgenero) {
                    params.set('subgenero', subgenero);
                }

                const url =
                    `${API_URL}questoes&${params.toString()}`;

                const res = await fetch(url, {
                    credentials: 'include'
                });

                if (!res.ok) {

                    window.location.href = './?page=login';
                    return;
                }

                const resposta = await res.json();

                if (!resposta.ok) return;

                const questoes =
                    resposta.dados?.questoes || [];

                questoesCarregadas = questoes;
                renderSugestoes();

                const lista =
                    document.getElementById('lista');


                if (!questoes.length) {

                    lista.innerHTML = `
                        <div class="vazio">
                            Nenhuma questão encontrada.
                            Adicione a primeira!
                        </div>
                    `;

                    return;
                }

                lista.innerHTML = questoes.map(q => `

                    <div class="questao-card">

                        <div 
                            class="questao-info"
                            onclick="window.location='./?page=questao_${q.tipo}&id=${encodeURIComponent(q.id)}'"
                        >

                            <span class="questao-titulo">
                                ${q.titulo || '(sem título)'}
                            </span>

                            <span class="tipo-badge">
                                ${q.tipo}
                            </span>

                            ${
                                q.status === 'rascunho'
                                ? '<span class="badge-rascunho">rascunho</span>'
                                : ''
                            }

                        </div>

                        <div class="genero-tags">

                            <button class="genero-btn">
                                ${q.genero || '-'}
                            </button>

                            ${
                                q.subgenero
                                ? `<span class="subgenero-text">${q.subgenero}</span>`
                                : ''
                            }

                        </div>

                        <div class="questao-acoes">

                            <button
                                class="btn btn-acoes enviar"
                                onclick="abrirModalEnvio(
                                    '${encodeURIComponent(q.id)}'
                                )"
                            >
                                📤 Enviar
                            </button>

                            <button
                                class="btn btn-acoes excluir"
                                onclick="abrirModalConfirmacaoExcluir(
                                    '${encodeURIComponent(q.id)}'
                                )"
                            >
                                🗑️ Excluir
                            </button>

                        </div>

                    </div>

                `).join('');

            } catch (erro) {

                console.error(
                    'Erro ao carregar questões:',
                    erro
                );

            } finally {

                carregandoQuestoes = false;
            }
        }


        async function verificarQuestoesRecebidas() {

            if (verificandoRecebidas) return;
            verificandoRecebidas = true;

            try {

                const res = await fetch(
                    `${API_URL}questoes&acao=recebidas`,
                    {
                        credentials: 'include'
                    }
                );

                if (!res.ok) return;

                const resposta = await res.json();
                const envios = resposta.dados?.envios || [];

                if (!resposta.ok || !envios.length) return;

                mostrarAvisoQuestoesRecebidas(envios);

                await fetch(
                    `${API_URL}questoes&acao=marcar_recebidas_notificadas`,
                    {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: envios.map(envio => envio.id_envio)
                        })
                    }
                );

                carregarQuestoes();

            } catch (erro) {

                console.error('Erro ao verificar questoes recebidas:', erro);
            } finally {

                verificandoRecebidas = false;
            }
        }


        function mostrarAvisoQuestoesRecebidas(envios) {

            const aviso =
                document.getElementById('aviso');

            const primeiro = envios[0];
            const remetente = limparTexto(
                primeiro.remetente_nome ||
                primeiro.remetente_email ||
                'um professor'
            );

            aviso.innerHTML = '';

            if (envios.length === 1) {

                const link = document.createElement('a');
                link.href =
                    `./?page=questao_${encodeURIComponent(primeiro.tipo)}&id=${encodeURIComponent(primeiro.id_questao_recebida)}`;
                link.textContent =
                    limparTexto(primeiro.titulo || 'questao recebida');

                aviso.append(
                    'Voce recebeu a questao ',
                    link,
                    ` de ${remetente}.`
                );

            } else {

                const link = document.createElement('a');
                link.href =
                    `./?page=questao_${encodeURIComponent(primeiro.tipo)}&id=${encodeURIComponent(primeiro.id_questao_recebida)}`;
                link.textContent = 'Abrir a mais recente';

                aviso.append(
                    `Voce recebeu ${envios.length} questoes novas. `,
                    link,
                    '.'
                );
            }

            aviso.className = 'aviso sucesso aviso-recebida';
        }


        function toggleDropdownUsuario() {

            document
                .getElementById('dropdownUsuario')
                .classList.toggle('ativo');
        }


        function abrirModalTipo() {

            document
                .getElementById('modal_tipo')
                .classList.add('ativo');
        }


        function fecharModalTipo() {

            document
                .getElementById('modal_tipo')
                .classList.remove('ativo');
        }


        function abrirModalEnvio(questaoId) {

            const id = decodeURIComponent(questaoId);
            const questao = questoesCarregadas.find(q => String(q.id) === String(id));

            questaoAtualParaEnviar = id;

            document.getElementById('questao_envio').value =
                montarTextoQuestaoEnvio(questao);

            document
                .getElementById('modal_envio')
                .classList.add('ativo');
        }


        function montarTextoQuestaoEnvio(q) {

            if (!q) {
                return 'Questao selecionada.';
            }

            const linhas = [
                `ID: ${q.id}`,
                `Titulo: ${limparTexto(q.titulo || '(sem titulo)')}`,
                `Tipo: ${limparTexto(q.tipo || '')}`,
                `Genero: ${limparTexto(q.genero || '-')}`
            ];

            if (q.subgenero) {
                linhas.push(`Subgenero: ${limparTexto(q.subgenero)}`);
            }

            if (q.especificacao) {
                linhas.push(`Especificacao: ${limparTexto(q.especificacao)}`);
            }

            linhas.push('', 'Enunciado:', limparTexto(q.enunciado || ''));

            if (q.tipo === 'objetiva' && q.alternativas) {
                linhas.push('', 'Alternativas:');
                Object.keys(q.alternativas).sort().forEach(letra => {
                    linhas.push(`${letra}) ${limparTexto(q.alternativas[letra] || '')}`);
                });

                if (q.correta) {
                    linhas.push('', `Resposta correta: ${limparTexto(q.correta)}`);
                }
            }

            if (q.explicacao) {
                linhas.push('', 'Explicacao:', limparTexto(q.explicacao));
            }

            return linhas.join('\n');
        }


        function limparTexto(valor) {

            const textarea = document.createElement('textarea');
            textarea.innerHTML = String(valor);
            return textarea.value.replace(/<[^>]*>/g, '').trim();
        }


        function fecharModalEnvio() {

            document
                .getElementById('modal_envio')
                .classList.remove('ativo');

            document.getElementById('destinatario_envio').value = '';

            document.getElementById('descricao_envio').value = '';

            document.getElementById('questao_envio').value = '';

            questaoAtualParaEnviar = null;
        }


        function renderSugestoes() {
            const busca = document
                .getElementById('campo_busca')
                .value.trim()
                .toLowerCase();

            const container =
                document.getElementById('sugestoesContainer');
            const lista =
                document.getElementById('sugestoesLista');

            lista.innerHTML = '';

            if (!busca || !questoesCarregadas.length) {
                container.style.display = 'none';
                return;
            }

            const itens = {};

            questoesCarregadas.forEach(q => {
                const titulo = (q.titulo || '').toLowerCase();
                const enunciado = (q.enunciado || '').toLowerCase();

                if (titulo.includes(busca) || enunciado.includes(busca)) {
                    const genero = q.genero || 'Sem gênero';
                    const subgenero = q.subgenero || '-';
                    const key = `${genero}|${subgenero}`;

                    if (!itens[key]) {
                        itens[key] = { genero, subgenero };
                    }
                }
            });

            const valores = Object.values(itens).slice(0, 10);

            if (!valores.length) {
                container.style.display = 'none';
                return;
            }

            valores.forEach(item => {
                const botao = document.createElement('button');
                botao.type = 'button';
                botao.className = 'sugestao-item';
                botao.textContent =
                    item.subgenero && item.subgenero !== '-'
                        ? `${item.genero} › ${item.subgenero}`
                        : item.genero;

                botao.addEventListener('click', () => {
                    document.getElementById('filtro_genero').value = item.genero;
                    document.getElementById('filtro_subgenero').value = item.subgenero === '-' ? '' : item.subgenero;
                    carregarQuestoes();
                });

                lista.appendChild(botao);
            });

            container.style.display = 'block';
        }

        async function enviarQuestao() {

            const destinatario =
                document.getElementById('destinatario_envio')
                .value.trim();

            const descricao =
                document.getElementById('descricao_envio')
                .value.trim();


            if (!destinatario) {

                alert('Digite o email ou nome de usuario do professor');
                return;
            }

            if (!descricao) {

                alert('Digite uma descricao');
                return;
            }

            const botao =
                document.getElementById('btn_enviar_questao');

            let destinatarioConfirmado = destinatario;

            try {

                botao.disabled = true;
                botao.textContent = 'Enviando...';

                const res = await fetch(`${API_URL}questoes&acao=enviar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({
                        id: decodeURIComponent(questaoAtualParaEnviar || ''),
                        destinatario,
                        descricao
                    })
                });

                const resposta = await res.json();

                if (!res.ok || !resposta.ok) {
                    const mensagem =
                        resposta.erros?.join('\n') ||
                        resposta.erro ||
                        'Erro ao enviar questao.';

                    alert(mensagem);
                    return;
                }

                destinatarioConfirmado =
                    resposta.dados?.destinatario?.nome ||
                    resposta.dados?.destinatario?.email ||
                    destinatario;

            } catch (erro) {

                console.error('Erro ao enviar questao:', erro);
                alert('Erro ao enviar questao.');
                return;

            } finally {

                botao.disabled = false;
                botao.textContent = 'Enviar';
            }

            const aviso =
                document.getElementById('aviso');

            aviso.textContent =
                'Questao enviada localmente para ' +
                destinatarioConfirmado + '!';

            aviso.className = 'aviso sucesso';

            fecharModalEnvio();

            setTimeout(() => {

                aviso.className = 'aviso';

            }, 4000);

        }


        function abrirModalConfirmacaoExcluir(questaoId) {

            questaoAtualParaExcluir = questaoId;

            document
                .getElementById('modal_confirmacao_excluir')
                .classList.add('ativo');
        }


        function fecharModalConfirmacao() {

            document
                .getElementById('modal_confirmacao_excluir')
                .classList.remove('ativo');

            questaoAtualParaExcluir = null;
        }


        async function confirmarExcluir() {

            if (!questaoAtualParaExcluir) return;

            try {

                const res = await fetch(
                    `${API_URL}questoes&acao=deletar`,
                    {
                        method: 'POST',

                        credentials: 'include',

                        headers: {
                            'Content-Type': 'application/json'
                        },

                        body: JSON.stringify({
                            id: questaoAtualParaExcluir
                        })
                    }
                );

                const resultado = await res.json();


                if (resultado.ok) {

                    const aviso =
                        document.getElementById('aviso');

                    aviso.textContent =
                        '🗑 Questão excluída com sucesso!';

                    aviso.className =
                        'aviso excluida';

                    fecharModalConfirmacao();

                    carregarQuestoes();

                    setTimeout(() => {

                        aviso.className = 'aviso';

                    }, 4000);

                } else {

                    alert(
                        'Erro ao excluir questão: ' +
                        (resultado.mensagem || 'Tente novamente')
                    );
                }

            } catch (erro) {

                console.error(erro);

                alert(
                    'Erro ao excluir questão.'
                );
            }
        }


        async function fazerLogout() {
            try {
                const res = await fetch(
                    `${API_URL}logout`,
                    {
                        method: 'GET',
                        credentials: 'include'
                    }
                );
                
                // Sempre redireciona para login, independente da resposta
                setTimeout(() => {
                    window.location.href = './?page=login';
                }, 100);
                
            } catch (erro) {
                console.error('Erro ao fazer logout:', erro);
                // Redireciona mesmo com erro
                window.location.href = './?page=login';
            }
        }

    </script>

</body>
</html>
