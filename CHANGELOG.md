# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2026-03-08

### Added

- **`composer.json`** — Project manifest with `php-cs-fixer` and `phpunit` as dev dependencies. Includes `cs-check`, `cs-fix`, and `test` scripts.
- **GitHub Actions CI** (`.github/workflows/ci.yml`) — Four jobs on every push/PR: PHP syntax check (8.1 / 8.2 / 8.3), PSR-12 style check, dangerous-function scan, PHPUnit tests.

### Changed

- **`SecurityHeaders::send()`** — Added `header_remove('X-Powered-By')` to strip the PHP version fingerprint header.
- **`bootstrap.php`** — Added `header_remove('X-Powered-By')` and `ini_set('expose_php', '0')` as early as possible, before any other output.
- **`PasswordResetController::sendEmail()`** — Hardened against email header injection: newlines and null bytes stripped from display name; email address checked for control characters; headers built as an explicit array.
- **`scripts/schema.sql`** — Now includes both `users` and `password_resets` tables. Running this file from scratch produces a fully operational database.
- **`SECURITY_REVIEW.md`** — Updated to document all fixes across v1.0.0 through v1.3.0.
- **`VERSION`** — Bumped to `1.3.0`.

### Security

- PHP version no longer exposed via `X-Powered-By` response header.
- `mail()` sender hardened against header injection attacks.
- Fresh install via `schema.sql` now includes all required tables.

---

## [1.2.0] - 2026-03-08

### Added

- **Password Reset** — Full forgot/reset flow: time-limited tokens (60 min), SHA-256 hashed storage, one-time use, automatic purge of expired tokens.
- **`/forgot-password` and `/reset-password` routes** with rate limiting.
- **`scripts/migration_password_reset.sql`** — Standalone migration for existing installs.
- **`User::updatePassword()`** — New model method.
- **`AuditLogger::log()`** — Generic public log method.
- **`.editorconfig`** — Consistent formatting across editors.
- **`.php-cs-fixer.php`** — PSR-12 enforcement config.
- Forgot password link on login page.

### Changed

- `Session::destroy()` — Now regenerates session ID on fresh session after logout.
- `HomeController::health()` — PHP version removed from JSON response.
- `VERSION` — Bumped to `1.2.0`.

### Security

- Reset tokens stored as SHA-256 hashes only.
- Single-use token enforcement.
- Health endpoint no longer fingerprints PHP version.
- Logout closes session fixation window.

---

## [1.1.0] - 2026-01-15

### Added

- **Rate Limiting** — IP-based, max 10 attempts per 5-minute window on `/login` and `/register`.
- **Account Lockout** — 15-minute lockout after 5 consecutive failed logins.
- **Audit Logging** — NDJSON log for all auth events with auto-rotation at 10 MB.
- **`.env` configuration** — Database credentials loaded from environment.
- **`storage/` protection** — Auto-created with `0700` permissions and HTTP-blocking `.htaccess`.

---

## [1.0.0] - 2025-01-01

### Added

- Secure session management with fingerprinting, idle timeout, and periodic regeneration.
- CSRF protection with token generation, verification, and Origin/Referer validation.
- Security headers: CSP with per-request nonce, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, optional HSTS.
- Lightweight URI-based router.
- Singleton PDO wrapper with prepared statements.
- Fluent input validator.
- Authentication flow: register, login, logout, protected dashboard.
- Flash message system.
- JSON health check at `/health`.
- Apache `.htaccess` for URL rewriting.
- SQL schema.
- Full OSS documentation.
