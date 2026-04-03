<?php
require_once __DIR__ . '/../includes/db.php';

$declarationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$declaration = null;
$assets = [];
$recentDeclarations = [];
$errorMessage = '';

if ($declarationId) {
    $statement = $pdo->prepare(
        'SELECT d.id, d.year, d.status, d.created_at,
                p.position,
                u.username,
                pa.name AS party_name
         FROM declarations d
         INNER JOIN politicians p ON p.id = d.politician_id
         INNER JOIN users u ON u.id = p.user_id
         LEFT JOIN parties pa ON pa.id = p.party_id
         WHERE d.id = :declaration_id'
    );

    $statement->execute(['declaration_id' => $declarationId]);
    $declaration = $statement->fetch();

    if ($declaration) {
        $assetsStatement = $pdo->prepare(
            'SELECT type, description, value
             FROM assets
             WHERE declaration_id = :declaration_id
             ORDER BY id ASC'
        );

        $assetsStatement->execute(['declaration_id' => $declarationId]);
        $assets = $assetsStatement->fetchAll();
    } else {
        $errorMessage = 'Declaration was not found.';
    }
} else {
    $recentStatement = $pdo->query(
        'SELECT d.id, d.year, d.status, u.username
         FROM declarations d
         INNER JOIN politicians p ON p.id = d.politician_id
         INNER JOIN users u ON u.id = p.user_id
         ORDER BY d.created_at DESC, d.id DESC
         LIMIT 10'
    );

    $recentDeclarations = $recentStatement->fetchAll();
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
    <title>Declaration Details</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/declaration.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
    <header>
        <div class="header-container">
            <nav class="navbar">
                <a href="../index.php" class="navbar-brand">Πόθεν Έσχες</a>
                <button class="navbar-burger" id="burger" onclick="toggleMenu()" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
                <ul class="navbar-menu" id="nav-links">
                    <li><a href="search_dashboard.php">Search Module</a></li>
                    <li><a href="../submit/dashboard.php">Submit Module</a></li>
                    <li><a href="../api/index.php">API</a></li>
                    <li><a href="list.php">Search</a></li>
                    <li><a href="declaration.php" class="active">Declaration</a></li>
                    <li class="navbar-divider"></li>
                    <li><a href="../admin/admin.php">Admin Module</a></li>
                    <li><a href="../auth/register.php">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="page-wrap">
        <section class="page-head">
            <p class="eyebrow">Financial Declaration</p>
            <h1>Declaration Page</h1>
            <p>View declaration information and the registered asset records for each submission.</p>
        </section>

        <?php if ($errorMessage !== ''): ?>
            <section class="card card-error">
                <h2>Not Found</h2>
                <p><?= esc($errorMessage) ?></p>
                <a href="declaration.php" class="btn-link">Browse recent declarations</a>
            </section>
        <?php elseif ($declaration): ?>
            <section class="card declaration-summary">
                <div>
                    <p class="meta-label">Declaration ID</p>
                    <p class="meta-value">#<?= (int) $declaration['id'] ?></p>
                </div>
                <div>
                    <p class="meta-label">Official</p>
                    <p class="meta-value"><?= esc($declaration['username']) ?></p>
                </div>
                <div>
                    <p class="meta-label">Party</p>
                    <p class="meta-value"><?= esc($declaration['party_name'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <p class="meta-label">Position</p>
                    <p class="meta-value"><?= esc($declaration['position'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <p class="meta-label">Year</p>
                    <p class="meta-value"><?= esc((string) $declaration['year']) ?></p>
                </div>
                <div>
                    <p class="meta-label">Status</p>
                    <p class="meta-value status-pill status-<?= esc((string) $declaration['status']) ?>"><?= esc((string) $declaration['status']) ?></p>
                </div>
            </section>

            <section class="card">
                <div class="card-title-row">
                    <h2>Assets</h2>
                    <p class="muted">Created: <?= esc((string) $declaration['created_at']) ?></p>
                </div>

                <?php if (count($assets) === 0): ?>
                    <p class="muted">No assets are registered for this declaration.</p>
                <?php else: ?>
                    <?php
                    $totalValue = 0.0;
                    foreach ($assets as $assetRow) {
                        $totalValue += (float) $assetRow['value'];
                    }
                    ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Value (EUR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assets as $asset): ?>
                                    <tr>
                                        <td><?= esc((string) $asset['type']) ?></td>
                                        <td><?= esc((string) $asset['description']) ?></td>
                                        <td><?= number_format((float) $asset['value'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td><?= number_format($totalValue, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <section class="card">
                <h2>Recent Declarations</h2>
                <p class="muted">Select one declaration to open its details page.</p>

                <?php if (count($recentDeclarations) === 0): ?>
                    <p class="muted">No declarations found in database.</p>
                <?php else: ?>
                    <ul class="declaration-list">
                        <?php foreach ($recentDeclarations as $recent): ?>
                            <li>
                                <div>
                                    <strong>#<?= (int) $recent['id'] ?></strong>
                                    <span><?= esc((string) $recent['username']) ?> - <?= esc((string) $recent['year']) ?></span>
                                </div>
                                <a href="declaration.php?id=<?= (int) $recent['id'] ?>" class="btn-link">Open</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="../index.php" class="footer-logo">Πόθεν Έσχες</a>
                    <p>Παρακολούθηση και αναζήτηση των δηλώσεων πόθεν έσχες των Αξιωματούχων της Κυπριακής Δημοκρατίας.</p>
                </div>
                <div class="footer-section">
                    <h4>Pages</h4>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="list.php">Search</a></li>
                        <li><a href="declaration.php">Declaration</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="../auth/register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>External</h4>
                    <ul>
                        <li><a href="https://www.parliament.cy/el/ποθεν-εσχες" target="_blank" rel="noopener">Parliament</a></li>
                        <li><a href="https://github.com/Russellvikra/CSE-326" target="_blank" rel="noopener">Repository</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 Πόθεν Έσχες.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/header.js"></script>
</body>
</html>
