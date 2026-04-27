<?php
require_once __DIR__ . '/_bootstrap.php';

api_apply_common_headers(['GET', 'POST', 'PUT', 'DELETE']);
api_require_auth();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);
$currentRole = (string) ($_SESSION['role'] ?? '');

function get_or_create_politician_id(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT id FROM politicians WHERE user_id = :user_id LIMIT 1');
    $stmt->execute(['user_id' => $userId]);
    $id = $stmt->fetchColumn();

    if ($id !== false) {
        return (int) $id;
    }

    $insert = $pdo->prepare('INSERT INTO politicians (user_id, position) VALUES (:user_id, :position)');
    $insert->execute([
        'user_id' => $userId,
        'position' => 'Not specified',
    ]);

    return (int) $pdo->lastInsertId();
}

function find_declaration(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT d.id, d.user_id, d.politician_id, d.title, d.details, d.year, d.status, d.created_at,
                u.username,
                p.position,
                COALESCE(pa.name, "N/A") AS party
         FROM declarations d
         LEFT JOIN users u ON u.id = d.user_id
         LEFT JOIN politicians p ON p.id = d.politician_id
         LEFT JOIN parties pa ON pa.id = p.party_id
         WHERE d.id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $assetsStmt = $pdo->prepare('SELECT id, type, description, value FROM assets WHERE declaration_id = :id ORDER BY id ASC');
    $assetsStmt->execute(['id' => $id]);
    $row['assets'] = $assetsStmt->fetchAll();
    return $row;
}

function can_mutate_declaration(array $declaration, int $currentUserId, string $currentRole): bool
{
    if ($currentRole === 'admin') {
        return true;
    }

    return (int) ($declaration['user_id'] ?? 0) === $currentUserId;
}

if ($method === 'GET') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id) {
        $declaration = find_declaration($pdo, (int) $id);
        if (!$declaration) {
            api_send_json(['error' => 'Declaration not found'], 404);
        }
        api_send_json(['data' => $declaration]);
    }

    $keyword = trim($_GET['keyword'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
    $orderMode = ($_GET['order'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';
    $status = in_array($status, ['draft', 'submitted'], true) ? $status : '';
    $year = $year ?: null;
    $keywordLike = '%' . $keyword . '%';

    $stmt = $pdo->prepare(
        'SELECT d.id, d.user_id, d.title, d.details, d.year, d.status, d.created_at,
                u.username, p.position, COALESCE(pa.name, "N/A") AS party
         FROM declarations d
         LEFT JOIN users u ON u.id = d.user_id
         INNER JOIN politicians p ON p.id = d.politician_id
         LEFT JOIN parties pa ON pa.id = p.party_id
         WHERE (:has_keyword = 0 OR (d.title LIKE :kw OR d.details LIKE :kw OR p.position LIKE :kw OR pa.name LIKE :kw OR CAST(d.year AS CHAR) LIKE :kw))
           AND (:has_status = 0 OR d.status = :status)
           AND (:has_year = 0 OR d.year = :year)
         ORDER BY
           CASE WHEN :order_mode = "oldest" THEN d.created_at END ASC,
           CASE WHEN :order_mode = "newest" THEN d.created_at END DESC,
           CASE WHEN :order_mode = "oldest" THEN d.id END ASC,
           CASE WHEN :order_mode = "newest" THEN d.id END DESC'
    );
    $stmt->execute([
        'has_keyword' => $keyword === '' ? 0 : 1,
        'kw' => $keywordLike,
        'has_status' => $status === '' ? 0 : 1,
        'status' => $status === '' ? 'draft' : $status,
        'has_year' => $year === null ? 0 : 1,
        'year' => $year === null ? 0 : $year,
        'order_mode' => $orderMode,
    ]);

    $data = $stmt->fetchAll();
    api_send_json(['count' => count($data), 'data' => $data]);
}

if ($method === 'POST') {
    $payload = api_request_data();
    $year = isset($payload['year']) ? (int) $payload['year'] : 0;
    $status = (string) ($payload['status'] ?? 'draft');
    $title = trim((string) ($payload['title'] ?? ''));
    $details = trim((string) ($payload['details'] ?? ''));
    $assets = isset($payload['assets']) && is_array($payload['assets']) ? $payload['assets'] : [];

    if ($year < 2000 || $year > 2100) {
        api_send_json(['error' => 'Invalid year. Use 2000-2100'], 422);
    }

    if (!in_array($status, ['draft', 'submitted'], true)) {
        api_send_json(['error' => 'Invalid status. Use draft|submitted'], 422);
    }

    $userId = $currentUserId;
    if ($currentRole === 'admin' && isset($payload['user_id'])) {
        $userId = max(1, (int) $payload['user_id']);
    }

    try {
        $pdo->beginTransaction();

        $politicianId = get_or_create_politician_id($pdo, $userId);

        $insert = $pdo->prepare(
            'INSERT INTO declarations (user_id, politician_id, title, details, year, status)
             VALUES (:user_id, :politician_id, :title, :details, :year, :status)'
        );
        $insert->execute([
            'user_id' => $userId,
            'politician_id' => $politicianId,
            'title' => $title !== '' ? $title : null,
            'details' => $details !== '' ? $details : null,
            'year' => $year,
            'status' => $status,
        ]);

        $declarationId = (int) $pdo->lastInsertId();

        if (count($assets) > 0) {
            $assetStmt = $pdo->prepare(
                'INSERT INTO assets (declaration_id, type, description, value)
                 VALUES (:declaration_id, :type, :description, :value)'
            );
            foreach ($assets as $asset) {
                $type = trim((string) ($asset['type'] ?? ''));
                $description = trim((string) ($asset['description'] ?? ''));
                $value = isset($asset['value']) ? (float) $asset['value'] : -1;
                if ($type === '' || $description === '' || $value < 0) {
                    continue;
                }
                $assetStmt->execute([
                    'declaration_id' => $declarationId,
                    'type' => $type,
                    'description' => $description,
                    'value' => $value,
                ]);
            }
        }

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_send_json(['error' => 'Could not create declaration'], 500);
    }

    api_send_json(['message' => 'Declaration created', 'id' => $declarationId], 201);
}

if ($method === 'PUT') {
    $payload = api_request_data();
    $id = isset($payload['id']) ? (int) $payload['id'] : 0;
    if ($id <= 0) {
        api_send_json(['error' => 'Missing declaration id'], 422);
    }

    $declaration = find_declaration($pdo, $id);
    if (!$declaration) {
        api_send_json(['error' => 'Declaration not found'], 404);
    }
    if (!can_mutate_declaration($declaration, $currentUserId, $currentRole)) {
        api_send_json(['error' => 'Forbidden'], 403);
    }

    $year = isset($payload['year']) ? (int) $payload['year'] : (int) $declaration['year'];
    $status = isset($payload['status']) ? (string) $payload['status'] : (string) $declaration['status'];
    $title = array_key_exists('title', $payload) ? trim((string) $payload['title']) : (string) ($declaration['title'] ?? '');
    $details = array_key_exists('details', $payload) ? trim((string) $payload['details']) : (string) ($declaration['details'] ?? '');

    if ($year < 2000 || $year > 2100) {
        api_send_json(['error' => 'Invalid year. Use 2000-2100'], 422);
    }
    if (!in_array($status, ['draft', 'submitted'], true)) {
        api_send_json(['error' => 'Invalid status. Use draft|submitted'], 422);
    }

    try {
        $pdo->beginTransaction();
        $update = $pdo->prepare(
            'UPDATE declarations
             SET title = :title, details = :details, year = :year, status = :status
             WHERE id = :id'
        );
        $update->execute([
            'title' => $title !== '' ? $title : null,
            'details' => $details !== '' ? $details : null,
            'year' => $year,
            'status' => $status,
            'id' => $id,
        ]);

        if (isset($payload['assets']) && is_array($payload['assets'])) {
            $deleteAssets = $pdo->prepare('DELETE FROM assets WHERE declaration_id = :id');
            $deleteAssets->execute(['id' => $id]);

            $insertAsset = $pdo->prepare(
                'INSERT INTO assets (declaration_id, type, description, value)
                 VALUES (:declaration_id, :type, :description, :value)'
            );
            foreach ($payload['assets'] as $asset) {
                $type = trim((string) ($asset['type'] ?? ''));
                $description = trim((string) ($asset['description'] ?? ''));
                $value = isset($asset['value']) ? (float) $asset['value'] : -1;
                if ($type === '' || $description === '' || $value < 0) {
                    continue;
                }
                $insertAsset->execute([
                    'declaration_id' => $id,
                    'type' => $type,
                    'description' => $description,
                    'value' => $value,
                ]);
            }
        }

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_send_json(['error' => 'Could not update declaration'], 500);
    }

    api_send_json(['message' => 'Declaration updated', 'id' => $id]);
}

if ($method === 'DELETE') {
    $payload = api_request_data();
    $id = isset($payload['id']) ? (int) $payload['id'] : 0;
    if ($id <= 0) {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
    }
    if ($id <= 0) {
        api_send_json(['error' => 'Missing declaration id'], 422);
    }

    $declaration = find_declaration($pdo, $id);
    if (!$declaration) {
        api_send_json(['error' => 'Declaration not found'], 404);
    }
    if (!can_mutate_declaration($declaration, $currentUserId, $currentRole)) {
        api_send_json(['error' => 'Forbidden'], 403);
    }

    $deleteStmt = $pdo->prepare('DELETE FROM declarations WHERE id = :id');
    $deleteStmt->execute(['id' => $id]);
    api_send_json(['message' => 'Declaration deleted', 'id' => $id]);
}

api_send_json(['error' => 'Method not allowed'], 405);
