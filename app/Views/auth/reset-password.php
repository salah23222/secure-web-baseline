<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password &mdash; Secure Web Baseline</title>
    <style nonce="<?= nonce() ?>">
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 420px; margin: 60px auto; padding: 0 16px; color: #1a1a1a; }
        h1 { margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 24px; }
        label { display: block; margin-bottom: 4px; font-weight: 500; }
        input[type="password"] { width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; }
        button { padding: 10px 24px; background: #111; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 15px; }
        button:hover { background: #333; }
        .alert { padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; }
        .alert-error { background: #fce4e4; color: #a00; }
        .hint { font-size: 13px; color: #888; margin-top: -12px; margin-bottom: 16px; }
        a { color: #111; }
    </style>
</head>
<body>
    <h1>Reset Password</h1>
    <p class="subtitle">Enter your new password below.</p>

    <?php if ($error = flash_error()): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/reset-password">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= e($email) ?>">
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <label for="password">New Password</label>
        <input type="password" id="password" name="password" required minlength="8" autofocus>
        <p class="hint">Minimum 8 characters</p>

        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">

        <button type="submit">Update Password</button>
    </form>

    <p style="margin-top:16px"><a href="/login">&larr; Back to login</a></p>
</body>
</html>
