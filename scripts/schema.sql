-- =========================================================
-- Secure Web Baseline — Complete Database Schema v1.3.0
-- =========================================================
-- Run this file to initialize the database from scratch:
--   mysql -u root -p < scripts/schema.sql
--
-- Includes:
--   - users
--   - password_resets
-- =========================================================

CREATE DATABASE IF NOT EXISTS secure_web_baseline
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE secure_web_baseline;

-- ---------------------------------------------------------
-- Users table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)  NOT NULL,
    email         VARCHAR(190)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          VARCHAR(50)   NOT NULL DEFAULT 'user',
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Password reset tokens table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(190)  NOT NULL,
    token_hash VARCHAR(255)  NOT NULL,
    expires_at DATETIME      NOT NULL,
    used       TINYINT(1)    NOT NULL DEFAULT 0,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_pr_email      (email),
    INDEX idx_pr_token_hash (token_hash),
    INDEX idx_pr_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
