<?php
require_once __DIR__ . '/_bootstrap.php';

api_apply_common_headers(['GET']);

if (!isset($_SESSION['user_id'])) {
    api_send_json([
        'authenticated' => false,
        'user' => null,
    ]);
}

api_send_json([
    'authenticated' => true,
    'user' => [
        'id' => (int) $_SESSION['user_id'],
        'username' => (string) ($_SESSION['username'] ?? ''),
        'email' => (string) ($_SESSION['email'] ?? ''),
        'role' => (string) ($_SESSION['role'] ?? ''),
    ],
]);
