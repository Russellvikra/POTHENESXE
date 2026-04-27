<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';
$activeNav = 'declaration';

// Action: Redirect unauthenticated users to login.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}

$declarationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$declaration = null;
$assets = [];
$recentDeclarations = [];
$errorMessage = '';

// Action: If an ID is provided, load that declaration; otherwise load recent declarations.
if ($declarationId) {
    $statement = $pdo->prepare(
        'SELECT d.id, d.user_id, d.year, d.status, d.created_at, d.title, d.details,
                u.username,
                p.position,
                pa.name AS party_name
         FROM declarations d
         INNER JOIN users u ON u.id = d.user_id
         INNER JOIN politicians p ON p.id = d.politician_id
         LEFT JOIN parties pa ON pa.id = p.party_id
         WHERE d.id = :declaration_id'
    );

    $statement->execute(['declaration_id' => $declarationId]);
    $declaration = $statement->fetch();

    if (!$declaration) {
        $errorMessage = 'Declaration was not found.';
    } else {
        // Action: Load all assets for this declaration
        $assetsStatement = $pdo->prepare(
            'SELECT id, type, description, value FROM assets WHERE declaration_id = :declaration_id ORDER BY id ASC'
        );
        $assetsStatement->execute(['declaration_id' => $declarationId]);
        $assets = $assetsStatement->fetchAll();
    }
} else {
    $recentStatement = $pdo->prepare(
        'SELECT d.id, d.year, d.status, p.position, pa.name AS party_name
         FROM declarations d
         INNER JOIN politicians p ON p.id = d.politician_id
         LEFT JOIN parties pa ON pa.id = p.party_id
         ORDER BY d.created_at DESC, d.id DESC
         LIMIT 10'
    );
    $recentStatement->execute();

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
    <?php include '../assets/include/header.html'; ?>

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
                    <p class="meta-label">Submitted By</p>
                    <p class="meta-value"><?= esc((string) ($declaration['username'] ?? 'N/A')) ?></p>
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
                <div>
                    <p class="meta-label">Submitted On</p>
                    <p class="meta-value"><?= esc((string) $declaration['created_at']) ?></p>
                </div>
            </section>

            <section class="card">
                <div class="card-title-row">
                    <h2>Assets</h2>
                </div>
                <?php if (count($assets) === 0): ?>
                    <p class="muted">No assets recorded for this declaration.</p>
                <?php else: ?>
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
                                <?php 
                                $totalValue = 0;
                                foreach ($assets as $asset): 
                                    $totalValue += (float) $asset['value'];
                                ?>
                                    <tr>
                                        <td><?= esc((string) $asset['type']) ?></td>
                                        <td><?= esc((string) $asset['description']) ?></td>
                                        <td><?= number_format((float) $asset['value'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="font-weight: bold; border-top: 2px solid #ddd;">
                                    <td colspan="2">Total Assets Value</td>
                                    <td><?= number_format($totalValue, 2) ?></td>
                                </tr>
                            </tbody>
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
                                    <span><?= esc((string) ($recent['party_name'] ?? 'N/A')) ?> - <?= esc((string) ($recent['position'] ?? 'N/A')) ?> - <?= esc((string) $recent['year']) ?></span>
                                </div>
                                <!-- Action: Open details page for selected declaration. -->
                                <a href="declaration.php?id=<?= (int) $recent['id'] ?>" class="btn-link">Open</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php include '../assets/include/footer.html'; ?>
    <script src="../assets/js/header.js"></script>
</body>
</html>
