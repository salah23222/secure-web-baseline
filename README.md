# Secure Web Baseline
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Security](https://img.shields.io/badge/security-CSRF%20%7C%20CSP%20%7C%20Sessions-red)
![Architecture](https://img.shields.io/badge/architecture-MVC-orange)
![Open Source](https://img.shields.io/badge/Open%20Source-Yes-brightgreen)

A lightweight, security-first PHP MVC starter framework for building secure web applications.

No heavy frameworks. No magic. Just clean, auditable PHP with security best practices baked in.

## Key Features

- **Session Hardening** — httponly, secure, samesite=strict cookies; idle timeout; periodic ID regeneration; browser/IP fingerprint-based hijack detection.
- **CSRF Protection** — Per-session token with constant-time verification; Origin/Referer header validation; auto-verified on every POST request.
- **Content Security Policy** — Strict CSP with a per-request nonce; no `unsafe-inline` or `unsafe-eval`.
- **Secure HTTP Headers** — X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, optional HSTS.
- **PDO Database Layer** — Singleton wrapper with prepared statements; query, fetch, insert, update, delete helpers; transaction support.
- **Input Validation** — Fluent validator for required, email, minLength, maxLength, integer, numeric, url, in, confirmed, and regex rules.
- **Authentication Flow** — Register, login (with session fixation prevention), logout, protected dashboard.
- **Clean MVC Structure** — Controllers, Models, Views, Core classes, and a simple URI-based router.

## Project Structure

```
secure-web-baseline/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php       # Landing page & health check
│   │   ├── AuthController.php       # Register, login, logout
│   │   └── DashboardController.php  # Protected dashboard
│   ├── Core/
│   │   ├── bootstrap.php            # Autoloader, session, headers, helpers
│   │   ├── Router.php               # URI-based GET/POST router
│   │   ├── Session.php              # Secure session management
│   │   ├── CSRF.php                 # CSRF token generation & verification
│   │   ├── SecurityHeaders.php      # HTTP security headers + CSP nonce
│   │   ├── Validator.php            # Fluent input validator
│   │   └── Database.php             # Singleton PDO wrapper
│   ├── Models/
│   │   └── User.php                 # User CRUD operations
│   └── Views/
│       ├── auth/login.php
│       ├── auth/register.php
│       ├── dashboard/index.php
│       ├── home/index.php
│       └── errors/403.php, 404.php
├── config/
│   └── database.php                 # Database credentials
├── public/
│   ├── index.php                    # Front controller
│   └── .htaccess                    # Apache URL rewriting
├── scripts/
│   └── schema.sql                   # Database schema
├── .htaccess                        # Root rewrite to public/
├── README.md
├── SECURITY.md
├── CONTRIBUTING.md
├── ROADMAP.md
├── CHANGELOG.md
├── LICENSE                          # MIT
└── VERSION
```

## Requirements

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled (or Nginx with equivalent rewrite rules)

## Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/salah23222/secure-web-baseline.git
   cd secure-web-baseline
   ```

2. **Create the database:**

   ```bash
   mysql -u root -p < scripts/schema.sql
   ```

3. **Configure database credentials:**

   Edit `config/database.php` with your MySQL host, username, password, and database name.

4. **Set up your web server:**

   **Option A — Apache Virtual Host (recommended):**

   Point the document root to the `public/` directory:

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

   **Option B — XAMPP / localhost:**

   Place the project in `htdocs/` and access it via `http://localhost/secure-web-baseline/`. The root `.htaccess` will route requests through `public/index.php`.

5. **Open in browser:**

   Navigate to `http://secure-baseline.local/` (or your configured URL).

## Available Routes

| Method | Path        | Description                    | Auth Required |
| ------ | ----------- | ------------------------------ | ------------- |
| GET    | `/`         | Landing page                   | No            |
| GET    | `/health`   | JSON health check              | No            |
| GET    | `/register` | Registration form              | No            |
| POST   | `/register` | Process registration           | No            |
| GET    | `/login`    | Login form                     | No            |
| POST   | `/login`    | Process login                  | No            |
| GET    | `/logout`   | Log out and destroy session    | No            |
| GET    | `/dashboard`| Protected user dashboard       | Yes           |

## Authentication Flow

1. **Register** at `/register` with name, email, and password (min 8 characters).
2. **Log in** at `/login`. On success, the session ID is regenerated (preventing fixation) and the user is redirected to `/dashboard`.
3. The **dashboard** is protected — unauthenticated users are redirected to `/login`.
4. **Log out** at `/logout` to destroy the session and return to the login page.

## Security Notes

| Layer              | Implementation                                                              |
| ------------------ | --------------------------------------------------------------------------- |
| Sessions           | httponly, secure (HTTPS), samesite=strict, 30-min idle timeout, 15-min regen, fingerprint check |
| CSRF               | 64-byte token, constant-time comparison, Origin/Referer validation, auto-verified on POST |
| CSP                | `default-src 'self'`; scripts and styles require a per-request nonce         |
| Headers            | DENY framing, nosniff, strict referrer policy, permissions policy, HSTS      |
| SQL Injection      | All queries use PDO prepared statements                                      |
| XSS                | All output uses `htmlspecialchars()` via the `e()` helper                    |
| Session Fixation   | `session_regenerate_id(true)` on login                                       |
| User Enumeration   | Generic error message on login failure                                       |
| Password Storage   | `password_hash()` with `PASSWORD_DEFAULT` (currently bcrypt)                 |

## Roadmap

See [ROADMAP.md](ROADMAP.md) for planned improvements including RBAC, rate limiting, audit logging, middleware, API auth, and more.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

[MIT](LICENSE)
