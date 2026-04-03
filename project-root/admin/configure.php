<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit('403 Forbidden'); }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add_party') {
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') {
                $stmt = $pdo->prepare('INSERT INTO parties (name) VALUES (:name)');
                $stmt->execute(['name' => $name]);
                $message = 'Party added.';
            }
        }

        if ($action === 'update_politician') {
            $id = (int) ($_POST['politician_id'] ?? 0);
            $partyIdRaw = trim($_POST['party_id'] ?? '');
            $position = trim($_POST['position'] ?? '');
            if ($id > 0) {
                $partyId = $partyIdRaw === '' ? null : (int) $partyIdRaw;
                $stmt = $pdo->prepare('UPDATE politicians SET party_id = :party_id, position = :position WHERE id = :id');
                $stmt->bindValue(':party_id', $partyId, $partyId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmt->bindValue(':position', $position);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $message = 'Politician profile updated.';
            }
        }
    } catch (PDOException $e) {
        $message = 'Action failed.';
    }
}

// Backfill politician profiles for role=politician users that were created before sync logic existed.
$backfillStmt = $pdo->prepare(
    'INSERT INTO politicians (user_id)
     SELECT u.id
     FROM users u
     LEFT JOIN politicians p ON p.user_id = u.id
     WHERE u.role = "politician" AND p.id IS NULL'
);
$backfillStmt->execute();

$partiesStmt = $pdo->prepare('SELECT id, name FROM parties ORDER BY name ASC');
$partiesStmt->execute();
$parties = $partiesStmt->fetchAll();

$politiciansStmt = $pdo->prepare('SELECT p.id, p.position, p.party_id, u.username FROM politicians p INNER JOIN users u ON u.id = p.user_id ORDER BY u.username ASC');
$politiciansStmt->execute();
$politicians = $politiciansStmt->fetchAll();

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Configure System</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<main class="page-wrap">
<section class="card">
<div class="card-header">
    <h1>System Configuration</h1>
    <p class="card-subtitle">Configure parties and politician profiles</p>
</div>
<?php if ($message !== ''): ?><div class="alert alert-success"><?= esc($message) ?></div><?php endif; ?>
<div class="form-section">
    <h2>➕ Add New Party</h2>
    <form method="POST" class="form-grid">
        <input type="hidden" name="action" value="add_party">
        <div class="form-row">
            <input type="text" name="name" placeholder="Enter party name" required>
            <button type="submit" class="btn btn-primary">Add Party</button>
        </div>
    </form>
</section>
<section class="card">
<h2>👥 Politicians & Positions</h2>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>Username</th><th>Position</th><th>Party</th><th>Action</th></tr></thead><tbody>
<?php if (count($politicians) === 0): ?>
    <tr><td colspan="4" class="text-center text-muted">No politicians found</td></tr>
<?php else: ?>
    <?php foreach ($politicians as $p): ?>
    <tr>
    <td><strong><?= esc((string)$p['username']) ?></strong></td>
    <td><?= esc((string)($p['position'] ?? '')) ?></td>
    <td><span class="badge badge-party"><?= esc((string)($p['party_id'] ?? 'N/A')) ?></span></td>
    <td><form method="POST" class="inline-form"><input type="hidden" name="action" value="update_politician"><input type="hidden" name="politician_id" value="<?= (int)$p['id'] ?>"><div class="edit-row"><input type="text" name="position" value="<?= esc((string)($p['position'] ?? '')) ?>" placeholder="Position"><select name="party_id"><option value="">No party</option><?php foreach ($parties as $party): ?><option value="<?= (int)$party['id'] ?>" <?= (int)$party['id'] === (int)$p['party_id'] ? 'selected' : '' ?>><?= esc((string)$party['name']) ?></option><?php endforeach; ?></select><button type="submit" class="btn btn-sm btn-success">✓ Save</button></div></form></td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody></table></div></section></main>
<script src="../assets/js/header.js"></script>
</body></html>
