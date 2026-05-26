# Documentação MVC - +Português

## Visão Geral

O +Português usa uma arquitetura MVC simples em PHP para separar tela, regra de negócio e acesso ao banco. A ideia é manter cada parte do sistema em um lugar previsível, facilitando manutenção e evolução.

- `public/`: ponto de entrada HTTP, CSS, uploads e API pública.
- `app/config/`: configuração global, sessão, banco, constantes e helpers.
- `app/models/`: acesso ao banco de dados.
- `app/controllers/`: regras de negócio.
- `app/routes/`: dispatchers internos chamados por `public/api.php`.
- `app/views/`: telas renderizadas pelo router público.

Em resumo: o navegador acessa `public/`, as rotas escolhem o que deve acontecer, os controllers coordenam a ação e os models consultam ou alteram o MySQL.

## Estrutura Atual

```text
mais_portugues/
├── public/
│   ├── index.php              # Router das telas
│   ├── api.php                # Entrada pública da API
│   ├── css/style.css
│   └── uploads/
├── app/
│   ├── config/config.php
│   ├── controllers/
│   │   ├── LoginController.php
│   │   ├── LogoutController.php
│   │   ├── SessaoController.php
│   │   └── QuestaoController.php
│   ├── models/
│   │   ├── Usuario.php
│   │   ├── Questao.php
│   │   └── Alternativa.php
│   ├── routes/
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── usuarios.php
│   │   └── questoes.php
│   └── views/
│       ├── index.php
│       ├── login.php
│       ├── signup.php
│       ├── home.php
│       ├── configuracoes.php
│       ├── criacao_objetiva.php
│       ├── criacao_dissertativa.php
│       ├── editar_questao.php
│       └── abas/
└── docs/
```

## Fluxo das Telas

As páginas visuais entram por `public/index.php`. O parâmetro `page` indica qual tela deve ser carregada.

```text
Navegador
  -> public/index.php?page=home
  -> app/config/config.php
  -> app/views/home.php
```

Se o usuário não estiver autenticado e tentar acessar uma tela protegida, o router carrega a tela de login.

## Fluxo da API

As ações assíncronas do frontend entram por `public/api.php`. A rota e a ação são enviadas pela URL, e o corpo da requisição leva os dados necessários quando houver cadastro, edição ou exclusão.

```text
Navegador fetch()
  -> public/api.php?rota=questoes&acao=listar
  -> app/routes/questoes.php
  -> QuestaoController::listar()
  -> Questao::listar()
  -> MySQL
```

O frontend deve chamar `public/api.php`, não `app/routes/*.php` diretamente.

## Constantes Importantes

Definidas em `app/config/config.php`, elas ajudam o sistema a montar caminhos e URLs sem repetir valores manualmente:

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
  "senha": "Teste@123"
}
```

### Logout

```http
GET /mais_portugues/public/api.php?rota=logout
```

### Verificar Sessão

```http
GET /mais_portugues/public/api.php?rota=usuarios&acao=verificar_sessao
```

### Criar Usuário

```http
POST /mais_portugues/public/api.php?rota=usuarios&acao=criar
Content-Type: application/json
```

```json
{
  "nome": "Usuário Teste",
  "email": "teste@teste.com",
  "senha": "Teste@123"
}
```

### Atualizar Perfil

```http
POST /mais_portugues/public/api.php?rota=usuarios&acao=atualizar_perfil
Content-Type: application/json
```

```json
{
  "nome": "Novo Nome",
  "email": "novo@email.com"
}
```

### Alterar Senha

```http
POST /mais_portugues/public/api.php?rota=usuarios&acao=alterar_senha
Content-Type: application/json
```

```json
{
  "senha_atual": "Teste@123",
  "nova_senha": "NovaSenha1"
}
```

### Listar Questões

```http
GET /mais_portugues/public/api.php?rota=questoes&acao=listar
```

Filtros opcionais:

- `busca`
- `tipo`
- `status`
- `genero`
- `subgenero`

### Buscar Questão

```http
GET /mais_portugues/public/api.php?rota=questoes&acao=buscar&id=1
```

### Salvar Questão

```http
POST /mais_portugues/public/api.php?rota=questoes&acao=salvar
Content-Type: multipart/form-data
```

Campos principais:

- `id`: opcional, usado para edição.
- `tipo`: `objetiva` ou `dissertativa`.
- `acao`: `salvar` ou `postar`.
- `titulo`
- `genero`
- `subgenero`
- `especificacao`
- `enunciado`
- `explicacao`
- `imagem`: opcional.
- `correta`: obrigatório para objetiva.
- `alt_A` até `alt_E`: obrigatórios para objetiva.

Quando `acao=salvar`, a questão fica como rascunho. Quando `acao=postar`, ela é marcada como publicada.

### Deletar Questão

```http
POST /mais_portugues/public/api.php?rota=questoes&acao=deletar
Content-Type: application/json
```

```json
{
  "id": 1
}
```

## Segurança

- Senhas com `password_hash(PASSWORD_DEFAULT)`.
- Login validado com `password_verify()`.
- Sessão PHP iniciada em `config.php`.
- Cookies com `httponly` e `samesite=Lax`.
- Controllers críticos chamam `verificarAutenticacao()`.
- Questões são sempre filtradas por `id_usuario_criador`.
- Buscar, editar e excluir questão exigem que a questão pertença ao usuário logado.

## Banco de Dados

O banco guarda usuários, questões e alternativas. As questões sempre carregam o identificador do usuário criador, o que permite filtrar o conteúdo de forma segura em todas as operações privadas.

Tabelas:

- `usuarios`
- `questoes`
- `alternativas_objetivas`

Relações:

- `questoes.id_usuario_criador` aponta para `usuarios.id`.
- `alternativas_objetivas.id_questao` aponta para `questoes.id`.

## Validações

- Email único.
- Senha com no mínimo 8 caracteres, letra maiúscula, letra minúscula e número.
- Questões objetivas exigem 5 alternativas e uma resposta correta.
- Upload máximo: 5 MB.
- Tipos aceitos: JPEG, PNG, WebP e GIF.

Atualizado em 25/05/2026.
