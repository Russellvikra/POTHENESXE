<?php
/**
 * Database Setup Script - Run once to initialize test data
 * Usage: Open in browser: http://localhost/CSE-326/project-root/setup.php
 */

require_once __DIR__ . '/includes/db.php';

try {
    // Check if test users already exist
    $checkStmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $checkStmt->fetch();
    
    if ($result['count'] > 0) {
        echo "<p style='color: #c82c3b;'>✗ Database already initialized. Delete users table to reset.</p>";
        exit;
    }
    
    // Create test users with password = 'test123'
    $testPassword = password_hash('test123', PASSWORD_DEFAULT);
    
    $insertUsers = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)'
    );
    
    $users = [
        ['admin', 'admin@test.com', $testPassword, 'admin'],
        ['nikos', 'nikos@test.com', $testPassword, 'politician'],
        ['maria', 'maria@test.com', $testPassword, 'politician'],
    ];
    
    foreach ($users as $user) {
        $insertUsers->execute([
            'username' => $user[0],
            'email' => $user[1],
            'password_hash' => $user[2],
            'role' => $user[3]
        ]);
    }
    
    // Create politician profiles
    $insertPoliticians = $pdo->prepare(
        'INSERT INTO politicians (user_id, party_id, position) VALUES (:user_id, :party_id, :position)'
    );
    
    $insertPoliticians->execute(['user_id' => 2, 'party_id' => 1, 'position' => 'MP']);
    $insertPoliticians->execute(['user_id' => 3, 'party_id' => 2, 'position' => 'Minister']);
    
    echo "<html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: sans-serif; padding: 40px; background: #f5f5f5; }
            .card { background: #fff; border: 1px solid #dbe3ee; border-radius: 8px; padding: 24px; max-width: 500px; }
            .success { color: #1d7f41; }
            table { border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background: #f0f0f0; }
            code { background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-family: monospace; }
            a { color: #0c3f91; text-decoration: none; margin-top: 20px; display: block; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class='card'>
            <h1 class='success'>✓ Database Initialized Successfully</h1>
            <p>Test users have been created. Use these credentials to login:</p>
            
            <table>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Role</th>
                </tr>
                <tr>
                    <td>admin</td>
                    <td>admin@test.com</td>
                    <td><code>test123</code></td>
                    <td>admin</td>
                </tr>
                <tr>
                    <td>nikos</td>
                    <td>nikos@test.com</td>
                    <td><code>test123</code></td>
                    <td>politician</td>
                </tr>
                <tr>
                    <td>maria</td>
                    <td>maria@test.com</td>
                    <td><code>test123</code></td>
                    <td>politician</td>
                </tr>
            </table>
            
            <a href='auth/login.php'>→ Go to Login Page</a>
        </div>
    </body>
    </html>";
    
} catch (PDOException $e) {
    echo "<p style='color: #c82c3b;'>✗ Error: Database initialization failed. Make sure schema.sql has been imported.</p>";
}
