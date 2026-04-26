<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
// Action: Set CORS headers so external systems can call this endpoint.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Action: Respond to CORS preflight checks without running report queries.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Action: Return unauthorized response when session is missing.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Action: Restrict this endpoint to admin users only.
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => 'Forbidden'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=UTF-8');

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
echo json_encode([
    'summary' => $summary,
    'by_year' => $byYear,
    'by_party' => $byParty,
    'asset_types' => $assetTypes,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
