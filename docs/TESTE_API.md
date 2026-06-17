# Teste Manual da API

Este roteiro usa `curl.exe` para validar o fluxo principal da API do +Português: cadastro, login, sessão, recuperação de senha, questões, envio local e logout.

Base local:

```text
http://localhost/mais_portugues/public/api.php?rota=
```

Os exemplos usam `cookies.txt` para manter a sessão entre requisições. No Windows, prefira chamar `curl.exe` para evitar o alias do PowerShell. As quebras com `^` funcionam no `cmd.exe`; no PowerShell, rode em uma única linha ou troque `^` por crase.

## 1. Criar Usuário

```bash
curl.exe -c cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=usuarios&acao=criar" ^
  -H "Content-Type: application/json" ^
  -d "{\"nome\":\"Usuário Teste\",\"email\":\"teste@teste.com\",\"senha\":\"Teste@123\"}"
```

Resposta esperada:

```json
{
  "ok": true,
  "mensagem": "Cadastro realizado com sucesso"
}
```

Se o usuário já existir, use outro email ou faça login.

## 2. Login

```bash
curl.exe -c cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=login" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"teste@teste.com\",\"senha\":\"Teste@123\",\"lembrar\":true}"
```

Resposta esperada:

```json
{
  "ok": true,
  "mensagem": "Login realizado com sucesso"
}
```

## 3. Verificar Sessão

```bash
curl.exe -b cookies.txt "http://localhost/mais_portugues/public/api.php?rota=usuarios&acao=verificar_sessao"
```

Resposta esperada:

```json
{
  "ok": true,
  "mensagem": "Usuario autenticado",
  "dados": {
    "usuario_id": 1,
    "usuario_email": "teste@teste.com",
    "tempo_sessao": 10
  }
}
```

## 4. Configurar Recuperação

Esta chamada exige usuário logado.

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=usuarios&acao=salvar_recuperacao" ^
  -H "Content-Type: application/json" ^
  -d "{\"pergunta\":\"primeira_escola\",\"resposta\":\"Escola Municipal\"}"
```

Conferir configuração:

```bash
curl.exe -b cookies.txt "http://localhost/mais_portugues/public/api.php?rota=usuarios&acao=obter_recuperacao"
```

## 5. Testar Recuperação Pública

Consultar a conta:

```bash
curl.exe -X POST "http://localhost/mais_portugues/public/api.php?rota=recuperacao&acao=consultar" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"teste@teste.com\"}"
```

Validar resposta:

```bash
curl.exe -X POST "http://localhost/mais_portugues/public/api.php?rota=recuperacao&acao=validar_pergunta" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"teste@teste.com\",\"resposta\":\"Escola Municipal\"}"
```

Redefinir senha:

```bash
curl.exe -X POST "http://localhost/mais_portugues/public/api.php?rota=recuperacao&acao=redefinir" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"teste@teste.com\",\"resposta\":\"Escola Municipal\",\"nova_senha\":\"NovaSenha1\"}"
```

Depois desse teste, a senha passa a ser `NovaSenha1`.

## 6. Criar Questão Objetiva

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=salvar" ^
  -F "tipo=objetiva" ^
  -F "acao=salvar" ^
  -F "titulo=O que é semântica?" ^
  -F "genero=descritivo" ^
  -F "enunciado=Semântica é o estudo do significado das palavras. Qual alternativa melhor define?" ^
  -F "explicacao=Semântica estuda o significado dos signos linguísticos." ^
  -F "especificacao=Conceitos linguísticos" ^
  -F "subgenero=Definição" ^
  -F "correta=A" ^
  -F "alt_A=Estudo do significado das palavras" ^
  -F "alt_B=Estudo da pronúncia" ^
  -F "alt_C=Estudo da gramática" ^
  -F "alt_D=Estudo da ortografia" ^
  -F "alt_E=Estudo da sintaxe"
```

Resposta esperada:

```json
{
  "ok": true,
  "mensagem": "Questão criada com sucesso",
  "dados": {
    "id": 1
  }
}
```

## 7. Criar Questão Dissertativa

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=salvar" ^
  -F "tipo=dissertativa" ^
  -F "acao=salvar" ^
  -F "titulo=Análise de texto" ^
  -F "genero=argumentativo" ^
  -F "enunciado=Leia o texto e produza uma análise crítica." ^
  -F "explicacao=A resposta deve considerar tese, argumentos e conclusão." ^
  -F "especificacao=Análise textual" ^
  -F "subgenero=Crítica"
```

## 8. Listar e Buscar Questões

Listar tudo do usuário logado:

```bash
curl.exe -b cookies.txt "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=listar"
```

Listar com filtros:

```bash
curl.exe -b cookies.txt "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=listar&tipo=objetiva&status=rascunho&busca=semântica"
```

Buscar por ID:

```bash
curl.exe -b cookies.txt "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=buscar&id=1"
```

## 9. Atualizar Questão

Envie o `id` da questão junto com os demais campos. Usar `acao=postar` muda o status para publicada.

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=salvar" ^
  -F "id=1" ^
  -F "tipo=objetiva" ^
  -F "acao=postar" ^
  -F "titulo=O que é semântica? (atualizada)" ^
  -F "genero=descritivo" ^
  -F "enunciado=Qual alternativa melhor define semântica?" ^
  -F "explicacao=Semântica estuda significados." ^
  -F "correta=A" ^
  -F "alt_A=Estudo do significado das palavras" ^
  -F "alt_B=Estudo da pronúncia" ^
  -F "alt_C=Estudo da gramática" ^
  -F "alt_D=Estudo da ortografia" ^
  -F "alt_E=Estudo da sintaxe"
```

## 10. Enviar Questão Para Outro Professor

O destinatário precisa estar cadastrado. Use email para evitar ambiguidade.

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=enviar" ^
  -H "Content-Type: application/json" ^
  -d "{\"id\":1,\"destinatario\":\"outro.professor@email.com\",\"descricao\":\"Questão para usar na revisão.\"}"
```

Consultar recebidas ainda não notificadas:

```bash
curl.exe -b cookies.txt "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=recebidas"
```

Marcar avisos como exibidos:

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=marcar_recebidas_notificadas" ^
  -H "Content-Type: application/json" ^
  -d "{\"ids\":[1]}"
```

## 11. Atualizar Perfil e Senha

Atualizar perfil:

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=usuarios&acao=atualizar_perfil" ^
  -H "Content-Type: application/json" ^
  -d "{\"nome\":\"Usuário Atualizado\",\"email\":\"teste@teste.com\"}"
```

Alterar senha:

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=usuarios&acao=alterar_senha" ^
  -H "Content-Type: application/json" ^
  -d "{\"senha_atual\":\"NovaSenha1\",\"nova_senha\":\"Teste@123\"}"
```

## 12. Deletar Questão

```bash
curl.exe -b cookies.txt -X POST "http://localhost/mais_portugues/public/api.php?rota=questoes&acao=deletar" ^
  -H "Content-Type: application/json" ^
  -d "{\"id\":1}"
```

## 13. Logout

```bash
curl.exe -b cookies.txt "http://localhost/mais_portugues/public/api.php?rota=logout"
```

## Notas

- Sempre use `-b cookies.txt` depois do login.
- No JavaScript, use `credentials: 'include'`.
- Buscar, editar, deletar e enviar só funcionam para questões do usuário logado.
- Uma resposta `401` normalmente significa sessão ausente, sessão expirada ou resposta de recuperação incorreta.
- Uma resposta `422` indica dados válidos no formato, mas impossíveis para a regra de negócio, como enviar questão para si mesmo.

Atualizado em 17/06/2026.
