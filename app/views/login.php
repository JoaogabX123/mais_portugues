<?php
/**
 * VIEW: Login
 * Pagina de autenticacao de usuarios
 * GET /app/views/login.php
 */

if (isset($_SESSION['usuario_id'])) {
    header('Location: ./?page=home');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>+Português - Login</title>
    <link rel="stylesheet" href="./css/style.css">
    <script>const BASE_URL = '<?php echo BASE_URL; ?>'; const API_URL = '<?php echo API_URL; ?>'; const UPLOAD_URL = '<?php echo UPLOAD_URL; ?>';</script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>Login</h1>
            <div class="erro" id="msg_erro"></div>

            <form id="form_login" onsubmit="fazerLogin(event)">
                <div class="form-group">
                    <input type="email" id="email" placeholder="E-mail" required>
                </div>

                <div class="form-group">
                    <input type="password" id="senha" placeholder="Senha" required>
                </div>

                <div class="linha-lembrar">
                    <label>
                        <input type="checkbox" id="lembrar"> Lembrar de mim
                    </label>
                    <a href="?page=recuperar_senha">Esqueci minha senha</a>
                </div>

                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>

            <div class="divisor">ou</div>

            <p class="signup-link">Não tem uma conta? <a href="?page=signup">Cadastre-se aqui</a></p>
        </div>
    </div>

    <script>
        async function fazerLogin(event) {
            event.preventDefault();

            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value.trim();
            const lembrar = document.getElementById('lembrar').checked;

            try {
                const res = await fetch(`${API_URL}login`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, senha, lembrar })
                });

                const data = await res.json();

                if (data.ok) {
                    window.location.href = './?page=home';
                    return;
                }

                mostrarErro(data.erro || 'Erro ao fazer login');
            } catch (erro) {
                mostrarErro('Erro na resposta do servidor: ' + erro.message);
            }
        }

        function mostrarErro(mensagem) {
            const el = document.getElementById('msg_erro');
            el.textContent = mensagem;
            el.style.display = 'block';
        }
    </script>
</body>
</html>
