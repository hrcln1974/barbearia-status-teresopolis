# Barbearia STATUS V3 Premium — Hostinger

Site e painel administrativos reconstruídos com visual premium responsivo.

## Funcionalidades
- Site público responsivo
- Navegação, serviços, galeria, agendamento e contato
- Agendamento gravado em `data/appointments.json` e enviado para WhatsApp
- Painel administrativo profissional
- Dashboard
- Agendamentos + alteração de status
- Upload/exclusão de galeria
- Configurações do negócio
- Sessão PHP + senha com hash
- Proteção de arquivos JSON/config
- Sem necessidade de MySQL nesta arquitetura

## Login inicial
Usuário: `admin`
Senha: `status123`

Troque a senha em `config.php` antes de produção.

## Publicação
Envie o CONTEÚDO desta pasta diretamente para a pasta pública do domínio (public_html). Não crie uma pasta `final/` dentro do public_html.
Não publique `final` como subpasta.

Depois teste:
- `/`
- `/admin.php`
- `/api.php?action=health`

Pastas `data/` e `uploads/gallery/` precisam ser graváveis pelo PHP.
