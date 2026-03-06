<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register &mdash; Secure Web Baseline</title>
    <style nonce="<?= nonce() ?>">
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 420px; margin: 60px auto; padding: 0 16px; color: #1a1a1a; }
        h1 { margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 24px; }
        label { display: block; margin-bottom: 4px; font-weight: 500; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px;
        }
        button { padding: 10px 24px; background: #111; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 15px; }
        button:hover { background: #333; }
        .alert { padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; }
        .alert-error { background: #fce4e4; color: #a00; }
        .hint { font-size: 13px; color: #888; margin-top: -12px; margin-bottom: 16px; }
        a { color: #111; }
    </style>
</head>
<body>
    <h1>Register</h1>
    <p class="subtitle">Secure Web Baseline</p>

    <?php if ($error = flash_error()): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/register">
        <?= csrf_field() ?>

        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= old('name') ?>" required autofocus>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= old('email') ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">
        <p class="hint">Minimum 8 characters</p>

        <button type="submit">Create account</button>
    </form>

    <p style="margin-top:16px"><a href="/login">Already have an account?</a></p>
</body>
</html>
