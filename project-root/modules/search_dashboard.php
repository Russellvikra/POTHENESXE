<?php
require_once __DIR__ . '/../includes/db.php';
$summary = $pdo->query('SELECT COUNT(*) AS declarations FROM declarations')->fetch();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Module Dashboard</title>
    <link rel="stylesheet" href="../assets/css/public.css">
</head>
<body>
<main class="page-wrap">
    <section class="hero-card">
        <p><a href="search_dashboard.php" class="clear-link">Search Dashboard</a> | <a href="list.php" class="clear-link">Search</a> | <a href="../auth/register.php" class="clear-link">Register</a> | <a href="stats.php" class="clear-link">Statistics</a></p>
        <h1>Search Module Dashboard</h1>
        <p>Public access area for search, registration, and statistics.</p>
        <p><strong>Total declarations:</strong> <?= (int) $summary['declarations'] ?></p>
        <div class="stats-grid">
            <article><p class="label">Search</p><p class="value"><a href="list.php" class="clear-link">Open</a></p></article>
            <article><p class="label">Register</p><p class="value"><a href="../auth/register.php" class="clear-link">Open</a></p></article>
            <article><p class="label">Statistics</p><p class="value"><a href="stats.php" class="clear-link">Open</a></p></article>
        </div>
    </section>
</main>
</body>
</html>
