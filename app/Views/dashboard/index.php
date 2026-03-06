<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &mdash; Secure Web Baseline</title>
    <style nonce="<?= nonce() ?>">
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 600px; margin: 60px auto; padding: 0 16px; color: #1a1a1a; }
        h1 { margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #e0e0e0; }
        th { background: #f7f7f7; font-weight: 600; width: 120px; }
        .actions { margin-top: 16px; }
        a.btn { display: inline-block; padding: 8px 20px; background: #111; color: #fff; text-decoration: none; border-radius: 4px; }
        a.btn:hover { background: #333; }
    </style>
</head>
<body>
    <h1>Dashboard</h1>
    <p class="subtitle">You are logged in.</p>

    <table>
        <tr><th>User ID</th><td><?= e((string) $user_id) ?></td></tr>
        <tr><th>Name</th><td><?= e((string) $user_name) ?></td></tr>
        <tr><th>Email</th><td><?= e((string) $user_email) ?></td></tr>
        <tr><th>Role</th><td><?= e((string) $user_role) ?></td></tr>
    </table>

    <div class="actions">
        <a class="btn" href="/logout">Log out</a>
    </div>
</body>
</html>
