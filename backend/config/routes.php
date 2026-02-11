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
        $builder->connect('/logout', ['controller' => 'Login', 'action' => 'logout']);
        $builder->connect('/dashboard', ['controller' => 'Users', 'action' => 'dashboard']);

       

        


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
