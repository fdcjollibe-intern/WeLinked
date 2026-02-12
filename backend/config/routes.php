<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {

    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
      
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

        // Component endpoints used by the Dashboard to fetch HTML fragments
        $builder->connect('/dashboard/left-sidebar', ['controller' => 'DashboardLeftSidebar', 'action' => 'index']);
        $builder->connect('/dashboard/middle-column', ['controller' => 'DashboardMiddleColumn', 'action' => 'index']);
        $builder->connect('/dashboard/right-sidebar', ['controller' => 'DashboardRightSidebar', 'action' => 'index']);

        // Backwards-compatible short paths (redirect to dashboard-prefixed)
        $builder->redirect('/left-sidebar', '/dashboard/left-sidebar', ['status' => 301]);
        $builder->redirect('/middle-column', '/dashboard/middle-column', ['status' => 301]);
        $builder->redirect('/right-sidebar', '/dashboard/right-sidebar', ['status' => 301]);

        // Upload endpoint for attachments (POST). Query param `type` = post|comment
        $builder->connect('/dashboard/upload', ['controller' => 'DashboardUploads', 'action' => 'upload']);
        // Posts API
        $builder->connect('/dashboard/posts/create', ['controller' => 'DashboardPosts', 'action' => 'create']);
        // Reactions API (toggle/add/remove)
        $builder->connect('/dashboard/posts/react', ['controller' => 'DashboardReactions', 'action' => 'react']);

        // Backwards-compatible redirects for API paths
        $builder->redirect('/api/upload', '/dashboard/upload', ['status' => 301]);
        $builder->redirect('/api/posts/create', '/dashboard/posts/create', ['status' => 301]);






        


        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
