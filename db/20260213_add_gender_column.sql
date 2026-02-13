-- ============================================
-- Migration: Add gender column to users table
-- Date: 2026-02-13
-- Description: Adds gender field with default value
-- ============================================

USE welinked_db;

-- Check if column exists and add it if not
SET @dbname = 'welinked_db';
SET @tablename = 'users';
SET @columnname = 'gender';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM(\'Male\', \'Female\', \'Prefer not to say\') NOT NULL DEFAULT \'Prefer not to say\' AFTER profile_photo_path')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for gender (optional, for future filtering/analytics)
SET @preparedStatement2 = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = 'idx_users_gender'
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_users_gender ON ', @tablename, '(gender)')
));
PREPARE alterIndexIfNotExists FROM @preparedStatement2;
EXECUTE alterIndexIfNotExists;
DEALLOCATE PREPARE alterIndexIfNotExists;
