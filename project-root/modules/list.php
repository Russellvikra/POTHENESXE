<?php
require_once __DIR__ . '/../includes/db.php';

$keyword = trim($_GET['keyword'] ?? '');
$status = trim($_GET['status'] ?? '');
$status = in_array($status, ['draft', 'submitted'], true) ? $status : '';
$order = ($_GET['order'] ?? 'newest') === 'oldest' ? 'ASC' : 'DESC';
$declarations = [];
$searchPerformed = false;

if (!empty($keyword) || $status !== '') {
    $searchPerformed = true;
    try {
        $where = ['(u.username LIKE :kw OR pa.name LIKE :kw OR p.position LIKE :kw)'];
        if ($status !== '') {
            $where[] = 'd.status = :status';
        }

        $stmt = $pdo->prepare(
            'SELECT d.id, d.year, d.status, d.created_at,
                    u.username, p.position, pa.name AS party_name
             FROM declarations d
             INNER JOIN politicians p ON p.id = d.politician_id
             INNER JOIN users u ON u.id = p.user_id
             LEFT JOIN parties pa ON pa.id = p.party_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY d.created_at ' . $order . ', d.id ' . $order
        );

        $keywordParam = '%' . $keyword . '%';
        $params = ['kw' => $keywordParam];
        if ($status !== '') {
            $params['status'] = $status;
        }
        $stmt->execute($params);
        $declarations = $stmt->fetchAll();
    } catch (PDOException $e) {
        $declarations = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Declarations</title>
    <link rel="stylesheet" href="../assets/css/list.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <p><a href="search_dashboard.php" class="nav-link">Search Dashboard</a> | <a href="list.php" class="nav-link">Search</a> | <a href="../auth/register.php" class="nav-link">Register</a> | <a href="stats.php" class="nav-link">Statistics</a></p>
            <a href="search_dashboard.php" class="nav-link">← Back to Dashboard</a>
            <h1>Search Declarations</h1>
            <p class="meta">Find declarations by official name, party, or position.</p>

            <form method="GET" class="search-box">
                <input type="text" name="keyword" placeholder="Search by name, party, or position..." value="<?= htmlspecialchars($keyword) ?>">
                <select name="status">
                    <option value="">All status</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="submitted" <?= $status === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                </select>
                <select name="order">
                    <option value="newest" <?= $order === 'DESC' ? 'selected' : '' ?>>Newest</option>
                    <option value="oldest" <?= $order === 'ASC' ? 'selected' : '' ?>>Oldest</option>
                </select>
                <button type="submit">Search</button>
                <?php if (!empty($keyword)): ?>
                    <a href="list.php" class="clear-btn" style="display: inline-block; text-decoration: none; color: #fff; padding: 10px 16px;">Clear</a>
                <?php endif; ?>
            </form>

            <?php if ($searchPerformed): ?>
                <?php if (count($declarations) === 0): ?>
                    <div class="no-results">
                        <p>No declarations found for <strong><?= htmlspecialchars($keyword) ?></strong></p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Official</th>
                                <th>Party</th>
                                <th>Position</th>
                                <th>Year</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($declarations as $decl): ?>
                                <tr>
                                    <td><a href="../modules/declaration.php?id=<?= (int) $decl['id'] ?>">#<?= (int) $decl['id'] ?></a></td>
                                    <td><?= htmlspecialchars($decl['username']) ?></td>
                                    <td><?= htmlspecialchars($decl['party_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($decl['position'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars((string) $decl['year']) ?></td>
                                    <td>
                                        <span class="status-pill status-<?= htmlspecialchars((string) $decl['status']) ?>">
                                            <?= htmlspecialchars((string) $decl['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="meta" style="margin-top: 16px;">
                        Found <?= count($declarations) ?> declaration<?= count($declarations) !== 1 ? 's' : '' ?>.
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Enter keyword or status filter to search declarations.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
