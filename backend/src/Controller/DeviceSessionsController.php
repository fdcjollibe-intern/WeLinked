<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * DeviceSessions Controller
 * 
 * Handles viewing and managing user device sessions
 */
class DeviceSessionsController extends AppController
{
    /**
     * Get user's logged in devices
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index()
    {
        $this->request->allowMethod(['get']);
        $identity = $this->request->getAttribute('identity');
        
        if (!$identity) {
            return $this->response
                ->withStatus(401)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]));
        }

        try {
            $userSessionsTable = $this->fetchTable('UserSessions');
            $currentSessionId = $this->request->getSession()->id();
            
            // Debug logging
            error_log('=== DEVICE SESSIONS REQUEST ===');
            error_log('Current Session ID: ' . $currentSessionId);
            error_log('User ID: ' . $identity->id);
            
            $sessions = $userSessionsTable->findActiveByUser($identity->id)
                ->select([
                    'id',
                    'session_id', 
                    'device_type',
                    'device_name',
                    'browser_name',
                    'browser_version',
                    'os_name',
                    'os_version',
                    'country',
                    'city',
                    'last_activity',
                    'created_at'
                ])
                ->toArray();

            // Mark current session and format data
            $formattedSessions = [];
            error_log('Processing ' . count($sessions) . ' sessions:');
            foreach ($sessions as $session) {
                $isCurrent = ($session->session_id === $currentSessionId);
                
                // Detailed comparison logging
                error_log('  Session: ' . $session->session_id);
                error_log('    - Matches current? ' . ($isCurrent ? 'YES' : 'NO'));
                error_log('    - Current ID: ' . $currentSessionId);
                error_log('    - Session ID: ' . $session->session_id);
                error_log('    - Lengths: current=' . strlen($currentSessionId) . ', session=' . strlen($session->session_id));
                
                $formattedSessions[] = [
                    'id' => $session->id,
                    'session_id' => $session->session_id,
                    'device_type' => $session->device_type,
                    'device_name' => $session->device_name ?: $this->getDefaultDeviceName($session),
                    'browser_name' => $session->browser_name,
                    'browser_version' => $session->browser_version,
                    'os_name' => $session->os_name,
                    'os_version' => $session->os_version,
                    'location' => $this->formatLocation($session->country, $session->city),
                    'last_activity' => $session->last_activity->format('Y-m-d H:i:s'),
                    'created_at' => $session->created_at->format('Y-m-d H:i:s'),
                    'is_current' => (bool)$isCurrent,
                    'icon' => $this->getDeviceIcon($session->device_type)
                ];
            }
            
            // Debug log
            error_log('Current Session ID: ' . $currentSessionId);
            error_log('Formatted Sessions: ' . json_encode($formattedSessions));

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'sessions' => $formattedSessions,
                    'total' => count($formattedSessions)
                ]));

        } catch (\Exception $e) {
            error_log('Failed to fetch user sessions: ' . $e->getMessage());
            
            return $this->response
                ->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to fetch sessions'
                ]));
        }
    }

    /**
     * Logout from a specific device
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function logoutDevice()
    {
        $this->request->allowMethod(['post', 'delete']);
        $identity = $this->request->getAttribute('identity');
        
        if (!$identity) {
            return $this->response
                ->withStatus(401)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]));
        }

        $sessionId = $this->request->getData('session_id');
        $isCurrent = $this->request->getData('is_current', false);
        
        if (!$sessionId) {
            return $this->response
                ->withStatus(400)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Session ID is required'
                ]));
        }

        try {
            $userSessionsTable = $this->fetchTable('UserSessions');
            
            // Verify session belongs to current user
            $session = $userSessionsTable->find()
                ->where([
                    'user_id' => $identity->id,
                    'session_id' => $sessionId
                ])
                ->first();

            if (!$session) {
                return $this->response
                    ->withStatus(404)
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Session not found'
                    ]));
            }

            // Check if it's current session and handle logout accordingly
            $currentSessionId = $this->request->getSession()->id();
            $isCurrentSession = $sessionId === $currentSessionId;
            
            if ($isCurrentSession && $isCurrent) {
                // Logout current user - this will invalidate the session
                $this->Authentication->logout();
                
                // Delete the session record
                $deleted = $userSessionsTable->invalidateSessions($identity->id, $sessionId);
                
                // Notify WebSocket server to disconnect this session
                $this->notifyWebSocketDisconnect($session->websocket_id);
                
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Current device logged out successfully',
                        'logout' => true
                    ]));
            }

            // Delete the session record (CakePHP will handle session cleanup)
            $deleted = $userSessionsTable->invalidateSessions($identity->id, $sessionId);
            
            error_log('Logout device: Deleted ' . $deleted . ' session records');
            
            // Also invalidate the actual session if it exists
            $this->invalidatePhpSession($sessionId);
            error_log('Logout device: Invalidated PHP session: ' . $sessionId);
            
            // Notify WebSocket server to disconnect this session immediately
            $this->notifyWebSocketDisconnect($session->websocket_id);
            
            // Also send a broadcast to force-check all user sessions
            $this->notifyWebSocketSessionInvalidated($identity->id, $sessionId);

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Device logged out successfully',
                    'deleted_count' => $deleted
                ]));

        } catch (\Exception $e) {
            error_log('Failed to logout device: ' . $e->getMessage());
            
            return $this->response
                ->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to logout device'
                ]));
        }
    }

    /**
     * Logout from all devices except current
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function logoutAllDevices()
    {
        $this->request->allowMethod(['post']);
        $identity = $this->request->getAttribute('identity');
        
        if (!$identity) {
            return $this->response
                ->withStatus(401)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]));
        }

        try {
            $userSessionsTable = $this->fetchTable('UserSessions');
            $currentSessionId = $this->request->getSession()->id();
            
            // Get all sessions to notify WebSocket server
            $allSessions = $userSessionsTable->find()
                ->where([
                    'user_id' => $identity->id,
                    'session_id !=' => $currentSessionId
                ])
                ->select(['session_id', 'websocket_id'])
                ->toArray();

            // Delete all sessions except current
            $deleted = $userSessionsTable->deleteAll([
                'user_id' => $identity->id,
                'session_id !=' => $currentSessionId
            ]);
            
            error_log('Logout all devices: Deleted ' . $deleted . ' session records');

            // Invalidate PHP sessions and notify WebSocket
            foreach ($allSessions as $session) {
                $this->invalidatePhpSession($session->session_id);
                error_log('Logout all: Invalidated PHP session: ' . $session->session_id);
                if ($session->websocket_id) {
                    $this->notifyWebSocketDisconnect($session->websocket_id);
                }
            }
            
            // Broadcast session invalidation to all user's connections
            $this->notifyWebSocketSessionInvalidatedAll($identity->id, $currentSessionId);

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Logged out from all other devices successfully',
                    'deleted_count' => $deleted
                ]));

        } catch (\Exception $e) {
            error_log('Failed to logout all devices: ' . $e->getMessage());
            
            return $this->response
                ->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to logout all devices'
                ]));
        }
    }

    /**
     * Get default device name based on OS/Browser
     * 
     * @param object $session Session object
     * @return string Default device name
     */
    private function getDefaultDeviceName($session): string
    {
        $os = $session->os_name;
        $browser = $session->browser_name;
        
        if ($os && $browser) {
            return "{$browser} on {$os}";
        } elseif ($os) {
            return "{$os} Device";
        } elseif ($browser) {
            return "{$browser} Browser";
        }
        
        return 'Unknown Device';
    }

    /**
     * Format location string
     * 
     * @param string|null $country Country name
     * @param string|null $city City name  
     * @return string Formatted location
     */
    private function formatLocation(?string $country, ?string $city): string
    {
        if ($city && $country) {
            return "{$city}, {$country}";
        } elseif ($country) {
            return $country;
        } elseif ($city) {
            return $city;
        }
        
        return 'Unknown Location';
    }

    /**
     * Get device icon class based on device type
     * 
     * @param string $deviceType Device type
     * @return string Icon class name
     */
    private function getDeviceIcon(string $deviceType): string
    {
        switch ($deviceType) {
            case 'mobile':
                return 'fas fa-mobile-alt';
            case 'tablet':
                return 'fas fa-tablet-alt';
            case 'desktop':
            default:
                return 'fas fa-laptop';
        }
    }

    /**
     * Invalidate PHP session by ID
     * 
     * @param string $sessionId Session ID to invalidate
     * @return void
     */
    private function invalidatePhpSession(string $sessionId): void
    {
        try {
            error_log('[DeviceSessionsController] invalidatePhpSession called for: ' . $sessionId);
            
            // Since CakePHP uses database sessions, we can delete from sessions table
            $connection = \Cake\Datasource\ConnectionManager::get('default');
            
            // Check if session exists first
            $checkQuery = $connection->execute('SELECT COUNT(*) as count FROM sessions WHERE id = ?', [$sessionId]);
            $existsBefore = $checkQuery->fetch('assoc')['count'];
            error_log('[DeviceSessionsController] Sessions in PHP sessions table BEFORE delete: ' . $existsBefore);
            
            $result = $connection->delete('sessions', ['id' => $sessionId]);
            error_log('[DeviceSessionsController] Delete executed successfully');
            
            // Verify deletion
            $checkQuery2 = $connection->execute('SELECT COUNT(*) as count FROM sessions WHERE id = ?', [$sessionId]);
            $existsAfter = $checkQuery2->fetch('assoc')['count'];
            error_log('[DeviceSessionsController] Sessions in PHP sessions table AFTER delete: ' . $existsAfter);
            
        } catch (\Exception $e) {
            error_log('[DeviceSessionsController] Failed to invalidate PHP session ' . $sessionId . ': ' . $e->getMessage());
        }
    }

    /**
     * Notify WebSocket server to disconnect a user
     * 
     * @param string|null $websocketId WebSocket connection ID
     * @return void
     */
    private function notifyWebSocketDisconnect(?string $websocketId): void
    {
        if (!$websocketId) {
            return;
        }

        try {
            // Send notification to WebSocket server
            $wsServerUrl = env('WEBSOCKET_SERVER_URL', 'http://localhost:3001');
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $wsServerUrl . '/disconnect');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['websocket_id' => $websocketId]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            error_log('Failed to notify WebSocket disconnect: ' . $e->getMessage());
        }
    }

    /**
     * Notify WebSocket server about session invalidation for immediate validation
     * 
     * @param int $userId User ID
     * @param string $sessionId Session ID that was invalidated
     * @return void
     */
    private function notifyWebSocketSessionInvalidated(int $userId, string $sessionId): void
    {
        try {
            $wsServerUrl = env('WEBSOCKET_SERVER_URL', 'http://localhost:3001');
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $wsServerUrl . '/invalidate-session');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'user_id' => $userId,
                'session_id' => $sessionId
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            error_log('Failed to notify WebSocket session invalidation: ' . $e->getMessage());
        }
    }

    /**
     * Notify WebSocket server about all sessions invalidation (logout all)
     * 
     * @param int $userId User ID
     * @param string $currentSessionId Current session to keep
     * @return void
     */
    private function notifyWebSocketSessionInvalidatedAll(int $userId, string $currentSessionId): void
    {
        try {
            $wsServerUrl = env('WEBSOCKET_SERVER_URL', 'http://localhost:3001');
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $wsServerUrl . '/invalidate-all-sessions');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'user_id' => $userId,
                'except_session_id' => $currentSessionId
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            error_log('Failed to notify WebSocket all sessions invalidation: ' . $e->getMessage());
        }
    }
}