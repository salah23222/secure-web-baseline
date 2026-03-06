# Contributing to Secure Web Baseline

Thank you for your interest in contributing! This document provides guidelines for contributing to the project.

## How to Contribute

1. **Fork** the repository on GitHub.
2. **Clone** your fork locally.
3. **Create a branch** for your change: `git checkout -b feature/my-improvement`
4. **Make your changes** following the code style guidelines below.
5. **Test** your changes locally.
6. **Commit** with a clear, descriptive message.
7. **Push** your branch and open a **Pull Request** against `main`.

## Pull Request Guidelines

- Keep PRs focused on a single change or feature.
- Describe what your PR does and why.
- Reference any related issues.
- Ensure the project still works end-to-end (register, login, dashboard, logout).
- Do not introduce external framework dependencies (Laravel, Symfony, etc.).

## Code Style

- PHP 8.1+ with `declare(strict_types=1);` in every PHP file.
- Follow PSR-12 coding style.
- Use meaningful variable and method names.
- Add comments only where the logic is not self-evident.
- All database queries must use PDO prepared statements.
- All user output must be escaped with `htmlspecialchars()` or the `e()` helper.
- Do not use `eval()`, `extract()` on user input, or other unsafe functions.

## What We Welcome

- Security improvements and hardening.
- Bug fixes.
- Documentation improvements.
- New security features that fit the lightweight philosophy (e.g., rate limiting, audit logging).
- Test coverage.

## What We Do Not Accept

- Introduction of heavy frameworks or large dependency trees.
- Business-specific features (e-commerce, payment, etc.).
- Changes that break the lightweight, educational nature of the project.

## Reporting Security Issues

Please see [SECURITY.md](SECURITY.md) for instructions on reporting vulnerabilities privately.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
