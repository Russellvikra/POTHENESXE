<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('403 Forbidden');
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id > 0 && in_array($status, ['draft', 'submitted'], true)) {
        $stmt = $pdo->prepare('UPDATE declarations SET status = :s WHERE id = :id');
        $stmt->execute(['s' => $status, 'id' => $id]);
        $message = 'Submission updated.';
    }
}

$status = trim($_GET['status'] ?? '');
$status = in_array($status, ['draft', 'submitted'], true) ? $status : '';
$order = trim($_GET['order'] ?? 'newest');
$orderMode = $order === 'oldest' ? 'oldest' : 'newest';

$stmt = $pdo->prepare(
    'SELECT d.id, d.year, d.status, d.created_at, u.username, COALESCE(SUM(a.value),0) AS total
     FROM declarations d
     INNER JOIN politicians p ON p.id = d.politician_id
     INNER JOIN users u ON u.id = p.user_id
     LEFT JOIN assets a ON a.declaration_id = d.id
     WHERE (:has_status = 0 OR d.status = :status)
     GROUP BY d.id, d.year, d.status, d.created_at, u.username
     ORDER BY
       CASE WHEN :order_mode = "oldest" THEN d.created_at END ASC,
       CASE WHEN :order_mode = "newest" THEN d.created_at END DESC,
       CASE WHEN :order_mode = "oldest" THEN d.id END ASC,
       CASE WHEN :order_mode = "newest" THEN d.id END DESC
        '
);
$stmt->execute([
    'has_status' => $status === '' ? 0 : 1,
    'status' => $status === '' ? 'draft' : $status,
    'order_mode' => $orderMode,
]);
$rows = $stmt->fetchAll();

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Manage Submissions</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<main class="page-wrap">
<section class="card"><h1>Manage Submissions</h1><?php if ($message !== ''): ?><div class="notice"><?= esc($message) ?></div><?php endif; ?>
<form method="GET" class="filter-form">
<select name="status"><option value="">All status</option><option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option><option value="submitted" <?= $status === 'submitted' ? 'selected' : '' ?>>Submitted</option></select>
<select name="order"><option value="newest" <?= $order === 'newest' ? 'selected' : '' ?>>Newest</option><option value="oldest" <?= $order === 'oldest' ? 'selected' : '' ?>>Oldest</option></select>
<button type="submit">Apply</button></form></section>
<section class="card"><div class="table-wrap"><table><thead><tr><th>ID</th><th>User</th><th>Year</th><th>Status</th><th>Total</th><th>Created</th><th>Action</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr>
<td><a href="../modules/declaration.php?id=<?= (int)$row['id'] ?>">#<?= (int)$row['id'] ?></a></td>
<td><?= esc((string)$row['username']) ?></td><td><?= esc((string)$row['year']) ?></td><td><?= esc((string)$row['status']) ?></td><td>EUR <?= number_format((float)$row['total'],2) ?></td><td><?= esc((string)$row['created_at']) ?></td>
<td><form method="POST" class="inline-form"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><select name="status"><option value="draft" <?= $row['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="submitted" <?= $row['status'] === 'submitted' ? 'selected' : '' ?>>Submitted</option></select><button type="submit">Save</button></form></td>
</tr><?php endforeach; ?>
</tbody></table></div></section></main>
<script src="../assets/js/header.js"></script>
</body></html>
