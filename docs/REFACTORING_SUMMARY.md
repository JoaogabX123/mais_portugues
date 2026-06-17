# Sumário da Organização MVC

Este documento resume o estado atual da organização MVC do +Português. Para detalhes de instalação, consulte `docs/INSTALACAO.md`; para endpoints completos, consulte `docs/MVC_DOCUMENTATION.md`.

## Resultado

O projeto está organizado com uma entrada pública para telas e outra para API:

- `public/index.php`: router das telas.
- `public/api.php`: entrada única da API.
- `app/config/config.php`: configuração global, sessão, banco, helpers e autoload.
- `app/controllers`: regras de negócio.
- `app/models`: acesso ao banco.
- `app/routes`: dispatchers internos da API.
- `app/views`: telas da aplicação.

Com isso, as URLs públicas ficam concentradas em `public/`, enquanto regras e consultas ficam fora do acesso direto do navegador.

## Recursos Entregues

- Cadastro, login e logout.
- Login persistente com token armazenado em cookie.
- Recuperação de senha por pergunta de segurança.
- Edição de perfil e alteração de senha.
- CRUD completo de questões.
- Questões objetivas com alternativas A-E.
- Questões dissertativas.
- Upload de imagens para `public/uploads/`.
- Busca e filtros no dashboard.
- Envio local de questões para outro professor cadastrado.
- Notificação de questões recebidas.
- API pública centralizada em `public/api.php`.
- Isolamento de dados por usuário.
- Validação de propriedade ao buscar, editar, excluir e enviar questões.

## Estrutura

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
|   |-- models/
|   |-- routes/
|   `-- views/
|-- docs/
`-- README.md
```

## Rotas Públicas

Telas:

```text
http://localhost/mais_portugues/public/
http://localhost/mais_portugues/public/?page=login
http://localhost/mais_portugues/public/?page=signup
http://localhost/mais_portugues/public/?page=recuperar_senha
```

API:

```text
http://localhost/mais_portugues/public/api.php?rota=
```

Rotas de API:

- `login`
- `logout`
- `usuarios`
- `recuperacao`
- `questoes`

## Banco de Dados

Tabelas principais:

- `usuarios`
- `questoes`
- `alternativas_objetivas`
- `envios_questoes`

Os models `Usuario` e `EnvioQuestao` incluem rotinas de compatibilidade para garantir colunas/tabelas novas em instalações antigas. Em instalação limpa, prefira rodar o schema completo de `docs/INSTALACAO.md`.

## Segurança

- Senhas armazenadas com `password_hash(PASSWORD_DEFAULT)`.
- Login validado com `password_verify()`.
- Tokens persistentes e respostas de recuperação armazenados como hash.
- Prepared statements com MySQLi.
- Sessão iniciada antes do output.
- Cookies com `httponly` e `samesite=Lax`.
- Upload validado por MIME type, extensão e tamanho máximo.
- Questões sempre filtradas por `id_usuario_criador`.
- Envio local cria uma cópia para o destinatário e registra o histórico em `envios_questoes`.

## Documentos Mantidos

- `README.md`
- `docs/INSTALACAO.md`
- `docs/MVC_DOCUMENTATION.md`
- `docs/TESTE_API.md`
- `docs/REFACTORING_SUMMARY.md`

Atualizado em 17/06/2026.
