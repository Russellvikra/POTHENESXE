<?php
require_once __DIR__ . '/_bootstrap.php';

api_apply_common_headers(['GET']);

// Action: Query high-level declaration and asset totals.
$summaryStmt = $pdo->prepare('SELECT COUNT(*) AS declarations, COALESCE(SUM(value),0) AS total_assets FROM declarations d LEFT JOIN assets a ON a.declaration_id = d.id');
$summaryStmt->execute();
$summary = $summaryStmt->fetch();

// Action: Query declaration totals grouped by year.
$byYearStmt = $pdo->prepare('SELECT d.year, COUNT(*) AS total FROM declarations d GROUP BY d.year ORDER BY d.year DESC');
$byYearStmt->execute();
$byYear = $byYearStmt->fetchAll();

// Action: Query declaration totals grouped by party.
$byPartyStmt = $pdo->prepare('SELECT COALESCE(pa.name, "N/A") AS party, COUNT(DISTINCT d.id) AS total FROM declarations d INNER JOIN politicians p ON p.id = d.politician_id LEFT JOIN parties pa ON pa.id = p.party_id GROUP BY COALESCE(pa.name, "N/A") ORDER BY total DESC');
$byPartyStmt->execute();
$byParty = $byPartyStmt->fetchAll();

// Action: Query asset totals grouped by asset type.
$assetTypesStmt = $pdo->prepare('SELECT type, COUNT(*) AS count, COALESCE(SUM(value),0) AS total_value FROM assets GROUP BY type ORDER BY total_value DESC');
$assetTypesStmt->execute();
$assetTypes = $assetTypesStmt->fetchAll();

// Action: Return statistics payload as JSON.
api_send_json([
    'summary' => $summary,
    'by_year' => $byYear,
    'by_party' => $byParty,
    'asset_types' => $assetTypes,
]);
