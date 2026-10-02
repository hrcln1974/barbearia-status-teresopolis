<?php
declare(strict_types=1);
session_start();
define('BASE_DIR', __DIR__);
define('DATA_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'data');
define('GALLERY_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'gallery');
define('SETTINGS_FILE', DATA_DIR . DIRECTORY_SEPARATOR . 'settings.json');
define('GALLERY_FILE', DATA_DIR . DIRECTORY_SEPARATOR . 'gallery.json');

if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0755, true);
if (!is_dir(GALLERY_DIR)) @mkdir(GALLERY_DIR, 0755, true);

function json_read(string $file, array $fallback=[]): array {
    if (!is_file($file)) return $fallback;
    $raw = @file_get_contents($file);
    $data = json_decode((string)$raw, true);
    return is_array($data) ? $data : $fallback;
}
function json_write(string $file, array $data): bool {
    return (bool)@file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function settings(): array {
    return json_read(SETTINGS_FILE, [
        'name'=>'Barbearia STATUS','city'=>'Teresópolis - RJ','phone'=>'21991525359',
        'instagram'=>'','facebook'=>'','hours'=>'Segunda a sábado • 09h às 19h',
        'address'=>'Teresópolis - RJ',
        'whatsapp_message'=>'Olá! Gostaria de agendar um horário na Barbearia STATUS.'
    ]);
}
function gallery(): array { return json_read(GALLERY_FILE, []); }
function is_admin(): bool { return !empty($_SESSION['status_admin']); }
function require_admin(): void { if (!is_admin()) { header('Location: admin.php'); exit; } }
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function whatsapp_url(string $phone, string $message=''): string {
    $digits = preg_replace('/\D+/', '', $phone);
    if (str_starts_with($digits, '55') === false) $digits = '55'.$digits;
    return 'https://wa.me/'.$digits.($message !== '' ? '?text='.rawurlencode($message) : '');
}
