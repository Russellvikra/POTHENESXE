<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit('403 Forbidden'); }

$summaryStmt = $pdo->prepare('SELECT COUNT(*) AS declarations, COALESCE(SUM(value),0) AS total_assets FROM declarations d LEFT JOIN assets a ON a.declaration_id = d.id');
$summaryStmt->execute();
$summary = $summaryStmt->fetch();

$byYearStmt = $pdo->prepare('SELECT d.year, COUNT(*) AS total FROM declarations d GROUP BY d.year ORDER BY d.year DESC');
$byYearStmt->execute();
$byYear = $byYearStmt->fetchAll();

$byPartyStmt = $pdo->prepare('SELECT COALESCE(pa.name, "N/A") AS party, COUNT(DISTINCT d.id) AS total FROM declarations d INNER JOIN politicians p ON p.id = d.politician_id LEFT JOIN parties pa ON pa.id = p.party_id GROUP BY COALESCE(pa.name, "N/A") ORDER BY total DESC');
$byPartyStmt->execute();
$byParty = $byPartyStmt->fetchAll();

$assetTypesStmt = $pdo->prepare('SELECT type, COUNT(*) AS cnt, COALESCE(SUM(value),0) AS total_value FROM assets GROUP BY type ORDER BY total_value DESC');
$assetTypesStmt->execute();
$assetTypes = $assetTypesStmt->fetchAll();

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
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reports</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<main class="page-wrap">
<section class="card"><h1>Reports</h1><div class="stats-grid"><article><p class="label">Declarations</p><p class="value"><?= (int)$summary['declarations'] ?></p></article><article><p class="label">Assets Total</p><p class="value">EUR <?= number_format((float)$summary['total_assets'],2) ?></p></article></div></section>
<section class="card"><h2>By Year</h2><div class="table-wrap"><table><thead><tr><th>Year</th><th>Submissions</th></tr></thead><tbody><?php foreach ($byYear as $r): ?><tr><td><?= esc((string)$r['year']) ?></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="card"><h2>By Party</h2><div class="table-wrap"><table><thead><tr><th>Party</th><th>Submissions</th></tr></thead><tbody><?php foreach ($byParty as $r): ?><tr><td><?= esc((string)$r['party']) ?></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="card"><h2>Asset Categories</h2><div class="table-wrap"><table><thead><tr><th>Type</th><th>Count</th><th>Total Value</th></tr></thead><tbody><?php foreach ($assetTypes as $r): ?><tr><td><?= esc((string)$r['type']) ?></td><td><?= (int)$r['cnt'] ?></td><td>EUR <?= number_format((float)$r['total_value'],2) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="card"><h2>Politicians With No Submission</h2><div class="table-wrap"><table><thead><tr><th>Username</th><th>Email</th><th>Position</th><th>Party</th></tr></thead><tbody><?php if (count($missingSubmissions) === 0): ?><tr><td colspan="4">All politicians have submitted at least one declaration.</td></tr><?php else: ?><?php foreach ($missingSubmissions as $r): ?><tr><td><?= esc((string)$r['username']) ?></td><td><?= esc((string)$r['email']) ?></td><td><?= esc((string)($r['position'] ?? 'N/A')) ?></td><td><?= esc((string)$r['party']) ?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></section>
</main>
<script src="../assets/js/header.js"></script>
</body></html>
