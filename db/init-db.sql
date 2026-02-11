-- Initialize WeLinked Database
USE welinked_db;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test users (password is 'password123' for both)
INSERT INTO users (username, email, password, created, modified) VALUES
('admin', 'admin@welinked.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFGz4K8X9Cqhq.l5c2kL5DfLhwLJdtGC', NOW(), NOW()),
('testuser', 'test@welinked.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFGz4K8X9Cqhq.l5c2kL5DfLhwLJdtGC', NOW(), NOW())
ON DUPLICATE KEY UPDATE modified = NOW();
