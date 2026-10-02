<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_name('status_admin_session');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ]);
    session_start();
}

/*
 * Credenciais iniciais:
 * usuário: admin
 * senha: status123
 *
 * Para produção, troque ADMIN_PASS_HASH por outro hash.
 * Gere um novo hash com:
 * php -r "echo password_hash('SUA_NOVA_SENHA', PASSWORD_DEFAULT), PHP_EOL;"
 */
const ADMIN_USER = 'admin';
const ADMIN_PASS_HASH = '$2y$12$Aq2egwd1VolwmHyUx4/TPuEkDaCCI0UB9RxP.u3EZ2QLCyR.TsUvC';

const DATA_DIR = __DIR__ . '/data';
const GALLERY_DIR = __DIR__ . '/uploads/gallery';

function ensure_dirs(): void {
    foreach ([DATA_DIR, GALLERY_DIR] as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}

function read_json(string $file, array $default = []): array {
    ensure_dirs();
    if (!is_file($file)) {
        write_json($file, $default);
        return $default;
    }
    $raw = @file_get_contents($file);
    $data = json_decode((string)$raw, true);
    return is_array($data) ? $data : $default;
}

function write_json(string $file, array $data): bool {
    ensure_dirs();
    $json = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    return $json !== false && @file_put_contents($file, $json, LOCK_EX) !== false;
}

function settings(): array {
    return read_json(DATA_DIR . '/settings.json', [
        'name' => 'Barbearia STATUS',
        'city' => 'Teresópolis - RJ',
        'phone' => '21991525359',
        'instagram' => '',
        'facebook' => '',
        'address' => 'Teresópolis - RJ',
        'hours' => 'Segunda a sábado, 09h às 19h',
        'about' => 'Cortes modernos, acabamento preciso e atendimento de qualidade em Teresópolis.'
    ]);
}

function gallery(): array {
    return read_json(DATA_DIR . '/gallery.json', []);
}

function appointments(): array {
    return read_json(DATA_DIR . '/appointments.json', []);
}

function logged(): bool {
    return !empty($_SESSION['admin']);
}

function json_response(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function require_admin(): void {
    if (!logged()) {
        json_response(['ok' => false, 'error' => 'Não autenticado.'], 401);
    }
}

ensure_dirs();
