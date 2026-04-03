<?php
require_once __DIR__ . '/../includes/db.php';

$keyword = trim($_GET['keyword'] ?? '');
$status = trim($_GET['status'] ?? '');
$status = in_array($status, ['draft', 'submitted'], true) ? $status : '';

$where = [];
$params = [];

if ($keyword !== '') {
    $where[] = '(u.username LIKE :keyword OR p.position LIKE :keyword OR pa.name LIKE :keyword)';
    $params['keyword'] = '%' . $keyword . '%';
}

if ($status !== '') {
    $where[] = 'd.status = :status';
    $params['status'] = $status;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stats = [
    'total_declarations' => 0,
    'submitted_declarations' => 0,
    'total_assets_value' => 0.0,
];

$declarations = [];

try {
    $statsStmt = $pdo->query(
        'SELECT COUNT(*) AS total_declarations,
                SUM(CASE WHEN status = "submitted" THEN 1 ELSE 0 END) AS submitted_declarations
         FROM declarations'
    );
    $statsData = $statsStmt->fetch();
    if ($statsData) {
        $stats['total_declarations'] = (int) $statsData['total_declarations'];
        $stats['submitted_declarations'] = (int) $statsData['submitted_declarations'];
    }

    $assetTotalStmt = $pdo->query('SELECT COALESCE(SUM(value), 0) AS total_assets_value FROM assets');
    $assetTotalData = $assetTotalStmt->fetch();
    if ($assetTotalData) {
        $stats['total_assets_value'] = (float) $assetTotalData['total_assets_value'];
    }

    $listStmt = $pdo->prepare(
        'SELECT d.id, d.year, d.status, d.created_at,
                u.username, p.position, pa.name AS party_name,
                COALESCE(SUM(a.value), 0) AS declaration_total
         FROM declarations d
         INNER JOIN politicians p ON p.id = d.politician_id
         INNER JOIN users u ON u.id = p.user_id
         LEFT JOIN parties pa ON pa.id = p.party_id
         LEFT JOIN assets a ON a.declaration_id = d.id
         ' . $whereSql . '
         GROUP BY d.id, d.year, d.status, d.created_at, u.username, p.position, pa.name
         ORDER BY d.created_at DESC, d.id DESC
         LIMIT 50'
    );

    $listStmt->execute($params);
    $declarations = $listStmt->fetchAll();
} catch (PDOException $e) {
    $declarations = [];
}

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Declarations</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/public.css">
</head>
<body>
<header>
    <div class="header-container">
        <nav class="navbar">
            <a href="../index.php" class="navbar-brand">Pothen Esxes</a>
            <button class="navbar-burger" id="burger" onclick="toggleMenu()" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <ul class="navbar-menu" id="nav-links">
                <li><a href="public.php" class="active">Public</a></li>
                <li><a href="../submit/submit.php">Submit</a></li>
                <li><a href="../api/index.php">API</a></li>
                <li><a href="../modules/list.php">Search</a></li>
                <li><a href="../modules/declaration.php">Declaration</a></li>
                <li><a href="../auth/login.php">Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="page-wrap">
    <section class="hero-card">
        <h1>Public Registry</h1>
        <p>Browse published and draft declarations for transparency and quick public lookup.</p>
        <div class="stats-grid">
            <article>
                <p class="label">Declarations</p>
                <p class="value"><?= $stats['total_declarations'] ?></p>
            </article>
            <article>
                <p class="label">Submitted</p>
                <p class="value"><?= $stats['submitted_declarations'] ?></p>
            </article>
            <article>
                <p class="label">Declared Assets</p>
                <p class="value">EUR <?= number_format($stats['total_assets_value'], 2) ?></p>
            </article>
        </div>
    </section>

    <section class="card">
        <h2>Filter Declarations</h2>
        <form method="GET" class="filters">
            <input
                type="text"
                name="keyword"
                value="<?= esc($keyword) ?>"
                placeholder="Search by name, position, or party"
            >
            <select name="status">
                <option value="">All statuses</option>
                <option value="submitted" <?= $status === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
            <button type="submit">Apply</button>
            <a href="public.php" class="clear-link">Clear</a>
        </form>
    </section>

    <section class="card">
        <div class="list-head">
            <h2>Recent Records</h2>
            <p><?= count($declarations) ?> result<?= count($declarations) === 1 ? '' : 's' ?></p>
        </div>

        <?php if (count($declarations) === 0): ?>
            <p class="empty">No declarations match the selected filters.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Official</th>
                            <th>Party</th>
                            <th>Position</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Assets Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($declarations as $row): ?>
                            <tr>
                                <td><a href="../modules/declaration.php?id=<?= (int) $row['id'] ?>">#<?= (int) $row['id'] ?></a></td>
                                <td><?= esc((string) $row['username']) ?></td>
                                <td><?= esc((string) ($row['party_name'] ?? 'N/A')) ?></td>
                                <td><?= esc((string) ($row['position'] ?? 'N/A')) ?></td>
                                <td><?= esc((string) $row['year']) ?></td>
                                <td>
                                    <span class="status status-<?= esc((string) $row['status']) ?>">
                                        <?= esc((string) $row['status']) ?>
                                    </span>
                                </td>
                                <td>EUR <?= number_format((float) $row['declaration_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer>
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="../index.php" class="footer-logo">Pothen Esxes</a>
                <p>Public registry and search for financial declaration records.</p>
            </div>
            <div class="footer-section">
                <h4>Pages</h4>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="public.php">Public</a></li>
                    <li><a href="../modules/list.php">Search</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Account</h4>
                <ul>
                    <li><a href="../auth/login.php">Login</a></li>
                    <li><a href="../auth/register.php">Register</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Project</h4>
                <ul>
                    <li><a href="../submit/submit.php">Submit</a></li>
                    <li><a href="../admin/admin.php">Admin</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<script src="../assets/js/header.js"></script>
</body>
</html>
