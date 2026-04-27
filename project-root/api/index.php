<?php
require_once __DIR__ . '/_bootstrap.php';

api_apply_common_headers(['GET']);
api_require_auth();

$baseUrl = api_base_url('/api/index.php');

api_send_json([
	'name' => 'Pothen Esxes API',
	'version' => '2.0',
	'description' => 'REST endpoints for declarations, parties, reviews, and session state.',
	'endpoints' => [
		[
			'path' => '/api/declarations.php',
			'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
			'url' => $baseUrl . '/declarations.php',
			'description' => 'Full CRUD for declarations. Supports filters and optional asset rows.',
		],
		[
			'path' => '/api/parties.php',
			'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
			'url' => $baseUrl . '/parties.php',
			'description' => 'Full CRUD for parties (admin required for write actions).',
		],
		[
			'path' => '/api/reviews.php',
			'methods' => ['GET', 'POST'],
			'url' => $baseUrl . '/reviews.php',
			'description' => 'List or create declaration reviews (admin for create).',
		],
		[
			'path' => '/api/stats.php',
			'methods' => ['GET'],
			'url' => $baseUrl . '/stats.php',
			'description' => 'Administrative statistics endpoint (admin session required).',
		],
		[
			'path' => '/api/auth_status.php',
			'methods' => ['GET'],
			'url' => $baseUrl . '/auth_status.php',
			'description' => 'Returns current authentication/session state.',
		],
		[
			'path' => '/api/index.php',
			'methods' => ['GET'],
			'url' => $baseUrl . '/index.php',
			'description' => 'API metadata and endpoint directory.',
		],
	],
]);
