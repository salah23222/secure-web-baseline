# Security Review

This document summarizes the security review performed for **Secure Web Baseline** before public open-source publication, and tracks all fixes applied across versions.

---

## Review Scope

- Sensitive data exposure
- Session security
- CSRF protection
- Content Security Policy (CSP)
- HTTP security headers
- Authentication flow
- Password reset flow
- Input validation
- Rate limiting and account lockout
- Audit logging
- Database access patterns
- Server fingerprinting
- Email header injection
- Safe public open-source release readiness

---

## Sensitive Data Audit

### Result — Clean

- No real credentials
- No API keys
- No production secrets
- No internal IP addresses or domains
- No internal email addresses
- No private business logic
- No customer data
- No internal or government branding

All configuration values are safe development placeholders. Credentials are loaded from `.env` (excluded from version control).

---

## Security Fixes by Version

### v1.0.0 — Initial Release

| # | Issue | Severity | Fix |
|---|-------|----------|-----|
| 1 | Open redirect in `redirect()` helper | High | Normalized to `'/' . ltrim($path, '/')` |
| 2 | Timing side-channel in login — email existence leak | Medium | Dummy hash fallback ensures `password_verify()` always runs |
| 3 | CSP violations on error pages (inline `style=""`) | Medium | Replaced with `<style nonce="...">` blocks |
| 4 | IPv6 crash risk in session fingerprint | Medium | Added IPv6-aware subnet detection |
| 5 | Path traversal in `view()` helper | Medium | Guards for `..` and null-byte sequences |
| 6 | Generic `SECURITY.md` contact wording | Low | Updated to GitHub Security Advisories workflow |

---

### v1.1.0 — Hardening

| # | Issue | Severity | Fix |
|---|-------|----------|-----|
| 7 | No rate limiting on `/login` and `/register` | High | `RateLimiter` — IP-based, max 10 per 5 min |
| 8 | No account lockout after repeated failures | High | `RateLimiter::recordFailedLogin()` — 15 min lockout after 5 attempts |
| 9 | No security event audit trail | Medium | `AuditLogger` — NDJSON log with rotation |
| 10 | Database credentials hard-coded in tracked files | Medium | `.env` loader in `config/database.php` |
| 11 | `storage/` not protected from HTTP access | Medium | Auto-created `.htaccess` blocking direct access |

---

### v1.2.0 — Password Reset & Session

| # | Issue | Severity | Fix |
|---|-------|----------|-----|
| 12 | No password recovery mechanism | High | Full forgot/reset flow with 60-min time-limited tokens |
| 13 | Reset tokens stored as plaintext (design) | High | Tokens stored as SHA-256 hashes; raw token only in email |
| 14 | No token invalidation on reuse | High | `PasswordReset::markUsed()` — one-time use enforced |
| 15 | `Session::destroy()` did not regenerate ID after logout | Medium | Now calls `session_regenerate_id(true)` on fresh session |
| 16 | `/health` endpoint exposed exact PHP version | Low | Removed `php` field from health response |

---

### v1.3.0 — Fingerprinting & Infrastructure

| # | Issue | Severity | Fix |
|---|-------|----------|-----|
| 17 | PHP sends `X-Powered-By: PHP/8.x` header by default | Medium | `header_remove('X-Powered-By')` + `ini_set('expose_php', '0')` in bootstrap and `SecurityHeaders::send()` |
| 18 | `mail()` subject/name fields vulnerable to header injection | Medium | Input sanitised — newlines and null bytes stripped from name; email validated; headers built safely |
| 19 | `schema.sql` missing `password_resets` table | Medium | Complete schema now includes all tables |
| 20 | No `composer.json` — dev tools uninstallable | Low | Added with `php-cs-fixer` and `phpunit` as dev dependencies |
| 21 | No CI pipeline — code quality not verified on push | Low | GitHub Actions: syntax check (PHP 8.1/8.2/8.3), PSR-12, security scan, PHPUnit |
| 22 | `SECURITY_REVIEW.md` not tracking post-v1.0 fixes | Low | This document updated to cover all versions |

---

## Current Security Guarantees (v1.3.0)

### Session Security
- HTTPOnly, SameSite=Strict, Secure cookies
- 30-minute idle timeout
- 15-minute periodic ID regeneration
- Subnet-safe fingerprint hijack detection
- Fresh session ID on logout

### CSRF Protection
- Per-session 64-byte token
- Constant-time comparison via `hash_equals()`
- Origin / Referer header validation
- Auto-verified on every POST

### HTTP Security Headers
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy`
- `Strict-Transport-Security` (HTTPS only)
- `X-Powered-By` removed

### Content Security Policy
- Strict `default-src 'self'`
- Per-request nonce for scripts and styles
- No `unsafe-inline`, no `unsafe-eval`

### Authentication
- `password_hash()` / `password_verify()` with bcrypt
- Session fixation prevention on login and logout
- Generic error messages (no user enumeration)
- Rate limiting: 10 attempts / 5 min per IP
- Account lockout: 15 min after 5 failed attempts

### Password Reset
- Time-limited tokens (60 minutes)
- SHA-256 hashed storage
- Single-use enforcement
- Previous tokens invalidated on new request
- Constant success message (no user enumeration)
- Header injection protection in email sender

### Database
- PDO prepared statements throughout
- No raw string concatenation for user input

### Output
- `htmlspecialchars()` / `e()` on all user-facing output

### Audit Logging
- NDJSON format with timestamp, IP, user-agent
- Events: login, logout, register, reset, lockout, CSRF failure
- Automatic rotation at 10 MB

---

## Open Source Release Verification

- ✅ Safe for public publication
- ✅ No production credentials in tracked files
- ✅ No internal-only dependencies
- ✅ No regulated or private integrations
- ✅ Clean documentation suitable for OSS release

---

## Conclusion

**Secure Web Baseline v1.3.0** has been reviewed and hardened across four iterations. It is suitable as a secure PHP MVC learning resource and reusable open-source foundation. All known vulnerabilities identified during review have been resolved.
