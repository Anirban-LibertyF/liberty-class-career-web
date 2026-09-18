<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/cbt/vendor/autoload.php';
if (class_exists(Dotenv\Dotenv::class) && is_file(dirname(__DIR__) . '/.env')) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($_ENV['SESSION_NAME'] ?? 'lcc_student_session');
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

require_once dirname(__DIR__) . '/config/database.php';

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

function require_csrf(): void
{
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if ($expected === '' || !hash_equals($expected, $provided)) {
        json_response(['success' => false, 'message' => 'Invalid security token. Refresh the page and try again.'], 419);
    }
}

function input_json(): array
{
    $body = file_get_contents('php://input');
    $data = json_decode($body ?: '{}', true);
    return is_array($data) ? $data : [];
}

function clean(mixed $value, int $max = 500): string
{
    $text = trim((string) $value);
    return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}
