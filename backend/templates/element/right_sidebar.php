<div class="p-4">
    <!-- Sponsored Section -->
    <div class="mb-6">
        <h2 class="text-gray-500 font-semibold text-sm mb-3">Sponsored</h2>
        <div class="space-y-4">
            <div class="flex space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=80&h=80&fit=crop&crop=center" alt="Pizza ad" class="w-16 h-16 rounded-lg object-cover">
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900">Lebo's Pizza</h3>
                    <p class="text-xs text-gray-500 mt-1">Experience the trendy pizza spot in Palo Alto being called the next big thing.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Birthdays Section -->
    <div class="mb-6">
        <h2 class="text-gray-500 font-semibold text-sm mb-3">Birthdays</h2>
        <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
            <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12,6A3,3 0 0,0 9,9A3,3 0 0,0 12,12A3,3 0 0,0 15,9A3,3 0 0,0 12,6M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/></svg>
            <div class="flex-1">
                <p class="text-sm text-gray-900">
                    <span class="font-semibold">Jessica, Erica</span> and <span class="font-semibold">2 others</span> have birthdays today.
                </p>
            </div>
        </div>
    </div>

    <!-- Friend Suggestions Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-gray-500 font-semibold text-sm">Friend Suggestions</h2>
        </div>
        <?php if (isset($suggestions) && !empty($suggestions)): ?>
            <div class="space-y-3">
                <?php foreach ($suggestions as $suggestion): ?>
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <?php if (!empty($suggestion['profile_photo_path'])): ?>
                                <img src="<?= h($suggestion['profile_photo_path']) ?>" alt="<?= h($suggestion['username']) ?>" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                    <?= strtoupper(substr($suggestion['username'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-900 font-semibold truncate"><?= h($suggestion['full_name']) ?></p>
                                <?php if ($suggestion['mutual_count'] > 0): ?>
                                    <p class="text-xs text-gray-500"><?= $suggestion['mutual_count'] ?> mutual <?= $suggestion['mutual_count'] === 1 ? 'friend' : 'friends' ?></p>
                                <?php else: ?>
                                    <p class="text-xs text-gray-500 truncate">@<?= h($suggestion['username']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button class="follow-suggestion-btn text-blue-500 hover:text-blue-600 text-xs font-semibold px-3 py-1 flex-shrink-0"
                                data-user-id="<?= $suggestion['id'] ?>"
                                data-username="<?= h($suggestion['username']) ?>">
                            Follow
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-6">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="text-sm text-gray-500">No suggestions available</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Handle follow button clicks
    document.addEventListener('click', function(e) {
        const followBtn = e.target.closest('.follow-suggestion-btn');
        if (!followBtn) return;
        
        const userId = followBtn.dataset.userId;
        const username = followBtn.dataset.username;
        
        followBtn.disabled = true;
        followBtn.textContent = 'Following...';
        
        fetch('/friends/follow', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ user_id: parseInt(userId) })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remove suggestion from list
                const suggestionCard = followBtn.closest('.hover\\:bg-gray-50');
                if (suggestionCard) {
                    suggestionCard.style.transition = 'all 0.3s ease';
                    suggestionCard.style.opacity = '0';
                    suggestionCard.style.transform = 'scale(0.95)';
                    setTimeout(() => suggestionCard.remove(), 300);
                }
            } else {
                alert(data.message || 'Failed to follow user');
                followBtn.disabled = false;
                followBtn.textContent = 'Follow';
            }
        })
        .catch(error => {
            console.error('Follow error:', error);
            alert('An error occurred while following');
            followBtn.disabled = false;
            followBtn.textContent = 'Follow';
        });
    });
    </script>
</div>
