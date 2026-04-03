<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$byYearStmt = $pdo->prepare('SELECT d.year, COUNT(*) AS total FROM declarations d GROUP BY d.year ORDER BY d.year DESC');
$byYearStmt->execute();
$byYear = $byYearStmt->fetchAll();

$byPartyStmt = $pdo->prepare('SELECT COALESCE(pa.name, "N/A") AS party, COUNT(DISTINCT d.id) AS total FROM declarations d INNER JOIN politicians p ON p.id = d.politician_id LEFT JOIN parties pa ON pa.id = p.party_id GROUP BY COALESCE(pa.name, "N/A") ORDER BY total DESC');
$byPartyStmt->execute();
$byParty = $byPartyStmt->fetchAll();

$assetTypesStmt = $pdo->prepare('SELECT type, COUNT(*) AS cnt, COALESCE(SUM(value),0) AS total_value FROM assets GROUP BY type ORDER BY total_value DESC');
$assetTypesStmt->execute();
$assetTypes = $assetTypesStmt->fetchAll();

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Statistics</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<main class="page-wrap">
    <section class="card"><h1>Statistics</h1></section>
    <section class="card"><h2>By Year</h2><div class="table-wrap"><table><thead><tr><th>Year</th><th>Total</th></tr></thead><tbody><?php foreach ($byYear as $r): ?><tr><td><?= esc((string)$r['year']) ?></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <section class="card"><h2>By Party</h2><div class="table-wrap"><table><thead><tr><th>Party</th><th>Total</th></tr></thead><tbody><?php foreach ($byParty as $r): ?><tr><td><?= esc((string)$r['party']) ?></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <section class="card"><h2>Assets</h2><div class="table-wrap"><table><thead><tr><th>Type</th><th>Count</th><th>Total Value</th></tr></thead><tbody><?php foreach ($assetTypes as $r): ?><tr><td><?= esc((string)$r['type']) ?></td><td><?= (int)$r['cnt'] ?></td><td>EUR <?= number_format((float)$r['total_value'],2) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
</main>
<script src="../assets/js/header.js"></script>
</body>
</html>
