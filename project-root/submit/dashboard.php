<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
// Action: Redirect unauthenticated users to the login page.
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
// Action: Restrict dashboard access to users with the politician role.
if (($_SESSION['role'] ?? '') !== 'politician') { http_response_code(403); exit('403 Forbidden'); }
$activeNav = 'submit';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Dashboard</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/submit.css">
</head>
<body>
<?php include '../assets/include/header.html'; ?>
<main class="page-wrap">
    <section class="card">
        <div class="card-header">
            <h1>Declaration Portal</h1>
            <p class="card-subtitle">Manage your asset declarations</p>
        </div>

        <div class="dashboard-grid dashboard-submit">
            <a href="submit.php" class="dashboard-card">
                <div class="card-icon">📄</div>
                <h3>New Declaration</h3>
                <p>Create and submit a new asset declaration</p>
                <span class="card-arrow">→</span>
            </a>
            <a href="my_submissions.php" class="dashboard-card">
                <div class="card-icon">📋</div>
                <h3>My Submissions</h3>
                <p>View and manage your declaration history</p>
                <span class="card-arrow">→</span>
            </a>
            <a href="profile.php" class="dashboard-card">
                <div class="card-icon">👤</div>
                <h3>My Profile</h3>
                <p>Edit your profile and password</p>
                <span class="card-arrow">→</span>
            </a>
        </div>
    </section>
</main>
<script src="../assets/js/header.js"></script>
</body>
</html>
