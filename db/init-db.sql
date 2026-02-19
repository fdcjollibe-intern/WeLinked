-- ============================================
-- WeLinked Database - Complete Schema
-- ============================================
-- Description: Social media platform database schema
-- Version: 1.0
-- Last Updated: 2026-02-12
-- ============================================

-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS welinked_db;
CREATE DATABASE welinked_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE welinked_db;

-- ============================================
-- CORE TABLES (Existing)
-- ============================================

-- ============================================
-- Table: users
-- Description: Stores user account information
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_photo_path VARCHAR(255) NULL,
    gender ENUM('Male', 'Female', 'Prefer not to say') NOT NULL DEFAULT 'Prefer not to say',
    theme_preference ENUM('system', 'light', 'dark') NOT NULL DEFAULT 'system',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: posts
-- Description: Main posts/threads created by users
-- ============================================
CREATE TABLE IF NOT EXISTS posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    content_text TEXT NULL,
    content_image_path VARCHAR(255) NULL,
    location VARCHAR(255) NULL,
    is_reel BOOLEAN NULL DEFAULT NULL COMMENT 'Whether this post should be displayed as a reel (true for posts with single video)',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    
    CONSTRAINT fk_posts_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    INDEX idx_posts_user_id (user_id),
    INDEX idx_posts_created_at (created_at DESC),
    INDEX idx_posts_deleted_at (deleted_at),
    INDEX idx_posts_user_created (user_id, created_at DESC),
    INDEX idx_posts_deleted_created (deleted_at, created_at DESC),
    INDEX idx_posts_is_reel (is_reel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: comments
-- Description: Comments on posts
-- ============================================
CREATE TABLE IF NOT EXISTS comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    content_text TEXT NULL,
    content_image_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    
    CONSTRAINT fk_comments_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_comments_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    INDEX idx_comments_post_id (post_id),
    INDEX idx_comments_user_id (user_id),
    INDEX idx_comments_created_at (created_at DESC),
    INDEX idx_comments_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: likes
-- Description: Legacy simple likes table (kept for compatibility)
-- ============================================
CREATE TABLE IF NOT EXISTS likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    target_type ENUM('post', 'comment') NOT NULL,
    target_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_likes_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    UNIQUE KEY uniq_user_target (user_id, target_type, target_id),
    INDEX idx_likes_user_id (user_id),
    INDEX idx_likes_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NEW TABLES (Additional Features)
-- ============================================

-- ============================================
-- Table: friendships
-- Description: Manages follow/following relationships
-- Usage: When user A follows user B, insert (follower_id=A, following_id=B)
-- ============================================
CREATE TABLE IF NOT EXISTS friendships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    follower_id BIGINT UNSIGNED NOT NULL COMMENT 'User who follows',
    following_id BIGINT UNSIGNED NOT NULL COMMENT 'User being followed',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_friendships_follower
        FOREIGN KEY (follower_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_friendships_following
        FOREIGN KEY (following_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    UNIQUE KEY uniq_follower_following (follower_id, following_id),
    INDEX idx_friendships_follower (follower_id),
    INDEX idx_friendships_following (following_id),
    INDEX idx_friendships_follower_created (follower_id, created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table: reactions
-- Description: Emoji reactions on posts and comments
-- Reaction Types: like, haha, love, wow, sad, angry
-- Note: One reaction per user per post/comment (enforced by unique key)
-- ============================================
CREATE TABLE IF NOT EXISTS reactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    target_type ENUM('post', 'comment') NOT NULL,
    target_id BIGINT UNSIGNED NOT NULL,
    reaction_type ENUM('like', 'haha', 'love', 'wow', 'sad', 'angry') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_reactions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    UNIQUE KEY uniq_user_target_reaction (user_id, target_type, target_id),
    INDEX idx_reactions_target (target_type, target_id),
    INDEX idx_reactions_user (user_id),
    INDEX idx_reactions_type (target_type, target_id, reaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: post_attachments
-- Description: Multiple images/videos per post
-- Max 250MB per file
-- Display order controls gallery sequence
-- ============================================
CREATE TABLE IF NOT EXISTS post_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'video') NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL COMMENT 'Size in bytes (max 250MB = 262144000 bytes)',
    display_order TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Order in gallery (0, 1, 2...)',
    upload_status ENUM('uploading', 'completed', 'failed') NOT NULL DEFAULT 'uploading',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_post_attachments_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,
    
    INDEX idx_post_attachments_post (post_id),
    INDEX idx_post_attachments_order (post_id, display_order),
    INDEX idx_post_attachments_status (upload_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: comment_attachments
-- Description: Single image or video per comment
-- Max 250MB per file
-- ============================================
CREATE TABLE IF NOT EXISTS comment_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comment_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'video') NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL COMMENT 'Size in bytes (max 250MB = 262144000 bytes)',
    upload_status ENUM('uploading', 'completed', 'failed') NOT NULL DEFAULT 'uploading',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_comment_attachments_comment
        FOREIGN KEY (comment_id)
        REFERENCES comments(id)
        ON DELETE CASCADE,
    
    UNIQUE KEY uniq_comment_attachment (comment_id),
    INDEX idx_comment_attachments_comment (comment_id),
    INDEX idx_comment_attachments_status (upload_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: activities
-- Description: Activity feed/notifications for users
-- Activity Types: follow, reaction, comment, post
-- ============================================
CREATE TABLE IF NOT EXISTS activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'User who will see this activity',
    actor_id BIGINT UNSIGNED NOT NULL COMMENT 'User who performed the action',
    activity_type ENUM('follow', 'reaction', 'comment', 'post') NOT NULL,
    target_type ENUM('user', 'post', 'comment') NULL,
    target_id BIGINT UNSIGNED NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_activities_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_activities_actor
        FOREIGN KEY (actor_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    INDEX idx_activities_user (user_id, created_at DESC),
    INDEX idx_activities_is_read (user_id, is_read),
    INDEX idx_activities_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- USEFUL QUERIES FOR CAKEPHP IMPLEMENTATION
-- ============================================

-- Query 1: Get "For You" feed (mixed posts from friends and random users)
-- SELECT p.*, u.username, u.full_name, u.profile_photo_path
-- FROM posts p
-- INNER JOIN users u ON p.user_id = u.id
-- LEFT JOIN friendships f ON p.user_id = f.following_id AND f.follower_id = ?
-- WHERE p.deleted_at IS NULL
-- ORDER BY RAND()
-- LIMIT 20 OFFSET ?;

-- Query 2: Get "Friends" feed (posts only from followed users)
-- SELECT p.*, u.username, u.full_name, u.profile_photo_path
-- FROM posts p
-- INNER JOIN users u ON p.user_id = u.id
-- INNER JOIN friendships f ON p.user_id = f.following_id
-- WHERE f.follower_id = ? AND p.deleted_at IS NULL
-- ORDER BY p.created_at DESC
-- LIMIT 20 OFFSET ?;

-- Query 3: Get post with attachments
-- SELECT p.*, pa.id as attachment_id, pa.file_path, pa.file_type, pa.display_order
-- FROM posts p
-- LEFT JOIN post_attachments pa ON p.id = pa.post_id
-- WHERE p.id = ? AND p.deleted_at IS NULL
-- ORDER BY pa.display_order ASC;

-- Query 4: Get reaction counts for a post
-- SELECT reaction_type, COUNT(*) as count
-- FROM reactions
-- WHERE target_type = 'post' AND target_id = ?
-- GROUP BY reaction_type;

-- Query 5: Check if user already reacted
-- SELECT reaction_type
-- FROM reactions
-- WHERE user_id = ? AND target_type = 'post' AND target_id = ?;

-- Query 6: Get suggested users to follow
-- SELECT u.id, u.username, u.full_name, u.profile_photo_path
-- FROM users u
-- WHERE u.id NOT IN (
--     SELECT following_id FROM friendships WHERE follower_id = ?
-- )
-- AND u.id != ?
-- LIMIT 10;

-- Query 7: Check if currently following a user
-- SELECT id FROM friendships
-- WHERE follower_id = ? AND following_id = ?;

-- Query 8: Get unread activity count
-- SELECT COUNT(*) as unread_count
-- FROM activities
-- WHERE user_id = ? AND is_read = FALSE;

-- Query 9: Get comments with attachments for a post
-- SELECT c.*, u.username, u.full_name, u.profile_photo_path,
--        ca.file_path, ca.file_type
-- FROM comments c
-- INNER JOIN users u ON c.user_id = u.id
-- LEFT JOIN comment_attachments ca ON c.id = ca.comment_id
-- WHERE c.post_id = ? AND c.deleted_at IS NULL
-- ORDER BY c.created_at ASC;

-- Query 10: Get who liked/reacted to a post
-- SELECT u.username, u.full_name, u.profile_photo_path, r.reaction_type
-- FROM reactions r
-- INNER JOIN users u ON r.user_id = u.id
-- WHERE r.target_type = 'post' AND r.target_id = ?
-- ORDER BY r.created_at DESC;

-- ============================================
-- END OF SCHEMA
-- ============================================