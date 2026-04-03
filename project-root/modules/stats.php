<?php
require_once __DIR__ . '/../includes/db.php';

$byYear = $pdo->query('SELECT d.year, COUNT(*) AS total FROM declarations d GROUP BY d.year ORDER BY d.year DESC')->fetchAll();
$byParty = $pdo->query('SELECT COALESCE(pa.name, "N/A") AS party, COUNT(DISTINCT d.id) AS total FROM declarations d INNER JOIN politicians p ON p.id = d.politician_id LEFT JOIN parties pa ON pa.id = p.party_id GROUP BY COALESCE(pa.name, "N/A") ORDER BY total DESC')->fetchAll();
$assetTypes = $pdo->query('SELECT type, COUNT(*) AS cnt, COALESCE(SUM(value),0) AS total_value FROM assets GROUP BY type ORDER BY total_value DESC')->fetchAll();

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
    <section class="card"><p><a href="search_dashboard.php" class="clear-link">Search Dashboard</a> | <a href="list.php" class="clear-link">Search</a> | <a href="../auth/register.php" class="clear-link">Register</a> | <a href="stats.php" class="clear-link">Statistics</a></p><h1>Statistics</h1><p><a href="search_dashboard.php" class="clear-link">Back to Search Dashboard</a></p></section>
    <section class="card"><h2>By Year</h2><div class="table-wrap"><table><thead><tr><th>Year</th><th>Total</th></tr></thead><tbody><?php foreach ($byYear as $r): ?><tr><td><?= esc((string)$r['year']) ?></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <section class="card"><h2>By Party</h2><div class="table-wrap"><table><thead><tr><th>Party</th><th>Total</th></tr></thead><tbody><?php foreach ($byParty as $r): ?><tr><td><?= esc((string)$r['party']) ?></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <section class="card"><h2>Assets</h2><div class="table-wrap"><table><thead><tr><th>Type</th><th>Count</th><th>Total Value</th></tr></thead><tbody><?php foreach ($assetTypes as $r): ?><tr><td><?= esc((string)$r['type']) ?></td><td><?= (int)$r['cnt'] ?></td><td>EUR <?= number_format((float)$r['total_value'],2) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
</main>
</body>
</html>
