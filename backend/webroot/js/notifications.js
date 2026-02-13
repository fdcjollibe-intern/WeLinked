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
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.attachVisibilityListener();
        this.fetchUnreadCount();
        this.startPolling();
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
                const notifId = parseInt(notifItem.dataset.notificationId);
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
                // Tab hidden - pause aggressive polling, switch to slower polling
                this.isVisible = false;
                this.stopPolling();
                this.startSlowPolling();
            } else {
                // Tab visible again - resume real-time polling
                this.isVisible = true;
                this.stopSlowPolling();
                this.fetchUnreadCount(); // Immediate fetch
                this.startPolling();
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
                 data-target-type="${notif.target_type}"
                 data-target-id="${notif.target_id}">
                ${notif.actor && notif.actor.profile_photo_path ? 
                    `<img src="${notif.actor.profile_photo_path}" alt="${notif.actor.username}" class="w-10 h-10 rounded-full mr-3">` :
                    `<div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3 text-sm font-semibold text-gray-600">
                        ${notif.actor ? notif.actor.username.charAt(0).toUpperCase() : '?'}
                    </div>`
                }
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">
                        <span class="font-semibold">${notif.actor ? notif.actor.username : 'Someone'}</span>
                        ${notif.message || 'mentioned you in a post'}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">${this.formatTimestamp(notif.created)}</p>
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
        
        // Mark as read
        await this.markAsRead(notificationId);
        
        // Navigate to target
        if (targetType === 'post' && targetId) {
            // Navigate to post (could scroll to post or navigate to profile with post highlighted)
            window.location.href = `/dashboard#post-${targetId}`;
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
            }
        } catch (error) {
            console.error('Mark as read error:', error);
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
    }
});
