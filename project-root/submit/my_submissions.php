<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';
$activeNav = 'submit';
// Action: Redirect unauthenticated users to the login page.
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
// Action: Block access unless the signed-in user has the politician role.
if (($_SESSION['role'] ?? '') !== 'politician') { http_response_code(403); exit('403 Forbidden'); }

$userId = (int) $_SESSION['user_id'];
// Action: Read and normalize the selected sort order (newest or oldest).
$orderInput = ($_GET['order'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';
// Action: Read the status filter and keep only allowed status values.
$status = trim($_GET['status'] ?? '');
$status = in_array($status, ['draft', 'submitted'], true) ? $status : '';

// Action: Prepare query to load this user's declarations and their total asset value.
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
// Action: Execute query using the selected filters and ordering.
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
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Submissions</title><link rel="stylesheet" href="../assets/css/header.css"><link rel="stylesheet" href="../assets/css/submit.css"><link rel="stylesheet" href="../assets/css/admin.css"><link rel="stylesheet" href="../assets/css/footer.css"></head><body>
<?php include '../assets/include/header.html'; ?>
<main class="page-wrap">
<section class="card">
<div class="card-header">
    <h1>My Submissions</h1>
    <p class="card-subtitle">Your declaration history</p>
</div>
<form method="GET" class="filter-bar">
    <div class="filter-group">
        <label for="status-filter">Status:</label>
        <select id="status-filter" name="status">
            <option value="">All Statuses</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="submitted" <?= $status === 'submitted' ? 'selected' : '' ?>>Submitted</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="order-filter">Sort:</label>
        <select id="order-filter" name="order">
            <option value="newest" <?= $order === 'DESC' ? 'selected' : '' ?>>Newest First</option>
            <option value="oldest" <?= $order === 'ASC' ? 'selected' : '' ?>>Oldest First</option>
        </select>
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Apply Filters</button>
    <a href="submit.php" class="btn btn-sm btn-success">+ New Declaration</a>
</form>
</section>
<section class="card">
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>Declaration</th><th>Year</th><th>Status</th><th>Total Value</th><th>Created</th><th>View</th></tr></thead><tbody>
<?php if (count($rows) === 0): ?>
    <tr><td colspan="6" class="text-center text-muted">No submissions yet. <a href="submit.php">Create one now</a></td></tr>
<?php else: ?>
    <?php foreach ($rows as $row): ?>
    <tr>
    <td><strong>#<?= (int)$row['id'] ?></strong></td>
    <td><?= esc((string)$row['year']) ?></td>
    <td><span class="status-badge status-<?= strtolower($row['status']) ?>"><?= ucfirst(esc((string)$row['status'])) ?></span></td>
    <td><strong>EUR <?= number_format((float)$row['total'],2) ?></strong></td>
    <td><small class="text-muted"><?= esc((string)$row['created_at']) ?></small></td>
    <td><a href="../modules/declaration.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary">View</a></td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody></table></div></section></main>
<?php include '../assets/include/footer.html'; ?>
<script src="../assets/js/header.js"></script>
</body></html>
