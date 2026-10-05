# Barbearia STATUS — Sistema de agendamento (V5)

Site + painel em PHP puro (Hostinger, PHP 8.1+). Sem MySQL: dados em `data/*.json` (bloqueados ao público).

## Publicar
1. Faça backup de `data/` e `uploads/` do site atual (fotos e configurações).
2. Envie o CONTEÚDO desta pasta para `public_html` (substitui a V4). Não restaure o `data/` antigo por cima do `appointments.json` se já houver agendamentos reais.
3. Garanta escrita em `data/` e `uploads/gallery/`.
4. Abra `/api.php?action=health` → deve mostrar `data_writable:true`.
5. Entre em `/admin.php` (admin / status123) e TROQUE A SENHA em Configurações → Trocar senha.
6. Em Configurações, preencha o WhatsApp de cada barbeiro (se vazio, usa o da loja).

## Como funciona
- Cliente escolhe barbeiro, serviço, data e horário livre; o horário é reservado e abre o WhatsApp do barbeiro.
- Dois clientes nunca pegam o mesmo horário do mesmo barbeiro (gravação com trava).
- Painel > Agenda: dia a dia por barbeiro, WhatsApp do cliente, status, agendamento de balcão e bloqueio de horário.
- Configurações: abertura/fechamento, intervalo, dias abertos, serviços (Nome|minutos|preço), WhatsApp dos barbeiros.
