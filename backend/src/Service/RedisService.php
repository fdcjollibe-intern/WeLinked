<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Redis;
use Exception;

/**
 * Redis Service for Pub/Sub Notifications
 * Publishes real-time notifications to WebSocket server via Redis
 */
class RedisService
{
    private ?Redis $redis = null;
    private bool $connected = false;

    public function __construct()
    {
        $this->connect();
    }

    /**
     * Connect to Redis server
     */
    private function connect(): void
    {
        try {
            // Check if Redis class exists (extension installed)
            if (!class_exists('Redis')) {
                error_log('Redis extension not installed, connection disabled');
                $this->connected = false;
                return;
            }
            
            $config = Configure::read('Redis');
            
            $this->redis = new Redis();
            $this->connected = $this->redis->connect(
                $config['host'] ?? 'redis',
                $config['port'] ?? 6379,
                $config['timeout'] ?? 2.5
            );

            if ($this->connected && !empty($config['password'])) {
                $this->redis->auth($config['password']);
            }

            if ($this->connected && isset($config['database'])) {
                $this->redis->select($config['database']);
            }
        } catch (Exception $e) {
            error_log('Redis connection failed: ' . $e->getMessage());
            $this->connected = false;
        }
    }

    /**
     * Check if Redis is connected
     */
    public function isConnected(): bool
    {
        return $this->connected && $this->redis !== null;
    }

    /**
     * Publish notification to specific user
     *
     * @param int $userId User ID to send notification to
     * @param string $type Notification type (new_notification, update, etc.)
     * @param array $data Notification data
     * @return bool Success status
     */
    public function publishNotification(int $userId, string $type, array $data): bool
    {
        if (!$this->isConnected()) {
            error_log('Redis not connected, cannot publish notification');
            return false;
        }

        try {
            $message = json_encode([
                'userId' => $userId,
                'type' => $type,
                'data' => $data,
                'timestamp' => time()
            ]);

            $result = $this->redis->publish('notifications', $message);
            
            // Log successful publish
            if ($result > 0) {
                error_log("✓ Published $type notification to user $userId ($result subscribers)");
            }
            
            return $result !== false;
        } catch (Exception $e) {
            error_log('Redis publish failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Publish notification count update
     *
     * @param int $userId User ID
     * @param int $count Unread notification count
     * @return bool Success status
     */
    public function publishNotificationCount(int $userId, int $count): bool
    {
        return $this->publishNotification($userId, 'notification_count', [
            'count' => $count
        ]);
    }

    /**
     * Publish new notification event
     *
     * @param int $userId User ID
     * @param array $notification Notification data
     * @return bool Success status
     */
    public function publishNewNotification(int $userId, array $notification): bool
    {
        return $this->publishNotification($userId, 'new_notification', [
            'notification' => $notification
        ]);
    }

    /**
     * Get Redis info (for debugging)
     */
    public function getInfo(): array
    {
        if (!$this->isConnected()) {
            return ['connected' => false];
        }

        try {
            return [
                'connected' => true,
                'info' => $this->redis->info()
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Close Redis connection
     */
    public function close(): void
    {
        if ($this->redis !== null) {
            try {
                $this->redis->close();
            } catch (Exception $e) {
                error_log('Redis close error: ' . $e->getMessage());
            }
            $this->connected = false;
        }
    }

    /**
     * Destructor - ensure connection is closed
     */
    public function __destruct()
    {
        $this->close();
    }
}
