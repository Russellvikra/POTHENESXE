<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

function record_login_audit(PDO $pdo, ?int $userId, string $email, string $status): void
{
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $pdo->prepare(
            'INSERT INTO login_audit (user_id, email, login_status, ip_address)
             VALUES (:user_id, :email, :login_status, :ip_address)'
        );
        $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':email', $email !== '' ? $email : null, $email !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':login_status', $status);
        $stmt->bindValue(':ip_address', $ip);
        $stmt->execute();
    } catch (PDOException $e) {
        // Ignore audit failures to avoid breaking authentication.
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php', true, 302);
    exit;
}

$error = '';
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';

// Action: Process login credentials when the form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Λανθασμένα στοιχεία σύνδεσης.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, username, email, password_hash, role FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                record_login_audit($pdo, (int) $user['id'], (string) $user['email'], 'success');
                // Action: Regenerate the session and store user identity after successful login.
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Action: Redirect authenticated user to the home page.
                header('Location: ../index.php', true, 302);
                exit;
            } else {
                record_login_audit($pdo, null, $email, 'failed');
                $error = 'Λανθασμένα στοιχεία σύνδεσης.';
            }
        } catch (PDOException $e) {
            record_login_audit($pdo, null, $email, 'failed');
            $error = 'Λανθασμένα στοιχεία σύνδεσης.';
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

            <!-- Action: Submit login form for authentication. -->
            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
