# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
