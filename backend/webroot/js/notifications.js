/**
 * Notifications handler for bell icon and dropdown
 * Displays mentions and updates notifications
 */

class NotificationsManager {
    constructor() {
        this.unreadCount = 0;
        this.notifications = [];
        this.pollInterval = null;
        this.slowPollInterval = null;
        this.isVisible = true;
        this.useWebSocket = true; // Try WebSocket first
        this.wsClient = null;
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.attachVisibilityListener();
        this.fetchUnreadCount();
        
        // Try WebSocket connection first
        this.initWebSocket();
    }

    /**
     * Initialize WebSocket connection
     */
    async initWebSocket() {
        try {
            // Check if Socket.io is loaded
            if (typeof io === 'undefined') {
                console.warn('[Notifications] Socket.io not loaded');
                this.enablePollingFallback();
                return;
            }
            
            // Check if WebSocketClient is available
            if (typeof WebSocketClient === 'undefined') {
                console.warn('[Notifications] WebSocketClient not available');
                this.enablePollingFallback();
                return;
            }

            // Get WebSocket token from backend
            console.log('[Notifications] Fetching WebSocket token...');
            const response = await fetch('/api/auth/websocket-token');
            
            if (!response.ok) {
                console.error('[Notifications] Failed to get WebSocket token:', response.status);
                this.enablePollingFallback();
                return;
            }
            
            const data = await response.json();
            if (!data.success || !data.token) {
                console.error('[Notifications] Invalid token response');
                this.enablePollingFallback();
                return;
            }
            
            console.log('[Notifications] ✓ WebSocket token obtained');

            // Create and connect WebSocket client
            this.wsClient = new WebSocketClient();
            
            // Register callbacks
            this.wsClient.onNotification((notification) => {
                this.handleWebSocketNotification(notification);
            });

            this.wsClient.onCountUpdate((count) => {
                this.updateUnreadBadge(count);
            });

            // Connect with token
            console.log('[Notifications] Connecting to WebSocket server...');
            await this.wsClient.connect(data.token);

            // Check connection after 5 seconds
            setTimeout(() => {
                if (!this.wsClient.isConnected()) {
                    console.warn('[Notifications] WebSocket not connected after 5s, falling back to polling');
                    this.enablePollingFallback();
                } else {
                    console.log('%c🍔 BURGER MODE ACTIVE - WebSocket real-time notifications!', 'color: #10B981; font-weight: bold; font-size: 14px;');
                    console.log('[Notifications] Connection stable, real-time mode enabled');
                }
            }, 5000);

        } catch (error) {
            console.error('%c[Notifications] ❌ WebSocket initialization error:', 'color: #EF4444; font-weight: bold;', error);
            this.enablePollingFallback();
        }
    }

    /**
     * Enable polling as fallback
     */
    enablePollingFallback() {
        // Don't enable polling if already active
        if (!this.useWebSocket && this.pollInterval) {
            console.log('[Notifications] Polling already active, skipping...');
            return;
        }
        
        // Don't enable polling if WebSocket is connected
        if (this.wsClient && this.wsClient.isConnected()) {
            console.log('[Notifications] WebSocket active, not enabling polling');
            return;
        }
        
        console.log('[Notifications] Enabling polling fallback...');
        this.useWebSocket = false;
        this.startPolling();
        console.log('%c🥚 EGG MODE ACTIVE - Polling every 2 seconds', 'color: #F97316; font-weight: bold; font-size: 14px;');
    }

    /**
     * Get session token from cookie
     */
    getSessionToken() {
        console.log('[Notifications] All cookies:', document.cookie);
        
        // Get session cookie for authentication
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            console.log(`[Notifications] Cookie found: ${name} = ${value?.substring(0, 20)}...`);
            
            if (name === 'welinked_session' || name === 'PHPSESSID') {
                console.log(`[Notifications] ✓ Using session cookie: ${name}`);
                return value;
            }
        }
        
        console.warn('[Notifications] ✗ No session cookie found (welinked_session or PHPSESSID)');
        return null;
    }

    /**
     * Handle WebSocket notification
     */
    handleWebSocketNotification(notification) {
        // Refresh notification count
        this.fetchUnreadCount();
        
        // Show toast notification
        this.showToastNotification(notification);
    }

    /**
     * Show toast notification
     */
    showToastNotification(notification) {
        // Simple toast implementation (you can enhance this)
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-blue-600 text-white px-6 py-4 rounded-lg shadow-xl z-50 animate-slide-in';
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined">notifications</span>
                <div>
                    <p class="font-semibold">${notification.message || 'New notification'}</p>
                    <p class="text-sm opacity-90">Click to view</p>
                </div>
            </div>
        `;
        toast.style.cursor = 'pointer';
        toast.onclick = () => {
            this.toggleDropdown();
            document.body.removeChild(toast);
        };
        
        document.body.appendChild(toast);
        
        // Remove after 5 seconds
        setTimeout(() => {
            if (document.body.contains(toast)) {
                toast.style.opacity = '0';
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }
        }, 5000);
    }

    /**
     * Attach event listeners
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
                console.log('Notification clicked:', notifItem);
                const notifId = parseInt(notifItem.dataset.notificationId);
                console.log('Notification ID:', notifId, 'Target type:', notifItem.dataset.targetType, 'Target ID:', notifItem.dataset.targetId);
                this.handleNotificationClick(notifId, notifItem);
            }
        });
    }

    /**
     * Attach visibility change listener to pause/resume polling
     */
    attachVisibilityListener() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Tab hidden
                this.isVisible = false;
                
                // If using polling, switch to slower interval
                if (!this.useWebSocket) {
                    this.stopPolling();
                    this.startSlowPolling();
                }
                // WebSocket remains connected but is more efficient
            } else {
                // Tab visible again
                this.isVisible = true;
                
                // If using polling, resume real-time polling
                if (!this.useWebSocket) {
                    this.stopSlowPolling();
                    this.fetchUnreadCount(); // Immediate fetch
                    this.startPolling();
                } else {
                    // Just refresh count for WebSocket
                    this.fetchUnreadCount();
                }
            }
        });
    }

    /**
     * Fetch unread count
     */
    async fetchUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            const data = await response.json();
            
            if (data.success) {
                this.updateUnreadBadge(data.count);
            }
        } catch (error) {
            console.error('Fetch unread count error:', error);
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
     * Create notifications dropdown
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
     * Fetch notifications
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
            console.error('Fetch notifications error:', error);
        }
    }

    /**
     * Render notifications in dropdown
     */
    renderNotifications(notifications) {
        const dropdown = document.getElementById('notifications-dropdown');
        if (!dropdown) return;

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
            <div class="max-h-200 overflow-y-auto">
                ${notificationsHTML}
            </div>
        `;
    }

    /**
     * Handle notification click
     */
    async handleNotificationClick(notificationId, notifElement) {
        console.log('[Notification] Click handler called:', { notificationId, notifElement });
        
        if (!notificationId || isNaN(notificationId)) {
            console.error('[Notification] Invalid notification ID:', notificationId);
            return;
        }
        
        const targetType = notifElement.dataset.targetType;
        const targetId = notifElement.dataset.targetId;
        
        console.log('[Notification] Target:', { targetType, targetId });
        
        // Mark as read first
        const marked = await this.markAsRead(notificationId);
        
        if (!marked) {
            console.warn('[Notification] Failed to mark as read, but continuing...');
        }
        
        // Update UI immediately
        notifElement.classList.remove('bg-blue-50');
        const unreadDot = notifElement.querySelector('.bg-blue-500');
        if (unreadDot) {
            unreadDot.remove();
        }
        
        // Navigate to target if applicable
        if (targetType === 'post' && targetId) {
            console.log('[Notification] Navigating to post:', targetId);
            window.location.href = `/post/${targetId}`;
        } else if (targetType === 'comment' && targetId) {
            console.log('[Notification] Navigating to comment:', targetId);
            // You can implement comment navigation if needed
            this.hideDropdown();
        } else {
            console.log('[Notification] No navigation target, closing dropdown');
            this.hideDropdown();
        }
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        console.log('[Notification] Marking as read:', notificationId);
        
        try {
            const csrfToken = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
            
            const response = await fetch(`/api/notifications/mark-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-Token': csrfToken })
                }
            });

            if (!response.ok) {
                console.error('[Notification] Mark as read failed:', response.status, response.statusText);
                return false;
            }

            const result = await response.json();
            console.log('[Notification] Mark as read response:', result);
            
            if (result.success) {
                // Update UI
                const notifElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notifElement) {
                    notifElement.classList.remove('bg-blue-50');
                    const unreadDot = notifElement.querySelector('.bg-blue-500');
                    if (unreadDot) {
                        unreadDot.remove();
                    }
                }
                
                // Update count
                this.fetchUnreadCount();
                
                // Notify WebSocket if connected
                if (this.wsClient && this.wsClient.isConnected()) {
                    this.wsClient.markNotificationRead(notificationId);
                }
                
                return true;
            } else {
                console.error('[Notification] Server returned error:', result.message);
                return false;
            }
        } catch (error) {
            console.error('[Notification] Mark as read error:', error);
            return false;
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
                // Refresh notifications
                await this.fetchNotifications();
                this.updateUnreadBadge(0);
            }
        } catch (error) {
            console.error('Mark all as read error:', error);
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
     * Start polling for new notifications (REAL-TIME - every 2 seconds)
     */
    startPolling() {
        // Poll every 2 seconds for real-time feel
        this.pollInterval = setInterval(() => {
            this.fetchUnreadCount();
        }, 2000); // 2 seconds - ALWAYS LISTENING
        
        console.log('[Notifications] Started polling every 2 seconds');
    }

    /**
     * Start slow polling when tab is hidden (every 30 seconds to save resources)
     */
    startSlowPolling() {
        this.slowPollInterval = setInterval(() => {
            this.fetchUnreadCount();
        }, 30000); // 30 seconds when hidden
    }

    /**
     * Stop polling
     */
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }

    /**
     * Stop slow polling
     */
    stopSlowPolling() {
        if (this.slowPollInterval) {
            clearInterval(this.slowPollInterval);
            this.slowPollInterval = null;
        }
    }
}

// Initialize notifications manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.notificationsManager = new NotificationsManager();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.notificationsManager) {
        window.notificationsManager.stopPolling();
        window.notificationsManager.stopSlowPolling();
        
        // Disconnect WebSocket if connected
        if (window.notificationsManager.wsClient) {
            window.notificationsManager.wsClient.disconnect();
        }
    }
});
