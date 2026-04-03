<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}
$activeNav = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../assets/include/header.html'; ?>
    <div class="container">
        <div class="card">
            <h1>Dashboard</h1>
            <p class="meta">Welcome back!</p>

            <div class="user-info">
                <div class="info-box">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?= htmlspecialchars($_SESSION['username']) ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">Role</div>
                    <div class="info-value"><?= htmlspecialchars($_SESSION['role']) ?></div>
                </div>
            </div>

            <a href="list.php">View Declarations</a>
            <a href="../auth/logout.php" style="background: #c82c3b; margin-left: 10px;">Logout</a>
        </div>
    </div>
    <script src="../assets/js/header.js"></script>
</body>
</html>
