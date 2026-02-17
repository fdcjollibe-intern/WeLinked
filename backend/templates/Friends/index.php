<?php
/**
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
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start space-x-4">
                            <!-- Profile Photo -->
                            <a href="/profile/<?= h($follower['username']) ?>" class="flex-shrink-0">
                                <?php if (!empty($follower['profile_photo_path'])): ?>
                                    <img src="<?= h($follower['profile_photo_path']) ?>" 
                                         alt="<?= h($follower['username']) ?>" 
                                         class="w-16 h-16 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-xl font-bold">
                                        <?= strtoupper(substr($follower['username'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Follower Info -->
                            <div class="flex-1 min-w-0">
                                <a href="/profile/<?= h($follower['username']) ?>" class="hover:underline">
                                    <h3 class="font-semibold text-gray-900 truncate">
                                        <?= h($follower['full_name']) ?>
                                    </h3>
                                </a>
                                <p class="text-sm text-gray-500 truncate">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Not following anyone yet</h3>
                <p class="text-gray-500 text-center max-w-md">
                    Start following people to build your network and see their posts in your feed
                </p>
            </div>
        <?php else: ?>
            <!-- Following Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($following as $friend): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start space-x-4">
                            <!-- Profile Photo -->
                            <a href="/profile/<?= h($friend['username']) ?>" class="flex-shrink-0">
                                <?php if (!empty($friend['profile_photo_path'])): ?>
                                    <img src="<?= h($friend['profile_photo_path']) ?>" 
                                         alt="<?= h($friend['username']) ?>" 
                                         class="w-16 h-16 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-xl font-bold">
                                        <?= strtoupper(substr($friend['username'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            
                            <!-- Friend Info -->
                            <div class="flex-1 min-w-0">
                                <a href="/profile/<?= h($friend['username']) ?>" class="hover:underline">
                                    <h3 class="font-semibold text-gray-900 truncate">
                                        <?= h($friend['full_name']) ?>
                                    </h3>
                                </a>
                                <p class="text-sm text-gray-500 truncate">
                                    @<?= h($friend['username']) ?>
                                </p>
                                
                                <?php if ($friend['mutual_count'] > 0): ?>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?= $friend['mutual_count'] ?> mutual <?= $friend['mutual_count'] === 1 ? 'friend' : 'friends' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="text-xs text-gray-400 mt-1">
                                    Friends since <?= $friend['friendship_date']->format('M Y') ?>
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
// Tab switching
document.addEventListener('click', function(e) {
    const tab = e.target.closest('.friends-tab');
    if (!tab) return;
    
    e.stopPropagation();
    const targetTab = tab.dataset.tab;
    
    // Update tab styles
    document.querySelectorAll('.friends-tab').forEach(t => {
        t.classList.remove('border-blue-500', 'text-blue-600');
        t.classList.add('border-transparent', 'text-gray-500');
    });
    tab.classList.remove('border-transparent', 'text-gray-500');
    tab.classList.add('border-blue-500', 'text-blue-600');
    
    // Show/hide content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    const targetContent = document.getElementById(targetTab + '-content');
    if (targetContent) {
        targetContent.classList.remove('hidden');
    }
}, true);

// Handle follow action
document.addEventListener('click', function(e) {
    const followBtn = e.target.closest('.follow-btn');
    if (!followBtn) return;
    
    const userId = followBtn.dataset.userId;
    const username = followBtn.dataset.username;
    
    followBtn.disabled = true;
    followBtn.textContent = 'Following...';
    
    fetch('/friends/follow', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId })
    })
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

// Handle unfollow action
document.addEventListener('click', function(e) {
    const unfollowBtn = e.target.closest('.unfollow-btn');
    if (!unfollowBtn) return;
    
    const userId = unfollowBtn.dataset.userId;
    const username = unfollowBtn.dataset.username;
    
    // Show confirmation modal instead of native confirm
    showUnfollowConfirm(`Are you sure you want to unfollow @${username}?`).then(async (confirmed) => {
        if (!confirmed) return;

        unfollowBtn.disabled = true;
        const originalText = unfollowBtn.textContent;
        unfollowBtn.textContent = 'Unfollowing...';

        try {
            const response = await fetch('/friends/unfollow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ user_id: userId })
            });

            if (!response.ok) {
                // Try to read response body for debug, but treat as failure
                let text = null;
                try { text = await response.text(); } catch (e) { /* ignore */ }
                throw new Error(text || 'Server returned an error');
            }

            let data = null;
            try {
                data = await response.json();
            } catch (err) {
                // If parsing fails but response.ok, consider it success (server performed action)
                data = { success: true };
            }

            if (data && data.success) {
                // Remove card from DOM with animation
                const card = unfollowBtn.closest('.bg-white');
                if (card) {
                    card.style.transition = 'opacity 220ms, transform 220ms';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        card.remove();

                        // If empty, reload to show empty state
                        const activeTab = document.querySelector('.friends-tab.border-blue-500');
                        if (activeTab) {
                            const grid = document.querySelector(`#${activeTab.dataset.tab}-content .grid`);
                            if (grid && grid.children.length === 0) {
                                location.reload();
                            }
                        }
                    }, 260);
                }
            } else {
                showToast('Failed to unfollow: ' + (data && data.message ? data.message : 'Unknown error'));
                unfollowBtn.disabled = false;
                unfollowBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('Unfollow error:', error);
            showToast('An error occurred while unfollowing');
            unfollowBtn.disabled = false;
            unfollowBtn.textContent = originalText;
        }
    });
});

// Confirmation modal helper
function showUnfollowConfirm(message) {
    return new Promise((resolve) => {
        let modal = document.getElementById('unfollow-confirm-modal');
        if (!modal) {
            // create modal
            modal = document.createElement('div');
            modal.id = 'unfollow-confirm-modal';
            modal.innerHTML = `
                <div class="fixed inset-0 z-60 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black opacity-50"></div>
                    <div class="bg-white rounded-lg shadow-lg z-70 max-w-sm w-full p-4">
                        <p id="unfollow-confirm-message" class="text-sm text-gray-800"></p>
                        <div class="mt-4 flex justify-end gap-2">
                            <button id="unfollow-cancel" class="px-4 py-2 rounded bg-gray-100">Cancel</button>
                            <button id="unfollow-confirm" class="px-4 py-2 rounded bg-red-600 text-white">Unfollow</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        const msg = document.getElementById('unfollow-confirm-message');
        msg.textContent = message;

        const btnConfirm = document.getElementById('unfollow-confirm');
        const btnCancel = document.getElementById('unfollow-cancel');

        const cleanup = () => {
            btnConfirm.removeEventListener('click', onConfirm);
            btnCancel.removeEventListener('click', onCancel);
            if (modal && modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        };

        const onConfirm = () => { cleanup(); resolve(true); };
        const onCancel = () => { cleanup(); resolve(false); };

        btnConfirm.addEventListener('click', onConfirm);
        btnCancel.addEventListener('click', onCancel);
    });
}

// Simple toast helper
function showToast(text) {
    let t = document.getElementById('global-toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'global-toast';
        t.className = 'fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white px-4 py-2 rounded shadow';
        document.body.appendChild(t);
    }
    t.textContent = text;
    t.style.opacity = '1';
    setTimeout(() => { if (t) t.style.opacity = '0'; }, 3000);
}
</script>
