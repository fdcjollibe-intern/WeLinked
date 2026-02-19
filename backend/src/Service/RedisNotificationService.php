<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Log\Log;
use Redis;

/**
 * Redis Service for Pub/Sub notifications
 * Publishes events to Node.js WebSocket server
 */
class RedisNotificationService
{
    private ?Redis $redis = null;
    private bool $enabled = true;

    public function __construct()
    {
        try {
            // Check if Redis class exists (extension installed)
            if (!class_exists('Redis')) {
                Log::warning('Redis extension not installed, real-time notifications disabled');
                $this->enabled = false;
                return;
            }
            
            $this->redis = new Redis();
            $host = getenv('REDIS_HOST') ?: 'redis';
            $port = (int)(getenv('REDIS_PORT') ?: 6379);
            
            if (!$this->redis->connect($host, $port, 2.5)) {
                Log::warning('Redis connection failed, real-time notifications disabled');
                $this->enabled = false;
            }
        } catch (\Exception $e) {
            Log::error('Redis initialization error: ' . $e->getMessage());
            $this->enabled = false;
        }
    }

    /**
     * Publish notification to user's channel
     *
     * @param int $userId User ID to notify
     * @param string $type Notification type (comment, reaction, mention, etc.)
     * @param array $data Notification data
     * @return bool Success status
     */
    public function publishNotification(int $userId, string $type, array $data): bool
    {
        if (!$this->enabled || !$this->redis) {
            return false;
        }

        try {
            $channel = "notifications:{$userId}";
            $message = json_encode([
                'type' => $type,
                'data' => $data,
                'timestamp' => date('c')
            ]);

            $result = $this->redis->publish($channel, $message);
            Log::debug("Published to Redis channel {$channel}: {$type}", ['result' => $result]);
            
            return $result > 0;
        } catch (\Exception $e) {
            Log::error('Redis publish error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Publish unread count update
     *
     * @param int $userId User ID
     * @param int $count Unread notification count
     * @return bool Success status
     */
    public function publishUnreadCount(int $userId, int $count): bool
    {
        return $this->publishNotification($userId, 'unread_count', [
            'count' => $count
        ]);
    }

    /**
     * Publish new notification event
     *
     * @param int $userId User ID to notify
     * @param array $notification Notification data from database
     * @return bool Success status
     */
    public function publishNewNotification(int $userId, array $notification): bool
    {
        return $this->publishNotification($userId, 'new_notification', $notification);
    }

    /**
     * Check if Redis is connected and working
     *
     * @return bool Connection status
     */
    public function isConnected(): bool
    {
        if (!$this->enabled || !$this->redis) {
            return false;
        }

        try {
            return $this->redis->ping() === '+PONG';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Close Redis connection
     */
    public function __destruct()
    {
        if ($this->redis) {
            try {
                $this->redis->close();
            } catch (\Exception $e) {
                // Ignore errors on destruct
            }
        }
    }
}
