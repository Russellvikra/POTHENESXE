<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
if (!in_array($_SESSION['role'] ?? '', ['politician', 'admin'], true)) { http_response_code(403); exit('403 Forbidden'); }

$userId = (int) $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    try {
        if ($username !== '' && $firstName !== '' && $lastName !== '') {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET username = :u, first_name = :first_name, last_name = :last_name, phone = :phone
                 WHERE id = :id'
            );
            $stmt->execute([
                'u' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => ($phone !== '' ? $phone : null),
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

$stmt = $pdo->prepare('SELECT username, first_name, last_name, phone, email, role, created_at FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Profile</title><link rel="stylesheet" href="../assets/css/submit.css"></head><body>
<main class="page-wrap"><section class="card"><p><a href="dashboard.php" class="clear-link">Submit Dashboard</a> | <a href="profile.php" class="clear-link">My Profile</a> | <a href="my_submissions.php" class="clear-link">My Submissions</a></p><h1>My Profile</h1><p><a href="dashboard.php" class="clear-link">Back to Submit Dashboard</a></p><?php if ($message !== ''): ?><div class="success"><?= esc($message) ?></div><?php endif; ?>
<form method="POST" class="submit-form">
<label>Username</label><input type="text" name="username" value="<?= esc((string)$user['username']) ?>" required>
<label>First Name</label><input type="text" name="first_name" value="<?= esc((string)($user['first_name'] ?? '')) ?>" required>
<label>Last Name</label><input type="text" name="last_name" value="<?= esc((string)($user['last_name'] ?? '')) ?>" required>
<label>Phone</label><input type="text" name="phone" value="<?= esc((string)($user['phone'] ?? '')) ?>" placeholder="e.g. 99000000">
<label>Email (read-only)</label><input type="email" value="<?= esc((string)$user['email']) ?>" readonly>
<label>New Password (optional)</label><input type="password" name="new_password" placeholder="At least 8 characters">
<button type="submit">Save Profile</button>
</form>
</section></main></body></html>
