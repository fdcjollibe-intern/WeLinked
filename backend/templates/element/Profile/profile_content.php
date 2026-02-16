<?php
/**
 * Profile Content Element
 * @var \App\View\AppView $this
 * @var object $user
 * @var int $postCount
 * @var int $followersCount
 * @var int $followingCount
 * @var object $identity
 * @var bool $isMobileView
 */
?>
<?php
$avatarSizeClass = $isMobileView ? 'w-24 h-24' : 'w-32 h-32';
$avatarTextClass = $isMobileView ? 'text-3xl' : 'text-5xl';
?>
<!-- Profile Section -->
<section class="profile-section" data-profile-user-id="<?= (int) $user->id ?>">
    <!-- Profile Header Card -->
    <article class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-4">
        <div class="flex <?= $isMobileView ? 'flex-col items-center text-center' : 'items-start' ?> gap-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                <?php if (!empty($user->profile_photo_path)): ?>
                    <img src="<?= h($user->profile_photo_path) ?>" 
                         alt="<?= h($user->username) ?>" 
                         class="<?= $avatarSizeClass ?> rounded-full object-cover border-2 border-gray-200"
                         data-profile-avatar
                         data-avatar-size-class="<?= h($avatarSizeClass) ?>"
                         data-avatar-text-class="<?= h($avatarTextClass) ?>">
                <?php else: ?>
                    <div class="<?= $avatarSizeClass ?> rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white <?= $avatarTextClass ?> font-bold"
                         data-profile-avatar
                         data-avatar-size-class="<?= h($avatarSizeClass) ?>"
                         data-avatar-text-class="<?= h($avatarTextClass) ?>">
                        <?= strtoupper(substr($user->username, 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Profile Info -->
            <div class="flex-1">
                <div class="flex <?= $isMobileView ? 'flex-col' : 'items-center' ?> gap-4 mb-1">
                    <h1 class="text-2xl font-semibold text-gray-900" data-profile-username><?= h($user->username) ?></h1>
                    <?php if ($identity->id === $user->id): ?>
                        <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors">
                            Edit Profile
                        </button>
                    <?php endif; ?>
                </div>
                <!-- Bio -->
                <div class="text-gray-600 font-medium m">
                    <div class="font-semibold" data-profile-full-name><?= h($user->full_name) ?></div>
                </div>
                
                <!-- Stats -->
                <div class="flex <?= $isMobileView ? 'justify-center' : '' ?> gap-6 mb-3">
                    <div>
                        <span class="font-semibold text-gray-900"><?= number_format($postCount) ?></span>
                        <span class="text-gray-600 text-sm ml-1">posts</span>
                    </div>
                    <button class="hover:opacity-70 transition-opacity" data-open-modal="followers">
                        <span class="font-semibold text-gray-900"><?= number_format($followersCount) ?></span>
                        <span class="text-gray-600 text-sm ml-1">followers</span>
                    </button>
                    <button class="hover:opacity-70 transition-opacity" data-open-modal="following">
                        <span class="font-semibold text-gray-900"><?= number_format($followingCount) ?></span>
                        <span class="text-gray-600 text-sm ml-1">following</span>
                    </button>
                </div>
            </div>
        </div>
    </article>
    
    <!-- Posts Section -->
    <article class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Posts</h2>
        <?php if ($postCount > 0): ?>
            <div class="text-gray-600 text-sm">Posts will appear here</div>
        <?php else: ?>
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-gray-600">No posts yet</p>
            </div>
        <?php endif; ?>
    </article>
</section>

<!-- Followers/Following Modal -->
<div id="followers-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[80vh] flex flex-col shadow-xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900"><?= h($user->username) ?></h3>
            <button class="close-modal text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Search Bar -->
        <div class="p-4 border-b border-gray-200">
            <input type="text" 
                   id="modal-search" 
                   placeholder="Search..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        
        <!-- Tabs -->
        <div class="flex border-b border-gray-200">
            <button class="modal-tab flex-1 px-4 py-3 text-sm font-semibold border-b-2 border-blue-500 text-blue-600" data-tab="followers">
                Followers
            </button>
            <button class="modal-tab flex-1 px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500" data-tab="following">
                Following
            </button>
        </div>
        
        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-4">
            <div id="modal-content" class="space-y-3">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('followers-modal');
    const modalContent = document.getElementById('modal-content');
    const searchInput = document.getElementById('modal-search');
    const profileRoot = document.querySelector('[data-profile-user-id]');
    const profileUserId = profileRoot ? Number(profileRoot.getAttribute('data-profile-user-id')) : null;
    const PROFILE_CSRF_TOKEN = window.CSRF_TOKEN || window.csrfToken || document.querySelector('meta[name="csrfToken"]')?.content || document.querySelector('meta[name="csrf-token"]')?.content || '';
    let username = <?= json_encode($user->username) ?>;
    let currentTab = 'followers';
    let allData = { followers: [], following: [] };

    function buildProfileJsonRequest(payload) {
        return {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': PROFILE_CSRF_TOKEN,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        };
    }

    function modalInitial(value) {
        return (value || '?').trim().charAt(0).toUpperCase() || '?';
    }
    
    // Open modal
    document.addEventListener('click', function(e) {
        const openBtn = e.target.closest('[data-open-modal]');
        if (!openBtn) return;
        
        const tab = openBtn.dataset.openModal;
        currentTab = tab;
        modal.classList.remove('hidden');
        
        // Update active tab
        document.querySelectorAll('.modal-tab').forEach(t => {
            t.classList.remove('border-blue-500', 'text-blue-600');
            t.classList.add('border-transparent', 'text-gray-500');
            if (t.dataset.tab === tab) {
                t.classList.remove('border-transparent', 'text-gray-500');
                t.classList.add('border-blue-500', 'text-blue-600');
            }
        });
        
        loadData(tab);
    });
    
    // Close modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.close-modal') || e.target === modal) {
            modal.classList.add('hidden');
            searchInput.value = '';
        }
    });
    
    // Tab switching
    document.addEventListener('click', function(e) {
        const tab = e.target.closest('.modal-tab');
        if (!tab) return;
        
        currentTab = tab.dataset.tab;
        
        // Update tab styles
        document.querySelectorAll('.modal-tab').forEach(t => {
            t.classList.remove('border-blue-500', 'text-blue-600');
            t.classList.add('border-transparent', 'text-gray-500');
        });
        tab.classList.remove('border-transparent', 'text-gray-500');
        tab.classList.add('border-blue-500', 'text-blue-600');
        
        loadData(currentTab);
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        filterData(query);
    });
    
    function loadData(tab) {
        modalContent.innerHTML = '<div class="text-center py-4 text-gray-500">Loading...</div>';
        
        fetch(`/profile/${username}/${tab}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allData[tab] = data[tab] || [];
                renderData(allData[tab]);
            } else {
                modalContent.innerHTML = '<div class="text-center py-4 text-red-500">Failed to load data</div>';
            }
        })
        .catch(error => {
            console.error('Load error:', error);
            modalContent.innerHTML = '<div class="text-center py-4 text-red-500">An error occurred</div>';
        });
    }
    
    function filterData(query) {
        const data = allData[currentTab];
        if (!query) {
            renderData(data);
            return;
        }
        
        const filtered = data.filter(user => 
            user.full_name.toLowerCase().includes(query) || 
            user.username.toLowerCase().includes(query)
        );
        renderData(filtered);
    }
    
    function renderData(data) {
        if (data.length === 0) {
            modalContent.innerHTML = '<div class="text-center py-8 text-gray-500">No users found</div>';
            return;
        }
        
        modalContent.innerHTML = data.map(user => `
            <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg" data-modal-user-id="${escapeHtml(String(user.id))}">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    ${user.profile_photo_path 
                        ? `<img src="${escapeHtml(user.profile_photo_path)}" class="w-12 h-12 rounded-full object-cover flex-shrink-0" data-avatar>`
                        : `<div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold flex-shrink-0" data-avatar>${escapeHtml(modalInitial(user.username))}</div>`
                    }
                    <div class="min-w-0 flex-1">
                        <a href="/profile/${escapeHtml(user.username)}" class="profile-link text-sm font-semibold text-gray-900 hover:underline block truncate" data-profile-link>
                            <span data-full-name>${escapeHtml(user.full_name)}</span>
                        </a>
                        <p class="text-xs text-gray-500 truncate" data-username-display>@${escapeHtml(user.username)}</p>
                    </div>
                </div>
                ${user.is_following 
                    ? `<button class="unfollow-user-btn px-4 py-1.5 text-xs font-semibold border border-gray-300 rounded-lg hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-colors" data-user-id="${user.id}">Unfollow</button>`
                    : `<button class="follow-user-btn px-4 py-1.5 text-xs font-semibold bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors" data-user-id="${user.id}">Follow</button>`
                }
            </div>
        `).join('');
    }
    
    // Follow/Unfollow handlers
    document.addEventListener('click', function(e) {
        const followBtn = e.target.closest('.follow-user-btn');
        if (!followBtn) return;
        
        const userId = followBtn.dataset.userId;
        followBtn.disabled = true;
        followBtn.textContent = 'Following...';
        
        fetch('/friends/follow', buildProfileJsonRequest({ user_id: userId }))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadData(currentTab);
            } else {
                alert(data.message || 'Failed to follow user');
                followBtn.disabled = false;
                followBtn.textContent = 'Follow';
            }
        })
        .catch(error => {
            console.error('Follow error:', error);
            alert('An error occurred');
            followBtn.disabled = false;
            followBtn.textContent = 'Follow';
        });
    });
    
    document.addEventListener('click', function(e) {
        const unfollowBtn = e.target.closest('.unfollow-user-btn');
        if (!unfollowBtn) return;
        
        const userId = unfollowBtn.dataset.userId;
        
        if (!confirm(`Unfollow this user?`)) return;
        
        unfollowBtn.disabled = true;
        unfollowBtn.textContent = 'Unfollowing...';
        
        fetch('/friends/unfollow', buildProfileJsonRequest({ user_id: userId }))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadData(currentTab);
            } else {
                alert(data.message || 'Failed to unfollow user');
                unfollowBtn.disabled = false;
                unfollowBtn.textContent = 'Unfollow';
            }
        })
        .catch(error => {
            console.error('Unfollow error:', error);
            alert('An error occurred');
            unfollowBtn.disabled = false;
            unfollowBtn.textContent = 'Unfollow';
        });
    });
    
    function updateHeroAvatar(detail) {
        if (!profileRoot || Number(detail.userId) !== profileUserId) {
            return;
        }
        const avatarNode = profileRoot.querySelector('[data-profile-avatar]');
        if (!avatarNode || !Object.prototype.hasOwnProperty.call(detail, 'photoUrl')) {
            return;
        }
        const sizeClass = avatarNode.dataset.avatarSizeClass || 'w-32 h-32';
        const textClass = avatarNode.dataset.avatarTextClass || 'text-5xl';
        const heroUsernameNode = profileRoot.querySelector('[data-profile-username]');
        const currentUsername = heroUsernameNode ? heroUsernameNode.textContent.trim() : '';
        const fallbackUsername = detail.username || currentUsername;
        if (detail.photoUrl) {
            if (avatarNode.tagName === 'IMG') {
                avatarNode.src = detail.photoUrl;
                avatarNode.alt = detail.username || avatarNode.alt || 'Profile photo';
            } else {
                const img = document.createElement('img');
                img.src = detail.photoUrl;
                img.alt = detail.username || 'Profile photo';
                img.className = `${sizeClass} rounded-full object-cover border-2 border-gray-200`;
                img.dataset.profileAvatar = 'true';
                img.dataset.avatarSizeClass = sizeClass;
                img.dataset.avatarTextClass = textClass;
                avatarNode.replaceWith(img);
            }
        } else if (avatarNode.tagName === 'IMG') {
            const fallback = document.createElement('div');
            fallback.className = `${sizeClass} rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white ${textClass} font-bold`;
            fallback.textContent = modalInitial(fallbackUsername);
            fallback.dataset.profileAvatar = 'true';
            fallback.dataset.avatarSizeClass = sizeClass;
            fallback.dataset.avatarTextClass = textClass;
            avatarNode.replaceWith(fallback);
        } else {
            avatarNode.textContent = modalInitial(fallbackUsername);
        }
    }

    function updateHeroText(detail) {
        if (!profileRoot || Number(detail.userId) !== profileUserId) {
            return;
        }
        if (detail.username) {
            const usernameNode = profileRoot.querySelector('[data-profile-username]');
            if (usernameNode) {
                usernameNode.textContent = detail.username;
            }
        }
        if (detail.fullName) {
            const nameNode = profileRoot.querySelector('[data-profile-full-name]');
            if (nameNode) {
                nameNode.textContent = detail.fullName;
            }
        }
    }

    function updateModalEntries(detail) {
        document.querySelectorAll(`[data-modal-user-id="${detail.userId}"]`).forEach((row) => {
            const usernameNode = row.querySelector('[data-username-display]');
            const existingUsername = usernameNode ? usernameNode.textContent.replace(/^@/, '').trim() : '';
            const fallbackUsername = detail.username || existingUsername;
            if (Object.prototype.hasOwnProperty.call(detail, 'photoUrl')) {
                const avatarNode = row.querySelector('[data-avatar]');
                if (avatarNode) {
                    if (detail.photoUrl) {
                        if (avatarNode.tagName === 'IMG') {
                            avatarNode.src = detail.photoUrl;
                        } else {
                            const img = document.createElement('img');
                            img.src = detail.photoUrl;
                            img.className = 'w-12 h-12 rounded-full object-cover flex-shrink-0';
                            img.dataset.avatar = 'true';
                            avatarNode.replaceWith(img);
                        }
                    } else if (avatarNode.tagName === 'IMG') {
                        const fallback = document.createElement('div');
                        fallback.className = 'w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold flex-shrink-0';
                        fallback.dataset.avatar = 'true';
                        fallback.textContent = modalInitial(fallbackUsername);
                        avatarNode.replaceWith(fallback);
                    } else {
                        avatarNode.textContent = modalInitial(fallbackUsername);
                    }
                }
            }
            if (detail.fullName) {
                const fullNameNode = row.querySelector('[data-full-name]');
                if (fullNameNode) {
                    fullNameNode.textContent = detail.fullName;
                }
            }
            if (detail.username) {
                if (usernameNode) {
                    usernameNode.textContent = `@${detail.username}`;
                }
                row.querySelectorAll('[data-profile-link]').forEach((link) => {
                    link.setAttribute('href', `/profile/${detail.username}`);
                });
            }
        });
    }

    function updateCachedUser(detail) {
        const hasPhoto = Object.prototype.hasOwnProperty.call(detail, 'photoUrl');
        ['followers', 'following'].forEach((key) => {
            allData[key] = allData[key].map((entry) => {
                if (Number(entry.id) !== Number(detail.userId)) {
                    return entry;
                }
                const updated = { ...entry };
                if (detail.username) {
                    updated.username = detail.username;
                }
                if (detail.fullName) {
                    updated.full_name = detail.fullName;
                }
                if (hasPhoto) {
                    updated.profile_photo_path = detail.photoUrl;
                }
                return updated;
            });
        });
    }

    window.addEventListener('user:profile-updated', (event) => {
        const detail = event.detail || {};
        if (!detail.userId) {
            return;
        }
        if (profileUserId !== null && Number(detail.userId) === profileUserId) {
            if (detail.username) {
                username = detail.username;
            }
            updateHeroAvatar(detail);
            updateHeroText(detail);
        }
        updateCachedUser(detail);
        updateModalEntries(detail);
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : text;
        return div.innerHTML;
    }
})();
</script>
