<?php
require_once __DIR__ . '/../includes/db.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

$keyword = trim($_GET['keyword'] ?? '');
$status = trim($_GET['status'] ?? '');
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$order = ($_GET['order'] ?? 'newest') === 'oldest' ? 'ASC' : 'DESC';

$where = [];
$params = [];
if ($keyword !== '') {
    $where[] = '(p.position LIKE :kw OR pa.name LIKE :kw OR CAST(d.year AS CHAR) LIKE :kw)';
    $params['kw'] = '%' . $keyword . '%';
}
if (in_array($status, ['draft', 'submitted'], true)) {
    $where[] = 'd.status = :status';
    $params['status'] = $status;
}
if ($year) {
    $where[] = 'd.year = :year';
    $params['year'] = $year;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $pdo->prepare(
    'SELECT d.id, d.year, d.status, d.created_at,
            p.position, COALESCE(pa.name, "N/A") AS party
     FROM declarations d
     INNER JOIN politicians p ON p.id = d.politician_id
     LEFT JOIN parties pa ON pa.id = p.party_id
     ' . $whereSql . '
     GROUP BY d.id, d.year, d.status, d.created_at, p.position, pa.name
     ORDER BY d.created_at ' . $order . ', d.id ' . $order
);
$stmt->execute($params);

echo json_encode([
    'count' => $stmt->rowCount(),
    'data' => $stmt->fetchAll(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
