<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('403 Forbidden');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $password = $_POST['password'] ?? '';

            if ($username !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 8) {
                $role = in_array($role, ['admin', 'politician', 'user'], true) ? $role : 'user';
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:u,:e,:p,:r)');
                $stmt->execute([
                    'u' => $username,
                    'e' => $email,
                    'p' => password_hash($password, PASSWORD_DEFAULT),
                    'r' => $role,
                ]);
                $message = 'User added.';
            }
        }

        if ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $newPassword = $_POST['new_password'] ?? '';
            if ($id > 0 && $username !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $role = in_array($role, ['admin', 'politician', 'user'], true) ? $role : 'user';
                if ($newPassword !== '') {
                    if (strlen($newPassword) < 8) {
                        $message = 'Password must be at least 8 characters.';
                    } else {
                        $stmt = $pdo->prepare('UPDATE users SET username = :u, email = :e, role = :r, password_hash = :p WHERE id = :id');
                        $stmt->execute([
                            'u' => $username,
                            'e' => $email,
                            'r' => $role,
                            'p' => password_hash($newPassword, PASSWORD_DEFAULT),
                            'id' => $id,
                        ]);
                        $message = 'User updated.';
                    }
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET username = :u, email = :e, role = :r WHERE id = :id');
                    $stmt->execute([
                        'u' => $username,
                        'e' => $email,
                        'r' => $role,
                        'id' => $id,
                    ]);
                    $message = 'User updated.';
                }
            }
        }

        if ($action === 'remove') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && $id !== (int) $_SESSION['user_id']) {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $message = 'User removed.';
            }
        }
    } catch (PDOException $e) {
        $message = 'Action failed.';
    }
}

$users = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<main class="page-wrap">
    <section class="card">
        <h1>Manage Users</h1>
        <?php if ($message !== ''): ?><div class="notice"><?= esc($message) ?></div><?php endif; ?>

        <h2>Add User</h2>
        <form method="POST" class="filter-form">
            <input type="hidden" name="action" value="add">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <select name="role">
                <option value="user">User</option>
                <option value="politician">Politician</option>
                <option value="admin">Admin</option>
            </select>
            <input type="password" name="password" placeholder="Password (min 8)" required>
            <button type="submit">Add</button>
        </form>
    </section>

    <section class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) $user['id'] ?></td>
                        <td><?= esc((string) $user['username']) ?></td>
                        <td><?= esc((string) $user['email']) ?></td>
                        <td><?= esc((string) $user['role']) ?></td>
                        <td><?= esc((string) $user['created_at']) ?></td>
                        <td>
                            <form method="POST" class="inline-form" style="margin-bottom:6px;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                <input type="text" name="username" value="<?= esc((string) $user['username']) ?>" required>
                                <input type="email" name="email" value="<?= esc((string) $user['email']) ?>" required>
                                <input type="password" name="new_password" placeholder="New password (optional)">
                                <select name="role">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="politician" <?= $user['role'] === 'politician' ? 'selected' : '' ?>>Politician</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                <button type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src="../assets/js/header.js"></script>
</body>
</html>
