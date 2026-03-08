# Secure Web Baseline
![PHP](https://img.shields.io/badge/PHP-8.1+-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Security](https://img.shields.io/badge/security-CSRF%20%7C%20CSP%20%7C%20Sessions-red)
![Architecture](https://img.shields.io/badge/architecture-MVC-orange)
![Open Source](https://img.shields.io/badge/Open%20Source-Yes-brightgreen)
![Version](https://img.shields.io/badge/version-1.1.0-blue)

A lightweight, security-first PHP MVC starter framework for building secure web applications.

No heavy frameworks. No magic. Just clean, auditable PHP with security best practices baked in.

## Screenshots

### Homepage — Product Listing
The public-facing storefront displaying the latest smartphones, installment options, pricing in AED, and category navigation. Fully RTL Arabic layout with responsive design.

![Homepage](docs/screenshots/01-homepage.png)

### Admin Dashboard — Financial Overview
The administration panel showing real-time financial statistics: total sales, purchase cost, net profit, and order status breakdown. Includes session tracking and KYC counters.

![Admin Dashboard](docs/screenshots/02-admin-dashboard.png)

### Installment Plan — Order Detail
A detailed view of a single installment plan showing customer information, order number, monthly payment, interest rate, plan status, and attached documents.

![Installment Plan Detail](docs/screenshots/03-installment-plan.png)

### Payment Schedule — Notification Log
The full payment schedule table listing each instalment due date and amount, alongside a complete log of automated collection notifications (reminder → first warning → final warning).

![Payment Schedule](docs/screenshots/04-payment-schedule.png)

---

## Key Features

- **Session Hardening** — httponly, secure, samesite=strict cookies; idle timeout; periodic ID regeneration; browser/IP fingerprint-based hijack detection.
- **CSRF Protection** — Per-session token with constant-time verification; Origin/Referer header validation; auto-verified on every POST request.
- **Content Security Policy** — Strict CSP with a per-request nonce; no `unsafe-inline` or `unsafe-eval`.
- **Secure HTTP Headers** — X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, optional HSTS.
- **PDO Database Layer** — Singleton wrapper with prepared statements; query, fetch, insert, update, delete helpers; transaction support.
- **Input Validation** — Fluent validator for required, email, minLength, maxLength, integer, numeric, url, in, confirmed, and regex rules.
- **Authentication Flow** — Register, login (with session fixation prevention), logout, protected dashboard.
- **Rate Limiting** — IP-based throttle on login and register endpoints (max 10 attempts per 5-minute window).
- **Account Lockout** — Automatic 15-minute lockout after 5 consecutive failed login attempts.
- **Audit Logging** — Structured NDJSON log for all security events with automatic rotation at 10 MB.
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
│   │   ├── Database.php             # Singleton PDO wrapper
│   │   ├── RateLimiter.php          # IP rate limiting & account lockout
│   │   └── AuditLogger.php          # Security event audit log
│   ├── Models/
│   │   └── User.php                 # User CRUD operations
│   └── Views/
│       ├── auth/login.php
│       ├── auth/register.php
│       ├── dashboard/index.php
│       ├── home/index.php
│       └── errors/403.php, 404.php
├── config/
│   └── database.php                 # Database credentials (reads from .env)
├── docs/
│   └── screenshots/                 # UI screenshots for documentation
├── public/
│   ├── index.php                    # Front controller
│   └── .htaccess                    # Apache URL rewriting
├── scripts/
│   └── schema.sql                   # Database schema
├── storage/                         # Runtime files: audit log, rate limit data
│   └── .gitkeep
├── .env.example                     # Environment variable template
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

3. **Configure environment:**

   ```bash
   cp .env.example .env
   ```

   Then edit `.env` with your database credentials:

   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=secure_web_baseline
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

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

   **Option B — Nginx:**

   ```nginx
   server {
       listen 80;
       server_name secure-baseline.local;
       root /path/to/secure-web-baseline/public;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       # Block access to sensitive directories
       location ~ ^/(app|config|scripts|logs|storage)/ {
           deny all;
       }
   }
   ```

   **Option C — XAMPP / localhost:**

   Place the project in `htdocs/` and access it via `http://localhost/secure-web-baseline/`. The root `.htaccess` will route requests through `public/index.php`.

5. **Ensure storage directory is writable:**

   ```bash
   chmod 700 storage/
   ```

6. **Open in browser:**

   Navigate to `http://secure-baseline.local/` (or your configured URL).

## Default Database Credentials (Development)

| Setting  | Value                  |
| -------- | ---------------------- |
| Host     | `127.0.0.1`            |
| Port     | `3306`                 |
| Database | `secure_web_baseline`  |
| Username | `root`                 |
| Password | *(empty)*              |

> ⚠️ Always change these before deploying to production. Use `.env` to manage credentials securely.

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
| Rate Limiting      | Max 10 attempts per 5-minute window per IP on login and register             |
| Account Lockout    | 15-minute lockout after 5 consecutive failed login attempts                  |
| Audit Logging      | NDJSON log of all auth events with IP, user-agent, and timestamp             |

## Roadmap

See [ROADMAP.md](ROADMAP.md) for planned improvements including RBAC, password reset, 2FA, middleware, API auth, and more.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

[MIT](LICENSE)
