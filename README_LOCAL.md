# +Português - Gerenciador de Questões

O **+Português** é uma aplicação PHP MVC feita para apoiar professores na criação, organização e reutilização de questões de língua portuguesa. O sistema trabalha com questões objetivas e dissertativas, permite anexar imagens aos enunciados e mantém os dados de cada usuário separados por autenticação.

Na prática, o professor acessa a área logada, cadastra suas questões, salva rascunhos, publica materiais e usa filtros para encontrar rapidamente o que já foi produzido.

## Status

- Cadastro, login e logout com sessões PHP.
- Criação, edição, listagem e exclusão de questões.
- Suporte a questões objetivas com alternativas A-E.
- Suporte a questões dissertativas.
- Upload de imagens em `public/uploads/`.
- Busca e filtros por tipo, status, gênero e subgênero.
- Área de configurações para atualizar perfil e senha.
- Isolamento de dados: cada usuário vê apenas as próprias questões.
- API pública centralizada em `public/api.php`.

## Como Funciona

O projeto segue uma organização MVC simples:

- `public/index.php` recebe os acessos das telas e carrega a view correta.
- `public/api.php` recebe as chamadas AJAX do frontend.
- `app/routes/` decide qual rota interna será executada.
- `app/controllers/` aplica as regras de negócio.
- `app/models/` conversa com o banco de dados.
- `app/views/` guarda as telas exibidas ao usuário.

As telas usam JavaScript para chamar a API com `fetch`. Como as requisições usam `credentials: 'include'`, a sessão do usuário continua válida entre as ações.

## Documentação

| Arquivo | Conteúdo |
| --- | --- |
| [docs/INSTALACAO.md](docs/INSTALACAO.md) | Instalação no XAMPP, schema SQL e troubleshooting |
| [docs/MVC_DOCUMENTATION.md](docs/MVC_DOCUMENTATION.md) | Estrutura MVC, fluxo da API e endpoints |
| [docs/TESTE_API.md](docs/TESTE_API.md) | Exemplos de testes com curl |

## Como Acessar

Com Apache e MySQL ligados no XAMPP:

```text
http://localhost/mais_portugues/public/
```

Login:

```text
http://localhost/mais_portugues/public/?page=login
```

Cadastro:

```text
http://localhost/mais_portugues/public/?page=signup
```

## Stack

- PHP 7.4+
- MySQL/MariaDB
- MySQLi OOP
- HTML, CSS e JavaScript vanilla
- XAMPP como ambiente local

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
└── docs/
```

## API

Base local:

```text
http://localhost/mais_portugues/public/api.php?rota=
```

Exemplos:

```text
POST /api.php?rota=login
GET  /api.php?rota=usuarios&acao=verificar_sessao
GET  /api.php?rota=questoes&acao=listar
POST /api.php?rota=questoes&acao=salvar
POST /api.php?rota=questoes&acao=deletar
```

No frontend, as requisições usam `credentials: 'include'` para manter a sessão.

Fluxo resumido de uma chamada:

```text
Tela no navegador
  -> public/api.php?rota=questoes&acao=listar
  -> app/routes/questoes.php
  -> QuestaoController
  -> Model Questao
  -> Banco de dados
```

## Banco de Dados

Banco padrão:

```text
mais_portugues
```

Tabelas:

- `usuarios`
- `questoes`
- `alternativas_objetivas`

O schema completo está em [docs/INSTALACAO.md](docs/INSTALACAO.md).

## Segurança

- Senhas com `password_hash(PASSWORD_DEFAULT)`.
- Queries com prepared statements.
- Autenticação obrigatória em operações privadas.
- Questões filtradas por `id_usuario_criador`.
- Buscar, editar e excluir validam a propriedade da questão.

## Verificação Rápida

```powershell
C:\xampp\php\php.exe -l public\index.php
C:\xampp\php\php.exe -l public\api.php
C:\xampp\mysql\bin\mysql.exe -uroot -e "USE mais_portugues; SHOW TABLES;"
```

Atualizado em 25/05/2026.
