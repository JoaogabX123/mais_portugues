<?php
/**
 * VIEW: Recuperar senha
 * GET /app/views/recuperar_senha.php
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>+Portugues - Recuperar senha</title>
    <link rel="stylesheet" href="./css/style.css">
    <script>
        const API_URL = '<?php echo API_URL; ?>';
    </script>
</head>
<body>
    <div class="login-container">
        <div class="login-card recuperar-card">
            <h1>Recuperar senha</h1>
            <div class="erro" id="msg_erro"></div>
            <div class="sucesso-login" id="msg_sucesso"></div>

            <form id="form_email" onsubmit="consultarConta(event)">
                <div class="form-group">
                    <input type="email" id="email" placeholder="E-mail cadastrado" required>
                </div>
                <button type="submit" class="btn btn-primary">Continuar</button>
            </form>

            <form id="form_pergunta" onsubmit="validarPergunta(event)" style="display:none;">
                <p class="texto-apoio" id="texto_pergunta"></p>
                <div class="form-group">
                    <input type="text" id="resposta" placeholder="Resposta de seguranca" required>
                </div>
                <button type="submit" class="btn btn-primary">Validar resposta</button>
            </form>

            <form id="form_nova_senha" onsubmit="redefinirSenha(event)" style="display:none;">
                <div class="form-group">
                    <input type="password" id="nova_senha" placeholder="Nova senha" required>
                </div>
                <div class="form-group">
                    <input type="password" id="confirmar_senha" placeholder="Confirmar nova senha" required>
                </div>
                <button type="submit" class="btn btn-primary">Salvar nova senha</button>
            </form>

            <p class="login-link"><a href="?page=login">Voltar ao login</a></p>
        </div>
    </div>

    <script>
        let emailAtual = '';
        let perguntaAtual = '';
        let respostaConfirmada = '';

        const perguntas = {
            animal: 'Qual era o nome do seu primeiro animal de estimacao?',
            escola: 'Qual foi o nome da sua primeira escola?',
            cidade: 'Em qual cidade voce nasceu?',
            mae: 'Qual e o nome de solteira da sua mae?'
        };

        async function consultarConta(event) {
            event.preventDefault();
            limparMensagens();
            emailAtual = document.getElementById('email').value.trim();

            try {
                const res = await fetch(`${API_URL}recuperacao&acao=consultar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: emailAtual })
                });
                const data = await res.json();

                if (!data.ok) {
                    mostrarErro(data.erro || 'Nao foi possivel localizar a conta.');
                    return;
                }

                perguntaAtual = data.dados?.pergunta || '';
                const podePerguntar = data.dados?.pergunta_configurada;
                if (!podePerguntar) {
                    mostrarErro('Esta conta ainda nao possui pergunta de seguranca configurada.');
                    return;
                }

                document.getElementById('form_email').style.display = 'none';
                mostrarPergunta();
            } catch (e) {
                mostrarErro('Erro de conexao: ' + e.message);
            }
        }

        function mostrarPergunta() {
            limparMensagens();
            document.getElementById('form_pergunta').style.display = 'block';
            document.getElementById('texto_pergunta').textContent = perguntas[perguntaAtual] || 'Responda sua pergunta de seguranca.';
        }

        async function validarPergunta(event) {
            event.preventDefault();
            limparMensagens();

            const resposta = document.getElementById('resposta').value.trim();
            try {
                const res = await fetch(`${API_URL}recuperacao&acao=validar_pergunta`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: emailAtual, resposta })
                });
                const data = await res.json();

                if (!data.ok) {
                    mostrarErro(data.erro || 'Resposta incorreta.');
                    return;
                }

                respostaConfirmada = resposta;
                document.getElementById('form_pergunta').style.display = 'none';
                document.getElementById('form_nova_senha').style.display = 'block';
                mostrarSucesso('Resposta confirmada. Cadastre uma nova senha.');
            } catch (e) {
                mostrarErro('Erro de conexao: ' + e.message);
            }
        }

        async function redefinirSenha(event) {
            event.preventDefault();
            limparMensagens();

            const nova = document.getElementById('nova_senha').value;
            const confirmar = document.getElementById('confirmar_senha').value;

            if (nova !== confirmar) {
                mostrarErro('A nova senha e a confirmacao nao coincidem.');
                return;
            }

            try {
                const res = await fetch(`${API_URL}recuperacao&acao=redefinir`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: emailAtual,
                        resposta: respostaConfirmada,
                        nova_senha: nova
                    })
                });
                const data = await res.json();

                if (!data.ok) {
                    mostrarErro(data.erro || 'Nao foi possivel redefinir a senha.');
                    return;
                }

                document.getElementById('form_nova_senha').style.display = 'none';
                mostrarSucesso('Senha redefinida com sucesso. Voce ja pode entrar.');
            } catch (e) {
                mostrarErro('Erro de conexao: ' + e.message);
            }
        }

        function limparMensagens() {
            document.getElementById('msg_erro').style.display = 'none';
            document.getElementById('msg_sucesso').style.display = 'none';
        }

        function mostrarErro(mensagem) {
            const el = document.getElementById('msg_erro');
            el.textContent = mensagem;
            el.style.display = 'block';
        }

        function mostrarSucesso(mensagem) {
            const el = document.getElementById('msg_sucesso');
            el.textContent = mensagem;
            el.style.display = 'block';
        }
    </script>
</body>
</html>
