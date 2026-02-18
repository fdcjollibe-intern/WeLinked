<?php $hasProfilePhoto = !empty($currentUser->profile_photo_path); ?>
<div class="flex flex-col h-full">
    <!-- User Profile Section -->
    <div class="text-center py-6 px-4">
        <a href="#" data-nav="profile" class="inline-block mb-3 cursor-pointer hover:opacity-80 transition-opacity">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-pink-400 via-purple-400 to-blue-400 p-1">
                <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                    <img
                        data-avatar="current-user"
                        src="<?= $hasProfilePhoto ? h($currentUser->profile_photo_path) : '' ?>"
                        alt="<?= h($currentUser->username ?? 'Profile photo') ?>"
                        class="w-full h-full object-cover <?= $hasProfilePhoto ? '' : 'hidden' ?>"
                    >
                    <div
                        data-avatar-fallback="current-user"
                        class="w-full h-full rounded-full flex items-center justify-center text-white text-2xl font-bold <?= $hasProfilePhoto ? 'hidden' : '' ?>"
                    >
                        <span data-user-initial><?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?></span>
                    </div>
                </div>
            </div>
        </a>
        <p href="#" data-nav="profile" class="block hover:underline cursor-pointer">
            <h3 class="font-semibold text-gray-900" data-user-fullname>
                <?= h($currentUser->full_name ?? $currentUser->fullname ?? 'Your Name') ?>
            </h3>
        </p>
        <a href="#" data-nav="profile" class="block hover:underline cursor-pointer">
            <p class="text-sm text-gray-500" data-user-username>
                @<?= h($currentUser->username ?? 'username') ?>
            </p>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4">
        <ul class="space-y-1">
            <li>
                <a href="#" data-nav="home" class="nav-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24"><path d="M19 20H5V9l7-5.26a2 2 0 002.22 0L21 9v11z"/></svg>
                    <span>Home</span>
                </a>
            </li>
            <li>
                <a href="#" data-nav="messages" class="nav-link group relative flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Messages</span>
                    <span class="ml-auto bg-blue-100 text-blue-600 text-xs font-semibold px-2 py-0.5 rounded-full">6</span>
                    <span class="absolute right-0 -top-8 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">Coming Soon</span>
                </a>
            </li>
            <li>
                <a href="#" data-nav="friends" class="nav-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Friends</span>
                </a>
            </li>
            <!-- Forums removed -->
            <!-- Media removed -->
            <li>
                <a href="#" data-nav="settings" class="nav-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Settings</span>
                </a>
            </li>
            <li>
                <a href="#" data-nav="logout" class="nav-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="text-red-500 px-2 py-1 rounded">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<!-- Logout Confirmation Modal -->
<div id="logout-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl transform transition-all">
        <h3 class="text-xl font-semibold text-center mb-2">Confirm Logout</h3>
        <p class="text-gray-600 text-center mb-6">Are you sure you want to log out?</p>
        <div class="flex gap-3">
            <button id="logout-cancel" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                Cancel
            </button>
            <button id="logout-confirm" class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors">
                Logout
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    function initLeftSidebar() {
        if (window.__leftSidebarInitialized) {
            return;
        }
        window.__leftSidebarInitialized = true;

        const modal = document.getElementById('logout-modal');
        
        // Track current user data so profile links stay accurate after edits
        let currentUsername = '<?= h($currentUser->username ?? 'username') ?>';

        function updateNameTargets(detail) {
            if (detail.fullName) {
                document.querySelectorAll('[data-user-fullname]').forEach(node => {
                    node.textContent = detail.fullName;
                });
            }
            if (detail.username) {
                document.querySelectorAll('[data-user-username]').forEach(node => {
                    node.textContent = '@' + detail.username;
                });
                document.querySelectorAll('[data-user-initial]').forEach(node => {
                    node.textContent = detail.username.charAt(0).toUpperCase();
                });
            }
        }

        function updateAvatarTargets(photoUrl) {
            const hasPhoto = !!photoUrl;
            document.querySelectorAll('[data-avatar="current-user"]').forEach(img => {
                if (!img) return;
                if (hasPhoto) {
                    img.src = photoUrl;
                    img.classList.remove('hidden');
                } else {
                    img.classList.add('hidden');
                }
            });
            document.querySelectorAll('[data-avatar-fallback="current-user"]').forEach(fallback => {
                if (!fallback) return;
                fallback.classList.toggle('hidden', hasPhoto);
            });
        }

        function hydrateCurrentUser() {
            fetch('/users/current-profile', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch current user');
                    }
                    return response.json();
                })
                .then(payload => {
                    if (!payload || !payload.success || !payload.user) {
                        return;
                    }
                    const payloadUser = payload.user;
                    const payloadName = payloadUser.full_name || payloadUser.fullname || '';
                    if (payloadUser.username) {
                        currentUsername = payloadUser.username;
                    }
                    updateNameTargets({
                        fullName: payloadName,
                        username: payloadUser.username
                    });
                    updateAvatarTargets(payloadUser.profile_photo_path || '');
                })
                .catch(err => {
                    console.error('Unable to hydrate current user', err);
                });
        }

        // Helper to insert HTML and execute inline scripts from fetched fragment
        function insertFragment(middleColumn, html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;

        // Move non-script children into middleColumn
        Array.from(temp.childNodes).forEach(node => {
            if (node.nodeName.toLowerCase() === 'script') return;
            middleColumn.appendChild(node.cloneNode(true));
        });

        // Execute scripts in order
        Array.from(temp.querySelectorAll('script')).forEach(oldScript => {
            try {
                const script = document.createElement('script');
                if (oldScript.src) {
                    script.src = oldScript.src;
                } else {
                    script.textContent = oldScript.textContent;
                }
                const head = document.head;
                head.appendChild(script);
                head.removeChild(script);
            } catch (e) {
                console.error('Script execution error:', e);
            }
        });
        }

        // Robust loader that requests fragments and runs scripts
        function loadMiddleColumn(url) {
        const middleColumn = document.getElementById('middle-component');
        if (!middleColumn) return;
        middleColumn.innerHTML = ''; // clear first to avoid duplication

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                insertFragment(middleColumn, html);
                middleColumn.scrollTop = 0;
                // Dispatch an event for other scripts to initialize themselves
                middleColumn.dispatchEvent(new CustomEvent('fragment:loaded', { 
                    detail: { path: url, container: 'middle-component' } 
                }));
            })
            .catch(err => console.error('Failed to load content:', err));
        }
        
        // Make loadMiddleColumn globally accessible
        window.loadMiddleColumn = loadMiddleColumn;

        // Handle navigation clicks using delegation
        document.body.addEventListener('click', (e) => {
        const nav = e.target.closest && e.target.closest('[data-nav]');
        if (nav) {
            e.preventDefault();
            const action = nav.dataset.nav;

            if (action === 'logout') {
                // Don't change active state when opening logout modal
                modal?.classList.remove('hidden');
                return;
            }

            updateNavigationState(action);

            if (action === 'profile') {
                const profileUrl = '/profile/' + currentUsername;
                loadMiddleColumn(profileUrl);
                history.pushState({}, '', profileUrl);
            } else if (action === 'settings') {
                loadMiddleColumn('/settings');
                history.pushState({}, '', '/settings');
            } else if (action === 'home') {
                loadMiddleColumn('/dashboard/middle-column');
                history.pushState({}, '', '/dashboard');
            } else if (action === 'friends') {
                loadMiddleColumn('/friends');
                history.pushState({}, '', '/friends');
            } else if (action === 'messages') {
            }
        }

        // Intercept settings internal links (delegated) so they render only middle column
        const settingsAnchor = e.target.closest && e.target.closest('a[href^="/settings"]');
        if (settingsAnchor) {
            const middleColumn = document.getElementById('middle-component');
            if (!middleColumn) return;
            e.preventDefault();
            const href = settingsAnchor.getAttribute('href');
            loadMiddleColumn(href);
            history.pushState({}, '', href);
            return;
        }

        // Intercept profile links (delegated) so they render only middle column
        const profileAnchor = e.target.closest && e.target.closest('a[href^="/profile"]');
        if (profileAnchor) {
            const middleColumn = document.getElementById('middle-component');
            if (!middleColumn) return;
            e.preventDefault();
            const href = profileAnchor.getAttribute('href');
            loadMiddleColumn(href);
            history.pushState({}, '', href);
            updateNavigationState('profile');
            return;
        }
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function() {
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('/profile')) {
            loadMiddleColumn(currentPath);
            updateNavigationState('profile');
        } else if (currentPath.includes('/settings')) {
            loadMiddleColumn(currentPath);
            updateNavigationState('settings');
        } else if (currentPath.includes('/friends')) {
            loadMiddleColumn('/friends');
            updateNavigationState('friends');
        } else if (currentPath.includes('/search')) {
            loadMiddleColumn(currentPath + window.location.search);
            updateNavigationState('search');
        } else if (currentPath === '/dashboard' || currentPath === '/') {
            loadMiddleColumn('/dashboard/middle-column');
            updateNavigationState('home');
        }
        });

        // Modal handlers
        document.getElementById('logout-cancel')?.addEventListener('click', () => {
        modal?.classList.add('hidden');
    });

        document.getElementById('logout-confirm')?.addEventListener('click', () => {
        window.location.href = '/logout';
    });

        // Close modal on backdrop click
        modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });

        // Function to update navigation state and manage hover behavior
        function updateNavigationState(activeNav) {
        // Remove active state from all links and restore hover behavior
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('bg-blue-500', 'text-white');
            if (!link.classList.contains('hover:bg-gray-100')) link.classList.add('hover:bg-gray-100');
            link.classList.add('text-gray-600');
        });

        // Add active state to the selected link and remove hover so it stays blue on hover
        const activeLink = document.querySelector(`[data-nav="${activeNav}"]`);
        if (activeLink) {
            activeLink.classList.remove('text-gray-600', 'hover:bg-gray-100');
            activeLink.classList.add('bg-blue-500', 'text-white');
        }
        }

        // Initialize navigation state based on current page
        function initializeNavigationState() {
        const currentPath = window.location.pathname;
        let activeNav = 'home'; // Default to home

        if (currentPath.includes('/profile')) {
            activeNav = 'profile';
        } else if (currentPath.includes('/settings')) {
            activeNav = 'settings';
        } else if (currentPath.includes('/friends')) {
            activeNav = 'friends';
        } else if (currentPath.includes('/search')) {
            activeNav = 'search';
        }

            updateNavigationState(activeNav);
        }

        // Listen for updates emitted from the settings panel so UI stays in sync
        window.addEventListener('user:profile-updated', (event) => {
        const detail = event.detail || {};
        if (detail.username) {
            currentUsername = detail.username;
        }
        if (detail.fullName || detail.username) {
            updateNameTargets(detail);
        }
        if (Object.prototype.hasOwnProperty.call(detail, 'photoUrl')) {
            updateAvatarTargets(detail.photoUrl);
        }
    });

        // Initialize navigation state
        initializeNavigationState();

        // Immediately hydrate nav + sidebar with authoritative data from backend
        hydrateCurrentUser();

        // When a fragment loads, allow it to initialize any handlers by listening to event
        document.getElementById('middle-component')?.addEventListener('fragment:loaded', () => {
            if (typeof window.initSettingsHandlers === 'function') {
                try {
                    window.initSettingsHandlers(document.getElementById('middle-component'));
                } catch (err) {
                    console.error(err);
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLeftSidebar, { once: true });
    } else {
        initLeftSidebar();
    }
})();
</script>
