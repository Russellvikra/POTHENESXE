<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();

require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}

if (($_SESSION['role'] ?? '') !== 'politician') {
    http_response_code(403);
    exit('403 Forbidden');
}

$errors = [];
$activeNav = 'submit';
$successId = filter_input(INPUT_GET, 'success_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 2000, 'max_range' => 2100],
    ]);
    $status = $_POST['status'] ?? 'draft';
    $status = in_array($status, ['draft', 'submitted'], true) ? $status : 'draft';

    $assetTypes = $_POST['asset_type'] ?? [];
    $assetDescriptions = $_POST['asset_description'] ?? [];
    $assetValues = $_POST['asset_value'] ?? [];

    if (!$year) {
        $errors[] = 'Please provide a valid declaration year.';
    }

    $assetRows = [];
    $assetCount = max(count($assetTypes), count($assetDescriptions), count($assetValues));

    for ($i = 0; $i < $assetCount; $i++) {
        $type = trim((string) ($assetTypes[$i] ?? ''));
        $description = trim((string) ($assetDescriptions[$i] ?? ''));
        $valueRaw = trim((string) ($assetValues[$i] ?? ''));

        if ($type === '' && $description === '' && $valueRaw === '') {
            continue;
        }

        if ($type === '' || $description === '' || $valueRaw === '') {
            $errors[] = 'Every asset row must include type, description, and value.';
            continue;
        }

        if (!is_numeric($valueRaw) || (float) $valueRaw < 0) {
            $errors[] = 'Asset values must be numeric and zero or positive.';
            continue;
        }

        $assetRows[] = [
            'type' => $type,
            'description' => $description,
            'value' => (float) $valueRaw,
        ];
    }

    if (count($assetRows) === 0) {
        $errors[] = 'Add at least one asset before submitting a declaration.';
    }

    if (count($errors) === 0) {
        try {
            $pdo->beginTransaction();

            $profileStmt = $pdo->prepare('SELECT id FROM politicians WHERE user_id = :user_id LIMIT 1');
            $profileStmt->execute(['user_id' => (int) $_SESSION['user_id']]);
            $politician = $profileStmt->fetch();

            if ($politician) {
                $politicianId = (int) $politician['id'];
            } else {
                $insertPoliticianStmt = $pdo->prepare(
                    'INSERT INTO politicians (user_id, party_id, position) VALUES (:user_id, :party_id, :position)'
                );
                $insertPoliticianStmt->execute([
                    'user_id' => (int) $_SESSION['user_id'],
                    'party_id' => null,
                    'position' => 'Not specified',
                ]);
                $politicianId = (int) $pdo->lastInsertId();
            }

            $insertDeclarationStmt = $pdo->prepare(
                'INSERT INTO declarations (user_id, politician_id, year, status) VALUES (:user_id, :politician_id, :year, :status)'
            );
            $insertDeclarationStmt->execute([
                'user_id' => (int) $_SESSION['user_id'],
                'politician_id' => $politicianId,
                'year' => (int) $year,
                'status' => $status,
            ]);

            $declarationId = (int) $pdo->lastInsertId();

            $insertAssetStmt = $pdo->prepare(
                'INSERT INTO assets (declaration_id, type, description, value)
                 VALUES (:declaration_id, :type, :description, :value)'
            );

            foreach ($assetRows as $asset) {
                $insertAssetStmt->execute([
                    'declaration_id' => $declarationId,
                    'type' => $asset['type'],
                    'description' => $asset['description'],
                    'value' => $asset['value'],
                ]);
            }

            $pdo->commit();
            header('Location: submit.php?success_id=' . $declarationId, true, 302);
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Could not submit declaration. Please try again.';
        }
    }
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
    <title>Submit Declaration</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/submit.css">
</head>
<body>
<?php include '../assets/include/header.html'; ?>
<main class="page-wrap">
    <section class="card">
        <h1>Submit Declaration</h1>
        <p>Create a new declaration and include one or more asset records.</p>

        <?php if ($successId): ?>
            <div class="success">
                Declaration #<?= $successId ?> was saved successfully.
                <a href="../modules/declaration.php?id=<?= $successId ?>">Open declaration</a>
            </div>
        <?php endif; ?>

        <?php if (count($errors) > 0): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="submit-form">
            <div class="row two-col">
                <div>
                    <label for="year">Declaration Year</label>
                    <input
                        type="number"
                        id="year"
                        name="year"
                        min="2000"
                        max="2100"
                        value="<?= esc((string) ($_POST['year'] ?? date('Y'))) ?>"
                        required
                    >
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="draft" <?= (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="submitted" <?= (($_POST['status'] ?? '') === 'submitted') ? 'selected' : '' ?>>Submitted</option>
                    </select>
                </div>
            </div>

            <h2>Assets</h2>
            <p class="hint">Add each asset on a separate row.</p>

            <div class="assets-grid assets-head">
                <span>Type</span>
                <span>Description</span>
                <span>Value (EUR)</span>
            </div>

            <?php
            $postedTypes = $_POST['asset_type'] ?? ['deposit', '', ''];
            $postedDescriptions = $_POST['asset_description'] ?? ['', '', ''];
            $postedValues = $_POST['asset_value'] ?? ['', '', ''];
            $rows = max(count($postedTypes), 3);
            for ($i = 0; $i < $rows; $i++):
            ?>
                <div class="assets-grid">
                    <input type="text" name="asset_type[]" value="<?= esc((string) ($postedTypes[$i] ?? '')) ?>" placeholder="e.g. house">
                    <input type="text" name="asset_description[]" value="<?= esc((string) ($postedDescriptions[$i] ?? '')) ?>" placeholder="Asset details">
                    <input type="number" name="asset_value[]" value="<?= esc((string) ($postedValues[$i] ?? '')) ?>" min="0" step="0.01" placeholder="0.00">
                </div>
            <?php endfor; ?>

            <button type="submit">Save Declaration</button>
        </form>
    </section>
</main>

<footer>
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="../index.php" class="footer-logo">Pothen Esxes</a>
                <p>Submit declarations with structured asset records.</p>
            </div>
            <div class="footer-section">
                <h4>Pages</h4>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../modules/list.php">Search Module</a></li>
                    <li><a href="../submit/dashboard.php">Submit Module</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Account</h4>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<script src="../assets/js/header.js"></script>
</body>
</html>
