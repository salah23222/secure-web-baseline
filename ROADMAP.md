# Roadmap

Planned improvements for Secure Web Baseline, organized by phase.

## Phase 1 — Hardening

- [x] Rate limiting on authentication endpoints (login, register). ✅ v1.1.0
- [x] Account lockout after repeated failed login attempts. ✅ v1.1.0
- [ ] Configurable password policy (minimum length, complexity rules).
- [x] Audit logging for security-relevant events (login, logout, failed attempts). ✅ v1.1.0

## Phase 2 — Access Control

- [ ] Role-Based Access Control (RBAC) middleware.
- [ ] Admin guard for protected routes.
- [ ] Per-route permission checks.
- [ ] Example admin dashboard with user management.

## Phase 3 — Infrastructure

- [ ] Middleware pipeline (before/after request hooks).
- [x] Environment-based configuration (`.env` file loader). ✅ v1.1.0
- [ ] Structured error and exception handler with log levels.
- [ ] Database migration runner.

## Phase 4 — Extended Security

- [ ] Secure file upload handler with type validation and storage isolation.
- [ ] Two-factor authentication (TOTP).
- [ ] Password reset flow with time-limited tokens.
- [ ] Content Security Policy reporting endpoint.

## Phase 5 — API

- [ ] API token authentication (Bearer tokens).
- [ ] JSON request/response helpers.
- [ ] API rate limiting.
- [ ] CORS configuration.

## Phase 6 — Developer Experience

- [ ] CLI tool for common tasks (create controller, run migrations).
- [ ] PHPUnit test scaffold and example tests.
- [ ] Docker Compose setup for local development.
- [ ] GitHub Actions CI pipeline.

---

Contributions toward any roadmap item are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md).
