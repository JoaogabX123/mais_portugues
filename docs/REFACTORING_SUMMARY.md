# Sumário de Refatoração MVC

Este documento resume o estado atual da refatoração MVC do +Português. Ele serve como uma visão rápida do que foi organizado, quais responsabilidades ficaram em cada pasta e quais recursos principais já estão prontos.

## Resultado

O projeto foi organizado em uma arquitetura MVC simples, com a entrada pública separada da lógica da aplicação:

- `public/index.php`: router das telas.
- `public/api.php`: entrada pública da API.
- `app/config/config.php`: configuração global, sessão, banco, helpers e autoload.
- `app/models`: acesso ao banco.
- `app/controllers`: regras de negócio.
- `app/routes`: dispatchers internos da API.
- `app/views`: telas da aplicação.

Com isso, as páginas ficam em `views`, as regras ficam nos `controllers` e as consultas ao banco ficam nos `models`. A pasta `public` concentra o que o navegador acessa diretamente.

## Melhorias Entregues

- Autenticação por sessão PHP.
- Cadastro, login e logout.
- CRUD completo de questões.
- Questões objetivas com alternativas A-E.
- Questões dissertativas.
- Upload de imagens para `public/uploads/`.
- Busca e filtros no dashboard.
- Página de configurações com edição de perfil e alteração de senha.
- API pública centralizada em `public/api.php`.
- Isolamento de dados por usuário.
- Validação de propriedade ao buscar, editar e excluir questões.

## Segurança

As proteções principais foram mantidas no fluxo de autenticação, nas consultas ao banco e nas operações privadas da API:

- Senhas armazenadas com `password_hash(PASSWORD_DEFAULT)`.
- Login com `password_verify()`.
- Prepared statements com MySQLi.
- Sessão iniciada antes do output.
- Cookies com `httponly` e `samesite=Lax`.
- Upload validado por MIME type, extensão e tamanho máximo.
- Questões sempre filtradas por `id_usuario_criador`.

## Estrutura

```text
mais_portugues/
├── public/
│   ├── index.php
│   ├── api.php
│   ├── css/style.css
│   └── uploads/
├── app/
│   ├── config/config.php
│   ├── controllers/
│   ├── models/
│   ├── routes/
│   └── views/
├── docs/
└── README.md
```

## Endereço Local

```text
http://localhost/mais_portugues/public/
```

## API

Base:

```text
http://localhost/mais_portugues/public/api.php?rota=
```

Rotas:

- `login`
- `logout`
- `usuarios`
- `questoes`

As rotas públicas passam por `public/api.php`, que encaminha a requisição para os arquivos em `app/routes/`. Esses arquivos chamam os controllers responsáveis por validar dados, consultar models e devolver JSON ao frontend.

## Documentos Atualizados

- `README.md`
- `docs/MVC_DOCUMENTATION.md`
- `docs/TESTE_API.md`
- `docs/REFACTORING_SUMMARY.md`

Atualizado em 25/05/2026.
