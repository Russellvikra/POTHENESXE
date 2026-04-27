<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';

function api_send_json($payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function api_apply_common_headers(array $methods): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: ' . implode(', ', $methods) . ', OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function api_require_auth(): void
{
    if (!isset($_SESSION['user_id'])) {
        api_send_json(['error' => 'Unauthorized'], 401);
    }
}

function api_require_role(string $role): void
{
    if (($_SESSION['role'] ?? '') !== $role) {
        api_send_json(['error' => 'Forbidden'], 403);
    }
}

function api_request_data(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') {
        return [];
    }

    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        return $json;
    }

    parse_str($raw, $formData);
    return is_array($formData) ? $formData : [];
}

function api_base_url(string $scriptNameFallback = '/api/index.php'): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? $scriptNameFallback)), '/');
    return $scheme . '://' . $host . $baseDir;
}
