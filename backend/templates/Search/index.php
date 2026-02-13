<?php
/**
 * Search Results View
 */
?>
<section class="flex flex-col h-full py-4">
    <!-- Search Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Search Results</h1>
        
        <!-- Search Type Tabs -->
        <div class="flex items-center space-x-2 border-b border-gray-200">
            <a href="/search?q=<?= urlencode($query) ?>&type=users" 
               class="px-4 py-2 text-sm font-medium <?= $type === 'users' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' ?>">
                Users
            </a>
            <a href="/search?q=<?= urlencode($query) ?>&type=posts" 
               class="px-4 py-2 text-sm font-medium <?= $type === 'posts' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' ?>">
                Posts
            </a>
        </div>
    </div>

    <?php if (empty($query)): ?>
        <!-- Empty Search State -->
        <div class="flex flex-col items-center justify-center py-12">
            <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Search for people and posts</h3>
            <p class="text-gray-500 text-center max-w-md">
                Enter a search term to find users or posts across WeLinked
            </p>
        </div>
    <?php elseif (empty($results)): ?>
        <!-- No Results State -->
        <div class="flex flex-col items-center justify-center py-12">
            <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No results found</h3>
            <p class="text-gray-500 text-center max-w-md">
                Try searching for something else or check the other tab
            </p>
        </div>
    <?php else: ?>
        <!-- Results -->
        <div class="space-y-4">
            <?php if ($type === 'users'): ?>
                <!-- User Results -->
                <?php foreach ($results as $user): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 flex-1 min-w-0">
                                <!-- Profile Photo -->
                                <a href="/profile/<?= h($user['username']) ?>" class="flex-shrink-0">
                                    <?php if (!empty($user['profile_photo_path'])): ?>
                                        <img src="<?= h($user['profile_photo_path']) ?>" 
                                             alt="<?= h($user['username']) ?>" 
                                             class="w-14 h-14 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-14 h-14 rounded-full bg-blue-500 flex items-center justify-center text-white text-lg font-bold">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                
                                <!-- User Info -->
                                <div class="flex-1 min-w-0">
                                    <a href="/profile/<?= h($user['username']) ?>" class="hover:underline">
                                        <h3 class="font-semibold text-gray-900 truncate">
                                            <?= h($user['full_name']) ?>
                                        </h3>
                                    </a>
                                    <p class="text-sm text-gray-500 truncate">
                                        @<?= h($user['username']) ?>
                                    </p>
                                    <?php if ($user['mutual_count'] > 0): ?>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <?= $user['mutual_count'] ?> mutual <?= $user['mutual_count'] === 1 ? 'friend' : 'friends' ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Action Button -->
                            <div class="flex-shrink-0 ml-4">
                                <?php if ($user['is_following']): ?>
                                    <button class="unfollow-user-btn px-4 py-2 bg-gray-100 hover:bg-red-50 border border-gray-200 hover:border-red-300 text-gray-600 hover:text-red-600 rounded-lg text-sm font-medium transition-colors"
                                            data-user-id="<?= $user['id'] ?>">
                                        Following
                                    </button>
                                <?php else: ?>
                                    <button class="follow-user-btn px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors"
                                            data-user-id="<?= $user['id'] ?>">
                                        Follow
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Post Results -->
                <?php foreach ($results as $post): ?>
                    <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5" data-post-id="<?= $post['id'] ?>">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <a href="/profile/<?= h($post['user']['username']) ?>">
                                    <?php if (!empty($post['user']['profile_photo_path'])): ?>
                                        <img src="<?= h($post['user']['profile_photo_path']) ?>" 
                                             alt="<?= h($post['user']['username']) ?>" 
                                             class="w-10 h-10 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-pink-400 flex items-center justify-center text-white font-bold">
                                            <?= strtoupper(substr($post['user']['username'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <div>
                                    <a href="/profile/<?= h($post['user']['username']) ?>" class="hover:underline">
                                        <h3 class="font-semibold text-gray-900"><?= h($post['user']['full_name']) ?></h3>
                                    </a>
                                    <p class="text-xs text-gray-400"><?= $post['created_at']->timeAgoInWords() ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Post Content with Highlighting -->
                        <div class="text-gray-700 mb-4">
                            <?= $post['highlighted_content'] ?>
                        </div>
                        
                        <?php if (!empty($post['location'])): ?>
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <?= h($post['location']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($post['content_image_path'])): ?>
                            <div class="rounded-xl overflow-hidden mb-4">
                                <img src="<?= h($post['content_image_path']) ?>" 
                                     alt="Post image" 
                                     class="w-full object-cover">
                            </div>
                        <?php endif; ?>
                        
                        <!-- Post Actions -->
                        <div class="border-t border-gray-100 pt-3 flex items-center justify-around">
                            <button class="flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span class="text-sm">Like</span>
                            </button>
                            <button class="flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span class="text-sm">Comment</span>
                            </button>
                            <button class="flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                </svg>
                                <span class="text-sm">Share</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Results Summary -->
        <div class="mt-6 text-center text-sm text-gray-500">
            Showing <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for "<?= h($query) ?>"
        </div>
    <?php endif; ?>
</section>

<script>
// Handle follow/unfollow in search results
document.addEventListener('click', function(e) {
    const followBtn = e.target.closest('.follow-user-btn');
    const unfollowBtn = e.target.closest('.unfollow-user-btn');
    
    if (followBtn) {
        const userId = followBtn.dataset.userId;
        
        followBtn.disabled = true;
        followBtn.textContent = 'Following...';
        
        fetch('/friends/follow', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                followBtn.textContent = 'Following';
                followBtn.classList.remove('follow-user-btn', 'bg-blue-500', 'hover:bg-blue-600');
                followBtn.classList.add('unfollow-user-btn', 'bg-gray-100', 'hover:bg-red-50', 'border', 'border-gray-200', 'hover:border-red-300', 'text-gray-600', 'hover:text-red-600');
                followBtn.disabled = false;
            } else {
                alert(data.message || 'Failed to follow user');
                followBtn.disabled = false;
                followBtn.textContent = 'Follow';
            }
        })
        .catch(error => {
            console.error('Follow error:', error);
            followBtn.disabled = false;
            followBtn.textContent = 'Follow';
        });
    }
    
    if (unfollowBtn) {
        const userId = unfollowBtn.dataset.userId;
        
        if (!confirm('Unfollow this user?')) return;
        
        unfollowBtn.disabled = true;
        unfollowBtn.textContent = 'Unfollowing...';
        
        fetch('/friends/unfollow', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                unfollowBtn.textContent = 'Follow';
                unfollowBtn.classList.remove('unfollow-user-btn', 'bg-gray-100', 'hover:bg-red-50', 'border', 'border-gray-200', 'hover:border-red-300', 'text-gray-600', 'hover:text-red-600');
                unfollowBtn.classList.add('follow-user-btn', 'bg-blue-500', 'hover:bg-blue-600', 'text-white');
                unfollowBtn.disabled = false;
            } else {
                alert(data.message || 'Failed to unfollow user');
                unfollowBtn.disabled = false;
                unfollowBtn.textContent = 'Following';
            }
        })
        .catch(error => {
            console.error('Unfollow error:', error);
            unfollowBtn.disabled = false;
            unfollowBtn.textContent = 'Following';
        });
    }
});
</script>
