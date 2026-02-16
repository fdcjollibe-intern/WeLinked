<?php
/**
 * Friends List Element
 * @var \App\View\AppView $this
 * @var array $following
 * @var array $followers
 */
?>
<section class="flex flex-col h-full py-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Friends</h1>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <div class="flex gap-8">
            <button class="friends-tab px-4 py-3 text-sm font-semibold border-b-2 border-blue-500 text-blue-600" data-tab="followers">
                Followers (<?= count($followers) ?>)
            </button>
            <button class="friends-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="following">
                Following (<?= count($following) ?>)
            </button>
        </div>
    </div>

    <!-- Followers Tab Content -->
    <div id="followers-content" class="tab-content">
        <?php if (empty($followers)): ?>
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No followers yet</h3>
                <p class="text-gray-500 text-center max-w-md">
                    When people follow you, they'll appear here
                </p>
            </div>
        <?php else: ?>
            <!-- Followers Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($followers as $follower): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow" data-user-card="<?= $follower['id'] ?>">
                        <div class="flex items-start space-x-4">
                            <!-- Profile Photo -->
                            <a href="/profile/<?= h($follower['username']) ?>" class="flex-shrink-0" data-profile-link>
                                <?php if (!empty($follower['profile_photo_path'])): ?>
                                    <img src="<?= h($follower['profile_photo_path']) ?>" 
                                         alt="<?= h($follower['username']) ?>" 
                                         class="w-16 h-16 rounded-full object-cover" data-avatar>
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-xl font-bold" data-avatar>
                                        <?= strtoupper(substr($follower['username'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Follower Info -->
                            <div class="flex-1 min-w-0">
                                <a href="/profile/<?= h($follower['username']) ?>" class="hover:underline" data-profile-link>
                                    <h3 class="font-semibold text-gray-900 truncate" data-full-name>
                                        <?= h($follower['full_name']) ?>
                                    </h3>
                                </a>
                                <p class="text-sm text-gray-500 truncate" data-username-display>
                                    @<?= h($follower['username']) ?>
                                </p>
                                
                                <?php if ($follower['mutual_count'] > 0): ?>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?= $follower['mutual_count'] ?> mutual <?= $follower['mutual_count'] === 1 ? 'friend' : 'friends' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="text-xs text-gray-400 mt-1">
                                    Following since <?= $follower['friendship_date']->format('M Y') ?>
                                </p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex flex-col space-y-2">
                                <?php if ($follower['is_following_back']): ?>
                                    <button class="unfollow-btn px-4 py-2 bg-white hover:bg-red-50 border border-gray-200 hover:border-red-300 text-gray-600 hover:text-red-600 rounded-lg text-sm font-medium transition-colors"
                                            data-user-id="<?= $follower['id'] ?>"
                                            data-username="<?= h($follower['username']) ?>">
                                        Unfollow
                                    </button>
                                <?php else: ?>
                                    <button class="follow-btn px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors"
                                            data-user-id="<?= $follower['id'] ?>"
                                            data-username="<?= h($follower['username']) ?>">
                                        Follow Back
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Following Tab Content -->
    <div id="following-content" class="tab-content hidden">
        <?php if (empty($following)): ?>
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-4a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Not following anyone yet</h3>
                <p class="text-gray-500 text-center max-w-md">
                    Start following people to see them here
                </p>
            </div>
        <?php else: ?>
            <!-- Following Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($following as $friend): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow" data-user-card="<?= $friend['id'] ?>">
                        <div class="flex items-start space-x-4">
                            <!-- Profile Photo -->
                            <a href="/profile/<?= h($friend['username']) ?>" class="flex-shrink-0" data-profile-link>
                                <?php if (!empty($friend['profile_photo_path'])): ?>
                                    <img src="<?= h($friend['profile_photo_path']) ?>" 
                                         alt="<?= h($friend['username']) ?>" 
                                         class="w-16 h-16 rounded-full object-cover" data-avatar>
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-xl font-bold" data-avatar>
                                        <?= strtoupper(substr($friend['username'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Friend Info -->
                            <div class="flex-1 min-w-0">
                                <a href="/profile/<?= h($friend['username']) ?>" class="hover:underline" data-profile-link>
                                    <h3 class="font-semibold text-gray-900 truncate" data-full-name>
                                        <?= h($friend['full_name']) ?>
                                    </h3>
                                </a>
                                <p class="text-sm text-gray-500 truncate" data-username-display>
                                    @<?= h($friend['username']) ?>
                                </p>
                                
                                <?php if ($friend['mutual_count'] > 0): ?>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?= $friend['mutual_count'] ?> mutual <?= $friend['mutual_count'] === 1 ? 'friend' : 'friends' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="text-xs text-gray-400 mt-1">
                                    Following since <?= $friend['friendship_date']->format('M Y') ?>
                                </p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex flex-col space-y-2">
                                <button class="unfollow-btn px-4 py-2 bg-white hover:bg-red-50 border border-gray-200 hover:border-red-300 text-gray-600 hover:text-red-600 rounded-lg text-sm font-medium transition-colors"
                                        data-user-id="<?= $friend['id'] ?>"
                                        data-username="<?= h($friend['username']) ?>">
                                    Unfollow
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
(function () {
    if (window.__friendsListScriptLoaded) {
        return;
    }
    window.__friendsListScriptLoaded = true;

    const FRIENDS_CSRF_TOKEN = window.CSRF_TOKEN || window.csrfToken || document.querySelector('meta[name="csrfToken"]')?.content || document.querySelector('meta[name="csrf-token"]')?.content || '';

    function buildFriendsJsonRequest(payload) {
        return {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': FRIENDS_CSRF_TOKEN,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        };
    }

    function uppercaseInitial(value) {
        return (value || '?').trim().charAt(0).toUpperCase() || '?';
    }

    function updateAvatarNode(node, detail, fallbackUsername) {
        if (!node || !Object.prototype.hasOwnProperty.call(detail, 'photoUrl')) {
            return;
        }
        const { photoUrl } = detail;
        const usernameForInitial = detail.username || fallbackUsername;
        if (photoUrl) {
            if (node.tagName === 'IMG') {
                node.src = photoUrl;
                node.alt = detail.username || node.alt || 'Profile photo';
            } else {
                const img = document.createElement('img');
                img.src = photoUrl;
                img.alt = detail.username || 'Profile photo';
                img.className = 'w-16 h-16 rounded-full object-cover';
                img.dataset.avatar = 'true';
                node.replaceWith(img);
            }
        } else if (node.tagName === 'IMG') {
            const fallback = document.createElement('div');
            fallback.className = 'w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-xl font-bold';
            fallback.textContent = uppercaseInitial(usernameForInitial);
            fallback.dataset.avatar = 'true';
            node.replaceWith(fallback);
        } else {
            node.textContent = uppercaseInitial(usernameForInitial);
        }
    }

    function updateCardFromDetail(card, detail) {
        if (!card) {
            return;
        }
        const avatarNode = card.querySelector('[data-avatar]');
        const usernameNode = card.querySelector('[data-username-display]');
        const existingUsername = usernameNode ? usernameNode.textContent.replace(/^@/, '').trim() : '';
        const usernameForInitial = detail.username || existingUsername;
        if (avatarNode) {
            updateAvatarNode(avatarNode, detail, usernameForInitial);
        }
        if (detail.fullName) {
            const nameNode = card.querySelector('[data-full-name]');
            if (nameNode) {
                nameNode.textContent = detail.fullName;
            }
        }
        if (detail.username) {
            if (usernameNode) {
                usernameNode.textContent = `@${detail.username}`;
            }
            card.querySelectorAll('[data-profile-link]').forEach((link) => {
                link.setAttribute('href', `/profile/${detail.username}`);
            });
        }
    }

    window.addEventListener('user:profile-updated', (event) => {
        const detail = event.detail || {};
        if (!detail.userId) {
            return;
        }
        document.querySelectorAll(`[data-user-card="${detail.userId}"]`).forEach((card) => updateCardFromDetail(card, detail));
    });

    document.addEventListener('click', function(e) {
        const tab = e.target.closest('.friends-tab');
        if (!tab) return;
        
        const targetTab = tab.dataset.tab;
        
        document.querySelectorAll('.friends-tab').forEach(t => {
            t.classList.remove('border-blue-500', 'text-blue-600');
            t.classList.add('border-transparent', 'text-gray-500');
        });
        tab.classList.add('border-blue-500', 'text-blue-600');
        tab.classList.remove('border-transparent', 'text-gray-500');
        
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(targetTab + '-content').classList.remove('hidden');
    });

    document.addEventListener('click', function(e) {
        const followBtn = e.target.closest('.follow-btn');
        if (!followBtn) return;
        
        const userId = followBtn.dataset.userId;
        
        followBtn.disabled = true;
        followBtn.textContent = 'Following...';
        
        fetch('/friends/follow', buildFriendsJsonRequest({ user_id: userId }))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                followBtn.textContent = 'Unfollow';
                followBtn.classList.remove('follow-btn', 'bg-blue-500', 'hover:bg-blue-600', 'text-white');
                followBtn.classList.add('unfollow-btn', 'bg-white', 'hover:bg-red-50', 'border', 'border-gray-200', 'hover:border-red-300', 'text-gray-600', 'hover:text-red-600');
                followBtn.disabled = false;
            } else {
                alert('Failed to follow: ' + (data.message || 'Unknown error'));
                followBtn.disabled = false;
                followBtn.textContent = 'Follow Back';
            }
        })
        .catch(error => {
            console.error('Follow error:', error);
            alert('An error occurred while following');
            followBtn.disabled = false;
            followBtn.textContent = 'Follow Back';
        });
    });

    document.addEventListener('click', function(e) {
        const unfollowBtn = e.target.closest('.unfollow-btn');
        if (!unfollowBtn) return;
        
        const userId = unfollowBtn.dataset.userId;
        const username = unfollowBtn.dataset.username;
        
        if (!confirm(`Are you sure you want to unfollow @${username}?`)) {
            return;
        }
        
        unfollowBtn.disabled = true;
        unfollowBtn.textContent = 'Unfollowing...';
        
        fetch('/friends/unfollow', buildFriendsJsonRequest({ user_id: userId }))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = unfollowBtn.closest('.bg-white');
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                card.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    card.remove();
                    const activeTab = document.querySelector('.friends-tab.border-blue-500');
                    if (activeTab) {
                        const grid = document.querySelector(`#${activeTab.dataset.tab}-content .grid`);
                        if (grid && grid.children.length === 0) {
                            location.reload();
                        }
                    }
                }, 300);
            } else {
                alert('Failed to unfollow: ' + (data.message || 'Unknown error'));
                unfollowBtn.disabled = false;
                unfollowBtn.textContent = 'Unfollow';
            }
        })
        .catch(error => {
            console.error('Unfollow error:', error);
            alert('An error occurred while unfollowing');
            unfollowBtn.disabled = false;
            unfollowBtn.textContent = 'Unfollow';
        });
    });
})();
</script>
