# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-03-08

### Added

- **Password Reset** (`PasswordResetController`, `PasswordReset` model) — Full forgot-password flow with time-limited tokens (60 min), SHA-256 hashed storage, one-time use enforcement, and automatic purge of expired tokens.
- **`/forgot-password` and `/reset-password` routes** — Registered in front controller with rate limiting on both endpoints.
- **`password_resets` table** — New migration: `scripts/migration_password_reset.sql`.
- **`User::updatePassword()`** — New model method for updating password hash after reset.
- **`AuditLogger::log()`** — Generic public log method for use by any controller (`password_reset_requested`, `password_reset_success`).
- **`.editorconfig`** — Consistent formatting across all editors and contributors.
- **`.php-cs-fixer.php`** — PSR-12 enforcement configuration.
- **Forgot password link** on login page.

### Changed

- `Session::destroy()` — Now starts a fresh session and regenerates the ID immediately after destroying the old one, preventing session fixation on re-login after logout.
- `Session` — Extracted internal `forceExpire()` method for hijack/timeout paths that should not create a new session.
- `HomeController::health()` — Removed PHP version from JSON response to prevent server fingerprinting.
- `CONTRIBUTING.md` — Updated class table and project structure to reflect v1.1.0 and v1.2.0 additions.
- `ROADMAP.md` — Marked completed items (rate limiting, lockout, audit log, .env).
- `VERSION` — Bumped to `1.2.0`.

### Security

- Password reset tokens are stored as SHA-256 hashes — raw token is only sent by email and never stored.
- Reset tokens expire after 60 minutes and are single-use.
- Existing unused tokens are invalidated when a new reset is requested.
- Health endpoint no longer exposes PHP version.
- Logout now issues a fresh session ID, closing a theoretical session fixation window.

---

## [1.1.0] - 2026-01-15

### Added

- **Rate Limiting** (`RateLimiter.php`) — IP-based rate limiting on `/login` and `/register` (max 10 attempts per 5-minute window).
- **Account Lockout** (`RateLimiter.php`) — 15-minute lockout after 5 consecutive failed login attempts per email.
- **Audit Logging** (`AuditLogger.php`) — NDJSON audit log for: `login_success`, `login_failed`, `logout`, `register_success`, `register_failed`, `account_locked`, `rate_limit_hit`, `csrf_failure`, `session_hijack_attempt`. Auto-rotation at 10 MB.
- **Environment-based configuration** — `config/database.php` reads from `.env`; `.env.example` template added.
- **`storage/` directory** — Auto-created on bootstrap with `0700` permissions and HTTP-blocking `.htaccess`.

### Changed

- `AuthController` — Integrated rate limiting, account lockout, and audit logging.
- `bootstrap.php` — Audit logs CSRF failures; ensures `storage/` exists on boot.
- `.gitignore` — `storage/` excluded from version control.

---

## [1.0.0] - 2025-01-01

### Added

- Secure session management with fingerprinting, idle timeout, and periodic regeneration.
- CSRF protection with token generation, verification, and Origin/Referer header validation.
- Security headers: CSP with per-request nonce, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, optional HSTS.
- Lightweight URI-based router supporting GET and POST methods.
- Singleton PDO database wrapper with prepared statements, query helpers, and transaction support.
- Fluent input validator.
- Authentication flow: registration, login, logout, protected dashboard.
- Flash message system.
- JSON health check endpoint at `/health`.
- Apache `.htaccess` for URL rewriting and directory protection.
- SQL schema for the `users` table.
- Full OSS documentation: README, SECURITY, CONTRIBUTING, ROADMAP, LICENSE (MIT).
