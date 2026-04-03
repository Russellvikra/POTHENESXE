<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();

require_once __DIR__ . '/../includes/db.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

$keyword = trim($_GET['keyword'] ?? '');
$status = trim($_GET['status'] ?? '');
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$orderMode = ($_GET['order'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';
$status = in_array($status, ['draft', 'submitted'], true) ? $status : '';
$year = $year ?: null;
$keywordLike = '%' . $keyword . '%';

$stmt = $pdo->prepare(
    'SELECT d.id, d.year, d.status, d.created_at,
            p.position, COALESCE(pa.name, "N/A") AS party
     FROM declarations d
     INNER JOIN politicians p ON p.id = d.politician_id
     LEFT JOIN parties pa ON pa.id = p.party_id
     WHERE (:has_keyword = 0 OR (p.position LIKE :kw OR pa.name LIKE :kw OR CAST(d.year AS CHAR) LIKE :kw))
       AND (:has_status = 0 OR d.status = :status)
       AND (:has_year = 0 OR d.year = :year)
     GROUP BY d.id, d.year, d.status, d.created_at, p.position, pa.name
     ORDER BY
       CASE WHEN :order_mode = "oldest" THEN d.created_at END ASC,
       CASE WHEN :order_mode = "newest" THEN d.created_at END DESC,
       CASE WHEN :order_mode = "oldest" THEN d.id END ASC,
       CASE WHEN :order_mode = "newest" THEN d.id END DESC
        '
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

echo json_encode([
    'count' => $stmt->rowCount(),
    'data' => $stmt->fetchAll(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
