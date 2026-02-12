-- Migration: create reactions table
-- Run this against your database if the `reactions` table is not present.

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
);
