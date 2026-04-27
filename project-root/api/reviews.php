<?php
require_once __DIR__ . '/_bootstrap.php';

api_apply_common_headers(['GET', 'POST']);
api_require_auth();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $declarationId = filter_input(INPUT_GET, 'declaration_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $stmt = $pdo->prepare(
        'SELECT r.id, r.declaration_id, r.reviewer_id, u.username AS reviewer_name,
                r.review_note, r.review_status, r.created_at
         FROM declaration_reviews r
         LEFT JOIN users u ON u.id = r.reviewer_id
         WHERE (:has_declaration = 0 OR r.declaration_id = :declaration_id)
         ORDER BY r.created_at DESC, r.id DESC'
    );
    $stmt->execute([
        'has_declaration' => $declarationId ? 1 : 0,
        'declaration_id' => $declarationId ?: 0,
    ]);
    $rows = $stmt->fetchAll();
    api_send_json(['count' => count($rows), 'data' => $rows]);
}

if ($method === 'POST') {
    api_require_role('admin');
    $payload = api_request_data();

    $declarationId = isset($payload['declaration_id']) ? (int) $payload['declaration_id'] : 0;
    $reviewNote = trim((string) ($payload['review_note'] ?? ''));
    $reviewStatus = (string) ($payload['review_status'] ?? 'needs_changes');

    if ($declarationId <= 0 || $reviewNote === '') {
        api_send_json(['error' => 'declaration_id and review_note are required'], 422);
    }

    if (!in_array($reviewStatus, ['approved', 'needs_changes', 'rejected'], true)) {
        api_send_json(['error' => 'Invalid review_status'], 422);
    }

    $checkStmt = $pdo->prepare('SELECT id FROM declarations WHERE id = :id LIMIT 1');
    $checkStmt->execute(['id' => $declarationId]);
    if (!$checkStmt->fetch()) {
        api_send_json(['error' => 'Declaration not found'], 404);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO declaration_reviews (declaration_id, reviewer_id, review_note, review_status)
         VALUES (:declaration_id, :reviewer_id, :review_note, :review_status)'
    );
    $stmt->execute([
        'declaration_id' => $declarationId,
        'reviewer_id' => (int) $_SESSION['user_id'],
        'review_note' => $reviewNote,
        'review_status' => $reviewStatus,
    ]);

    api_send_json(['message' => 'Review created', 'id' => (int) $pdo->lastInsertId()], 201);
}

api_send_json(['error' => 'Method not allowed'], 405);
