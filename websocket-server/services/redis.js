/**
 * Redis Service
 * Handles Pub/Sub for notifications
 */

const redis = require('redis');

class RedisService {
    constructor() {
        this.client = null;
        this.subscriber = null;
    }

    async connect() {
        try {
            // Publisher client
            this.client = redis.createClient({
                socket: {
                    host: process.env.REDIS_HOST || 'redis',
                    port: process.env.REDIS_PORT || 6379
                }
            });

            // Subscriber client (separate connection for pub/sub)
            this.subscriber = redis.createClient({
                socket: {
                    host: process.env.REDIS_HOST || 'redis',
                    port: process.env.REDIS_PORT || 6379
                }
            });

            this.client.on('error', (err) => {
                console.error('[Redis] Client Error:', err);
            });

            this.subscriber.on('error', (err) => {
                console.error('[Redis] Subscriber Error:', err);
            });

            await this.client.connect();
            await this.subscriber.connect();

            console.log('[Redis] Connected successfully');
        } catch (error) {
            console.error('[Redis] Connection failed:', error);
            throw error;
        }
    }

    async disconnect() {
        try {
            if (this.client) {
                await this.client.quit();
            }
            if (this.subscriber) {
                await this.subscriber.quit();
            }
            console.log('[Redis] Disconnected successfully');
        } catch (error) {
            console.error('[Redis] Disconnect error:', error);
        }
    }

    /**
     * Subscribe to Redis channels with pattern matching
     * @param {string} pattern - Channel pattern (e.g., 'notifications:*')
     * @param {function} callback - Callback function (channel, message)
     */
    async subscribe(pattern, callback) {
        try {
            await this.subscriber.pSubscribe(pattern, (message, channel) => {
                callback(channel, message);
            });
            console.log(`[Redis] Subscribed to pattern: ${pattern}`);
        } catch (error) {
            console.error('[Redis] Subscribe error:', error);
            throw error;
        }
    }

    /**
     * Publish message to channel
     * @param {string} channel - Channel name
     * @param {object} data - Data to publish
     */
    async publish(channel, data) {
        try {
            const message = typeof data === 'string' ? data : JSON.stringify(data);
            await this.client.publish(channel, message);
        } catch (error) {
            console.error('[Redis] Publish error:', error);
            throw error;
        }
    }

    /**
     * Get value from Redis
     */
    async get(key) {
        try {
            return await this.client.get(key);
        } catch (error) {
            console.error('[Redis] Get error:', error);
            return null;
        }
    }

    /**
     * Set value in Redis
     */
    async set(key, value, expirySeconds = null) {
        try {
            if (expirySeconds) {
                await this.client.setEx(key, expirySeconds, value);
            } else {
                await this.client.set(key, value);
            }
        } catch (error) {
            console.error('[Redis] Set error:', error);
        }
    }
}

module.exports = RedisService;
