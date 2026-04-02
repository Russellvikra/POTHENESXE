<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=UTF-8');

$summary = $pdo->query('SELECT COUNT(*) AS declarations, COALESCE(SUM(value),0) AS total_assets FROM declarations d LEFT JOIN assets a ON a.declaration_id = d.id')->fetch();
$byYear = $pdo->query('SELECT d.year, COUNT(*) AS total FROM declarations d GROUP BY d.year ORDER BY d.year DESC')->fetchAll();
$byParty = $pdo->query('SELECT COALESCE(pa.name, "N/A") AS party, COUNT(DISTINCT d.id) AS total FROM declarations d INNER JOIN politicians p ON p.id = d.politician_id LEFT JOIN parties pa ON pa.id = p.party_id GROUP BY COALESCE(pa.name, "N/A") ORDER BY total DESC')->fetchAll();
$assetTypes = $pdo->query('SELECT type, COUNT(*) AS count, COALESCE(SUM(value),0) AS total_value FROM assets GROUP BY type ORDER BY total_value DESC')->fetchAll();

echo json_encode([
    'summary' => $summary,
    'by_year' => $byYear,
    'by_party' => $byParty,
    'asset_types' => $assetTypes,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
