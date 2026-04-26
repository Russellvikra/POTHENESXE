<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();

require_once __DIR__ . '/../includes/db.php';
$activeNav = 'admin';

// Action: Redirect unauthenticated users to login.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}

// Action: Restrict this page to admin users only.
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
        <div class="card-header">
            <h1>Admin Dashboard</h1>
            <p class="card-subtitle">Administrative controls & system management</p>
        </div>

<<<<<<< HEAD
        <div class="stats-grid dashboard-grid">
            <a href="manage_users.php" class="dashboard-card">
                <div class="card-icon users-icon">👥</div>
                <h3>Manage Users</h3>
                <p>Add, edit, and manage user accounts and roles</p>
                <span class="card-arrow">→</span>
            </a>
            <a href="manage_submissions.php" class="dashboard-card">
                <div class="card-icon submissions-icon">📋</div>
                <h3>Manage Submissions</h3>
                <p>Review and manage asset declarations</p>
                <span class="card-arrow">→</span>
            </a>
            <a href="configure.php" class="dashboard-card">
                <div class="card-icon config-icon">⚙️</div>
                <h3>Configure System</h3>
                <p>Set up parties, positions, and system settings</p>
                <span class="card-arrow">→</span>
            </a>
            <a href="reports.php" class="dashboard-card">
                <div class="card-icon reports-icon">📊</div>
                <h3>Reports & Analytics</h3>
                <p>View statistics and system reports</p>
                <span class="card-arrow">→</span>
            </a>
=======
        <div class="stats-grid">
            <article>
                <p class="label">Manage Users</p>
                <p class="value">Users</p>
                <!-- Action: Open user management page. -->
                <a href="manage_users.php" class="clear-link">Open</a>
            </article>
            <article>
                <p class="label">Manage Submissions</p>
                <p class="value">Declarations</p>
                <!-- Action: Open submission management page. -->
                <a href="manage_submissions.php" class="clear-link">Open</a>
            </article>
            <article>
                <p class="label">Configure System</p>
                <p class="value">Settings</p>
                <!-- Action: Open configuration page for parties and politician profiles. -->
                <a href="configure.php" class="clear-link">Open</a>
            </article>
            <article>
                <p class="label">Reports</p>
                <p class="value">Statistics</p>
                <!-- Action: Open reports and analytics page. -->
                <a href="reports.php" class="clear-link">Open</a>
            </article>
>>>>>>> e7daf47 (added comments)
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
