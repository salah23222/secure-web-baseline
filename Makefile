.PHONY: install test cs cs-fix schema purge help

# ── Default ──────────────────────────────────────────────────────────
help:
	@echo ""
	@echo "  Secure Web Baseline — Available Commands"
	@echo ""
	@echo "  make install    Install dev dependencies via Composer"
	@echo "  make test       Run PHPUnit test suite"
	@echo "  make cs         Check code style (PSR-12, dry-run)"
	@echo "  make cs-fix     Apply code style fixes"
	@echo "  make schema     Initialize database from schema.sql"
	@echo "  make purge      Purge expired rate-limit files"
	@echo ""

# ── Dependencies ─────────────────────────────────────────────────────
install:
	composer install --no-interaction --prefer-dist --optimize-autoloader

# ── Tests ────────────────────────────────────────────────────────────
test:
	vendor/bin/phpunit --testdox

# ── Code Style ───────────────────────────────────────────────────────
cs:
	vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.php

cs-fix:
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php

# ── Database ─────────────────────────────────────────────────────────
schema:
	@read -p "MySQL user [root]: " user; \
	user=$${user:-root}; \
	mysql -u $$user -p < scripts/schema.sql && echo "Schema applied."

# ── Maintenance ──────────────────────────────────────────────────────
purge:
	php scripts/purge_rate_limits.php
