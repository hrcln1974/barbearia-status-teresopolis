<?php
require __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($action === 'health' && $method === 'GET') {
    json_response([
        'ok' => true,
        'site' => 'Barbearia STATUS',
        'php' => PHP_VERSION,
        'data_writable' => is_writable(DATA_DIR),
        'gallery_writable' => is_writable(GALLERY_DIR),
        'session' => session_status() === PHP_SESSION_ACTIVE
    ]);
}

if ($action === 'login' && $method === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $user = trim((string)($in['user'] ?? ''));
    $pass = (string)($in['pass'] ?? '');

    if (hash_equals(ADMIN_USER, $user) && password_verify($pass, ADMIN_PASS_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'Usuário ou senha inválidos.'], 401);
}

if ($action === 'logout' && $method === 'POST') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
    }
    session_destroy();
    json_response(['ok' => true]);
}

if ($action === 'session' && $method === 'GET') {
    json_response(['ok' => true, 'authenticated' => logged()]);
}

require_admin();

if ($action === 'dashboard' && $method === 'GET') {
    json_response([
        'ok' => true,
        'stats' => [
            'photos' => count(gallery()),
            'appointments' => count(appointments()),
            'pending' => count(array_filter(appointments(), fn($x) => ($x['status'] ?? 'pending') === 'pending'))
        ]
    ]);
}

if ($action === 'settings' && $method === 'GET') {
    json_response(['ok' => true, 'settings' => settings()]);
}

if ($action === 'settings' && $method === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    $old = settings();

    foreach (['name','city','phone','instagram','facebook','address','hours','about'] as $key) {
        if (array_key_exists($key, $in)) {
            $old[$key] = trim((string)$in[$key]);
        }
    }

    if (!write_json(DATA_DIR . '/settings.json', $old)) {
        json_response(['ok' => false, 'error' => 'Não foi possível gravar as configurações. Verifique a permissão da pasta data/.'], 500);
    }

    json_response(['ok' => true, 'settings' => $old]);
}

if ($action === 'gallery' && $method === 'GET') {
    json_response(['ok' => true, 'items' => array_reverse(gallery())]);
}

if ($action === 'gallery' && $method === 'POST') {
    if (!is_writable(GALLERY_DIR) || !is_writable(DATA_DIR)) {
        json_response(['ok' => false, 'error' => 'Pastas sem permissão de escrita. Use 755 para pastas e confirme a propriedade dos arquivos.'], 500);
    }

    if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        json_response(['ok' => false, 'error' => 'Selecione uma imagem válida.'], 400);
    }

    $file = $_FILES['photo'];

    if ($file['size'] > 6 * 1024 * 1024) {
        json_response(['ok' => false, 'error' => 'Imagem maior que 6 MB.'], 400);
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif'
    ];

    if (!isset($map[$mime])) {
        json_response(['ok' => false, 'error' => 'Formato permitido: JPG, PNG, WEBP ou GIF.'], 400);
    }

    $name = bin2hex(random_bytes(10)) . '.' . $map[$mime];
    $target = GALLERY_DIR . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        json_response(['ok' => false, 'error' => 'Não foi possível salvar a imagem.'], 500);
    }

    $items = gallery();
    $item = [
        'id' => bin2hex(random_bytes(8)),
        'url' => 'uploads/gallery/' . $name,
        'title' => trim((string)($_POST['title'] ?? '')),
        'created_at' => date('c')
    ];
    $items[] = $item;

    if (!write_json(DATA_DIR . '/gallery.json', $items)) {
        @unlink($target);
        json_response(['ok' => false, 'error' => 'Imagem salva, mas não foi possível atualizar a galeria.'], 500);
    }

    json_response(['ok' => true, 'item' => $item]);
}

if ($action === 'gallery_delete' && $method === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    $items = gallery();
    $new = [];
    $deleted = false;

    foreach ($items as $item) {
        if (($item['id'] ?? '') === ($in['id'] ?? '')) {
            $path = __DIR__ . '/' . ltrim((string)($item['url'] ?? ''), '/');
            if (is_file($path)) {
                @unlink($path);
            }
            $deleted = true;
        } else {
            $new[] = $item;
        }
    }

    if (!write_json(DATA_DIR . '/gallery.json', $new)) {
        json_response(['ok' => false, 'error' => 'Não foi possível atualizar a galeria.'], 500);
    }

    json_response(['ok' => $deleted]);
}

if ($action === 'appointments' && $method === 'GET') {
    json_response(['ok' => true, 'items' => array_reverse(appointments())]);
}

if ($action === 'appointment_status' && $method === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    $allowed = ['pending', 'confirmed', 'done', 'cancelled'];
    $items = appointments();
    $found = false;

    foreach ($items as &$item) {
        if (($item['id'] ?? '') === ($in['id'] ?? '')) {
            $status = (string)($in['status'] ?? 'pending');
            if (!in_array($status, $allowed, true)) {
                json_response(['ok' => false, 'error' => 'Status inválido.'], 400);
            }
            $item['status'] = $status;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found || !write_json(DATA_DIR . '/appointments.json', $items)) {
        json_response(['ok' => false, 'error' => 'Agendamento não encontrado ou não foi possível salvar.'], 404);
    }

    json_response(['ok' => true]);
}

json_response(['ok' => false, 'error' => 'Ação inválida.'], 404);
