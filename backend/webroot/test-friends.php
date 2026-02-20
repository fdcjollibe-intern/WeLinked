<?php
/**
 * Test script to debug friendships fetching
 */
// Bootstrap the application
require dirname(__DIR__) . '/config/bootstrap.php';

use Cake\ORM\TableRegistry;

// Get friendships table
$friendshipsTable = TableRegistry::getTableLocator()->get('Friendships');
$usersTable = TableRegistry::getTableLocator()->get('Users');

// Test user ID (usually the logged-in user)
$currentUserId = 2;

echo "<h1>Testing Friendships</h1>\n";

// Test 1: Get Friends (people current user follows)
echo "<h2>Test 1: Get Friends (Following)</h2>\n";
try {
    $friendsQuery = $friendshipsTable->getFriends($currentUserId);
    echo "<pre>SQL: " . $friendsQuery->sql() . "</pre>\n";
    
    $friends = $friendsQuery->all();
    echo "<p>Found " . count($friends) . " friends</p>\n";
    
    foreach ($friends as $friendship) {
        echo "<pre>";
        echo "Friendship ID: " . $friendship->id . "\n";
        echo "Follower ID: " . $friendship->follower_id . "\n";
        echo "Following ID: " . $friendship->following_id . "\n";
        echo "Has 'following' property: " . (property_exists($friendship, 'following') ? 'yes' : 'no') . "\n";
        echo "Following data: ";
        var_dump($friendship->following ?? 'NULL');
        echo "</pre>\n";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

// Test 2: Get Followers (people who follow current user)
echo "<h2>Test 2: Get Followers</h2>\n";
try {
    $followersQuery = $friendshipsTable->getFollowers($currentUserId);
    echo "<pre>SQL: " . $followersQuery->sql() . "</pre>\n";
    
    $followers = $followersQuery->all();
    echo "<p>Found " . count($followers) . " followers</p>\n";
    
    foreach ($followers as $friendship) {
        echo "<pre>";
        echo "Friendship ID: " . $friendship->id . "\n";
        echo "Follower ID: " . $friendship->follower_id . "\n";
        echo "Following ID: " . $friendship->following_id . "\n";
        echo "Has 'followers' property: " . (property_exists($friendship, 'followers') ? 'yes' : 'no') . "\n";
        echo "Followers data: ";
        var_dump($friendship->followers ?? 'NULL');
        echo "</pre>\n";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

// Test 3: Direct query
echo "<h2>Test 3: Check Users Table</h2>\n";
$users = $usersTable->find()->select(['id', 'username', 'full_name'])->limit(5)->all();
echo "<p>Found " . count($users) . " users</p>\n";
foreach ($users as $user) {
    echo "<p>User {$user->id}: {$user->username} ({$user->full_name})</p>\n";
}
