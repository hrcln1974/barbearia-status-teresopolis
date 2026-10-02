<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Método inválido.'], 405);
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

foreach (['name','phone','date','time','service'] as $key) {
    if (empty(trim((string)($data[$key] ?? '')))) {
        json_response(['ok' => false, 'error' => 'Preencha nome, WhatsApp, data, horário e serviço.'], 400);
    }
}

$items = appointments();
$item = [
    'id' => bin2hex(random_bytes(8)),
    'name' => trim((string)$data['name']),
    'phone' => trim((string)$data['phone']),
    'date' => trim((string)$data['date']),
    'time' => trim((string)$data['time']),
    'service' => trim((string)$data['service']),
    'message' => trim((string)($data['message'] ?? '')),
    'status' => 'pending',
    'created_at' => date('c')
];

$items[] = $item;

if (!write_json(DATA_DIR . '/appointments.json', $items)) {
    json_response(['ok' => false, 'error' => 'Não foi possível registrar o agendamento. Verifique a permissão da pasta data/.'], 500);
}

$s = settings();
$wa = preg_replace('/\D+/', '', (string)$s['phone']);
$text = "Olá! Quero agendar na Barbearia STATUS.%0A%0ANome: " . rawurlencode($item['name'])
    . "%0AWhatsApp: " . rawurlencode($item['phone'])
    . "%0AServiço: " . rawurlencode($item['service'])
    . "%0AData: " . rawurlencode($item['date'])
    . "%0AHorário: " . rawurlencode($item['time'])
    . "%0AObservação: " . rawurlencode($item['message']);

json_response([
    'ok' => true,
    'whatsapp' => 'https://wa.me/55' . $wa . '?text=' . $text
]);
