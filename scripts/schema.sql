-- =========================================================
-- Secure Web Baseline — Database Schema
-- =========================================================
-- Run this file to initialize the database:
--   mysql -u root < scripts/schema.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS secure_web_baseline
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE secure_web_baseline;

-- ---------------------------------------------------------
-- Users table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)  NOT NULL,
    email         VARCHAR(190)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          VARCHAR(50)   NOT NULL DEFAULT 'user',
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
