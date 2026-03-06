<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Web Baseline</title>
    <style nonce="<?= nonce() ?>">
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 600px; margin: 60px auto; padding: 0 16px; color: #1a1a1a; }
        h1 { margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 24px; }
        ul { line-height: 1.8; }
        a { color: #111; }
        .nav { margin-top: 24px; }
        .nav a { margin-right: 16px; }
    </style>
</head>
<body>
    <h1>Secure Web Baseline</h1>
    <p class="subtitle">A lightweight, security-first PHP MVC starter framework.</p>

    <h2>Features</h2>
    <ul>
        <li>Hardened session management</li>
        <li>CSRF protection with origin validation</li>
        <li>Content Security Policy with per-request nonce</li>
        <li>Secure HTTP headers</li>
        <li>PDO-based database layer</li>
        <li>Input validation</li>
        <li>Clean MVC structure</li>
    </ul>

    <div class="nav">
        <?php if (is_logged_in()): ?>
            <a href="/dashboard">Dashboard</a>
            <a href="/logout">Log out</a>
        <?php else: ?>
            <a href="/login">Log in</a>
            <a href="/register">Register</a>
        <?php endif; ?>
        <a href="/health">Health check</a>
    </div>
</body>
</html>
