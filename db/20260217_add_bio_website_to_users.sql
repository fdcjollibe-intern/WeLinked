-- ============================================
-- Migration: Add bio and website columns to users table
-- Date: 2026-02-17
-- Description: Add bio and website fields for user profiles
-- ============================================

USE welinked_db;

-- Add bio column (max 180 characters)
ALTER TABLE users
ADD COLUMN bio VARCHAR(180) NULL AFTER gender;

-- Add website column (max 180 characters)
ALTER TABLE users
ADD COLUMN website VARCHAR(180) NULL AFTER bio;

-- Verify changes
DESCRIBE users;
