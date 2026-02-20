<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {

    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
      
        // Profile routes FIRST before fallbacks to ensure usernames map correctly
        $builder->connect('/profile/update', ['controller' => 'Profile', 'action' => 'update'])
            ->setMethods(['POST']);
        $builder->connect('/profile', ['controller' => 'Profile', 'action' => 'index', '_name' => 'profile_own']);
        $builder->connect('/profile/{username}/followers', ['controller' => 'Profile', 'action' => 'followers', '_name' => 'profile_followers'])
            ->setPass(['username'])
            ->setPatterns(['username' => '[a-zA-Z0-9_.]+']);
        $builder->connect('/profile/{username}/following', ['controller' => 'Profile', 'action' => 'following', '_name' => 'profile_following'])
            ->setPass(['username'])
            ->setPatterns(['username' => '[a-zA-Z0-9_.]+']);
        $builder->connect('/profile/{username}', ['controller' => 'Profile', 'action' => 'index', '_name' => 'profile_view'])
            ->setPass(['username'])
            ->setPatterns(['username' => '[a-zA-Z0-9_.]+']);

        // Fallback last to catch any extra /profile/{username}/* paths (e.g., legacy tabs)
        $builder->connect('/profile/{username}/*', ['controller' => 'Profile', 'action' => 'index', '_name' => 'profile_fallback'])
            ->setPass(['username'])
            ->setPatterns(['username' => '[a-zA-Z0-9_.]+']);
        
        // Ignore .well-known paths to prevent routing errors
        $builder->connect('/.well-known/*', ['controller' => 'Error', 'action' => 'error404']);
        
        $builder->connect('/', ['controller' => 'Login', 'action' => 'index']);
        $builder->connect('/login', ['controller' => 'Login', 'action' => 'index']);

        $builder->connect('/register', ['controller' => 'Register', 'action' => 'index']);
        
        $builder->connect('/forgot-password', ['controller' => 'Passwords', 'action' => 'forgot']);
        $builder->connect('/forgot-password/verify', ['controller' => 'Passwords', 'action' => 'verify']);
        $builder->connect('/forgot-password/reset', ['controller' => 'Passwords', 'action' => 'reset']);
        
        $builder->connect('/logout', ['controller' => 'Login', 'action' => 'logout']);
        $builder->connect('/dashboard', ['controller' => 'Dashboard', 'action' => 'index']);
        
        // Reels 
        $builder->connect('/reels', ['controller' => 'Reels', 'action' => 'index']);
        
        // Settings
        $builder->connect('/settings', ['controller' => 'Settings', 'action' => 'index']);
        $builder->connect('/settings/update-account', ['controller' => 'Settings', 'action' => 'updateAccount']);
        $builder->connect('/settings/update-password', ['controller' => 'Settings', 'action' => 'updatePassword']);
        $builder->connect('/settings/update-theme', ['controller' => 'Settings', 'action' => 'updateTheme']);
        $builder->connect('/settings/upload-profile-photo', ['controller' => 'Settings', 'action' => 'uploadProfilePhoto']);
        $builder->connect('/settings/enable-two-factor', ['controller' => 'Settings', 'action' => 'enableTwoFactor']);
        $builder->connect('/settings/disable-two-factor', ['controller' => 'Settings', 'action' => 'disableTwoFactor']);

        // Device Sessions (for logged in devices management)
        $builder->connect('/api/device-sessions', ['controller' => 'DeviceSessions', 'action' => 'index'])
            ->setMethods(['GET']);
        $builder->connect('/api/device-sessions/logout', ['controller' => 'DeviceSessions', 'action' => 'logoutDevice'])
            ->setMethods(['POST', 'DELETE']);
        $builder->connect('/api/device-sessions/logout-all', ['controller' => 'DeviceSessions', 'action' => 'logoutAllDevices'])
            ->setMethods(['POST']);

        // Component endpoints used by the Dashboard to fetch HTML fragments
        $builder->connect('/dashboard/left-sidebar', ['controller' => 'DashboardLeftSidebar', 'action' => 'index']);
        $builder->connect('/dashboard/middle-column', ['controller' => 'DashboardMiddleColumn', 'action' => 'index']);
        $builder->connect('/dashboard/right-sidebar', ['controller' => 'DashboardRightSidebar', 'action' => 'index']);

        // Backwards-compatible short paths (redirect to dashboard-prefixed)
        $builder->redirect('/left-sidebar', '/dashboard/left-sidebar', ['status' => 301]);
        $builder->redirect('/middle-column', '/dashboard/middle-column', ['status' => 301]);
        $builder->redirect('/right-sidebar', '/dashboard/right-sidebar', ['status' => 301]);

        // Upload endpoint for attachments (POST). Query param `type` = post|comment
        // DELETE requests to /dashboard/upload go to delete() action
        $builder->connect('/dashboard/upload', ['controller' => 'DashboardUploads', 'action' => 'delete'])
            ->setMethods(['DELETE']);
        // POST requests to /dashboard/upload go to upload() action
        $builder->connect('/dashboard/upload', ['controller' => 'DashboardUploads', 'action' => 'upload'])
            ->setMethods(['POST']);
        $builder->connect('/dashboard/upload/delete', ['controller' => 'DashboardUploads', 'action' => 'delete']);
        
        // Posts API - CRUD operations
        $builder->connect('/dashboard/posts/create', ['controller' => 'DashboardPosts', 'action' => 'create']);
        $builder->connect('/dashboard/posts/edit/{id}', ['controller' => 'DashboardPosts', 'action' => 'edit'])
            ->setPass(['id']);
        $builder->connect('/dashboard/posts/delete/{id}', ['controller' => 'DashboardPosts', 'action' => 'delete'])
            ->setPass(['id']);
        
        // Comments API
        $builder->connect('/dashboard/comments/create', ['controller' => 'DashboardComments', 'action' => 'create']);
        $builder->connect('/dashboard/comments/list', ['controller' => 'DashboardComments', 'action' => 'list']);
        $builder->connect('/dashboard/comments/edit', ['controller' => 'DashboardComments', 'action' => 'edit']);
        $builder->connect('/dashboard/comments/delete', ['controller' => 'DashboardComments', 'action' => 'delete']);
        $builder->connect('/dashboard/comments/add', ['controller' => 'DashboardComments', 'action' => 'add']);
        
        // Single post view
        $builder->connect('/post/{id}', ['controller' => 'DashboardPosts', 'action' => 'view'])
            ->setPass(['id'])
            ->setPatterns(['id' => '\d+']);
        
        // Reactions API (toggle/add/remove)
        $builder->connect('/dashboard/posts/react', ['controller' => 'DashboardReactions', 'action' => 'react']);
        $builder->connect('/dashboard/reactions/react', ['controller' => 'DashboardReactions', 'action' => 'react']);

        // Mentions API - Autocomplete for @mentions
        $builder->connect('/api/mentions/search', ['controller' => 'Mentions', 'action' => 'search']);
        
        // Auth API - WebSocket token verification
        $builder->connect('/api/auth/websocket-token', ['controller' => 'AuthApi', 'action' => 'generateWebSocketToken']);
        $builder->connect('/api/auth/verify-token', ['controller' => 'AuthApi', 'action' => 'verifyToken']);
        
        // Notifications API
        $builder->connect('/api/notifications', ['controller' => 'Notifications', 'action' => 'index']);
        $builder->connect('/api/notifications/unread-count', ['controller' => 'Notifications', 'action' => 'unreadCount']);
        $builder->connect('/api/notifications/mark-read/{id}', ['controller' => 'Notifications', 'action' => 'markAsRead'])
            ->setPass(['id']);
        $builder->connect('/api/notifications/mark-all-read', ['controller' => 'Notifications', 'action' => 'markAllAsRead']);

        // Friends API
        $builder->connect('/friends', ['controller' => 'Friends', 'action' => 'index']);
        $builder->connect('/api/friends/suggestions', ['controller' => 'Friends', 'action' => 'suggestions']);
        $builder->connect('/api/friends/count', ['controller' => 'Friends', 'action' => 'count']);
        $builder->connect('/friends/follow', ['controller' => 'Friends', 'action' => 'follow']);
        $builder->connect('/friends/unfollow', ['controller' => 'Friends', 'action' => 'unfollow']);

        // Birthdays API
        $builder->connect('/birthdays', ['controller' => 'Birthdays', 'action' => 'index']);
        $builder->connect('/birthdays/get-count', ['controller' => 'Birthdays', 'action' => 'getCount']);
        $builder->connect('/birthdays/get-sidebar-data', ['controller' => 'Birthdays', 'action' => 'getSidebarData']);
        $builder->connect('/birthday', ['controller' => 'Birthdays', 'action' => 'list']);
        $builder->connect('/birthday/sent', ['controller' => 'Birthdays', 'action' => 'sent']);
        $builder->connect('/birthday/messages', ['controller' => 'Birthdays', 'action' => 'messages']);
        $builder->connect('/birthday/check-today', ['controller' => 'Birthdays', 'action' => 'checkToday']);
        $builder->connect('/birthday/get-message-count', ['controller' => 'Birthdays', 'action' => 'getMessageCount']);
        $builder->connect('/birthday/mark-as-read/{id}', ['controller' => 'Birthdays', 'action' => 'markAsRead'])
            ->setPass(['id']);
        $builder->connect('/birthday/send-message', ['controller' => 'Birthdays', 'action' => 'sendMessage']);
        $builder->connect('/birthday/edit-message/{id}', ['controller' => 'Birthdays', 'action' => 'editMessage'])
            ->setPass(['id']);
        $builder->connect('/birthday/delete-message/{id}', ['controller' => 'Birthdays', 'action' => 'deleteMessage'])
            ->setPass(['id']);

        // Search API
        $builder->connect('/search', ['controller' => 'Search', 'action' => 'index']);
        $builder->connect('/api/search/suggest', ['controller' => 'Search', 'action' => 'suggest']);

        // Users API
        $builder->connect('/users/current-profile', ['controller' => 'Users', 'action' => 'currentProfile']);

        // Backwards-compatible redirects for API paths
        $builder->redirect('/api/upload', '/dashboard/upload', ['status' => 301]);
        $builder->redirect('/api/posts/create', '/dashboard/posts/create', ['status' => 301]);

        // Enable fallbacks for any unmatched routes
        $builder->fallbacks();
    });
};