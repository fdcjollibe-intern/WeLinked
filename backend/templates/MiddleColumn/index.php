<?php
/**
 * Middle column component: post composer + posts list
 * Variables: $posts (array), $start, $limit
 */

// Helper function to format time ago
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

// Reaction emoji mapping
$reactionEmojis = [
    'like' => '❤️',
    'haha' => '😆',
    'love' => '😍',
    'wow' => '😮',
    'sad' => '😢',
    'angry' => '😠'
];

// Current feed type
$currentFeed = $feed ?? 'friends';
?>
<section class="middle-column flex flex-col h-full py-4" data-current-feed="<?= h($currentFeed) ?>">
    <!-- Header with Tabs -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Feeds</h1>
        <div class="flex items-center space-x-4 text-sm" id="feed-tabs">
            <a href="#" class="feed-tab <?= $currentFeed === 'foryou' ? 'text-blue-500 font-medium border-b-2 border-blue-500 pb-1' : 'text-gray-400 hover:text-gray-600' ?>" data-feed="foryou">For You</a>
            <a href="#" class="feed-tab <?= $currentFeed === 'friends' ? 'text-blue-500 font-medium border-b-2 border-blue-500 pb-1' : 'text-gray-400 hover:text-gray-600' ?>" data-feed="friends">Friends</a>
            <a href="#" class="feed-tab text-gray-400 hover:text-gray-600 opacity-50 cursor-not-allowed" data-feed="reels">Reels</a>
        </div>
    </div>

    <!-- Post Composer -->
    <div id="post-composer" class="composer bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-4 relative">
        <div class="flex items-start space-x-3 mb-3">
            <?php 
                $userInitial = isset($currentUser->username) ? strtoupper(substr($currentUser->username, 0, 1)) : 'Y';
            ?>
            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm"><?= $userInitial ?></div>
            <div class="flex-1">
                <textarea id="post-composer-textarea" placeholder="What's on your mind? Use @ to mention friends..." rows="3" class="w-full mt-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-gray-600 placeholder-gray-400 resize-none overflow-y-hidden" style="min-height:72px;max-height:320px;line-height:1.4;transition:height 140ms ease"></textarea>
            </div>
        </div>
        <!-- Location Input (initially hidden) -->
        <div id="location-input-container" class="mb-3 hidden">
            <div class="flex items-center space-x-2 px-3 py-2 bg-gray-50 rounded-lg">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <input type="text" id="post-location-input" placeholder="Add location..." class="flex-1 bg-transparent border-0 focus:ring-0 text-sm text-gray-700 placeholder-gray-400">
                <button id="remove-location-btn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <input id="attachment-input" type="file" multiple accept="image/*,video/*" class="hidden">
                <label for="attachment-input" class="flex items-center space-x-1 text-gray-500 text-sm cursor-pointer hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Image</span>
                </label>
                <label for="attachment-input" class="flex items-center space-x-1 text-gray-500 text-sm cursor-pointer hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Video</span>
                </label>
                <button id="toggle-location-btn" class="flex items-center space-x-1 text-gray-500 text-sm hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Location</span>
                </button>
            </div>
            <button id="post-submit-btn" class="bg-blue-500 text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-blue-600 transition-colors">Send</button>
        </div>
        <div id="attachment-preview" class="mt-3"></div>
        <div id="composer-drop-overlay" class="hidden absolute inset-0 bg-white bg-opacity-80 rounded-2xl flex items-center justify-center text-gray-600 text-lg font-medium border-2 border-dashed border-gray-300" style="pointer-events:none">Drop files here to attach</div>
    </div>

    <div id="posts-list" data-start="<?= h($start ?? 0) ?>" data-feed="<?= h($currentFeed) ?>">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $i => $post): ?>
                <?php
                    $idx = ($start ?? 0) + $i + 1;
                    $attachments = $post->attachments ?? [];
                    $user = $post->user ?? null;
                    $username = $user->username ?? 'Unknown';
                    $fullName = $user->full_name ?? $username;
                    $profilePhoto = $user->profile_photo_path ?? null;
                    $userInitial = strtoupper(substr($username, 0, 1));
                    
                    // Reaction data
                    $reactionCounts = $post->reaction_counts ?? [];
                    $totalReactions = $post->total_reactions ?? 0;
                    $userReaction = $post->user_reaction ?? null;
                    
                    // Get top 3 reaction types for display
                    arsort($reactionCounts);
                    $topReactions = array_slice(array_keys($reactionCounts), 0, 3);
                ?>
                <article class="post bg-white rounded-2xl shadow-sm border border-gray-100 p-5" data-index="<?= $idx ?>" data-post-id="<?= h($post->id ?? $idx) ?>">
                    <!-- Post Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden">
                                <?php if ($profilePhoto): ?>
                                    <img src="<?= h($profilePhoto) ?>" alt="<?= h($username) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                        <?= h($userInitial) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900"><?= h($fullName) ?></h3>
                                <p class="text-xs text-gray-400"><?= h(timeAgo($post->created_at)) ?></p>
                            </div>
                        </div>
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Post Content -->
                    <?php if (!empty($post->content_text)): ?>
                        <p class="text-gray-700 mb-4"><?= nl2br(h($post->content_text)) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($post->location)): ?>
                        <div class="flex items-center space-x-1 text-sm text-gray-500 mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span><?= h($post->location) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Post Images/Attachments -->
                    <?php if (!empty($attachments)): ?>
                        <div class="post-gallery mt-3 mb-4 overflow-hidden rounded-xl bg-gray-50">
                            <?php if (count($attachments) === 1): ?>
                                <img src="<?= h($attachments[0]) ?>" alt="Post image" class="w-full h-auto object-cover">
                            <?php elseif (count($attachments) === 2): ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php foreach ($attachments as $img): ?>
                                        <img src="<?= h($img) ?>" alt="Post image" class="w-full h-full object-cover">
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif (count($attachments) === 3): ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <img src="<?= h($attachments[0]) ?>" alt="Post image" class="w-full h-full object-cover row-span-2">
                                    <div class="grid grid-rows-2 gap-2">
                                        <img src="<?= h($attachments[1]) ?>" alt="Post image" class="w-full h-full object-cover">
                                        <img src="<?= h($attachments[2]) ?>" alt="Post image" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- 4+ photos: show 3 with +N overlay -->
                                <div class="grid grid-cols-2 gap-2">
                                    <img src="<?= h($attachments[0]) ?>" alt="Post image" class="w-full h-full object-cover row-span-2">
                                    <div class="grid grid-rows-2 gap-2">
                                        <img src="<?= h($attachments[1]) ?>" alt="Post image" class="w-full h-full object-cover">
                                        <div class="relative">
                                            <img src="<?= h($attachments[2]) ?>" alt="Post image" class="w-full h-full object-cover">
                                            <?php if (count($attachments) > 3): ?>
                                                <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                                                    <span class="text-white text-2xl font-bold">+<?= count($attachments) - 3 ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
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
                        <div class="flex items-center space-x-4">
                            <span class="comments-count">0 comments</span>
                            <span class="shares-count">0 shares</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="border-t border-gray-100 pt-2 flex items-center justify-around">
                        <button class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" data-user-reaction="<?= h($userReaction ?? '') ?>">
                            <svg class="like-icon w-5 h-5 <?= $userReaction ? 'text-red-500 fill-current' : 'text-gray-500' ?>" fill="<?= $userReaction ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span class="reaction-label text-sm font-medium <?= $userReaction ? 'text-red-500' : 'text-gray-700' ?>">
                                <?= $userReaction ? ucfirst($userReaction) : 'Like' ?>
                            </span>
                        </button>
                        <button class="comment-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Comment</span>
                        </button>
                        <button class="share-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Share</span>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-posts bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <p class="text-lg font-medium">No posts yet</p>
                <p class="text-sm text-gray-400 mt-2">Be the first to share something!</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<script>
console.debug('[MiddleColumn] Template rendered with <?= count($posts) ?> posts, feed=<?= h($currentFeed) ?>, start=<?= h($start ?? 0) ?>');
console.debug('[MiddleColumn] Posts list element exists:', !!document.getElementById('posts-list'));
console.debug('[MiddleColumn] Composer element exists:', !!document.getElementById('post-composer'));
console.debug('[MiddleColumn] Post textarea exists:', !!document.getElementById('post-composer-textarea'));
console.debug('[MiddleColumn] Submit button exists:', !!document.getElementById('post-submit-btn'));
</script>
<?= $this->Html->script('middle') ?>
