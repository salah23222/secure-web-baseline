# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-03-08

### Added

- **Rate Limiting** (`RateLimiter.php`) — IP-based rate limiting on `/login` and `/register` endpoints (max 10 attempts per 5-minute window). No external dependencies; uses file-based storage.
- **Account Lockout** (`RateLimiter.php`) — Automatic 15-minute lockout after 5 consecutive failed login attempts per email address.
- **Audit Logging** (`AuditLogger.php`) — Structured NDJSON audit log for security events: `login_success`, `login_failed`, `logout`, `register_success`, `register_failed`, `account_locked`, `rate_limit_hit`, `csrf_failure`, `session_hijack_attempt`. Includes automatic log rotation at 10 MB.
- **Environment-based configuration** (`config/database.php`) — Reads database credentials from `.env` file when present; falls back to development defaults. Added `.env.example` template.
- **Storage directory protection** — `storage/` directory auto-created on bootstrap with mode `0700` and an `.htaccess` denying direct HTTP access.

### Changed

- `AuthController` — Integrated rate limiting, account lockout, and audit logging into `login()`, `register()`, and `logout()` flows.
- `bootstrap.php` — Audit logs CSRF failures; ensures `storage/` exists and is protected on every boot.
- `config/database.php` — Now loads `.env` automatically; no credentials hard-coded in tracked files.
- `.gitignore` — `storage/` directory excluded from version control.

### Security

- Brute-force protection on authentication endpoints (rate limiting + account lockout).
- All security events now produce a tamper-evident audit trail.
- Database credentials no longer need to be committed to version control.

---

## [1.0.0] - 2025-01-01

### Added

- Secure session management with fingerprinting, idle timeout, and periodic regeneration.
- CSRF protection with token generation, verification, and Origin/Referer header validation.
- Security headers: CSP with per-request nonce, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, optional HSTS.
- Lightweight URI-based router supporting GET and POST methods.
- Singleton PDO database wrapper with prepared statements, query helpers, and transaction support.
- Fluent input validator with required, email, minLength, maxLength, integer, numeric, url, in, confirmed, and regex rules.
- User model with create, findByEmail, findById, all, updateRole, and delete operations.
- Authentication flow: registration, login (with session fixation prevention), and logout.
- Protected dashboard with session-guarded access.
- Flash message system for user feedback across redirects.
- Clean view rendering with `e()`, `nonce()`, `csrf_field()`, `old()`, and `redirect()` helpers.
- JSON health check endpoint at `/health`.
- Apache `.htaccess` for URL rewriting and directory protection.
- SQL schema for the `users` table.
- Full OSS documentation: README, SECURITY, CONTRIBUTING, ROADMAP, LICENSE (MIT).
