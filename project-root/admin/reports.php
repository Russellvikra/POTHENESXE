<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';

// Action: Redirect unauthenticated users to login.
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
// Action: Restrict reports page access to admin users only.
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit('403 Forbidden'); }

// Action: Load high-level declaration and asset totals.
$summaryStmt = $pdo->prepare('SELECT COUNT(*) AS declarations, COALESCE(SUM(value),0) AS total_assets FROM declarations d LEFT JOIN assets a ON a.declaration_id = d.id');
$summaryStmt->execute();
$summary = $summaryStmt->fetch();

// Action: Load submission counts grouped by declaration year.
$byYearStmt = $pdo->prepare('SELECT d.year, COUNT(*) AS total FROM declarations d GROUP BY d.year ORDER BY d.year DESC');
$byYearStmt->execute();
$byYear = $byYearStmt->fetchAll();

// Action: Load submission counts grouped by party.
$byPartyStmt = $pdo->prepare('SELECT COALESCE(pa.name, "N/A") AS party, COUNT(DISTINCT d.id) AS total FROM declarations d INNER JOIN politicians p ON p.id = d.politician_id LEFT JOIN parties pa ON pa.id = p.party_id GROUP BY COALESCE(pa.name, "N/A") ORDER BY total DESC');
$byPartyStmt->execute();
$byParty = $byPartyStmt->fetchAll();

// Action: Load asset category totals and counts.
$assetTypesStmt = $pdo->prepare('SELECT type, COUNT(*) AS cnt, COALESCE(SUM(value),0) AS total_value FROM assets GROUP BY type ORDER BY total_value DESC');
$assetTypesStmt->execute();
$assetTypes = $assetTypesStmt->fetchAll();

// Action: List politicians that have not submitted any declaration.
$missingSubmissionsStmt = $pdo->prepare(
	'SELECT u.username, u.email, p.position, COALESCE(pa.name, "N/A") AS party
	 FROM politicians p
	 INNER JOIN users u ON u.id = p.user_id
	 LEFT JOIN parties pa ON pa.id = p.party_id
	 LEFT JOIN declarations d ON d.politician_id = p.id
	 WHERE d.id IS NULL
	 ORDER BY u.username ASC'
);
$missingSubmissionsStmt->execute();
$missingSubmissions = $missingSubmissionsStmt->fetchAll();

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reports & Analytics</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<main class="page-wrap">
<section class="card">
<div class="card-header">
    <h1>Reports & Analytics</h1>
    <p class="card-subtitle">System statistics and key metrics</p>
</div>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= (int)$summary['declarations'] ?></div>
        <div class="stat-label">Total Declarations</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">EUR <?= number_format((float)$summary['total_assets'],0) ?></div>
        <div class="stat-label">Total Assets Value</div>
    </div>
</div>
</section>
<section class="card">
<h2>📅 Declarations by Year</h2>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>Year</th><th>Submissions</th></tr></thead><tbody>
<?php if (count($byYear) === 0): ?>
    <tr><td colspan="2" class="text-center text-muted">No data available</td></tr>
<?php else: ?>
    <?php foreach ($byYear as $r): ?><tr><td><strong><?= esc((string)$r['year']) ?></strong></td><td><span class="badge badge-count"><?= (int)$r['total'] ?></span></td></tr><?php endforeach; ?>
<?php endif; ?>
</tbody></table></div></section>
<section class="card">
<h2>🏛️ Declarations by Party</h2>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>Party</th><th>Submissions</th></tr></thead><tbody>
<?php if (count($byParty) === 0): ?>
    <tr><td colspan="2" class="text-center text-muted">No data available</td></tr>
<?php else: ?>
    <?php foreach ($byParty as $r): ?><tr><td><strong><?= esc((string)$r['party']) ?></strong></td><td><span class="badge badge-count"><?= (int)$r['total'] ?></span></td></tr><?php endforeach; ?>
<?php endif; ?>
</tbody></table></div></section>
<section class="card">
<h2>💰 Asset Categories</h2>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>Type</th><th>Count</th><th>Total Value</th></tr></thead><tbody>
<?php if (count($assetTypes) === 0): ?>
    <tr><td colspan="3" class="text-center text-muted">No assets recorded</td></tr>
<?php else: ?>
    <?php foreach ($assetTypes as $r): ?><tr><td><strong><?= esc((string)$r['type']) ?></strong></td><td><span class="badge badge-count"><?= (int)$r['cnt'] ?></span></td><td><strong>EUR <?= number_format((float)$r['total_value'],2) ?></strong></td></tr><?php endforeach; ?>
<?php endif; ?>
</tbody></table></div></section>
<section class="card">
<h2>⚠️ Missing Submissions</h2>
<p class="card-subtitle">Politicians without any declaration submissions</p>
<div class="table-wrap">
<table class="data-table">
<thead><tr><th>Username</th><th>Email</th><th>Position</th><th>Party</th></tr></thead><tbody>
<?php if (count($missingSubmissions) === 0): ?><tr><td colspan="4" class="text-center"><span class="badge badge-success">✓ All politicians have submitted at least one declaration</span></td></tr><?php else: ?><?php foreach ($missingSubmissions as $r): ?><tr><td><strong><?= esc((string)$r['username']) ?></strong></td><td><?= esc((string)$r['email']) ?></td><td><?= esc((string)($r['position'] ?? 'N/A')) ?></td><td><?= esc((string)$r['party']) ?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></section>
</main>
<script src="../assets/js/header.js"></script>
</body></html>
