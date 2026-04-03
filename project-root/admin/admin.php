<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();

require_once __DIR__ . '/../includes/db.php';
$activeNav = 'admin';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo '<h1>403 Forbidden</h1><p>Admin access only.</p>';
    exit;
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
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php include '../assets/include/header.html'; ?>

<main class="page-wrap">
    <section class="card">
        <h1>Admin Dashboard</h1>
        <p>Choose an administration section.</p>

        <div class="stats-grid">
            <article>
                <p class="label">Manage Users</p>
                <p class="value">Users</p>
                <a href="manage_users.php" class="clear-link">Open</a>
            </article>
            <article>
                <p class="label">Manage Submissions</p>
                <p class="value">Declarations</p>
                <a href="manage_submissions.php" class="clear-link">Open</a>
            </article>
            <article>
                <p class="label">Configure System</p>
                <p class="value">Settings</p>
                <a href="configure.php" class="clear-link">Open</a>
            </article>
            <article>
                <p class="label">Reports</p>
                <p class="value">Statistics</p>
                <a href="reports.php" class="clear-link">Open</a>
            </article>
        </div>
    </section>
</main>

<footer>
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="../index.php" class="footer-logo">Pothen Esxes</a>
                <p>Administrative controls for declaration governance.</p>
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
                <h4>Admin</h4>
                <ul>
                    <li><a href="admin.php">Dashboard</a></li>
                    <li><a href="../modules/declaration.php">Declaration</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Account</h4>
                <ul>
                    <li><a href="../submit/dashboard.php">Submit Dashboard</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<script src="../assets/js/header.js"></script>
</body>
</html>
