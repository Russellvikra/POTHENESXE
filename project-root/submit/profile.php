<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';
$activeNav = 'submit';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
if (($_SESSION['role'] ?? '') !== 'politician') { http_response_code(403); exit('403 Forbidden'); }

$userId = (int) $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    try {
        if ($username !== '') {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET username = :u
                 WHERE id = :id'
            );
            $stmt->execute([
                'u' => $username,
                'id' => $userId,
            ]);
            $_SESSION['username'] = $username;
            $message = 'Profile updated.';
        }
        if ($newPassword !== '') {
            if (strlen($newPassword) >= 8) {
                $stmt = $pdo->prepare('UPDATE users SET password_hash = :p WHERE id = :id');
                $stmt->execute(['p' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $userId]);
                $message = 'Profile and password updated.';
            } else {
                $message = 'Password must be at least 8 characters.';
            }
        }
    } catch (PDOException $e) {
        $message = 'Update failed.';
    }
}

$stmt = $pdo->prepare('SELECT username, email, role, created_at FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Profile</title><link rel="stylesheet" href="../assets/css/header.css"><link rel="stylesheet" href="../assets/css/submit.css"></head><body>
<?php include '../assets/include/header.html'; ?>
<main class="page-wrap"><section class="card"><h1>My Profile</h1><?php if ($message !== ''): ?><div class="success"><?= esc($message) ?></div><?php endif; ?>
<form method="POST" class="submit-form">
<label>Username</label><input type="text" name="username" value="<?= esc((string)$user['username']) ?>" required>
<label>Email (read-only)</label><input type="email" value="<?= esc((string)$user['email']) ?>" readonly>
<label>New Password (optional)</label><input type="password" name="new_password" placeholder="At least 8 characters">
<button type="submit">Save Profile</button>
</form>
</section></main></body></html>
<script src="../assets/js/header.js"></script>
