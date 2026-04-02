<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pothen Esxes - Home</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
    <?php include 'assets/include/header.html'; ?>

    <main>
        <div class="hero">
            <h1>Πόθεν Έσχες</h1>
            <p class="subtitle">Financial Declaration Monitoring System</p>
            <p>Access, search, and analyze financial declarations of public officials in Cyprus.</p>
            
            <div class="cta-group">
                <a href="auth/register.php" class="btn btn-primary">Register Now</a>
                <a href="auth/login.php" class="btn btn-secondary">Already have an account? Login</a>
            </div>
        </div>

        <div class="features">
            <div class="feature-card">
                <h3>Search Module</h3>
                <p>Public dashboard for search, registration and statistics.</p>
                <p><a href="modules/search_dashboard.php">Go to Search Module</a></p>
            </div>
            <div class="feature-card">
                <h3>Submit Module</h3>
                <p>Dashboard for politicians to manage profile and submissions.</p>
                <p><a href="submit/dashboard.php">Go to Submit Module</a></p>
            </div>
            <div class="feature-card">
                <h3>Admin Module</h3>
                <p>Administration dashboard for users, submissions, configuration and reports.</p>
                <p><a href="admin/admin.php">Go to Admin Module</a></p>
            </div>

        </div>
    </main>

    <?php include 'assets/include/footer.html'; ?>
    <script src="assets/js/header.js"></script>
</body>
</html>