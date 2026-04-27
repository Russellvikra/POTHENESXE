<?php
require_once __DIR__ . '/includes/session.php';
app_session_start();

if (isset($_SESSION['user_id'])) {
    $role = (string) ($_SESSION['role'] ?? 'user');
    if ($role === 'admin') {
        header('Location: admin/admin.php', true, 302);
        exit;
    }
    if ($role === 'politician') {
        header('Location: submit/dashboard.php', true, 302);
        exit;
    }
    header('Location: modules/list.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pothen Esxes - Public Home</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #102040;
            background: linear-gradient(180deg, #f3f7fc 0%, #e8eff7 100%);
        }

        .page {
            width: min(1000px, calc(100% - 28px));
            margin: 40px auto;
            display: grid;
            gap: 18px;
        }

        .hero,
        .card {
            background: #fff;
            border: 1px solid #dbe6f2;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 14px 30px rgba(20, 38, 70, 0.08);
        }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(30px, 5vw, 46px);
            color: #16335f;
        }

        .subtitle {
            margin: 0;
            color: #4f6685;
            line-height: 1.7;
            font-size: 16px;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 14px;
        }

        .card h2 {
            margin: 0 0 8px;
            font-size: 22px;
        }

        .card p {
            margin: 0 0 14px;
            color: #526b8b;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: #0c3f91;
            color: #fff;
            box-shadow: 0 10px 18px rgba(12, 63, 145, 0.15);
        }

        .btn-secondary {
            background: #eef4ff;
            color: #1d4074;
            border: 1px solid #cbdafb;
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="hero">
            <h1>Πόθεν Έσχες</h1>
            <p class="subtitle">
                Pothen Esxes is a financial declaration monitoring system for public officials in Cyprus.
                Use the public API module directly, or log in to continue to the user and admin dashboards.
            </p>
        </section>

        <section class="actions">
            <article class="card">
                <h2>Login</h2>
                <p>Sign in to access user or admin pages based on your role.</p>
                <a class="btn btn-primary" href="auth/login.php">Go to Login</a>
            </article>

            <article class="card">
                <h2>API Module</h2>
                <p>Open the API module directly. Public access is enabled.</p>
                <a class="btn btn-secondary" href="api/index.php">Open API Module</a>
            </article>
        </section>
    </main>
</body>
</html>