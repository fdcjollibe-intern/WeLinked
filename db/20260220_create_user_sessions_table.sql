-- ============================================
-- Migration: Create user_sessions table
-- Description: Track user device sessions for login management
-- Date: 2026-02-20
-- ============================================

USE welinked_db;

CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_id VARCHAR(40) NOT NULL,
    websocket_id VARCHAR(255) NULL,
    device_type ENUM('desktop', 'mobile', 'tablet') NOT NULL DEFAULT 'desktop',
    device_name VARCHAR(100) NULL,
    browser_name VARCHAR(50) NULL,
    browser_version VARCHAR(20) NULL,
    os_name VARCHAR(50) NULL,
    os_version VARCHAR(20) NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    country VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    is_current BOOLEAN NOT NULL DEFAULT FALSE,
    last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_user_sessions_user_id
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,
    
    INDEX idx_user_sessions_user_id (user_id),
    INDEX idx_user_sessions_session_id (session_id),
    INDEX idx_user_sessions_websocket_id (websocket_id),
    INDEX idx_user_sessions_last_activity (last_activity),
    INDEX idx_user_sessions_user_activity (user_id, last_activity DESC),
    
    UNIQUE KEY unique_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;