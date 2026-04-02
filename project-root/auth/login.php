<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: ../admin/admin.php', true, 302);
    } elseif (($_SESSION['role'] ?? '') === 'politician') {
        header('Location: ../submit/dashboard.php', true, 302);
    } else {
        header('Location: ../modules/search_dashboard.php', true, 302);
    }
    exit;
}

$error = '';
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Incorrect login details.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, username, email, password_hash, role FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: ../admin/admin.php', true, 302);
                } elseif ($user['role'] === 'politician') {
                    header('Location: ../submit/dashboard.php', true, 302);
                } else {
                    header('Location: ../modules/search_dashboard.php', true, 302);
                }
                exit;
            } else {
                $error = 'Incorrect login details.';
            }
        } catch (PDOException $e) {
            $error = 'Incorrect login details.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>

        <?php if ($registered): ?>
            <div class="success">
                <strong>Registration successful!</strong> You can now login.
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
