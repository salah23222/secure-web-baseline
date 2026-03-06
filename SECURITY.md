# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability in Secure Web Baseline, **please do not open a public issue.**

Instead, report it privately using one of these methods:

1. **GitHub Security Advisories** — Use the "Report a vulnerability" button on the repository's Security tab (preferred).
2. **Email** — If GitHub advisories are not available, contact the maintainer directly via the email listed in their GitHub profile.

Please include:

- A clear description of the vulnerability.
- Steps to reproduce.
- Potential impact assessment.

We aim to acknowledge reports within 48 hours and provide a fix or mitigation within 7 days for critical issues.

## Security Philosophy

Secure Web Baseline is designed around the principle of **defense in depth**:

- **Session hardening** — httponly, secure, samesite=strict cookies; idle timeout; periodic regeneration; browser fingerprint checks.
- **CSRF protection** — Per-session tokens verified on every POST; Origin/Referer header validation.
- **Content Security Policy** — Strict CSP with per-request nonces; no `unsafe-inline` or `unsafe-eval`.
- **Secure headers** — X-Frame-Options DENY, X-Content-Type-Options nosniff, strict Referrer-Policy, Permissions-Policy lockdown, HSTS when HTTPS is detected.
- **Prepared statements** — All database queries use PDO prepared statements to prevent SQL injection.
- **Output encoding** — All user-supplied data is escaped with `htmlspecialchars()` before rendering.
- **No user enumeration** — Login errors use a generic message that does not reveal whether an email exists.
- **Session fixation prevention** — Session ID is regenerated on login.

## Scope

This project is a **starter framework** intended to demonstrate secure patterns. It is not a hardened production application out of the box. Before deploying to production:

- Move database credentials to environment variables.
- Enable HTTPS and verify HSTS is active.
- Review and tighten the CSP policy for your specific assets.
- Add rate limiting to authentication endpoints.
- Implement audit logging.
- Consider adding two-factor authentication.
