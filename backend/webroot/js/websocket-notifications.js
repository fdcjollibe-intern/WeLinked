/**
 * WebSocket Notifications Manager
 * Real-time notifications using Socket.io
 */

class WebSocketNotificationsManager {
    constructor() {
        this.socket = null;
        this.unreadCount = 0;
        this.notifications = [];
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.connected = false;
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.connectWebSocket();
    }

    /**
     * Connect to WebSocket server
     */
    connectWebSocket() {
        try {
            // Get authentication token from cookies or session storage
            const token = this.getAuthToken();
            
            if (!token) {
                console.error('[WebSocket] No authentication token found');
                this.fallbackToPolling();
                return;
            }

            // Connect to WebSocket server
            const wsUrl = window.location.hostname === 'localhost' 
                ? 'http://localhost:3000' 
                : `wss://${window.location.hostname}:3000`;

            this.socket = io(wsUrl, {
                auth: {
                    token: token
                },
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionDelay: 1000,
                reconnectionDelayMax: 5000,
                reconnectionAttempts: this.maxReconnectAttempts,
                // Disable Socket.io debug logs
                debug: false,
                logger: false
            });

            this.setupSocketListeners();
        } catch (error) {
            console.error('[WebSocket] Connection error:', error);
            this.fallbackToPolling();
        }
    }

    /**
     * Setup Socket.io event listeners
     */
    setupSocketListeners() {
        // Connection successful
        this.socket.on('connect', () => {
            this.connected = true;
            this.reconnectAttempts = 0;
            this.fetchInitialNotifications();
        });

        // Authentication confirmed
        this.socket.on('authenticated', (data) => {
            // Authenticated
        });

        // New notification received
        this.socket.on('notification', (data) => {
            this.handleIncomingNotification(data);
        });

        // Connection error
        this.socket.on('connect_error', (error) => {
            this.reconnectAttempts++;
            
            if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                this.fallbackToPolling();
            }
        });

        // Disconnected
        this.socket.on('disconnect', (reason) => {
            this.connected = false;
            
            if (reason === 'io server disconnect') {
                // Server disconnected, try to reconnect manually
                this.socket.connect();
            }
        });

        // Reconnect attempt
        this.socket.on('reconnect_attempt', (attemptNumber) => {
            // Reconnecting...
        });

        // Reconnected successfully
        this.socket.on('reconnect', (attemptNumber) => {
            this.fetchInitialNotifications();
        });
    }

    /**
     * Handle incoming notification from WebSocket
     */
    handleIncomingNotification(data) {
        if (data.type === 'new_notification') {
            // New notification
            this.notifications.unshift(data.data);
            this.showNotificationToast(data.data);
            this.fetchUnreadCount(); // Update badge
        } else if (data.type === 'unread_count') {
            // Unread count update
            this.updateUnreadBadge(data.data.count);
        }
    }

    /**
     * Show notification toast (optional: you can use a toast library)
     */
    showNotificationToast(notification) {
        // Simple browser notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('WeLinked', {
                body: notification.message,
                icon: '/assets/logo.png',
                tag: `notification-${notification.id}`
            });
        }

        // Play notification sound (optional)
        this.playNotificationSound();
    }

    /**
     * Play notification sound
     */
    playNotificationSound() {
        try {
            const audio = new Audio('/assets/notification-sound.mp3');
            audio.volume = 0.3;
            audio.play().catch(e => console.log('Sound play failed:', e));
        } catch (e) {
            // Silent fail
        }
    }

    /**
     * Fetch initial notifications and unread count
     */
    async fetchInitialNotifications() {
        await  this.fetchUnreadCount();
        // Optionally fetch recent notifications if dropdown is open
        const dropdown = document.getElementById('notifications-dropdown');
        if (dropdown && !dropdown.classList.contains('hidden')) {
            await this.fetchNotifications();
        }
    }

    /**
     * Fetch unread count (still need this for initial load)
     */
    async fetchUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            const data = await response.json();
            
            if (data.success) {
                this.updateUnreadBadge(data.count);
            }
        } catch (error) {
            console.error('[Notifications] Fetch unread count error:', error);
        }
    }

    /**
     * Update unread badge
     */
    updateUnreadBadge(count) {
        this.unreadCount = count;
        const badge = document.getElementById('notifications-badge');
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    /**
     * Attach event listeners for UI interactions
     */
    attachEventListeners() {
        // Bell icon click
        const bellIcon = document.getElementById('notifications-bell');
        if (bellIcon) {
            bellIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown();
            });
        }

        // Mark all read button
        document.addEventListener('click', (e) => {
            const markAllBtn = e.target.closest('#mark-all-read-btn');
            if (markAllBtn) {
                this.markAllAsRead();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('notifications-dropdown');
            const bellIcon = document.getElementById('notifications-bell');
            
            if (dropdown && !dropdown.contains(e.target) && e.target !== bellIcon && !bellIcon.contains(e.target)) {
                this.hideDropdown();
            }
        });

        // Individual notification clicks
        document.addEventListener('click', (e) => {
            const notifItem = e.target.closest('.notification-item');
            if (notifItem) {
                const notifId = parseInt(notifItem.dataset.notificationId);
                this.handleNotificationClick(notifId, notifItem);
            }
        });

        // Request notification permission on page load
        this.requestNotificationPermission();
    }

    /**
     * Request browser notification permission
     */
    async requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            await Notification.requestPermission();
        }
    }

    /**
     * Get authentication token from cookies/localStorage
     */
    getAuthToken() {
        // Try to get from cookie first
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'auth_token') {
                return decodeURIComponent(value);
            }
        }
        
        // Fallback to localStorage
        return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    }

    /**
     * Toggle notifications dropdown
     */
    async toggleDropdown() {
        const dropdown = document.getElementById('notifications-dropdown');
        
        if (!dropdown) {
            await this.createDropdown();
            return;
        }
        
        if (dropdown.classList.contains('hidden')) {
            await this.fetchNotifications();
            dropdown.classList.remove('hidden');
        } else {
            this.hideDropdown();
        }
    }

    /**
     * Hide dropdown
     */
    hideDropdown() {
        const dropdown = document.getElementById('notifications-dropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }

    /**
     * Create notifications dropdown (same as before)
     */
    async createDropdown() {
        const bellIcon = document.getElementById('notifications-bell');
        if (!bellIcon) return;

        const dropdown = document.createElement('div');
        dropdown.id = 'notifications-dropdown';
        dropdown.className = 'absolute right-0 mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-xl z-50 hidden';
        dropdown.style.top = '100%';
        
        bellIcon.parentElement.style.position = 'relative';
        bellIcon.parentElement.appendChild(dropdown);

        await this.fetchNotifications();
        dropdown.classList.remove('hidden');
    }

    /**
     * Fetch notifications from API
     */
    async fetchNotifications() {
        try {
            const response = await fetch('/api/notifications');
            const data = await response.json();
            
            if (data.success) {
                this.notifications = data.notifications;
                this.renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('[Notifications] Fetch error:', error);
        }
    }

    /**
     * Render notifications (same as before - reuse your existing renderNotifications code)
     */
    renderNotifications(notifications) {
        const dropdown = document.getElementById('notifications-dropdown');
        if (!dropdown) return;

        // Use your existing renderNotifications implementation here
        // Copy from your current notifications.js file
        if (notifications.length === 0) {
            dropdown.innerHTML = `
                <div class="p-6 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p>No notifications</p>
                </div>
            `;
            return;
        }

        const notificationsHTML = notifications.map(notif => `
            <div class="notification-item flex items-start px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 ${!notif.is_read ? 'bg-blue-50' : ''}"
                 data-notification-id="${notif.id}"
                 data-target-type="${notif.target ? notif.target.type : ''}"
                 data-target-id="${notif.target ? notif.target.id : ''}">
                ${notif.actor && notif.actor.profile_photo ? 
                    `<img src="${notif.actor.profile_photo}" alt="${notif.actor.username}" class="w-10 h-10 rounded-full mr-3">` :
                    `<div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3 text-sm font-semibold text-gray-600">
                        ${notif.actor ? notif.actor.username.charAt(0).toUpperCase() : '?'}
                    </div>`
                }
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">
                        <span class="font-semibold">${notif.actor ? notif.actor.username : 'Someone'}</span>
                        ${notif.message || 'mentioned you in a post'}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">${this.formatTimestamp(notif.created_at)}</p>
                </div>
                ${!notif.is_read ? 
                    `<div class="w-2 h-2 bg-blue-500 rounded-full ml-2"></div>` : 
                    ''
                }
            </div>
        `).join('');

        dropdown.innerHTML = `
            <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-semibold text-gray-900">Notifications</h3>
                ${this.unreadCount > 0 ? 
                    `<button id="mark-all-read-btn" class="text-sm text-blue-600 hover:text-blue-700">Mark all read</button>` : 
                    ''
                }
            </div>
            <div class="max-h-96 overflow-y-auto">
                ${notificationsHTML}
            </div>
        `;
    }

    /**
     * Handle notification click
     */
    async handleNotificationClick(notificationId, notifElement) {
        const targetType = notifElement.dataset.targetType;
        const targetId = notifElement.dataset.targetId;
        
        await this.markAsRead(notificationId);
        
        if (targetType === 'post' && targetId) {
            window.location.href = `/post/${targetId}`;
        }
        
        this.hideDropdown();
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/api/notifications/mark-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();
            
            if (result.success) {
                const notifElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notifElement) {
                    notifElement.classList.remove('bg-blue-50');
                    const unreadDot = notifElement.querySelector('.bg-blue-500');
                    if (unreadDot) unreadDot.remove();
                }
                
                this.fetchUnreadCount();
                
                // Emit via WebSocket to sync across devices
                if (this.socket && this.connected) {
                    this.socket.emit('notification:read', notificationId);
                }
            }
        } catch (error) {
            console.error('[Notifications] Mark as read error:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();
            
            if (result.success) {
                await this.fetchNotifications();
                this.updateUnreadBadge(0);
            }
        } catch (error) {
            console.error('[Notifications] Mark all as read error:', error);
        }
    }

    /**
     * Format timestamp
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return date.toLocaleDateString();
    }

    /**
     * Fallback to polling if WebSocket fails
     */
    fallbackToPolling() {
        this.socket = null;
        this.connected = false;
        
        // Start polling every 5 seconds
        setInterval(() => {
            this.fetchUnreadCount();
        }, 5000);
    }

    /**
     * Cleanup on page unload
     */
    disconnect() {
        if (this.socket && this.connected) {
            this.socket.disconnect();
        }
    }
}

// Initialize WebSocket notifications manager
document.addEventListener('DOMContentLoaded', () => {
    window.wsNotificationsManager = new WebSocketNotificationsManager();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.wsNotificationsManager) {
        window.wsNotificationsManager.disconnect();
    }
});
