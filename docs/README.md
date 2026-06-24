# GitHub Pages - Mais Português

Este diretório contém o site estático para o GitHub Pages do projeto Mais Português.

## 📁 Estrutura

```
docs-site/
├── index.html          # Página principal
├── css/
│   └── style.css      # Estilos do site
├── js/
│   └── script.js      # Scripts JavaScript
└── img/               # Imagens (adicione aqui)
```

## 🚀 Como Deploy no GitHub Pages

### Opção 1: Usar a pasta `/docs` (Recomendado)

1. **Copie o conteúdo** desta pasta para uma pasta chamada `docs` na raiz do seu repositório:
   ```bash
   cp -r docs-site/* docs/
   ```

2. **Vá para GitHub > Repositório > Settings > Pages**

3. **Configuração:**
   - Branch: `main` (ou a sua branch padrão)
   - Pasta: `/docs`
   - Clique em Save

4. **Seu site estará em:** `https://joaogabx123.github.io/mais_portugues`

### Opção 2: Usar a raiz do repositório

1. **Copie os arquivos** para a raiz do repositório
2. **Remova o arquivo `index.php`** da pasta public (se houver conflito)
3. **Siga os mesmos passos da Opção 1, mas escolha "root"**

### Opção 3: Usar uma branch separada (`gh-pages`)

```bash
# Criar branch gh-pages
git checkout --orphan gh-pages

# Remover arquivos desnecessários
git rm -rf .

# Copiar arquivos do site
cp -r docs-site/* .

# Fazer commit
git add .
git commit -m "Initial GitHub Pages"

# Push para gh-pages
git push origin gh-pages

# Voltar para main
git checkout main
```

## 📝 Personalizações

### Adicionar Logo/Imagens

1. Coloque as imagens na pasta `img/`
2. Atualize as referências no `index.html`:
   ```html
   <img src="img/seu-logo.png" alt="Logo">
   ```

### Alterar Cores

No arquivo `css/style.css`, modifique as variáveis CSS:
```css
:root {
    --primary-color: #2563eb;      /* Cor principal */
    --primary-dark: #1e40af;       /* Cor escura */
    --secondary-color: #10b981;    /* Cor secundária */
    /* ... outras cores ... */
}
```

### Adicionar Mais Seções

Copie a estrutura de uma seção existente e adicione ao `index.html`. Exemplo:

```html
<section id="sua-secao" class="sua-secao">
    <div class="container">
        <h2>Seu Título</h2>
        <!-- Conteúdo aqui -->
    </div>
</section>
```

Depois adicione o CSS em `css/style.css`:

```css
.sua-secao {
    padding: 5rem 0;
    background-color: var(--bg-light);
}
```

## 🔗 Links Úteis

- [GitHub Pages Documentação](https://docs.github.com/en/pages)
- [Exemplo do Site](https://joaogabx123.github.io/mais_portugues)
- [Repositório](https://github.com/JoaogabX123/mais_portugues)

## ✅ Checklist Antes de Fazer Deploy

- [ ] Verificar se todos os links funcionam
- [ ] Testar responsividade em mobile
- [ ] Verificar ortografia e conteúdo
- [ ] Testarlocalhost com `npx http-server` ou similar
- [ ] Fazer commit e push dos arquivos
- [ ] Ativar GitHub Pages nas configurações do repositório

## 💡 Dicas

- Para testar localmente, use um servidor HTTP simples:
  ```bash
  # Com Python 3
  python -m http.server 8000

  # Ou com Node.js
  npx http-server
  ```

- Então acesse: `http://localhost:8000`

## 📧 Suporte

Dúvidas? Crie uma [Issue no GitHub](https://github.com/JoaogabX123/mais_portugues/issues)
