<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
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
        <h1>Submit Module Dashboard</h1>
        <p>Choose an action.</p>
        <div class="row two-col">
            <a href="profile.php" class="clear-link">My Profile</a>
            <a href="my_submissions.php" class="clear-link">My Submissions</a>
        </div>
    </section>
</main>
<script src="../assets/js/header.js"></script>
</body>
</html>
