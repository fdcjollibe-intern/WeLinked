<?php
// Simple test to verify posts query works
require dirname(__DIR__) . '/vendor/autoload.php';

use Cake\Datasource\ConnectionManager;
use Cake\Database\Connection;

// Load database config
$config = require dirname(__DIR__) . '/config/app.php';
ConnectionManager::setConfig($config['Datasources']);

$connection = ConnectionManager::get('default');

echo "<h1>Testing Posts Query</h1>\n";

try {
    // Test basic query
    $posts = $connection->execute('SELECT id, user_id, content_text, created_at FROM posts WHERE deleted_at IS NULL ORDER BY RAND() LIMIT 10')->fetchAll('assoc');
    
    echo "<h2>Found " . count($posts) . " posts:</h2>\n<pre>\n";
    print_r($posts);
    echo "</pre>\n";
    
    // Test with user join
    $postsWithUser = $connection->execute('SELECT p.id, p.user_id, p.content_text, p.created_at, u.username, u.full_name FROM posts p INNER JOIN users u ON p.user_id = u.id WHERE p.deleted_at IS NULL ORDER BY RAND() LIMIT 10')->fetchAll('assoc');
    
    echo "<h2>Posts with users:</h2>\n<pre>\n";
    print_r($postsWithUser);
    echo "</pre>\n";
    
} catch (Exception $e) {
    echo "<h2>ERROR:</h2>\n<pre>" . $e->getMessage() . "</pre>\n";
}
