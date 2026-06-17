# Guia de Instalação - +Português

Este guia prepara o +Português em um ambiente local com XAMPP. A aplicação usa PHP MVC, MySQL/MariaDB, autenticação por sessão, recuperação de senha por pergunta de segurança, upload de imagens e isolamento de dados por usuário.

## Requisitos

- XAMPP com Apache, PHP 7.4+ e MySQL/MariaDB.
- Navegador moderno.
- Projeto em `C:\xampp\htdocs\mais_portugues`.
- Extensões PHP `mysqli`, `fileinfo` e `session` habilitadas.

## 1. Banco de Dados

Crie o banco pelo phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Nome do banco:

```text
mais_portugues
```

Charset recomendado:

```text
utf8mb4_general_ci
```

Ou pelo PowerShell:

```powershell
C:\xampp\mysql\bin\mysql.exe -uroot -e "CREATE DATABASE mais_portugues CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
```

## 2. Schema SQL

Execute este SQL no banco `mais_portugues`:

```sql
CREATE TABLE IF NOT EXISTS usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(120) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  nome VARCHAR(100) NOT NULL,
  tipo ENUM('professor', 'admin') NOT NULL DEFAULT 'professor',
  status TINYINT(1) NOT NULL DEFAULT 1,
  recuperacao_metodo ENUM('email', 'perguntas') NOT NULL DEFAULT 'email',
  recuperacao_pergunta VARCHAR(50) NULL,
  recuperacao_resposta_hash VARCHAR(255) NULL,
  reset_token_hash VARCHAR(255) NULL,
  reset_token_expira_em DATETIME NULL,
  lembrar_token_hash VARCHAR(255) NULL,
  lembrar_expira_em DATETIME NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_login DATETIME NULL
);

CREATE TABLE IF NOT EXISTS questoes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  titulo VARCHAR(255) NOT NULL,
  tipo ENUM('objetiva', 'dissertativa') NOT NULL,
  status ENUM('rascunho', 'publicada') NOT NULL DEFAULT 'rascunho',
  genero VARCHAR(100) NOT NULL,
  subgenero VARCHAR(100) NULL,
  especificacao VARCHAR(100) NULL,
  enunciado LONGTEXT NOT NULL,
  explicacao LONGTEXT NULL,
  resposta_correta CHAR(1) NULL,
  imagem VARCHAR(255) NULL,
  id_usuario_criador INT NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_questoes_usuario (id_usuario_criador),
  CONSTRAINT fk_questoes_usuario
    FOREIGN KEY (id_usuario_criador) REFERENCES usuarios(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS alternativas_objetivas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_questao INT NOT NULL,
  alternativa CHAR(1) NOT NULL,
  texto LONGTEXT NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_alternativas_questao (id_questao),
  CONSTRAINT fk_alternativas_questao
    FOREIGN KEY (id_questao) REFERENCES questoes(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS envios_questoes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_questao INT NOT NULL,
  id_questao_copia INT NULL,
  id_usuario_remetente INT NOT NULL,
  id_usuario_destinatario INT NULL,
  email_destinatario VARCHAR(255) NOT NULL,
  nome_destinatario VARCHAR(100) NULL,
  descricao TEXT NOT NULL,
  status ENUM('pendente', 'enviado', 'falha') NOT NULL DEFAULT 'enviado',
  erro TEXT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  enviado_em DATETIME NULL,
  notificado_em DATETIME NULL,
  INDEX idx_envios_questao (id_questao),
  INDEX idx_envios_questao_copia (id_questao_copia),
  INDEX idx_envios_remetente (id_usuario_remetente),
  INDEX idx_envios_destinatario (id_usuario_destinatario),
  CONSTRAINT fk_envios_questao_original
    FOREIGN KEY (id_questao) REFERENCES questoes(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_envios_questao_copia
    FOREIGN KEY (id_questao_copia) REFERENCES questoes(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_envios_usuario_remetente
    FOREIGN KEY (id_usuario_remetente) REFERENCES usuarios(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_envios_usuario_destinatario
    FOREIGN KEY (id_usuario_destinatario) REFERENCES usuarios(id)
    ON DELETE SET NULL
);
```

Observação: os models `Usuario` e `EnvioQuestao` também tentam criar ou ajustar colunas/tabelas necessárias automaticamente. Mesmo assim, usar o schema acima deixa uma instalação nova pronta de primeira.

## 3. Configuração

A configuração fica em:

```text
app/config/config.php
```

Valores padrão para XAMPP:

```php
$db_config = [
    'servername' => 'localhost',
    'usuario'    => 'root',
    'senha'      => '',
    'banco'      => 'mais_portugues',
    'port'       => 3306,
    'charset'    => 'utf8mb4'
];
```

Se o seu MySQL tiver senha, altere o campo `senha`.

## 4. Acessar

Com Apache e MySQL ligados no XAMPP, abra:

```text
http://localhost/mais_portugues/public/
```

Telas úteis:

```text
http://localhost/mais_portugues/public/?page=login
http://localhost/mais_portugues/public/?page=signup
http://localhost/mais_portugues/public/?page=recuperar_senha
```

## 5. API Pública

O frontend usa uma entrada única:

```text
public/api.php
```

Formato:

```text
http://localhost/mais_portugues/public/api.php?rota=usuarios&acao=verificar_sessao
```

Rotas disponíveis:

- `rota=login`
- `rota=logout`
- `rota=usuarios`
- `rota=recuperacao`
- `rota=questoes`

## 6. Usuário de Teste

Você pode criar um usuário pela tela de cadastro. Se quiser inserir manualmente:

```sql
INSERT INTO usuarios (email, senha, nome, tipo, status) VALUES (
  'teste@teste.com',
  '$2y$10$6RYIekPXSIWWp7w7EF7WaOqF9HXEzaAMwFfGtDJZUlRZy7xP0NYxC',
  'Usuário Teste',
  'professor',
  1
);
```

Credenciais:

- Email: `teste@teste.com`
- Senha: `Teste@123`

## 7. Verificação Rápida

No PowerShell, a partir da pasta `mais_portugues`:

```powershell
C:\xampp\php\php.exe -l public\index.php
C:\xampp\php\php.exe -l public\api.php
C:\xampp\php\php.exe -l app\routes\usuarios.php
C:\xampp\php\php.exe -l app\routes\recuperacao.php
C:\xampp\mysql\bin\mysql.exe -uroot -e "USE mais_portugues; SHOW TABLES;"
```

## Troubleshooting

- `Class "mysqli" not found`: use o PHP do XAMPP ou habilite a extensão `mysqli`.
- Upload falhando: habilite `fileinfo`, confirme `public/uploads/` e permissão de escrita do Apache.
- Erro de conexão: confira se o MySQL está ligado e se `app/config/config.php` aponta para o banco correto.
- Página abre, mas ações falham: acesse pela URL `/mais_portugues/public/`.
- Recuperação indisponível: faça login e configure a pergunta em `Configurações`.
- Sessão expirada: faça login novamente.

Atualizado em 17/06/2026.
