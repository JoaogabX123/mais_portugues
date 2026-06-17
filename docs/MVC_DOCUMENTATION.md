# Documentação MVC - +Português

## Visão Geral

O +Português usa uma arquitetura MVC simples em PHP para separar telas, regras de negócio e acesso ao banco. O navegador acessa `public/`, o router escolhe a tela ou a rota da API, os controllers coordenam validações e os models fazem as operações no MySQL.

```text
Navegador
  -> public/index.php ou public/api.php
  -> app/config/config.php
  -> app/routes ou app/views
  -> app/controllers
  -> app/models
  -> MySQL
```

## Estrutura Atual

```text
mais_portugues/
|-- public/
|   |-- index.php
|   |-- api.php
|   |-- css/style.css
|   `-- uploads/
|-- app/
|   |-- config/config.php
|   |-- controllers/
|   |   |-- LoginController.php
|   |   |-- LogoutController.php
|   |   |-- SessaoController.php
|   |   |-- RecuperacaoController.php
|   |   `-- QuestaoController.php
|   |-- models/
|   |   |-- Usuario.php
|   |   |-- Questao.php
|   |   |-- Alternativa.php
|   |   `-- EnvioQuestao.php
|   |-- routes/
|   |   |-- login.php
|   |   |-- logout.php
|   |   |-- usuarios.php
|   |   |-- recuperacao.php
|   |   `-- questoes.php
|   `-- views/
|       |-- login.php
|       |-- signup.php
|       |-- recuperar_senha.php
|       |-- home.php
|       |-- configuracoes.php
|       |-- criacao_objetiva.php
|       |-- criacao_dissertativa.php
|       |-- editar_questao.php
|       `-- abas/
`-- docs/
```

## Fluxo das Telas

As páginas entram por `public/index.php`. O parâmetro `page` indica qual view deve ser carregada.

```text
public/index.php?page=home
public/index.php?page=login
public/index.php?page=signup
public/index.php?page=recuperar_senha
```

Se o usuário não estiver autenticado e tentar acessar uma tela protegida, o router carrega a tela de login. As telas `login`, `signup` e `recuperar_senha` são públicas.

## Fluxo da API

As ações assíncronas entram por `public/api.php`. A rota vem em `rota`, a ação vem em `acao`, e o corpo da requisição leva JSON ou `multipart/form-data` conforme o endpoint.

```text
fetch()
  -> public/api.php?rota=questoes&acao=listar
  -> app/routes/questoes.php
  -> QuestaoController::listar()
  -> Questao::listar()
  -> MySQL
```

O frontend deve chamar `public/api.php`, não `app/routes/*.php` diretamente.

## Constantes Importantes

Definidas em `app/config/config.php`:

- `BASE_URL`: caminho público detectado automaticamente.
- `API_URL`: `BASE_URL . 'api.php?rota='`.
- `UPLOAD_URL`: `BASE_URL . 'uploads/'`.
- `APP_PATH`: caminho da pasta `app`.
- `PUBLIC_PATH`: caminho da pasta `public`.
- `UPLOADS_PATH`: caminho físico para uploads.

## Endpoints

Base local:

```text
http://localhost/mais_portugues/public/api.php?rota=
```

### Login

```http
POST /mais_portugues/public/api.php?rota=login
Content-Type: application/json
```

```json
{
  "email": "teste@teste.com",
  "senha": "Teste@123",
  "lembrar": true
}
```

### Logout

```http
GET /mais_portugues/public/api.php?rota=logout
```

### Usuários

```http
GET  /mais_portugues/public/api.php?rota=usuarios&acao=verificar_sessao
POST /mais_portugues/public/api.php?rota=usuarios&acao=criar
POST /mais_portugues/public/api.php?rota=usuarios&acao=atualizar_perfil
POST /mais_portugues/public/api.php?rota=usuarios&acao=alterar_senha
GET  /mais_portugues/public/api.php?rota=usuarios&acao=obter_recuperacao
POST /mais_portugues/public/api.php?rota=usuarios&acao=salvar_recuperacao
```

Exemplo para configurar recuperação:

```json
{
  "pergunta": "primeira_escola",
  "resposta": "Escola Municipal"
}
```

### Recuperação de Senha

```http
POST /mais_portugues/public/api.php?rota=recuperacao&acao=consultar
POST /mais_portugues/public/api.php?rota=recuperacao&acao=validar_pergunta
POST /mais_portugues/public/api.php?rota=recuperacao&acao=redefinir
```

Exemplo para redefinir:

```json
{
  "email": "teste@teste.com",
  "resposta": "Escola Municipal",
  "nova_senha": "NovaSenha1"
}
```

### Questões

```http
GET  /mais_portugues/public/api.php?rota=questoes&acao=listar
GET  /mais_portugues/public/api.php?rota=questoes&acao=buscar&id=1
POST /mais_portugues/public/api.php?rota=questoes&acao=salvar
POST /mais_portugues/public/api.php?rota=questoes&acao=deletar
POST /mais_portugues/public/api.php?rota=questoes&acao=enviar
GET  /mais_portugues/public/api.php?rota=questoes&acao=recebidas
POST /mais_portugues/public/api.php?rota=questoes&acao=marcar_recebidas_notificadas
```

Filtros de listagem:

- `busca`
- `tipo`
- `status`
- `genero`
- `subgenero`

Campos principais de `salvar`:

- `id`: opcional, usado em edição.
- `tipo`: `objetiva` ou `dissertativa`.
- `acao`: `salvar` gera rascunho, `postar` publica.
- `titulo`, `genero`, `subgenero`, `especificacao`, `enunciado`, `explicacao`.
- `imagem`: opcional.
- `correta` e `alt_A` até `alt_E`: obrigatórios para questões objetivas.

Exemplo de envio local:

```json
{
  "id": 1,
  "destinatario": "professor@email.com",
  "descricao": "Questão para revisar na próxima aula."
}
```

## Banco de Dados

Tabelas principais:

- `usuarios`: conta, senha, sessão persistente e recuperação.
- `questoes`: enunciado, metadados, status e dono da questão.
- `alternativas_objetivas`: alternativas A-E das questões objetivas.
- `envios_questoes`: histórico de envio local e cópia recebida.

Relações:

- `questoes.id_usuario_criador` aponta para `usuarios.id`.
- `alternativas_objetivas.id_questao` aponta para `questoes.id`.
- `envios_questoes.id_usuario_remetente` e `id_usuario_destinatario` apontam para `usuarios.id`.
- `envios_questoes.id_questao` aponta para a questão original.
- `envios_questoes.id_questao_copia` aponta para a cópia recebida.

## Segurança

- Senhas com `password_hash(PASSWORD_DEFAULT)`.
- Login com `password_verify()`.
- Tokens de lembrar login e reset salvos como hash.
- Resposta da pergunta de segurança salva como hash.
- Sessão PHP iniciada em `config.php`.
- Cookies com `httponly` e `samesite=Lax`.
- Controllers privados chamam `verificarAutenticacao()`.
- Questões são sempre filtradas por `id_usuario_criador`.
- Buscar, editar, excluir e enviar exigem que a questão pertença ao usuário logado.
- Upload validado por MIME type, extensão e tamanho máximo.

## Validações

- Email válido e único.
- Senha com no mínimo 8 caracteres, letra maiúscula, letra minúscula e número.
- Questões objetivas exigem 5 alternativas e uma resposta correta.
- Upload máximo: 5 MB.
- Tipos aceitos: JPEG, PNG, WebP e GIF.
- Envio local exige questão própria, destinatário cadastrado e descrição.

Atualizado em 17/06/2026.
