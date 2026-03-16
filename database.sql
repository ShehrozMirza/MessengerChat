-- ============================================
-- FastMessenger Database Setup
-- ============================================
-- Run this file in phpMyAdmin or MySQL CLI:
--   mysql -u root < database.sql
-- ============================================


-- 1. Create the database
CREATE DATABASE IF NOT EXISTS robinsnest
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;


-- 2. Create the database user
CREATE USER IF NOT EXISTS 'robinsnest'@'localhost'
  IDENTIFIED BY 'password';

GRANT ALL PRIVILEGES ON robinsnest.*
  TO 'robinsnest'@'localhost';

FLUSH PRIVILEGES;


-- 3. Select the database
USE robinsnest;


-- ============================================
-- TABLE: members (user accounts)
-- ============================================
CREATE TABLE IF NOT EXISTS members (
    user    VARCHAR(16),
    pass    VARCHAR(255),
    email   VARCHAR(255),
    INDEX(user(6))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
-- TABLE: messages (public & private messages)
-- ============================================
-- pm = '0' for public, '1' for private
CREATE TABLE IF NOT EXISTS messages (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auth    VARCHAR(16),
    recip   VARCHAR(16),
    pm      CHAR(1),
    time    INT UNSIGNED,
    message VARCHAR(4096),
    image   VARCHAR(255),
    audio   VARCHAR(255),
    INDEX(auth(6)),
    INDEX(recip(6))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
-- TABLE: friends (follow relationships)
-- ============================================
-- A row means 'user' follows 'friend'.
-- Mutual friendship = both directions exist.
CREATE TABLE IF NOT EXISTS friends (
    user    VARCHAR(16),
    friend  VARCHAR(16),
    INDEX(user(6)),
    INDEX(friend(6))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
-- TABLE: profiles (user bios)
-- ============================================
CREATE TABLE IF NOT EXISTS profiles (
    user    VARCHAR(16),
    text    VARCHAR(4096),
    INDEX(user(6))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
-- TABLE: password_resets (reset tokens)
-- ============================================
CREATE TABLE IF NOT EXISTS password_resets (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user    VARCHAR(16),
    token   VARCHAR(64),
    expires INT UNSIGNED,
    INDEX(token(12))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
-- CLEANUP QUERIES (run when needed)
-- ============================================

-- Clean data only (keep member accounts):
-- DELETE FROM messages;
-- DELETE FROM friends;
-- DELETE FROM profiles;
-- DELETE FROM password_resets;

-- Full reset (remove everything):
-- DELETE FROM messages;
-- DELETE FROM friends;
-- DELETE FROM profiles;
-- DELETE FROM password_resets;
-- DELETE FROM members;
