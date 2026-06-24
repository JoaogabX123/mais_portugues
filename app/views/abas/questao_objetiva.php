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
                
                if (q.tipo !== 'objetiva') { 
                    window.location.href = `./?page=questao_dissertativa&id=${id}`;
                    return;
                }

                const sub = [q.especificacao, q.subgenero].filter(Boolean).join(' / ');
                
                // Construir URL de imagem corretamente
                let imgTag = '';
                if (q.imagem) {
                    const caminhoImg = q.imagem.startsWith('uploads/') ? q.imagem : 'uploads/' + q.imagem.replace(/^.*\//, '');
                    imgTag = `<img src="${BASE_URL}${caminhoImg}" alt="Imagem da questão" style="max-width:100%;margin:10px 0;border-radius:8px;">`;
                }

                document.getElementById('conteudo').innerHTML = `
                    <div id="conteudo-questao">
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
                    </div>
                    <div class="botoes">
                        <button class="btn btn-secondary" onclick="window.location='./?page=home'">Voltar</button>
                        <button class="btn btn-warning" onclick="window.location='./?page=editar_questao&id=${encodeURIComponent(q.id)}'">Editar</button>
                        <button class="btn btn-primary" onclick="copiar()">📋 Copiar</button>
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

        async function copiar() {
            const elemento = document.getElementById('conteudo-questao');
            const clone = elemento.cloneNode(true);
            
            // Converter imagens para base64
            const imagens = clone.querySelectorAll('img');
            for (let img of imagens) {
                try {
                    const response = await fetch(img.src);
                    if (!response.ok) throw new Error('Falha ao buscar imagem');
                    
                    const blob = await response.blob();
                    const base64 = await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = reject;
                        reader.readAsDataURL(blob);
                    });
                    
                    img.src = base64;
                } catch (e) {
                    console.error('Erro ao converter imagem:', e);
                }
            }
            
            const html = clone.innerHTML;
            
            navigator.clipboard.write([
                new ClipboardItem({
                    'text/html': new Blob([html], { type: 'text/html' }),
                    'text/plain': new Blob([elemento.innerText], { type: 'text/plain' })
                })
            ]).then(() => {
                alert('Questão copiada com imagem para a área de transferência!');
            }).catch((err) => {
                console.error('Erro no clipboard:', err);
                alert('Erro ao copiar questão');
            });
        }
    </script>
</body>
</html>
