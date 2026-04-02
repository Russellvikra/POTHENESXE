<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=UTF-8');

$keyword = trim($_GET['keyword'] ?? '');
$status = trim($_GET['status'] ?? '');
$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$order = ($_GET['order'] ?? 'newest') === 'oldest' ? 'ASC' : 'DESC';

$where = [];
$params = [];
if ($keyword !== '') {
    $where[] = '(u.username LIKE :kw OR p.position LIKE :kw OR pa.name LIKE :kw)';
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
            u.username, p.position, COALESCE(pa.name, "N/A") AS party,
            COALESCE(SUM(a.value),0) AS total_assets
     FROM declarations d
     INNER JOIN politicians p ON p.id = d.politician_id
     INNER JOIN users u ON u.id = p.user_id
     LEFT JOIN parties pa ON pa.id = p.party_id
     LEFT JOIN assets a ON a.declaration_id = d.id
     ' . $whereSql . '
     GROUP BY d.id, d.year, d.status, d.created_at, u.username, p.position, pa.name
     ORDER BY d.created_at ' . $order . ', d.id ' . $order
);
$stmt->execute($params);

echo json_encode([
    'count' => $stmt->rowCount(),
    'data' => $stmt->fetchAll(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
