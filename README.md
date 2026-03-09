# Secure Web Baseline
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Security](https://img.shields.io/badge/security-CSRF%20%7C%20CSP%20%7C%20Sessions-red)
![Architecture](https://img.shields.io/badge/architecture-MVC-orange)
![Open Source](https://img.shields.io/badge/Open%20Source-Yes-brightgreen)
![Version](https://img.shields.io/badge/version-1.4.0-blue)
![CI](https://img.shields.io/badge/CI-GitHub%20Actions-black)

A lightweight, security-first PHP MVC starter framework for building secure web applications.

No heavy frameworks. No magic. Just clean, auditable PHP with security best practices baked in.

---

## Screenshots

### Homepage — Product Listing
The public-facing storefront displaying the latest smartphones, installment options, pricing in AED, and category navigation. Fully RTL Arabic layout with responsive design.

![Homepage](docs/screenshots/01-homepage.png)

### Admin Dashboard — Financial Overview
The administration panel showing real-time financial statistics: total sales, purchase cost, net profit, and order status breakdown. Includes session tracking and KYC counters.

![Admin Dashboard](docs/screenshots/02-admin-dashboard.png)

### Installment Plan — Order Detail
A detailed view of a single installment plan showing customer information, order number, monthly payment, interest rate, plan status, and attached documents.

![Installment Plan Detail](docs/screenshots/03-installment-plan.png)

### Payment Schedule — Notification Log
The full payment schedule table listing each instalment due date and amount, alongside a complete log of automated collection notifications (reminder → first warning → final warning).

![Payment Schedule](docs/screenshots/04-payment-schedule.png)

---

## Key Features

- **Session Hardening** — httponly, secure, samesite=strict cookies; idle timeout; periodic ID regeneration; browser/IP fingerprint-based hijack detection; fresh ID on logout.
- **CSRF Protection** — Per-session token with constant-time verification; Origin/Referer header validation; auto-verified on every POST request.
- **Content Security Policy** — Strict CSP with a per-request nonce; no `unsafe-inline` or `unsafe-eval`.
- **Secure HTTP Headers** — X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, HSTS, explicit `charset=UTF-8`, `X-Powered-By` removed.
- **PDO Database Layer** — Singleton wrapper with prepared statements; query, fetch, insert, update, delete helpers; transaction support.
- **Input Validation** — Fluent validator for required, email, minLength, maxLength, integer, numeric, url, in, confirmed, and regex rules.
- **Authentication Flow** — Register, login (with session fixation prevention), logout, protected dashboard.
- **Password Reset** — Time-limited tokens (60 min), SHA-256 hashed, single-use, header-injection-safe email.
- **Rate Limiting** — IP-based throttle on login, register, and password reset endpoints (max 10/5 min).
- **Account Lockout** — 15-minute lockout after 5 consecutive failed login attempts.
- **Audit Logging** — Structured NDJSON log for all security events with automatic rotation.
- **Dynamic Router** — Supports static and parameterized routes (`/user/{id}`).
- **Clean MVC Structure** — Controllers, Models, Views, Core classes, and a URI-based router.
- **PHPUnit Test Suite** — Unit tests for Validator and Router with bootstrap and config.
- **CI Pipeline** — GitHub Actions: syntax check (PHP 8.1/8.2/8.3), PSR-12, security scan, tests.

---

## Project Structure

```
secure-web-baseline/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php            # Landing page & health check
│   │   ├── AuthController.php            # Register, login, logout
│   │   ├── DashboardController.php       # Protected dashboard
│   │   └── PasswordResetController.php   # Forgot & reset password
│   ├── Core/
│   │   ├── bootstrap.php                 # Autoloader, session, headers, helpers
│   │   ├── Router.php                    # URI router (static + dynamic routes)
│   │   ├── Session.php                   # Secure session management
│   │   ├── CSRF.php                      # CSRF token generation & verification
│   │   ├── SecurityHeaders.php           # HTTP security headers + CSP nonce
│   │   ├── Validator.php                 # Fluent input validator
│   │   ├── Database.php                  # Singleton PDO wrapper
│   │   ├── RateLimiter.php               # IP rate limiting & account lockout
│   │   └── AuditLogger.php               # Security event audit log
│   ├── Models/
│   │   ├── User.php                      # User CRUD operations
│   │   └── PasswordReset.php             # Password reset token model
│   └── Views/
│       ├── auth/login.php
│       ├── auth/register.php
│       ├── auth/forgot-password.php
│       ├── auth/reset-password.php
│       ├── dashboard/index.php
│       ├── home/index.php
│       └── errors/403.php, 404.php
├── config/
│   └── database.php                      # Reads from .env
├── docs/
│   └── screenshots/                      # UI screenshots
├── public/
│   ├── index.php                         # Front controller
│   └── .htaccess                         # Apache URL rewriting
├── scripts/
│   ├── schema.sql                        # Complete database schema
│   └── purge_rate_limits.php             # Cron script for cleanup
├── storage/                              # Runtime: audit log, rate limit data
│   └── .gitkeep
├── tests/
│   ├── bootstrap.php                     # Test autoloader
│   ├── Core/
│   │   ├── ValidatorTest.php
│   │   └── RouterTest.php
│   └── Controllers/
├── .editorconfig
├── .env.example
├── .github/
│   └── workflows/ci.yml                  # GitHub Actions CI
├── .gitignore
├── .htaccess
├── .php-cs-fixer.php
├── CHANGELOG.md
├── composer.json
├── CONTRIBUTING.md
├── LICENSE
├── Makefile
├── phpunit.xml
├── README.md
├── ROADMAP.md
├── SECURITY.md
├── SECURITY_REVIEW.md
└── VERSION
```

---

## Requirements

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled (or Nginx)

---

## Installation

```bash
# 1. Clone
git clone https://github.com/salah23222/secure-web-baseline.git
cd secure-web-baseline

# 2. Install dev dependencies
make install

# 3. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 4. Initialize database
make schema

# 5. Run tests
make test
```

---

## Default Database Credentials (Development)

| Setting  | Value                 |
| -------- | --------------------- |
| Host     | `127.0.0.1`           |
| Port     | `3306`                |
| Database | `secure_web_baseline` |
| Username | `root`                |
| Password | *(empty)*             |

> ⚠️ Change these before deploying to production. Use `.env` to manage credentials securely.

---

## Web Server Setup

**Apache Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName secure-baseline.local
    DocumentRoot /path/to/secure-web-baseline/public
    <Directory /path/to/secure-web-baseline/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name secure-baseline.local;
    root /path/to/secure-web-baseline/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ ^/(app|config|scripts|logs|storage)/ {
        deny all;
    }
}
```

---

## Available Routes

| Method | Path                | Description                  | Auth Required |
| ------ | ------------------- | ---------------------------- | ------------- |
| GET    | `/`                 | Landing page                 | No            |
| GET    | `/health`           | JSON health check            | No            |
| GET    | `/register`         | Registration form            | No            |
| POST   | `/register`         | Process registration         | No            |
| GET    | `/login`            | Login form                   | No            |
| POST   | `/login`            | Process login                | No            |
| GET    | `/logout`           | Log out and destroy session  | No            |
| GET    | `/forgot-password`  | Password reset request form  | No            |
| POST   | `/forgot-password`  | Send reset link              | No            |
| GET    | `/reset-password`   | New password form            | No            |
| POST   | `/reset-password`   | Apply new password           | No            |
| GET    | `/dashboard`        | Protected user dashboard     | Yes           |

---

## Security Notes

| Layer             | Implementation                                                                           |
| ----------------- | ---------------------------------------------------------------------------------------- |
| Sessions          | httponly, secure, samesite=strict, 30-min idle, 15-min regen, fingerprint, fresh ID on logout |
| CSRF              | 64-byte token, constant-time compare, Origin/Referer validation, auto-verified on POST  |
| CSP               | `default-src 'self'`; per-request nonce; no `unsafe-inline`; no `unsafe-eval`           |
| Headers           | DENY framing, nosniff, strict referrer, permissions policy, HSTS, charset=UTF-8, no X-Powered-By |
| SQL Injection     | All queries use PDO prepared statements                                                  |
| XSS               | All output uses `htmlspecialchars()` via the `e()` helper                                |
| Session Fixation  | `session_regenerate_id(true)` on login and logout                                        |
| User Enumeration  | Generic error messages on login and password reset                                       |
| Password Storage  | `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)                                       |
| Password Reset    | SHA-256 hashed tokens, 60-min expiry, single-use, header-injection-safe email           |
| Rate Limiting     | Max 10 attempts / 5 min per IP on login, register, forgot, reset                        |
| Account Lockout   | 15-min lockout after 5 failed login attempts                                             |
| Audit Logging     | NDJSON log: login, logout, register, reset, lockout, CSRF failure                       |
| Fingerprinting    | PHP version not exposed in headers or health endpoint                                    |

---

## Maintenance

```bash
# Purge expired rate-limit files (add to cron)
make purge

# Recommended cron entry (every 15 minutes):
# */15 * * * * php /path/to/project/scripts/purge_rate_limits.php >> /var/log/swb-purge.log 2>&1
```

---

## Roadmap

See [ROADMAP.md](ROADMAP.md) for planned improvements including RBAC, 2FA, middleware pipeline, Docker, and more.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

[MIT](LICENSE)
