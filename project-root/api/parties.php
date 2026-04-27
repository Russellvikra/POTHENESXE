<?php
require_once __DIR__ . '/_bootstrap.php';

api_apply_common_headers(['GET', 'POST', 'PUT', 'DELETE']);
api_require_auth();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id) {
        $stmt = $pdo->prepare('SELECT id, name FROM parties WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $party = $stmt->fetch();
        if (!$party) {
            api_send_json(['error' => 'Party not found'], 404);
        }
        api_send_json(['data' => $party]);
    }

    $stmt = $pdo->prepare('SELECT id, name FROM parties ORDER BY name ASC');
    $stmt->execute();
    $rows = $stmt->fetchAll();
    api_send_json(['count' => count($rows), 'data' => $rows]);
}

if ($method === 'POST') {
    api_require_role('admin');
    $payload = api_request_data();
    $name = trim((string) ($payload['name'] ?? ''));
    if ($name === '') {
        api_send_json(['error' => 'Party name is required'], 422);
    }

    $stmt = $pdo->prepare('INSERT INTO parties (name) VALUES (:name)');
    $stmt->execute(['name' => $name]);
    api_send_json(['message' => 'Party created', 'id' => (int) $pdo->lastInsertId()], 201);
}

if ($method === 'PUT') {
    api_require_role('admin');
    $payload = api_request_data();
    $id = isset($payload['id']) ? (int) $payload['id'] : 0;
    $name = trim((string) ($payload['name'] ?? ''));
    if ($id <= 0 || $name === '') {
        api_send_json(['error' => 'id and name are required'], 422);
    }

    $stmt = $pdo->prepare('UPDATE parties SET name = :name WHERE id = :id');
    $stmt->execute(['name' => $name, 'id' => $id]);
    if ($stmt->rowCount() === 0) {
        api_send_json(['error' => 'Party not found'], 404);
    }
    api_send_json(['message' => 'Party updated', 'id' => $id]);
}

if ($method === 'DELETE') {
    api_require_role('admin');
    $payload = api_request_data();
    $id = isset($payload['id']) ? (int) $payload['id'] : 0;
    if ($id <= 0) {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
    }
    if ($id <= 0) {
        api_send_json(['error' => 'id is required'], 422);
    }

    $stmt = $pdo->prepare('DELETE FROM parties WHERE id = :id');
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() === 0) {
        api_send_json(['error' => 'Party not found'], 404);
    }
    api_send_json(['message' => 'Party deleted', 'id' => $id]);
}

api_send_json(['error' => 'Method not allowed'], 405);
