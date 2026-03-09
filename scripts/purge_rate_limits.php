#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Purge expired rate-limit and account-lockout files.
 *
 * Run via cron (every 15 minutes recommended):
 *   *\/15 * * * * php /path/to/secure-web-baseline/scripts/purge_rate_limits.php
 *
 * Or manually:
 *   php scripts/purge_rate_limits.php
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');

require_once APP_PATH . '/Core/RateLimiter.php';

$deleted = \App\Core\RateLimiter::purgeExpired();

echo '[' . date('c') . "] Purged {$deleted} expired rate-limit file(s).\n";
