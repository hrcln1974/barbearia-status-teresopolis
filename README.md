# BARBEARIA STATUS — Teresópolis/RJ

Site institucional + painel administrativo com galeria de fotos.

## Stack
- PHP 8+ (backend)
- HTML5 / CSS3 / JavaScript
- JSON para configurações e catálogo da galeria
- Upload de imagens no próprio servidor
- Sessão PHP para acesso administrativo
- Sem banco de dados obrigatório

## Estrutura
- `index.php` — site público
- `admin.php` — painel administrativo
- `config.php` — funções/configuração
- `data/settings.json` — configurações
- `data/gallery.json` — índice da galeria
- `uploads/gallery/` — fotos enviadas pelo admin
- `assets/` — CSS, JS e imagens

## Primeiro acesso
Usuário: `admin`
Senha: `status123`

**IMPORTANTE:** altere essas credenciais no `admin.php` antes de publicar.

## Publicação no Hostinger
1. Faça upload dos arquivos para a pasta pública do domínio.
2. Confirme que o domínio está usando PHP 8.x ou superior.
3. Dê permissão de escrita para `data/` e `uploads/gallery/` (normalmente 755; se o servidor exigir, ajuste somente essas pastas).
4. Acesse `/admin.php`.
5. Cadastre Instagram/Facebook no painel.
6. Envie as fotos da galeria pelo painel.
7. Teste o WhatsApp, menu mobile, galeria e login.

## Git / VS Code
```bash
git init
git branch -M main
git add .
git commit -m "feat: barbearia status pronta para producao"
git remote add origin SEU_REPOSITORIO
git push -u origin main
```

Não coloque senhas reais ou arquivos `.env` no Git.
