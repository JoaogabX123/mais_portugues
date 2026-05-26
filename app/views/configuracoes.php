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
    <title>Configurações - +Português</title>
    <link rel="stylesheet" href="./css/style.css">
    <script>
        const API_URL = '<?php echo API_URL; ?>';
    </script>
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
        <div class="container doc-narrow">
            <div class="page-header">
                <h1>Configurações</h1>
                <p>Atualize seu perfil, senha e recuperação de acesso.</p>
            </div>

            <div class="aviso" id="aviso_config"></div>

            <section class="form-container">
                <div class="form-header">
                    <h1>Dados do Usuário</h1>
                    <p>Essas informações são usadas no login e identificação dentro do sistema.</p>
                </div>

                <div class="form-row">
                    <div class="campo">
                        <label for="cfg_nome">Nome completo</label>
                        <input type="text" id="cfg_nome" placeholder="Carregando...">
                    </div>
                    <div class="campo">
                        <label for="cfg_email">E-mail</label>
                        <input type="email" id="cfg_email" placeholder="Carregando...">
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-secondary" type="button" onclick="recarregarDados()">Cancelar</button>
                    <button class="btn btn-primary" type="button" id="btn_salvar_perfil" onclick="salvarPerfil()">Salvar Perfil</button>
                </div>
            </section>

            <section class="form-container" style="margin-top:22px;">
                <div class="form-header">
                    <h1>Alterar Senha</h1>
                    <p>Use uma senha com pelo menos 8 caracteres.</p>
                </div>

                <div class="campo">
                    <label for="cfg_senha_atual">Senha atual</label>
                    <input type="password" id="cfg_senha_atual" placeholder="Digite sua senha atual">
                </div>
                <div class="form-row">
                    <div class="campo">
                        <label for="cfg_nova_senha">Nova senha</label>
                        <input type="password" id="cfg_nova_senha" placeholder="Mínimo 8 caracteres">
                    </div>
                    <div class="campo">
                        <label for="cfg_confirmar_senha">Confirmar nova senha</label>
                        <input type="password" id="cfg_confirmar_senha" placeholder="Repita a nova senha">
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-secondary" type="button" onclick="limparCamposSenha()">Cancelar</button>
                    <button class="btn btn-warning" type="button" onclick="alterarSenha()">Alterar Senha</button>
                </div>
            </section>

            <section class="form-container" style="margin-top:22px;">
                <div class="form-header">
                    <h1>Recuperação de Senha</h1>
                    <p>Configure uma pergunta secreta para redefinir o acesso localmente.</p>
                </div>

                <div class="form-row">
                    <div class="campo">
                        <label for="cfg_pergunta">Pergunta de segurança</label>
                        <select id="cfg_pergunta">
                            <option value="">Selecione uma pergunta...</option>
                            <option value="animal">Qual era o nome do seu primeiro animal de estimação?</option>
                            <option value="escola">Qual foi o nome da sua primeira escola?</option>
                            <option value="cidade">Em qual cidade você nasceu?</option>
                            <option value="mae">Qual é o nome de solteira da sua mãe?</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="cfg_resposta_secreta">Resposta</label>
                        <input type="text" id="cfg_resposta_secreta" placeholder="Digite sua resposta...">
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-success" type="button" onclick="salvarRecuperacao()">Salvar Preferência</button>
                </div>
            </section>
        </div>
    </main>

    <script>
        let dadosUsuario = null;

        window.addEventListener('DOMContentLoaded', async () => {
            await recarregarDados();
            await carregarRecuperacao();
        });

        async function recarregarDados() {
            try {
                const res = await fetch(`${API_URL}usuarios&acao=verificar_sessao`, {
                    credentials: 'include'
                });

                if (!res.ok) {
                    window.location.href = './?page=login';
                    return;
                }

                const resposta = await res.json();
                dadosUsuario = resposta.dados?.usuario || resposta.usuario || resposta.dados || resposta;
                document.getElementById('cfg_nome').value = dadosUsuario.nome || '';
                document.getElementById('cfg_email').value = dadosUsuario.email || '';
            } catch (e) {
                mostrarAviso('Erro ao carregar dados do usuário.', 'excluida');
            }
        }

        async function carregarRecuperacao() {
            try {
                const res = await fetch(`${API_URL}usuarios&acao=obter_recuperacao`, {
                    credentials: 'include'
                });
                const data = await res.json();
                const recuperacao = data.dados || {};
                if (recuperacao.pergunta) {
                    document.getElementById('cfg_pergunta').value = recuperacao.pergunta;
                }
            } catch (e) {
                console.warn('Recuperação ainda não configurada.');
            }
        }

        async function salvarPerfil() {
            const nome = document.getElementById('cfg_nome').value.trim();
            const email = document.getElementById('cfg_email').value.trim();

            if (!nome || !email) {
                mostrarAviso('Preencha nome e e-mail antes de salvar.', 'excluida');
                return;
            }

            const btn = document.getElementById('btn_salvar_perfil');
            btn.disabled = true;
            btn.textContent = 'Salvando...';

            try {
                const res = await fetch(`${API_URL}usuarios&acao=atualizar_perfil`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nome, email })
                });
                const data = await res.json();
                mostrarAviso(data.ok ? 'Perfil atualizado com sucesso!' : (data.erro || 'Erro ao salvar perfil.'), data.ok ? 'sucesso' : 'excluida');
            } catch (e) {
                mostrarAviso('Erro de conexão: ' + e.message, 'excluida');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Salvar Perfil';
            }
        }

        async function alterarSenha() {
            const atual = document.getElementById('cfg_senha_atual').value;
            const nova = document.getElementById('cfg_nova_senha').value;
            const confirmar = document.getElementById('cfg_confirmar_senha').value;

            if (!atual || !nova || !confirmar) {
                mostrarAviso('Preencha todos os campos de senha.', 'excluida');
                return;
            }

            if (nova.length < 8) {
                mostrarAviso('A nova senha precisa ter pelo menos 8 caracteres.', 'excluida');
                return;
            }

            if (nova !== confirmar) {
                mostrarAviso('A nova senha e a confirmação não coincidem.', 'excluida');
                return;
            }

            try {
                const res = await fetch(`${API_URL}usuarios&acao=alterar_senha`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ senha_atual: atual, nova_senha: nova })
                });
                const data = await res.json();
                if (data.ok) limparCamposSenha();
                mostrarAviso(data.ok ? 'Senha alterada com sucesso!' : (data.erro || 'Erro ao alterar senha.'), data.ok ? 'sucesso' : 'excluida');
            } catch (e) {
                mostrarAviso('Erro de conexão: ' + e.message, 'excluida');
            }
        }

        function limparCamposSenha() {
            ['cfg_senha_atual', 'cfg_nova_senha', 'cfg_confirmar_senha'].forEach(id => {
                document.getElementById(id).value = '';
            });
        }

        async function salvarRecuperacao() {
            const pergunta = document.getElementById('cfg_pergunta').value;
            const resposta = document.getElementById('cfg_resposta_secreta').value.trim();

            if (!pergunta || !resposta) {
                mostrarAviso('Selecione uma pergunta e informe a resposta.', 'excluida');
                return;
            }

            try {
                const res = await fetch(`${API_URL}usuarios&acao=salvar_recuperacao`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ metodo: 'perguntas', pergunta, resposta })
                });
                const data = await res.json();
                mostrarAviso(data.ok ? 'Preferência de recuperação salva!' : (data.erro || 'Erro ao salvar preferência.'), data.ok ? 'sucesso' : 'excluida');
            } catch (e) {
                mostrarAviso('Erro de conexão: ' + e.message, 'excluida');
            }
        }

        function mostrarAviso(msg, tipo) {
            const el = document.getElementById('aviso_config');
            el.textContent = msg;
            el.className = 'aviso ' + tipo;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>
