# Barbearia STATUS — V2 Hostinger

Site + painel administrativo em PHP, responsivo e preparado para Hostinger.

## O que foi corrigido
- Botão/link do painel administrativo no site público.
- Login com sessão PHP e senha armazenada em hash.
- Dashboard funcional.
- Agendamentos visíveis e com status.
- Galeria com upload e exclusão.
- Configurações do negócio.
- Endpoint de diagnóstico `/api.php?action=health`.
- Proteção de JSON e arquivos sensíveis via `.htaccess`.
- Proteção contra execução de PHP dentro da pasta de uploads.
- Mensagens de erro mais claras para permissões.

## Banco de dados
Esta versão **não precisa de MySQL**. Os dados ficam em `data/*.json`, adequado para um site pequeno.

Se futuramente houver muitos agendamentos, usuários, histórico, relatórios ou múltiplos administradores, a migração para MySQL pode ser feita sem mudar o visual público.

## Acesso inicial
Usuário: `admin`
Senha: `status123`

**Troque a senha antes de colocar o painel em uso real.**

Gere o hash:
`php -r "echo password_hash('SUA_NOVA_SENHA', PASSWORD_DEFAULT), PHP_EOL;"`

Depois substitua o valor de `ADMIN_PASS_HASH` em `config.php`.

## Hostinger
1. Faça backup da versão atual.
2. Envie **o conteúdo da pasta `final/`** para a pasta pública do domínio.
3. Confirme PHP 8.1+.
4. Confirme permissões padrão: arquivos 644 e pastas 755.
5. As pastas `data/` e `uploads/gallery/` precisam permitir escrita pelo PHP.
6. Acesse `/api.php?action=health` para diagnóstico.
7. Acesse `/admin.php`, faça login e teste Dashboard, Agendamentos, Galeria e Configurações.

Referência de permissões: Hostinger recomenda 644 para arquivos e 755 para pastas.
