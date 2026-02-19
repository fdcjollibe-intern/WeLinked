<?php
/**
 * Search Results View - Full Page with Dashboard Layout
 */

// Get current user
$currentUser = $this->get('currentUser') ?? (object)['username' => 'User', 'fullname' => 'User'];
$navHasPhoto = !empty($currentUser->profile_photo_path);
?>
<?= $this->Html->css('dashboard') ?>
<script>
    window.csrfToken = '<?= $this->request->getAttribute('csrfToken') ?>';
    window.currentUserId = <?= json_encode($currentUser->id ?? null) ?>;
    window.currentUserPhoto = <?= json_encode($currentUser->profile_photo_path ?? '') ?>;
    window.currentUserInitial = <?= json_encode(strtoupper(substr($currentUser->username ?? 'U', 0, 1))) ?>;
</script>

<!-- Navbar (copied from Dashboard) -->
<nav class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50 h-16">
    <div class="flex items-center justify-between px-6 h-full max-w-screen-2xl mx-auto">
        <div class="flex items-center space-x-6">
            <a href="<?= $this->Url->build('/') ?>" class="flex items-center space-x-2">
                <picture>
                    <source srcset="/assets/logo.avif" type="image/avif">
                    <img src="/assets/logo.png" alt="WeLinked logo" class="w-10 h-10" />
                </picture>
                <span class="text-xl font-bold text-gray-900 hidden sm:block" style="margin-left: -2px;">eLinked</span>
            </a>
            <div class="hidden md:flex items-center bg-gray-100 rounded-full px-4 py-2">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search" id="global-search-input" placeholder="Search users & posts..." class="bg-transparent border-0 focus:ring-0 focus:outline-none focus:border-transparent text-sm w-64 placeholder-gray-400" autocomplete="off" value="<?= h($query) ?>" />
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <button id="notifications-bell" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span id="notifications-badge" class="absolute top-1 right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                </button>
            </div>
            <div class="relative">
                <div class="w-10 h-10 rounded-full p-0.5 bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600">
                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                        <?php if ($navHasPhoto): ?>
                            <img src="<?= h($currentUser->profile_photo_path) ?>" alt="Profile" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm">
                                <?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Main 3-column layout -->
<div class="max-w-screen-2xl mx-auto mt-16 pt-4">
    <div class="flex gap-6">
        <!-- Left Sidebar -->
        <aside id="left-component" class="hidden lg:block w-64 flex-shrink-0 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto">
            <?= $this->element('left_sidebar') ?>
        </aside>

        <!-- Middle Column (Search Results) -->
        <main id="middle-component" class="flex-1 min-w-0 max-w-2xl mx-auto">
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
                <?php
                // Reaction emoji mapping
                $reactionEmojis = [
                    'like' => '❤️',
                    'haha' => '😆',
                    'love' => '🥰',
                    'wow' => '😮',
                    'sad' => '😢',
                    'angry' => '😡'
                ];
                ?>
                <?php foreach ($results as $post): ?>
                    <?php
                        // Extract reaction data
                        $reactionCounts = $post['reaction_counts'] ?? [];
                        $totalReactions = $post['total_reactions'] ?? 0;
                        $userReaction = $post['user_reaction'] ?? null;
                        $commentCount = $post['comments_count'] ?? 0;
                        
                        // Get top 3 reaction types for display
                        arsort($reactionCounts);
                        $topReactions = array_slice(array_keys($reactionCounts), 0, 3);
                        
                        // Separate video and image attachments
                        $videoAttachments = [];
                        $imageAttachments = [];
                        if (!empty($post['post_attachments'])) {
                            foreach ($post['post_attachments'] as $attachment) {
                                if ($attachment['file_type'] === 'video') {
                                    $videoAttachments[] = $attachment;
                                } else {
                                    $imageAttachments[] = $attachment;
                                }
                            }
                        }
                        
                        // If no PostAttachments but has old content_image_path, use that
                        if (empty($imageAttachments) && !empty($post['attachments'])) {
                            $imageAttachments = $post['attachments'];
                        }
                    ?>
                    <article id="post-<?= h($post['id']) ?>" class="post bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4" data-post-id="<?= h($post['id']) ?>">
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
                        
                        <!-- Video Attachments -->
                        <?php if (!empty($videoAttachments)): ?>
                            <?php foreach ($videoAttachments as $video): ?>
                                <div class="post-video mt-3 mb-3 rounded-xl bg-black flex items-center justify-center overflow-hidden" style="max-height: 500px;">
                                    <video 
                                        class="w-full h-auto object-contain"
                                        style="max-height: 500px;"
                                        src="<?= h($video['file_path']) ?>"
                                        controls
                                        preload="metadata"
                                        playsinline
                                    >
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Post Images/Attachments -->
                        <?php if (!empty($imageAttachments) && !($post->is_reel ?? false)): ?>
                                <?php 
                                // Normalize attachments to URLs
                                $imageUrls = [];
                                foreach ($imageAttachments as $img) {
                                    if (is_array($img) && isset($img['file_path'])) {
                                        $imageUrls[] = $img['file_path'];
                                    } elseif (is_string($img) && !empty(trim($img))) {
                                        $imageUrls[] = $img;
                                    }
                                }
                                // Re-index array to ensure sequential keys
                                $imageUrls = array_values($imageUrls);
                                $imageUrlsJson = json_encode($imageUrls);
                                ?>
                                <?php if (count($imageUrls) === 0): ?>
                                    <!-- No valid image URLs -->
                                <?php elseif (count($imageUrls) === 1): ?>
                                    <div class="photo-collage cursor-pointer <?= !empty($videoAttachments) ? 'mt-2' : 'mt-3' ?> mb-4 rounded-xl overflow-hidden" data-post-id="<?= h($post->id) ?>" data-images='<?= h($imageUrlsJson) ?>' data-index="0" style="max-height: 450px;">
                                        <img src="<?= h($imageUrls[0]) ?>" alt="Post image" class="w-full h-auto object-cover" style="max-height: 450px;">
                                    </div>
                                <?php elseif (count($imageUrls) === 2): ?>
                                    <div class="photo-collage grid grid-cols-2 gap-2 cursor-pointer <?= !empty($videoAttachments) ? 'mt-2' : 'mt-3' ?> mb-4 rounded-xl overflow-hidden" 
                                         data-post-id="<?= h($post->id) ?>" 
                                         data-images='<?= h($imageUrlsJson) ?>'
                                         style="max-height: 350px;">
                                        <img src="<?= h($imageUrls[0]) ?>" alt="Post image" class="w-full h-full object-cover" data-index="0">
                                        <img src="<?= h($imageUrls[1]) ?>" alt="Post image" class="w-full h-full object-cover" data-index="1">
                                    </div>
                                <?php elseif (count($imageUrls) === 3): ?>
                                    <div class="photo-collage grid grid-cols-2 gap-2 cursor-pointer <?= !empty($videoAttachments) ? 'mt-2' : 'mt-3' ?> mb-4 rounded-xl overflow-hidden" 
                                         data-post-id="<?= h($post->id) ?>" 
                                         data-images='<?= h($imageUrlsJson) ?>'
                                         style="max-height: 350px;">
                                        <img src="<?= h($imageUrls[0]) ?>" alt="Post image" class="w-full h-full object-cover" data-index="0">
                                        <div class="relative overflow-hidden">
                                            <img src="<?= h($imageUrls[1]) ?>" alt="Post image" class="w-full h-full object-cover" data-index="1">
                                            <div class="absolute inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center pointer-events-none">
                                                <span class="text-white text-4xl font-bold">+1</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif (count($imageUrls) >= 4): ?>
                                    <!-- 4+ images: 2x2 grid with overlay on bottom-right -->
                                    <div class="photo-collage grid grid-cols-2 grid-rows-2 gap-2 cursor-pointer <?= !empty($videoAttachments) ? 'mt-2' : 'mt-3' ?> mb-4 rounded-xl overflow-hidden" 
                                         data-post-id="<?= h($post->id) ?>" 
                                         data-images='<?= h($imageUrlsJson) ?>'
                                         style="max-height: 400px;">
                                        <img src="<?= h($imageUrls[0]) ?>" alt="Post image" class="w-full h-full object-cover" data-index="0">
                                        <img src="<?= h($imageUrls[1]) ?>" alt="Post image" class="w-full h-full object-cover" data-index="1">
                                        <img src="<?= h($imageUrls[2]) ?>" alt="Post image" class="w-full h-full object-cover" data-index="2">
                                        <div class="relative overflow-hidden">
                                            <img src="<?= h($imageUrls[3]) ?>" alt="Post image" class="w-full h-full object-cover" data-index="3">
                                            <?php if (count($imageUrls) > 4): ?>
                                                <div class="absolute inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center pointer-events-none">
                                                    <span class="text-white text-4xl font-bold">+<?= count($imageUrls) - 4 ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Reactions Summary & Counts -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-2 px-1">
                            <?php if ($totalReactions > 0): ?>
                                <div class="flex items-center space-x-1 reaction-summary" data-total="<?= $totalReactions ?>">
                                    <span class="reaction-emojis" style="display:flex;align-items:center">
                                        <?php foreach ($topReactions as $index => $type): ?>
                                            <?php if (isset($reactionEmojis[$type])): ?>
                                                <span class="reaction-emoji" style="display:inline-block;<?= $index > 0 ? 'margin-left:-4px;' : '' ?>position:relative;z-index:<?= 3 - $index ?>;text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white"><?= $reactionEmojis[$type] ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </span>
                                    <span class="reaction-count"><?= $totalReactions ?></span>
                                </div>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>
                            <div class="flex items-center space-x-4 mt-2">
                                <span class="comments-count" data-count="<?= $commentCount ?>">
                                    <?= $commentCount ?> <?= $commentCount === 1 ? 'comment' : 'comments' ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="border-t border-gray-100 pt-2 flex items-center justify-around">
                            <button class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" data-user-reaction="<?= h($userReaction ?? '') ?>">
                                <?php if ($userReaction === 'like'): ?>
                                    <svg class="like-icon w-5 h-5 text-red-500" fill="currentColor" stroke="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                <?php elseif ($userReaction && isset($reactionEmojis[$userReaction])): ?>
                                    <svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <span class="reaction-emoji-active text-xl leading-none mr-1"><?= h($reactionEmojis[$userReaction]) ?></span>
                                <?php else: ?>
                                    <svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                <?php endif; ?>
                                <span class="reaction-label text-sm font-medium <?= $userReaction === 'like' ? 'text-red-500' : 'text-gray-700' ?>">
                                    <?= $userReaction ? ucfirst($userReaction) : 'Like' ?>
                                </span>
                            </button>
                            <button class="comment-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Comment</span>
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

        </section>
        </main>

        <!-- Right Sidebar -->
        <aside id="right-component" class="hidden xl:block w-80 flex-shrink-0 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto">
            <?= $this->element('right_sidebar') ?>
        </aside>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40">
    <div class="flex justify-around items-center h-16">
        <a href="<?= $this->Url->build('/') ?>" class="flex flex-col items-center justify-center flex-1 text-blue-500">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
            <span class="text-xs mt-1">Home</span>
        </a>
        <a href="<?= $this->Url->build('/search') ?>" class="flex flex-col items-center justify-center flex-1 text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-xs mt-1">Search</span>
        </a>
        <a href="<?= $this->Url->build('/friends') ?>" class="flex flex-col items-center justify-center flex-1 text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-xs mt-1">Friends</span>
        </a>
        <a href="<?= $this->Url->build('/profile/' . $currentUser->username) ?>" class="flex flex-col items-center justify-center flex-1 text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="text-xs mt-1">Profile</span>
        </a>
    </div>
</nav>

<script src="/js/middle.js"></script>
<script src="/js/search.js"></script>
<script src="/js/dashboard.js"></script>
<script src="/js/reactions.js"></script>
<script src="/js/comments.js"></script>
<script src="/js/composer-modal.js"></script>
<script src="/js/gallery.js"></script>
<script src="/js/notifications.js"></script>
<script>
// Initialize middle.js for post interactions on search results page
if (window.initializeMiddleColumn) {
    console.log('[Search] Initializing middle.js for post interactions');
    window.initializeMiddleColumn();
}
</script>

