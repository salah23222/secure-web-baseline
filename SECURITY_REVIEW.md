# Security Review

This document summarizes the security review performed for **Secure Web Baseline** before public open-source publication.

The purpose of this review is to verify that the repository is safe for public release, free of sensitive data, and aligned with secure coding best practices.

---

## Review Scope

The review covered the following areas:

- Sensitive data exposure
- Session security
- CSRF protection
- Content Security Policy (CSP)
- HTTP security headers
- Authentication flow
- Input validation
- Database access patterns
- Safe public open-source release readiness

---

## Sensitive Data Audit

A repository-wide review was performed to identify any data that should not be published publicly.

### Result

No sensitive data was found.

### Verified clean

- No real credentials
- No API keys
- No production secrets
- No internal IP addresses
- No internal domains
- No internal email addresses
- No private business logic
- No payment integrations
- No KYC or identity workflows
- No customer data
- No internal or government branding
- No e-commerce remnants

All configuration values in the repository are safe development placeholders intended for public open-source use.

---

## Security Fixes Applied

The following issues were identified and fixed during review.

| # | Issue | Severity | Fix |
|---|------|----------|-----|
| 1 | Open redirect in `redirect()` helper could allow paths like `//evil.com` | High | Normalized redirects using `'/' . ltrim($path, '/')` |
| 2 | Timing side-channel in login flow could leak whether an email exists | Medium | Added dummy hash fallback so `password_verify()` always executes |
| 3 | CSP violations on `403` and `404` pages due to inline `style=""` usage | Medium | Replaced inline styles with `<style nonce="...">` blocks |
| 4 | IPv6 crash risk in session fingerprint logic | Medium | Added IPv6-aware detection and subnet-safe fingerprint handling |
| 5 | Path traversal risk in `view()` helper | Medium | Added guards for `..` and null-byte sequences |
| 6 | Placeholder contact wording in `SECURITY.md` was too generic | Low | Updated to point users to GitHub Security Advisories workflow |

---

## Security Guarantees

The project includes the following security protections by default.

### Session Security

- HTTPOnly cookies
- SameSite=Strict cookies
- Secure cookie flag when HTTPS is enabled
- Idle timeout enforcement
- Periodic session ID regeneration
- Basic fingerprint-based hijack detection

### CSRF Protection

- Per-session CSRF token generation
- Constant-time token comparison
- Hidden form token helper
- Automatic verification on POST requests
- Origin / Referer validation

### HTTP Security Headers

- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy`
- Optional `Strict-Transport-Security` when HTTPS is active

### Content Security Policy (CSP)

- Strict default policy
- Per-request nonce support
- No `unsafe-inline`
- No `unsafe-eval`

### Database Security

- PDO prepared statements
- Parameter binding
- No raw SQL string concatenation for user input
- Transaction support

### Authentication Security

- `password_hash()` for password storage
- `password_verify()` for password checking
- Session fixation mitigation on login
- Generic invalid-credentials responses to reduce user enumeration

### Output Escaping

All user-facing output is expected to be escaped using:

- `htmlspecialchars()`
- `e()` helper where applicable

---

## Open Source Release Verification

The repository was reviewed for public GitHub readiness.

### Verified

- Safe for public publication
- No production-only values
- No internal-only dependencies
- No regulated or private integrations
- No hidden proprietary modules
- Clean documentation suitable for OSS release

---

## Conclusion

**Secure Web Baseline** has been reviewed and prepared as a clean public open-source repository.

It is intended to serve as:

- A secure PHP MVC baseline
- A learning resource for secure web development
- A reusable open-source foundation for PHP applications

No sensitive information is included in the repository.
