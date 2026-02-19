<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Auth API Controller
 * Handles authentication verification for WebSocket server
 */
class AuthApiController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->disableAutoLayout();
    }
    
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow verifyToken to be called without authentication
        // This is needed for the WebSocket server to validate tokens
        $this->Authentication->addUnauthenticatedActions(['verifyToken']);
    }

    /**
     * Verify authentication token for WebSocket connection
     * Called by Node.js WebSocket server to validate user tokens
     */
    public function verifyToken()
    {
        $this->request->allowMethod(['post']);
        
        // Get token from request body
        $token = $this->request->getData('token');
        
        if (!$token) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'No token provided'
            ], 400);
        }
        
        // Validate token format: userId:hash
        $parts = explode(':', $token, 2);
        if (count($parts) !== 2) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid token format'
            ], 401);
        }
        
        [$userId, $hash] = $parts;
        
        // Verify hash
        $expectedHash = $this->generateTokenHash((int)$userId);
        if (!hash_equals($expectedHash, $hash)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }
        
        // Load user to get additional info
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($userId);
        
        return $this->jsonResponse([
            'success' => true,
            'userId' => $user->id,
            'username' => $user->username,
            'fullName' => $user->full_name
        ]);
    }
    
    /**
     * Generate WebSocket token for current user
     * Returns a token that can be used to authenticate WebSocket connections
     */
    public function generateWebSocketToken()
    {
        $this->request->allowMethod(['get']);
        
        $identity = $this->request->getAttribute('identity');
        
        if (!$identity) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        // Generate token: userId:hash
        $token = $identity->id . ':' . $this->generateTokenHash($identity->id);
        
        return $this->jsonResponse([
            'success' => true,
            'token' => $token,
            'expiresIn' => 3600 // 1 hour
        ]);
    }
    
    /**
     * Generate a secure hash for WebSocket token
     */
    private function generateTokenHash(int $userId): string
    {
        $secret = \Cake\Core\Configure::read('Security.salt');
        
        // Fallback to a default if not configured (should not happen in production)
        if (empty($secret)) {
            $secret = '269ce9bd98b994e5b3baa433be9dd4bc269ce9bd98b994e5b3baa433be9dd4bc';
        }
        
        $timestamp = floor(time() / 3600); // Changes every hour
        return hash_hmac('sha256', $userId . ':' . $timestamp, $secret);
    }

    /**
     * Helper method to return JSON responses
     */
    private function jsonResponse(array $data, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }
}
