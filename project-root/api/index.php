<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
	http_response_code(204);
	exit;
}

if (!isset($_SESSION['user_id'])) {
	http_response_code(401);
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	exit;
}

header('Content-Type: application/json; charset=UTF-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/api/index.php')), '/');
$baseUrl = $scheme . '://' . $host . $baseDir;

echo json_encode([
	'name' => 'Pothen Esxes API',
	'version' => '1.0',
	'description' => 'Endpoints for third-party systems to fetch declaration data.',
	'endpoints' => [
		[
			'path' => '/api/declarations.php',
			'method' => 'GET',
			'url' => $baseUrl . '/declarations.php',
			'description' => 'Fetch declarations with optional filters.',
			'query_params' => [
				'keyword' => 'Search by year, party, or position',
				'status' => 'draft|submitted',
				'year' => 'e.g. 2025',
				'order' => 'newest|oldest',
			],
		],
		[
			'path' => '/api/stats.php',
			'method' => 'GET',
			'url' => $baseUrl . '/stats.php',
			'description' => 'Administrative statistics endpoint (admin session required).',
		],
	],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
