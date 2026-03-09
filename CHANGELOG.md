# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.0] - 2026-03-08

### Added

- **PHPUnit test suite** — `tests/bootstrap.php`, `phpunit.xml`, `ValidatorTest` (20 assertions), `RouterTest` (6 assertions covering static, dynamic, trailing slash, and 404).
- **Dynamic Router** — `Router` now supports parameterized routes (`/user/{id}`, `/post/{slug}/comment/{id}`). Static routes still resolved first for performance.
- **`Makefile`** — Developer shortcuts: `make install`, `make test`, `make cs`, `make cs-fix`, `make schema`, `make purge`.
- **`scripts/purge_rate_limits.php`** — CLI/cron script to delete expired rate-limit and lockout files. Recommended schedule: every 15 minutes.
- **`RateLimiter::purgeExpired()`** — New public method; returns count of deleted files; safe to call from cron.

### Changed

- **`SecurityHeaders::send()`** — Added explicit `Content-Type: text/html; charset=UTF-8` header so charset is enforced at the HTTP level, not just via HTML meta tag.
- **`README.md`** — Fully updated: v1.4.0 features, complete project structure, Maintenance section with cron instructions, updated Security Notes table covering all layers.
- **`VERSION`** — Bumped to `1.4.0`.

### Security

- `charset=UTF-8` now declared at HTTP header level — prevents charset-sniffing attacks on older browsers.
- Expired rate-limit files automatically purged via cron — reduces storage growth and ensures lockout expiry is enforced even without a login attempt.

---

## [1.3.0] - 2026-03-08

### Added
- `composer.json` with `php-cs-fixer` and `phpunit` as dev dependencies.
- GitHub Actions CI — syntax check (PHP 8.1/8.2/8.3), PSR-12, security scan, PHPUnit.

### Changed
- `SecurityHeaders::send()` — `header_remove('X-Powered-By')`.
- `bootstrap.php` — `ini_set('expose_php', '0')`.
- `PasswordResetController::sendEmail()` — header injection protection.
- `scripts/schema.sql` — now includes `users` and `password_resets`.
- `SECURITY_REVIEW.md` — updated through v1.3.

---

## [1.2.0] - 2026-03-08

### Added
- Full password reset flow (`PasswordResetController`, `PasswordReset` model).
- `/forgot-password` and `/reset-password` routes with rate limiting.
- `User::updatePassword()` method.
- `AuditLogger::log()` generic method.
- `.editorconfig` and `.php-cs-fixer.php`.

### Changed
- `Session::destroy()` — regenerates ID on fresh session after logout.
- `HomeController::health()` — PHP version removed.

---

## [1.1.0] - 2026-01-15

### Added
- `RateLimiter` — IP rate limiting + account lockout.
- `AuditLogger` — NDJSON security event log.
- `.env` configuration loader.
- `storage/` auto-protection.

---

## [1.0.0] - 2025-01-01

### Added
- Secure session management, CSRF protection, CSP, secure HTTP headers.
- PDO database layer, fluent input validator.
- Authentication flow: register, login, logout, dashboard.
- Flash messages, health check, Apache `.htaccess`, SQL schema.
- Full OSS documentation.
