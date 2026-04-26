<?php
require_once __DIR__ . '/../includes/session.php';
app_session_start();
require_once __DIR__ . '/../includes/db.php';

// Action: Redirect unauthenticated users to login.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php', true, 302);
    exit;
}
// Action: Restrict this page to admin users only.
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('403 Forbidden');
}

$message = '';

// Action: Handle admin user actions (add, update, remove) submitted from forms.
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
                // Action: Insert new user record with hashed password.
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (:u,:e,:p,:r)');
                $stmt->execute([
                    'u' => $username,
                    'e' => $email,
                    'p' => password_hash($password, PASSWORD_DEFAULT),
                    'r' => $role,
                ]);

                if ($role === 'politician') {
                    // Action: Link newly created politician user to politicians table.
                    $userId = (int) $pdo->lastInsertId();
                    $linkStmt = $pdo->prepare('INSERT INTO politicians (user_id) VALUES (:user_id)');
                    $linkStmt->execute(['user_id' => $userId]);
                }

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

                $existingRoleStmt = $pdo->prepare('SELECT role FROM users WHERE id = :id');
                $existingRoleStmt->execute(['id' => $id]);
                $existingRole = (string) ($existingRoleStmt->fetchColumn() ?: '');

                if ($newPassword !== '') {
                    if (strlen($newPassword) < 8) {
                        $message = 'Password must be at least 8 characters.';
                    } else {
                        // Action: Update profile fields and replace password hash.
                        $stmt = $pdo->prepare('UPDATE users SET username = :u, email = :e, role = :r, password_hash = :p WHERE id = :id');
                        $stmt->execute([
                            'u' => $username,
                            'e' => $email,
                            'r' => $role,
                            'p' => password_hash($newPassword, PASSWORD_DEFAULT),
                            'id' => $id,
                        ]);

                        if ($role === 'politician' && $existingRole !== 'politician') {
                            // Action: Ensure politician profile exists after role promotion.
                            $linkStmt = $pdo->prepare('INSERT INTO politicians (user_id) SELECT :user_id WHERE NOT EXISTS (SELECT 1 FROM politicians WHERE user_id = :user_id)');
                            $linkStmt->execute(['user_id' => $id]);
                        }

                        $message = 'User updated.';
                    }
                } else {
                    // Action: Update profile fields while keeping existing password.
                    $stmt = $pdo->prepare('UPDATE users SET username = :u, email = :e, role = :r WHERE id = :id');
                    $stmt->execute([
                        'u' => $username,
                        'e' => $email,
                        'r' => $role,
                        'id' => $id,
                    ]);

                    if ($role === 'politician' && $existingRole !== 'politician') {
                        // Action: Ensure politician profile exists after role promotion.
                        $linkStmt = $pdo->prepare('INSERT INTO politicians (user_id) SELECT :user_id WHERE NOT EXISTS (SELECT 1 FROM politicians WHERE user_id = :user_id)');
                        $linkStmt->execute(['user_id' => $id]);
                    }

                    $message = 'User updated.';
                }
            }
        }

        if ($action === 'remove') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && $id !== (int) $_SESSION['user_id']) {
                // Action: Remove selected user (except currently logged-in admin).
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $message = 'User removed.';
            }
        }
    } catch (PDOException $e) {
        $message = 'Action failed.';
    }
}

$usersStmt = $pdo->prepare('SELECT id, username, email, role, created_at FROM users ORDER BY id DESC');
$usersStmt->execute();
$users = $usersStmt->fetchAll();

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
        <div class="card-header">
            <h1>Manage Users</h1>
            <p class="card-subtitle">Add, edit, and remove user accounts</p>
        </div>
        <?php if ($message !== ''): ?><div class="alert alert-success"><?= esc($message) ?></div><?php endif; ?>

<<<<<<< HEAD
        <div class="form-section">
            <h2>➕ Add New User</h2>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div>
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter username" required>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="user@example.com" required>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="user">User</option>
                            <option value="politician">Politician</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add User</button>
            </form>
        </div>
=======
        <h2>Add User</h2>
        <!-- Action: Submit form to create a new user account. -->
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
>>>>>>> e7daf47 (added comments)
    </section>

    <section class="card">
        <h2>Users Directory</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($users) === 0): ?>
                    <tr><td colspan="6" class="text-center text-muted">No users found</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><span class="badge badge-id"><?= (int) $user['id'] ?></span></td>
                        <td><strong><?= esc((string) $user['username']) ?></strong></td>
                        <td><?= esc((string) $user['email']) ?></td>
                        <td><span class="role-badge role-<?= strtolower(esc((string) $user['role'])) ?>"><?= esc((string) $user['role']) ?></span></td>
                        <td><small class="text-muted"><?= esc((string) $user['created_at']) ?></small></td>
                        <td>
<<<<<<< HEAD
                            <div class="action-buttons">
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <div class="edit-row">
                                        <input type="text" name="username" value="<?= esc((string) $user['username']) ?>" placeholder="Username" required>
                                        <input type="email" name="email" value="<?= esc((string) $user['email']) ?>" placeholder="Email" required>
                                        <input type="password" name="new_password" placeholder="New password (opt)">
                                        <select name="role">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="politician" <?= $user['role'] === 'politician' ? 'selected' : '' ?>>Politician</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-success">✓ Update</button>
                                    </div>
                                </form>
                                <form method="POST" class="inline-form" style="margin-top: 6px;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove user?')">✕ Delete</button>
                                </form>
                            </div>
=======
                            <!-- Action: Submit form to update an existing user. -->
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
                            <!-- Action: Submit form to remove this user account. -->
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                <button type="submit">Remove</button>
                            </form>
>>>>>>> e7daf47 (added comments)
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script src="../assets/js/header.js"></script>
</body>
</html>
