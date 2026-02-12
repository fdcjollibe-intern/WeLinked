<?php
// Simple database connection test
$host = 'db';
$db = 'welinked_db';
$user = 'welinked';
$pass = 'welinked@password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connected successfully\n\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists\n\n";
        
        // Get user count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Users in database: $count\n\n";
        
        // List users (without passwords)
        $stmt = $pdo->query("SELECT id, username, email, created FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "User list:\n";
        foreach ($users as $user) {
            echo "  - ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}\n";
        }
    } else {
        echo "✗ Users table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}
