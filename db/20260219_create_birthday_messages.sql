-- ============================================
-- Migration: Create Birthday Messages Table
-- Created: 2026-02-19
-- Description: Stores birthday messages sent between users
-- ============================================

USE welinked_db;

CREATE TABLE IF NOT EXISTS birthday_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    
    CONSTRAINT fk_birthday_messages_sender
        FOREIGN KEY (sender_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    CONSTRAINT fk_birthday_messages_recipient
        FOREIGN KEY (recipient_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    INDEX idx_birthday_messages_recipient (recipient_id, deleted_at),
    INDEX idx_birthday_messages_sender (sender_id, deleted_at),
    INDEX idx_birthday_messages_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Notes:
-- - is_read tracks if recipient has viewed the message
-- - deleted_at for soft deletes
-- - Indexes for efficient querying of sent/received messages
-- ============================================
