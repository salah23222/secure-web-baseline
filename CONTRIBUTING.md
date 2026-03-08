# Contributing to Secure Web Baseline

Thank you for your interest in contributing! This document provides guidelines for contributing to the project.

## How to Contribute

1. **Fork** the repository on GitHub.
2. **Clone** your fork locally.
3. **Create a branch** for your change: `git checkout -b feature/my-improvement`
4. **Make your changes** following the code style guidelines below.
5. **Test** your changes locally.
6. **Commit** with a clear, descriptive message.
7. **Push** your branch and open a **Pull Request** against `main`.

## Pull Request Guidelines

- Keep PRs focused on a single change or feature.
- Describe what your PR does and why.
- Reference any related issues.
- Ensure the project still works end-to-end (register, login, forgot password, reset, dashboard, logout).
- Do not introduce external framework dependencies (Laravel, Symfony, etc.).

## Code Style

- PHP 8.1+ with `declare(strict_types=1);` in every PHP file.
- Follow PSR-12 coding style — enforced via `.php-cs-fixer.php`.
- Use meaningful variable and method names.
- Add comments only where the logic is not self-evident.
- All database queries must use PDO prepared statements.
- All user output must be escaped with `htmlspecialchars()` or the `e()` helper.
- Do not use `eval()`, `extract()` on user input, or other unsafe functions.

## Running the Code Style Fixer

```bash
# Install (once)
composer require --dev friendsofphp/php-cs-fixer

# Check
vendor/bin/php-cs-fixer fix --dry-run --diff

# Apply fixes
vendor/bin/php-cs-fixer fix
```

## Core Classes

| Class | Purpose |
|-------|---------|
| `Session` | Secure session with fingerprinting, idle timeout, regeneration |
| `CSRF` | Token generation, constant-time verification, origin validation |
| `SecurityHeaders` | CSP nonce, HTTP security headers |
| `Database` | Singleton PDO wrapper |
| `Validator` | Fluent input validation |
| `RateLimiter` | IP rate limiting + account lockout |
| `AuditLogger` | NDJSON security event log |

## Project Structure

```
secure-web-baseline/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── PasswordResetController.php
│   ├── Core/
│   │   ├── bootstrap.php
│   │   ├── Router.php
│   │   ├── Session.php
│   │   ├── CSRF.php
│   │   ├── SecurityHeaders.php
│   │   ├── Validator.php
│   │   ├── Database.php
│   │   ├── RateLimiter.php
│   │   └── AuditLogger.php
│   ├── Models/
│   │   ├── User.php
│   │   └── PasswordReset.php
│   └── Views/
│       ├── auth/login.php
│       ├── auth/register.php
│       ├── auth/forgot-password.php
│       ├── auth/reset-password.php
│       ├── dashboard/index.php
│       ├── home/index.php
│       └── errors/403.php, 404.php
├── config/
├── docs/screenshots/
├── public/
├── scripts/
├── storage/
├── .editorconfig
├── .env.example
├── .php-cs-fixer.php
└── ...
```

## What We Welcome

- Security improvements and hardening.
- Bug fixes.
- Documentation improvements.
- New security features that fit the lightweight philosophy.
- Test coverage.

## What We Do Not Accept

- Introduction of heavy frameworks or large dependency trees.
- Business-specific features (e-commerce, payment, etc.).
- Changes that break the lightweight, educational nature of the project.

## Reporting Security Issues

Please see [SECURITY.md](SECURITY.md) for instructions on reporting vulnerabilities privately.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
