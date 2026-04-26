<?php
session_start();
// Action: Redirect unauthenticated users to login.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
$activeNav = 'search';

$keyword = trim($_GET['keyword'] ?? '');

$declarations = [];
$searchPerformed = $keyword !== '';

// Action: Run declaration search when a keyword is provided.
if ($searchPerformed) {
    try {
        $stmt = $pdo->prepare(
            'SELECT d.id, d.title, d.details, d.year, d.created_at, u.username
             FROM declarations d
             INNER JOIN users u ON u.id = d.user_id
             WHERE d.title LIKE :kw OR d.details LIKE :kw OR CAST(d.year AS CHAR) LIKE :kw OR u.username LIKE :kw
             ORDER BY d.created_at DESC, d.id DESC'
        );

        $keywordParam = '%' . $keyword . '%';
        $stmt->execute(['kw' => $keywordParam]);
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
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/list.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
    <?php include '../assets/include/header.html'; ?>
    <div class="container">
        <div class="card">
            <h1>Search Declarations</h1>
            <p class="meta">Find declarations by keyword, title, details, user, or year.</p>

            <form method="GET" class="search-box">
                <input type="text" name="keyword" placeholder="Search by keyword, title, or year..." value="<?= htmlspecialchars($keyword) ?>">
                <!-- Action: Submit search keyword and refresh result list. -->
                <button type="submit">Search</button>
                <?php if (!empty($keyword)): ?>
                    <!-- Action: Clear current keyword and reset search results. -->
                    <a href="list.php" class="clear-btn" style="display: inline-block; text-decoration: none; color: #fff; padding: 10px 16px;">Clear</a>
                <?php endif; ?>
            </form>

            <?php if ($searchPerformed): ?>
                <?php if (count($declarations) === 0): ?>
                    <div class="no-results">
                        <p>No declarations found for <strong><?= htmlspecialchars($keyword) ?></strong></p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Title</th>
                                <th>Year</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($declarations as $decl): ?>
                                <tr>
                                    <!-- Action: Open details page for this declaration record. -->
                                    <td><a href="../modules/declaration.php?id=<?= (int) $decl['id'] ?>">#<?= (int) $decl['id'] ?></a></td>
                                    <td><?= htmlspecialchars($decl['username'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($decl['title'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars((string) $decl['year']) ?></td>
                                    <td><?= htmlspecialchars((string) $decl['details']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <p class="meta" style="margin-top: 16px;">
                        Found <?= count($declarations) ?> declaration<?= count($declarations) !== 1 ? 's' : '' ?>.
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Enter a keyword to search declarations.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include '../assets/include/footer.html'; ?>
    <script src="../assets/js/header.js"></script>
</body>
</html>
