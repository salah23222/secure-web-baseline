-- =========================================================
-- Password Reset Tokens — Migration
-- =========================================================
-- Run after schema.sql:
--   mysql -u root -p secure_web_baseline < scripts/migration_password_reset.sql
-- =========================================================

USE secure_web_baseline;

CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(190)  NOT NULL,
    token_hash VARCHAR(255)  NOT NULL,
    expires_at DATETIME      NOT NULL,
    used       TINYINT(1)    NOT NULL DEFAULT 0,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_pr_email      (email),
    INDEX idx_pr_token_hash (token_hash),
    INDEX idx_pr_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
