<?php
require_once __DIR__ . '/includes/session.php';
app_session_start();
require_once __DIR__ . '/includes/db.php';
$activeNav = 'home';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php', true, 302);
    exit;
}

$role = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pothen Esxes - Home</title>
    <link rel="stylesheet" href="./assets/css/header.css">
    <link rel="stylesheet" href="./assets/css/footer.css">
    <link rel="stylesheet" href="./assets/css/home.css">
</head>
<body>
    <?php include './assets/include/header.html'; ?>

    <main>
        <div class="hero">
            <h1>Πόθεν Έσχες</h1>
            <p class="subtitle">Financial Declaration Monitoring System</p>
            <p>Choose one of your available modules below.</p>
        </div>

        <div class="features">
            <div class="feature-card">
                <h3>Search Module</h3>
                <p>Search declarations by year, party, and position.</p>
                <p><a href="modules/list.php">Open Search Module</a></p>
            </div>

            <?php if ($role === 'politician'): ?>
                <div class="feature-card">
                    <h3>Submit Module</h3>
                    <p>Manage your profile and submissions.</p>
                    <p><a href="submit/dashboard.php">Open Submit Module</a></p>
                </div>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <div class="feature-card">
                    <h3>Admin Module</h3>
                    <p>Manage users, submissions, configuration, and reports.</p>
                    <p><a href="admin/admin.php">Open Admin Module</a></p>
                </div>
            <?php endif; ?>

            <div class="feature-card">
                <h3>API Module</h3>
                <p>Access the integration endpoints for third-party systems.</p>
                <p><a href="api/index.php">Open API Module</a></p>
            </div>

        </div>
    </main>

    <?php include './assets/include/footer.html'; ?>
    <script src="./assets/js/header.js"></script>
</body>
</html>