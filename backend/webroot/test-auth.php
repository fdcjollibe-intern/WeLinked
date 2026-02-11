<?php
// Test authentication configuration
require dirname(__DIR__) . '/vendor/autoload.php';

use Authentication\AuthenticationService;
use Authentication\Identifier\PasswordIdentifier;
use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Datasource\ConnectionManager;

// Configure database connection
ConnectionManager::setConfig('default', [
    'className' => Connection::class,
    'driver' => Mysql::class,
    'persistent' => false,
    'host' => 'db',
    'username' => 'welinked',
    'password' => 'welinked@!password',
    'database' => 'welinked_db',
    'encoding' => 'utf8mb4',
    'timezone' => 'UTC',
]);

echo "✓ Database connection configured\n\n";

// Test authentication service
try {
    $authenticationService = new AuthenticationService([
        'unauthenticatedRedirect' => '/login',
        'queryParam' => 'redirect',
    ]);

    $authenticationService->loadAuthenticator('Authentication.Session');
    $authenticationService->loadAuthenticator('Authentication.Form', [
        'fields' => [
            'username' => 'username',
            'password' => 'password',
        ],
        'loginUrl' => '/login',
        'identifier' => [
            'className' => 'Authentication.Password',
            'fields' => [
                'username' => 'username',
                'password' => 'password',
            ],
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Users',
            ],
        ],
    ]);

    echo "✓ Authentication service configured successfully\n";
    echo "✓ Session authenticator loaded\n";
    echo "✓ Form authenticator with Password identifier loaded\n";
    echo "✓ ORM resolver configured for Users model\n\n";
    
    echo "Authentication setup is READY!\n\n";
    echo "Test credentials:\n";
    echo "  - Username: admin, Password: password123\n";
    echo "  - Username: testuser, Password: password123\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
