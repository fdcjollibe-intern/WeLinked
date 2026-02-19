-- ============================================
-- Migration: Add Birthday Fields to Users Table
-- Created: 2026-02-19
-- Description: Adds birthdate and is_birthday_public fields to users table
-- ============================================

USE welinked_db;

-- Add birthdate column (DATE type to store only the date, not time)
ALTER TABLE users 
ADD COLUMN birthdate DATE NULL AFTER gender,
ADD COLUMN is_birthday_public TINYINT(1) NOT NULL DEFAULT 0 AFTER birthdate,
ADD INDEX idx_users_birthdate (birthdate);

-- Update existing users to have NULL birthdate by default
-- is_birthday_public defaults to 0 (false) for privacy

-- ============================================
-- Notes:
-- - birthdate is nullable to allow users who haven't set it yet
-- - is_birthday_public defaults to 0 (private) for user privacy
-- - Index on birthdate for efficient birthday queries
-- ============================================
