<?php
/**
 * Test script to verify reels query logic
 */

// Simple PDO connection
$host = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'welinked';
$user = getenv('DB_USER') ?: 'welinked_user';
$pass = getenv('DB_PASS') ?: 'welinked_password';

try {
    $connection = new PDO(
        "mysql:host={$host};dbname={$dbName};charset=utf8mb4",
        $user,
        $pass
    );
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "=== Testing Reels Query Logic ===\n\n";

// Step 1: Find all posts and their attachment counts
echo "Step 1: All posts with their attachment counts:\n";
$stmt = $connection->query("
    SELECT 
        p.id,
        p.content_text,
        COUNT(pa.id) as attachment_count,
        GROUP_CONCAT(pa.file_type) as file_types
    FROM posts p
    LEFT JOIN post_attachments pa ON p.id = pa.post_id AND pa.upload_status = 'completed'
    WHERE p.deleted_at IS NULL
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 20
");

$allPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($allPosts as $post) {
    $content = substr($post['content_text'] ?? 'No content', 0, 50);
    echo "  Post #{$post['id']}: {$post['attachment_count']} attachments ({$post['file_types']}) - {$content}\n";
}

// Step 2: Test the subquery - posts with exactly 1 attachment
echo "\nStep 2: Posts with exactly 1 attachment:\n";
$stmt = $connection->query("
    SELECT 
        post_id,
        COUNT(*) as count
    FROM post_attachments
    WHERE upload_status = 'completed'
    GROUP BY post_id
    HAVING COUNT(*) = 1
");

$singleAttachment = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($singleAttachment) . " posts with exactly 1 attachment:\n";
foreach ($singleAttachment as $pa) {
    echo "  Post #{$pa['post_id']}\n";
}

// Step 3: Test the full query - posts with exactly 1 attachment that is a video
echo "\nStep 3: Posts with exactly 1 attachment AND it's a video:\n";
$stmt = $connection->query("
    SELECT 
        p.id,
        p.content_text,
        pa.file_type,
        pa.file_path
    FROM posts p
    INNER JOIN post_attachments pa ON pa.post_id = p.id
    WHERE p.deleted_at IS NULL
        AND pa.file_type = 'video'
        AND pa.upload_status = 'completed'
        AND p.id IN (
            SELECT post_id
            FROM post_attachments
            WHERE upload_status = 'completed'
            GROUP BY post_id
            HAVING COUNT(*) = 1
        )
    ORDER BY p.created_at DESC
");

$reelsPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($reelsPosts) . " posts for reels:\n";
foreach ($reelsPosts as $post) {
    $content = substr($post['content_text'] ?? 'No content', 0, 50);
    echo "  Post #{$post['id']}: {$post['file_type']} - {$content}\n";
    echo "    Video: {$post['file_path']}\n";
}

echo "\n=== Test Complete ===\n";
