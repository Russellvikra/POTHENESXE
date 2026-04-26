<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';
$activeNav = 'submit';
// Action: Redirect unauthenticated users to the login page.
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php', true, 302); exit; }
// Action: Restrict profile management to users with the politician role.
if (($_SESSION['role'] ?? '') !== 'politician') { http_response_code(403); exit('403 Forbidden'); }

$userId = (int) $_SESSION['user_id'];
$message = '';

// Action: Handle profile updates when the form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    try {
        if ($username !== '') {
            // Action: Save the updated username in the users table.
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
                // Action: Hash and store the new password securely.
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
<main class="page-wrap"><section class="card">
<div class="card-header">
    <h1>My Profile</h1>
    <p class="card-subtitle">Update your account information</p>
</div>
<?php if ($message !== ''): ?><div class="alert alert-success"><?= esc($message) ?></div><?php endif; ?>
<form method="POST" class="submit-form">
<<<<<<< HEAD
<div class="form-section">
    <h2>Account Information</h2>
    <div class="form-row">
        <div>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= esc((string)$user['username']) ?>" required>
        </div>
        <div>
            <label for="email">Email (read-only)</label>
            <input type="email" id="email" value="<?= esc((string)$user['email']) ?>" readonly>
        </div>
    </div>
</div>
<div class="form-section">
    <h2>Security</h2>
    <div class="form-row">
        <div>
            <label for="new-password">New Password (optional)</label>
            <input type="password" id="new-password" name="new_password" placeholder="Leave blank to keep current password">
            <small class="form-help">Minimum 8 characters if changing</small>
        </div>
    </div>
</div>
<div class="form-actions">
    <button type="submit" class="btn btn-primary">Save Profile</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
</div>
=======
<label>Username</label><input type="text" name="username" value="<?= esc((string)$user['username']) ?>" required>
<label>Email (read-only)</label><input type="email" value="<?= esc((string)$user['email']) ?>" readonly>
<label>New Password (optional)</label><input type="password" name="new_password" placeholder="At least 8 characters">
<!-- Action: Submit profile changes for saving. -->
<button type="submit">Save Profile</button>
>>>>>>> e7daf47 (added comments)
</form>
</section></main></body></html>
<script src="../assets/js/header.js"></script>
