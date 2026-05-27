<?php
/**
 * VIEW: Aba Questão Objetiva
 * GET /public/?page=questao_objetiva&id=X
 */

// Sessão já foi iniciada em config.php
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
    <title>+Português - Questão</title>
    <link rel="stylesheet" href="./css/style.css">
    <script>const BASE_URL = '<?php echo BASE_URL; ?>'; const API_URL = '<?php echo API_URL; ?>'; const UPLOAD_URL = '<?php echo UPLOAD_URL; ?>';</script>
</head>
<body>
    <header class="topbar">
        <nav class="topbar-inner">
            <a href="./?page=home" class="logo">+Português</a>
            <div class="nav-actions">
                <a class="btn btn-ghost" href="./?page=home">Voltar ao Dashboard</a>
            </div>
        </nav>
    </header>

    <main class="app-shell">
        <div class="container">
            <div class="questao-aberta" id="conteudo">
                <p style="text-align:center;color:#888;">Carregando...</p>
            </div>
        </div>
    </main>

    <button class="btn-add" onclick="window.location='./?page=criacao_objetiva'">+ Adicionar questão</button>

    <script>
        const id = new URLSearchParams(window.location.search).get('id');
        let questaoAtual = null;

        window.addEventListener('DOMContentLoaded', async () => {
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
                const q = resposta.dados || resposta;
                questaoAtual = q;
                
                if (q.tipo !== 'objetiva') { 
                    window.location.href = `./?page=questao_dissertativa&id=${id}`;
                    return;
                }

                const sub = [q.especificacao, q.subgenero].filter(Boolean).join(' / ');
                const imgTag = q.imagem
                    ? `<img src="${UPLOAD_URL}${q.imagem.replace(/^uploads\//, '')}" alt="Imagem da questão" style="max-width:100%;margin:10px 0;">`
                    : '';

                document.getElementById('conteudo').innerHTML = `
                    <div class="topo-questao">
                        <div class="titulo">${q.titulo || '(sem título)'}</div>
                        <button class="genero-btn">${q.genero || '-'}</button>
                    </div>
                    <div class="subgenero">${sub}</div>
                    <div class="enunciado">${imgTag}${q.enunciado || ''}</div>
                    <div class="alternativas">
                        ${Object.entries(q.alternativas || {}).map(([l, t]) => `
                            <div class="alternativa" onclick="selecionar(this)">
                                <strong>${l})</strong> ${t}
                            </div>
                        `).join('')}
                    </div>
                    <div class="explicacao"><b>Resposta correta: ${q.correta}</b><br><br>${q.explicacao || ''}</div>
                    <div class="botoes">
                        <button class="btn btn-secondary" onclick="window.location='./?page=home'">Voltar</button>
                        <button class="btn btn-warning" onclick="window.location='./?page=editar_questao&id=${encodeURIComponent(q.id)}'">Editar</button>
                        <button class="btn btn-ghost" onclick="copiarQuestao(this)">Copiar</button>
                    </div>
                `;
            } catch (e) {
                console.error('Erro:', e);
                window.location.href = './?page=home';
            }
        });

        function selecionar(el) {
            document.querySelectorAll('.alternativa').forEach(a => a.classList.remove('selecionada'));
            el.classList.add('selecionada');
        }

        async function copiarQuestao(botao) {
            if (!questaoAtual) return;

            const texto = montarTextoCopiar(questaoAtual);
            const rotuloOriginal = botao ? botao.textContent : '';

            try {
                if (botao) {
                    botao.disabled = true;
                    botao.textContent = 'Copiando...';
                }

                const html = await montarHTMLCopiar(questaoAtual);

                if (navigator.clipboard && window.ClipboardItem) {
                    await navigator.clipboard.write([
                        new ClipboardItem({
                            'text/html': new Blob([html], { type: 'text/html' }),
                            'text/plain': new Blob([texto], { type: 'text/plain' })
                        })
                    ]);
                } else if (navigator.clipboard) {
                    await navigator.clipboard.writeText(texto);
                } else {
                    copiarTextoFallback(texto);
                }

                alert('Questao copiada para a area de transferencia!');
            } catch (erro) {
                console.error('Erro ao copiar questao:', erro);
                try {
                    await navigator.clipboard.writeText(texto);
                    alert('Questao copiada sem a imagem. O navegador bloqueou a copia formatada.');
                } catch (erroTexto) {
                    console.error('Erro ao copiar texto:', erroTexto);
                    alert('Nao foi possivel copiar a questao automaticamente.');
                }
            } finally {
                if (botao) {
                    botao.disabled = false;
                    botao.textContent = rotuloOriginal || 'Copiar';
                }
            }
        }

        function montarTextoCopiar(q) {
            const linhas = [
                limparTexto(q.titulo || '(sem titulo)'),
                '',
                `Tipo: ${limparTexto(q.tipo || 'objetiva')}`,
                `Genero: ${limparTexto(q.genero || '-')}`
            ];

            if (q.subgenero) linhas.push(`Subgenero: ${limparTexto(q.subgenero)}`);
            if (q.especificacao) linhas.push(`Especificacao: ${limparTexto(q.especificacao)}`);

            linhas.push('', 'Enunciado:', limparTexto(q.enunciado || ''));

            if (q.alternativas) {
                linhas.push('', 'Alternativas:');
                Object.keys(q.alternativas).sort().forEach(letra => {
                    linhas.push(`${letra}) ${limparTexto(q.alternativas[letra] || '')}`);
                });
            }

            if (q.correta) linhas.push('', `Resposta correta: ${limparTexto(q.correta)}`);
            if (q.explicacao) linhas.push('', 'Explicacao:', limparTexto(q.explicacao));
            if (q.imagem) linhas.push('', 'Imagem: incluida na copia formatada quando suportado pelo navegador.');

            return linhas.join('\n');
        }

        async function montarHTMLCopiar(q) {
            const imagem = await obterImagemComoDataURL(q.imagem);
            const alternativas = Object.keys(q.alternativas || {}).sort().map(letra => `
                <p style="margin:6px 0;"><strong>${escapeHTML(letra)})</strong> ${escapeHTML(limparTexto(q.alternativas[letra] || ''))}</p>
            `).join('');

            return `
                <article style="font-family:Arial,sans-serif;color:#222;line-height:1.5;">
                    <h1 style="font-size:20px;margin:0 0 12px;">${escapeHTML(limparTexto(q.titulo || '(sem titulo)'))}</h1>
                    <p style="margin:0 0 4px;"><strong>Tipo:</strong> Objetiva</p>
                    <p style="margin:0 0 4px;"><strong>Genero:</strong> ${escapeHTML(limparTexto(q.genero || '-'))}</p>
                    ${q.subgenero ? `<p style="margin:0 0 4px;"><strong>Subgenero:</strong> ${escapeHTML(limparTexto(q.subgenero))}</p>` : ''}
                    ${q.especificacao ? `<p style="margin:0 0 12px;"><strong>Especificacao:</strong> ${escapeHTML(limparTexto(q.especificacao))}</p>` : ''}
                    ${imagem ? `<p style="margin:14px 0;"><img src="${imagem}" alt="Imagem da questao" style="max-width:620px;width:auto;height:auto;"></p>` : ''}
                    <h2 style="font-size:16px;margin:16px 0 6px;">Enunciado</h2>
                    <div style="margin:0 0 12px;">${limparHTMLBasico(q.enunciado || '')}</div>
                    <h2 style="font-size:16px;margin:16px 0 6px;">Alternativas</h2>
                    ${alternativas}
                    ${q.correta ? `<p style="margin:16px 0 6px;"><strong>Resposta correta:</strong> ${escapeHTML(limparTexto(q.correta))}</p>` : ''}
                    ${q.explicacao ? `<h2 style="font-size:16px;margin:16px 0 6px;">Explicacao</h2><div>${limparHTMLBasico(q.explicacao)}</div>` : ''}
                </article>
            `;
        }

        async function obterImagemComoDataURL(caminhoImagem) {
            if (!caminhoImagem) return '';

            try {
                const url = `${UPLOAD_URL}${String(caminhoImagem).replace(/^uploads\//, '')}`;
                const resposta = await fetch(url, { credentials: 'include' });
                if (!resposta.ok) return '';

                const blob = await resposta.blob();
                return await new Promise((resolve, reject) => {
                    const leitor = new FileReader();
                    leitor.onload = () => resolve(leitor.result);
                    leitor.onerror = reject;
                    leitor.readAsDataURL(blob);
                });
            } catch (erro) {
                console.warn('Imagem nao incluida na copia:', erro);
                return '';
            }
        }

        function limparHTMLBasico(valor) {
            return escapeHTML(limparTexto(valor)).replace(/\n/g, '<br>');
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

        function copiarTextoFallback(texto) {
            const area = document.createElement('textarea');
            area.value = texto;
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.focus();
            area.select();
            document.execCommand('copy');
            area.remove();
        }

        function copiar() {
            navigator.clipboard.writeText(document.getElementById('conteudo').innerText)
                .then(() => alert('Questão copiada!'));
        }
    </script>
</body>
</html>
