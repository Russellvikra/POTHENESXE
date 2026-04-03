<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';
$activeNav = 'submit';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
if (($_SESSION['role'] ?? '') !== 'politician') { http_response_code(403); exit('403 Forbidden'); }

$userId = (int) $_SESSION['user_id'];
$orderInput = ($_GET['order'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';
$status = trim($_GET['status'] ?? '');
$status = in_array($status, ['draft', 'submitted'], true) ? $status : '';

$stmt = $pdo->prepare(
    'SELECT d.id, d.year, d.status, d.created_at, COALESCE(SUM(a.value),0) AS total
     FROM declarations d
     INNER JOIN politicians p ON p.id = d.politician_id
     LEFT JOIN assets a ON a.declaration_id = d.id
         WHERE p.user_id = :uid
             AND (:has_status = 0 OR d.status = :status)
     GROUP BY d.id, d.year, d.status, d.created_at
         ORDER BY
             CASE WHEN :order_mode = "oldest" THEN d.created_at END ASC,
             CASE WHEN :order_mode = "newest" THEN d.created_at END DESC,
             CASE WHEN :order_mode = "oldest" THEN d.id END ASC,
             CASE WHEN :order_mode = "newest" THEN d.id END DESC
    '
);
$stmt->execute([
        'uid' => $userId,
        'has_status' => $status === '' ? 0 : 1,
        'status' => $status === '' ? 'draft' : $status,
        'order_mode' => $orderInput,
]);
$rows = $stmt->fetchAll();

$order = $orderInput === 'oldest' ? 'ASC' : 'DESC';

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Submissions</title><link rel="stylesheet" href="../assets/css/header.css"><link rel="stylesheet" href="../assets/css/submit.css"><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<?php include '../assets/include/header.html'; ?>
<main class="page-wrap"><section class="card"><h1>My Submissions</h1>
<form method="GET" class="filter-form"><select name="status"><option value="">All</option><option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option><option value="submitted" <?= $status === 'submitted' ? 'selected' : '' ?>>Submitted</option></select><select name="order"><option value="newest" <?= $order === 'DESC' ? 'selected' : '' ?>>Newest</option><option value="oldest" <?= $order === 'ASC' ? 'selected' : '' ?>>Oldest</option></select><button type="submit">Apply</button></form>
</section>
<section class="card"><div class="table-wrap"><table><thead><tr><th>ID</th><th>Year</th><th>Status</th><th>Total</th><th>Created</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><a href="../modules/declaration.php?id=<?= (int)$row['id'] ?>">#<?= (int)$row['id'] ?></a></td><td><?= esc((string)$row['year']) ?></td><td><?= esc((string)$row['status']) ?></td><td>EUR <?= number_format((float)$row['total'],2) ?></td><td><?= esc((string)$row['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></section></main></body></html>
<script src="../assets/js/header.js"></script>
