# Publicação rápida — Barbearia STATUS

## 1. Git
No VS Code/Git Bash, dentro desta pasta:
```bash
git init
git branch -M main
git add .
git commit -m "feat: site barbearia status"
git remote add origin https://github.com/SEU-USUARIO/SEU-REPOSITORIO.git
git push -u origin main
```

## 2. Hostinger
Envie os arquivos para a pasta pública do domínio (normalmente `public_html`).
Aponte o domínio/subdomínio para essa pasta.

## 3. Permissões
Garanta permissão de escrita em:
- `data/`
- `uploads/gallery/`

Comece com 755. Só aumente se a hospedagem solicitar.

## 4. Painel
Acesse:
`https://SEU-DOMINIO/admin.php`

Inicial:
- usuário: `admin`
- senha: `status123`

Altere as credenciais no `admin.php` antes de divulgar o site.

## 5. Redes sociais
No painel, preencha as URLs oficiais da Barbearia STATUS para:
- Instagram
- Facebook

O WhatsApp já está configurado para `21 99152-5359`.

## 6. Galeria
No painel:
1. Escolha a imagem.
2. Informe um título opcional.
3. Clique em "Adicionar foto".
4. A foto passa a aparecer na seção Galeria do site.

Limite do upload: 8 MB por imagem.
Formatos: JPG, PNG, WEBP e GIF.
