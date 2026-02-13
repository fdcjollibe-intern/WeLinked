-- ============================================
-- Migration: Posts, Mentions, and Notifications Enhancement
-- Date: 2026-02-13
-- Description: Add mentions, notifications, and location support
-- ============================================

USE welinked_db;

-- ============================================
-- Add location field to posts table
-- ============================================
SET @dbname = 'welinked_db';
SET @tablename = 'posts';
SET @columnname = 'location';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL AFTER content_image_path')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- Table: mentions
-- Description: Track @mentions in posts
-- ============================================
CREATE TABLE IF NOT EXISTS mentions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    mentioned_user_id BIGINT UNSIGNED NOT NULL COMMENT 'User who was mentioned',
    mentioned_by_user_id BIGINT UNSIGNED NOT NULL COMMENT 'User who created the mention',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_mentions_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_mentions_mentioned_user
        FOREIGN KEY (mentioned_user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_mentions_mentioned_by_user
        FOREIGN KEY (mentioned_by_user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    UNIQUE KEY uniq_post_mentioned_user (post_id, mentioned_user_id),
    INDEX idx_mentions_mentioned_user (mentioned_user_id),
    INDEX idx_mentions_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: notifications
-- Description: User notifications system
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'User receiving the notification',
    actor_id BIGINT UNSIGNED NULL COMMENT 'User who triggered the notification',
    type ENUM('mention', 'reaction', 'comment', 'follow') NOT NULL,
    target_type ENUM('post', 'comment', 'user') NULL,
    target_id BIGINT UNSIGNED NULL COMMENT 'ID of the target (post_id, comment_id, user_id)',
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_notifications_actor
        FOREIGN KEY (actor_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    INDEX idx_notifications_user_read (user_id, is_read, created_at DESC),
    INDEX idx_notifications_type (type),
    INDEX idx_notifications_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Add performance indexes
-- ============================================

-- Index for fetching user's posts efficiently (if not exists)
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'welinked_db' AND TABLE_NAME = 'posts' AND INDEX_NAME = 'idx_posts_user_created');
SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX idx_posts_user_created ON posts(user_id, created_at DESC)', 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index for feed queries (non-deleted posts)
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'welinked_db' AND TABLE_NAME = 'posts' AND INDEX_NAME = 'idx_posts_deleted_created');
SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX idx_posts_deleted_created ON posts(deleted_at, created_at DESC)', 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Composite index for friendships queries
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'welinked_db' AND TABLE_NAME = 'friendships' AND INDEX_NAME = 'idx_friendships_follower_created');
SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX idx_friendships_follower_created ON friendships(follower_id, created_at DESC)', 
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- END OF MIGRATION
-- ============================================
