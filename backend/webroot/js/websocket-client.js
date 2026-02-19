/**
 * WebSocket Client for Real-time Notifications
 * Connects to Node.js WebSocket server via Socket.io
 */

class WebSocketClient {
    constructor() {
        this.socket = null;
        this.connected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.onNotificationCallback = null;
        this.onCountUpdateCallback = null;
        this.authToken = null;
    }

    /**
     * Initialize WebSocket connection
     * @param {string} token - Authentication token from session
     */
    async connect(token) {
        if (this.connected || !token) {
            return;
        }

        this.authToken = token;

        try {
            // Socket.io client (loaded from CDN in HTML)
            if (typeof io === 'undefined') {
                console.error('%c[WebSocket] ❌ Socket.io client not loaded!', 'color: #EF4444; font-weight: bold;');
                throw new Error('Socket.io not loaded');
            }

            const wsUrl = window.WEBSOCKET_URL || 'http://localhost:3000';
            
            // Disable Socket.io verbose logging
            if (typeof localStorage !== 'undefined') {
                localStorage.removeItem('debug');  // Clear any debug settings
            }
            
            this.socket = io(wsUrl, {
                auth: {
                    token: token
                },
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionAttempts: this.maxReconnectAttempts,
                reconnectionDelay: 1000,
                reconnectionDelayMax: 5000,
                timeout: 20000
            });

            this.attachEventListeners();

        } catch (error) {
            console.error('[WebSocket] Connection error:', error);
            throw error;
        }
    }

    /**
     * Attach Socket.io event listeners
     */
    attachEventListeners() {
        // Connection successful
        this.socket.on('connect', () => {
            this.connected = true;
            this.reconnectAttempts = 0;
            console.log('%c[WebSocket] ✓ Connected successfully', 'color: #10B981;');
        });

        // Authentication successful
        this.socket.on('authenticated', (data) => {
            // Authenticated successfully
        });

        // Incoming notification
        this.socket.on('notification', (data) => {
            this.handleNotification(data);
        });

        // Disconnection
        this.socket.on('disconnect', (reason) => {
            this.connected = false;
            console.warn('%c[WebSocket] ⚠️ Disconnected - Reason: ' + reason, 'color: #F59E0B; font-weight: bold;');

            // Auto-reconnect unless disconnect was intentional
            if (reason === 'io server disconnect') {
                console.log('[WebSocket] Server disconnected, attempting reconnect...');
                // Server kicked us out, try to reconnect
                this.socket.connect();
            }
        });

        // Connection error
        this.socket.on('connect_error', (error) => {
            console.error('%c[WebSocket] ❌ Connection error:', 'color: #EF4444; font-weight: bold;', error.message);
            console.error('[WebSocket] Error details:', error);
            this.reconnectAttempts++;

            if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                console.warn('%c[WebSocket] ⛔ Max reconnection attempts (' + this.maxReconnectAttempts + ') reached', 'color: #DC2626; font-weight: bold;');
                console.log('[WebSocket] Switching to EGG MODE (polling)...');
                this.disconnect();
                // Trigger fallback to polling
                if (window.notificationsManager) {
                    window.notificationsManager.enablePollingFallback();
                }
            }
        });

        // Reconnection attempt
        this.socket.on('reconnect_attempt', (attemptNumber) => {
            console.log(`[WebSocket] Reconnection attempt ${attemptNumber}`);
        });

        // Reconnection successful
        this.socket.on('reconnect', (attemptNumber) => {
            console.log(`[WebSocket] Reconnected after ${attemptNumber} attempts`);
            this.reconnectAttempts = 0;
            // Refresh notifications after reconnection
            if (window.notificationsManager) {
                window.notificationsManager.fetchUnreadCount();
            }
        });
    }

    /**
     * Handle incoming notification
     */
    handleNotification(data) {
        const { type, data: payload } = data;

        switch(type) {
            case 'new_notification':
                if (this.onNotificationCallback) {
                    this.onNotificationCallback(payload);
                }
                // Update UI
                if (window.notificationsManager) {
                    window.notificationsManager.fetchUnreadCount();
                    // Show browser notification if permitted
                    this.showBrowserNotification(payload);
                }
                break;

            case 'unread_count':
                if (this.onCountUpdateCallback) {
                    this.onCountUpdateCallback(payload.count);
                }
                // Update badge
                if (window.notificationsManager) {
                    window.notificationsManager.updateUnreadBadge(payload.count);
                }
                break;

            case 'notification:read':
                // Another device marked notification as read
                if (window.notificationsManager) {
                    window.notificationsManager.fetchUnreadCount();
                }
                break;

            default:
                console.log('[WebSocket] Unknown notification type:', type);
        }
    }

    /**
     * Show browser notification
     */
    showBrowserNotification(notification) {
        if (!('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'granted') {
            const actor = notification.actor || {};
            new Notification('WeLinked', {
                body: notification.message || 'You have a new notification',
                icon: actor.profile_photo || '/assets/logo.png',
                badge: '/assets/logo.png',
                tag: `notification-${notification.id}`,
                requireInteraction: false
            });
        }
    }

    /**
     * Register callback for new notifications
     */
    onNotification(callback) {
        this.onNotificationCallback = callback;
    }

    /**
     * Register callback for count updates
     */
    onCountUpdate(callback) {
        this.onCountUpdateCallback = callback;
    }

    /**
     * Emit notification read event
     */
    markNotificationRead(notificationId) {
        if (this.connected && this.socket) {
            this.socket.emit('notification:read', notificationId);
        }
    }

    /**
     * Disconnect from WebSocket server
     */
    disconnect() {
        if (this.socket) {
            this.socket.disconnect();
            this.socket = null;
            this.connected = false;
            console.log('[WebSocket] Disconnected manually');
        }
    }

    /**
     * Check if connected
     */
    isConnected() {
        return this.connected && this.socket && this.socket.connected;
    }

    /**
     * Request browser notification permission
     */
    static async requestNotificationPermission() {
        if (!('Notification' in window)) {
            console.log('[WebSocket] Browser notifications not supported');
            return false;
        }

        if (Notification.permission === 'granted') {
            return true;
        }

        if (Notification.permission !== 'denied') {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }

        return false;
    }
}

// Export for use in other modules
window.WebSocketClient = WebSocketClient;
