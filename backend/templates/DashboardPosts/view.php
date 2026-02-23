<?php
/**
 * Single Post View
 * Variables: $post, $currentUser, $suggestions, $friendsCount, $isMobileView
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
    return 'Just now';
}

// Reaction emoji mapping
$reactionEmojis = [
    'like' => '❤️',
    'haha' => '😆',
    'love' => '🥰',
    'wow' => '😮',
    'sad' => '😢',
    'angry' => '😡',
];
?>
<?= $this->Html->css('dashboard') ?>
<script>
    window.csrfToken = '<?= $this->request->getAttribute('csrfToken') ?>';
    window.currentUserId = <?= json_encode($currentUser->id ?? null) ?>;
    window.currentUserPhoto = <?= json_encode($currentUser->profile_photo_path ?? '') ?>;
    window.currentUserInitial = <?= json_encode(strtoupper(substr($currentUser->username ?? 'U', 0, 1))) ?>;
    
    // Debug logging for page load
    console.log('[view.php] Page loaded - Post ID: <?= h($post->id) ?>');
    
    // Test to see if DOM is ready and buttons exist
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[view.php] DOM Content Loaded');
        setTimeout(function() {
            const posts = document.querySelectorAll('.post');
            const commentBtns = document.querySelectorAll('.comment-btn');
            const reactionBtns = document.querySelectorAll('.reaction-btn');
            console.log('[view.php] Posts found:', posts.length);
            console.log('[view.php] Comment buttons found:', commentBtns.length);
            console.log('[view.php] Reaction buttons found:', reactionBtns.length);
            
            if (commentBtns.length > 0) {
                console.log('[view.php] First comment button element:', commentBtns[0]);
                console.log('[view.php] First comment button classes:', commentBtns[0].className);
            }
        }, 500);
    });
</script>

<!-- Desktop / large view navbar -->
<?php $navHasPhoto = !empty($currentUser->profile_photo_path); ?>
<?php if (empty($isMobileView)): ?>
<nav class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50 h-16">
    <div class="flex items-center justify-between px-6 h-full max-w-screen-2xl mx-auto">
        <!-- Left: Logo + Search -->
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
                <input type="search" id="global-search-input" placeholder="Search users & posts..." class="bg-transparent border-0 focus:ring-0 focus:outline-none focus:border-transparent text-sm w-64 placeholder-gray-400" autocomplete="off" />
            </div>
        </div>

        <!-- Right: User actions -->
        <div class="flex items-center space-x-4">
            <!-- Notifications Bell -->
            <div class="relative">
                <button id="notifications-bell" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span id="notifications-badge" class="absolute top-1 right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                </button>
            </div>
            <!-- Profile Avatar with gradient border -->
            <div class="relative">
                <div class="w-10 h-10 rounded-full p-0.5 bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600">
                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                        <img
                            data-avatar="current-user"
                            src="<?= $navHasPhoto ? h($currentUser->profile_photo_path) : '' ?>"
                            alt="<?= h($currentUser->username ?? 'Profile photo') ?>"
                            class="w-full h-full object-cover <?= $navHasPhoto ? '' : 'hidden' ?>"
                        >
                        <div
                            data-avatar-fallback="current-user"
                            class="w-full h-full rounded-full flex items-center justify-center text-white font-semibold text-sm <?= $navHasPhoto ? 'hidden' : '' ?>"
                        >
                            <span data-user-initial><?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>



<?php else: ?>
<!-- Mobile top bar: logo (eLinked) + right icons -->
<nav class="bg-white border-b fixed top-0 left-0 right-0 z-50 h-14 flex items-center px-4">
    <div class="flex items-center justify-between w-full max-w-screen-2xl mx-auto">
        <a href="<?= $this->Url->build('/') ?>" class="flex items-center space-x-2">
            <picture class="inline-block">
                <source srcset="/assets/logo.avif" type="image/avif">
                <img src="/assets/logo.png" alt="eLinked logo" class="w-8 h-8" />
            </picture>
            <div class="brand-name header-name text-lg font-bold">eLinked</div>
        </a>

        <div class="flex items-center space-x-3">
            <!-- Create icon -->
            <button aria-label="Create" class="p-2 rounded-md text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <!-- Search icon -->
            <button aria-label="Search" class="p-2 rounded-md text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <!-- Message bubble icon -->
            <button aria-label="Messages" class="p-2 rounded-md text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8-1.45 0-2.83-.27-4.065-.76L3 21l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </button>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Main layout -->
<?php $mobilePad = (!empty($isMobileView) ? 'pt-14 pb-20' : 'pt-20'); ?>
<div class="bg-gray-50 min-h-screen <?= $mobilePad ?>">
    <div class="max-w-screen-2xl mx-auto flex">
        <!-- Left Sidebar -->
        <aside id="left-component" class="hidden lg:block w-72 flex-shrink-0 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto px-4">
            <?= $this->element('left_sidebar') ?>
        </aside>

        <!-- Center Content Area -->
        <main id="middle-component" class="flex-1 min-w-0 px-4 lg:px-8">
            <section class="middle-column flex flex-col h-full py-4">
                <!-- Back button -->
                <div class="mb-4">
                    <button onclick="window.history.back()" class="flex items-center space-x-2 px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="font-medium">Back</span>
                    </button>
                </div>

                <!-- Single Post -->
                <article id="post-<?= h($post->id) ?>" class="post bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5" data-post-id="<?= h($post->id) ?>">
                    <!-- Post Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <a href="/profile/<?= h($post->user->username) ?>" class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                <?php if (!empty($post->user->profile_photo_path)): ?>
                                    <img src="<?= h($post->user->profile_photo_path) ?>" alt="<?= h($post->user->username) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                        <?= strtoupper(substr($post->user->username, 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div>
                                <a href="/profile/<?= h($post->user->username) ?>" class="hover:underline">
                                    <h3 class="font-semibold text-gray-900"><?= h($post->user->full_name) ?></h3>
                                </a>
                                <p class="text-xs text-gray-400"><?= timeAgo($post->created_at) ?></p>
                            </div>
                        </div>
                        <?php if ($currentUser && isset($currentUser->id) && $post->user->id == $currentUser->id): ?>
                        <div class="relative post-menu-container">
                            <button class="post-menu-btn text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors" data-post-id="<?= h($post->id) ?>">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"/>
                                </svg>
                            </button>
                            <div class="post-menu-dropdown hidden absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <button class="post-edit-btn w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2" data-post-id="<?= h($post->id) ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Edit Post</span>
                                </button>
                                <button class="post-delete-btn w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center space-x-2" data-post-id="<?= h($post->id) ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Delete Post</span>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
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
                    <?php 
                    $imageAttachments = [];
                    $videoAttachments = [];
                    if (!empty($post->post_attachments)) {
                        foreach ($post->post_attachments as $attachment) {
                            if ($attachment->file_type === 'video') {
                                $videoAttachments[] = $attachment;
                            } else {
                                $imageAttachments[] = $attachment;
                            }
                        }
                    }
                    ?>
                    
                    <!-- Video Attachments -->
                    <?php if (!empty($videoAttachments)): ?>
                        <?php foreach ($videoAttachments as $video): ?>
                            <div class="post-video mt-3 mb-3 rounded-xl bg-black flex items-center justify-center overflow-hidden" style="max-height: 500px;">
                                <video 
                                    class="w-full h-auto object-contain"
                                    style="max-height: 500px;"
                                    src="<?= h($video->file_path) ?>"
                                    controls
                                    preload="metadata"
                                    playsinline
                                >
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($imageAttachments) && !($post->is_reel ?? false)): ?>
                        <?php 
                        // Normalize attachments to URLs
                        $imageUrls = [];
                        foreach ($imageAttachments as $img) {
                            if (is_object($img) && isset($img->file_path)) {
                                $imageUrls[] = $img->file_path;
                            } elseif (is_string($img)) {
                                $imageUrls[] = $img;
                            }
                        }
                        $imageUrlsJson = json_encode($imageUrls);
                        // If there's a video, limit images to avoid overpopulation
                        $maxImages = !empty($videoAttachments) ? 3 : count($imageUrls);
                        ?>
                        <?php if (count($imageUrls) === 1): ?>
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
                        <?php else: ?>
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
                        <?php 
                            $reactionCounts = $post->reaction_counts ?? [];
                            $totalReactions = $post->total_reactions ?? 0;
                            $topReactions = [];
                            if (!empty($reactionCounts)) {
                                arsort($reactionCounts);
                                $topReactions = array_slice(array_keys($reactionCounts), 0, 3);
                            }
                        ?>
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
                            <?php $commentTotal = (int)($post->comments_count ?? 0); ?>
                            <span class="comments-count" data-count="<?= $commentTotal ?>">
                                <?= h(__n('{0} comment', '{0} comments', $commentTotal, $commentTotal)) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="border-t border-gray-100 pt-2 flex items-center justify-around">
                        <!-- Reaction button - hover to show picker, click for quick like -->
                        <button class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" 
                                data-user-reaction="<?= h($post->user_reaction ?? '') ?>" 
                                style="cursor: pointer; position: relative; z-index: 1;">
                            <?php if ($post->user_reaction === 'like'): ?>
                                <svg class="like-icon w-5 h-5 text-red-500" fill="currentColor" stroke="none" viewBox="0 0 24 24" style="pointer-events: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            <?php elseif ($post->user_reaction && isset($reactionEmojis[$post->user_reaction])): ?>
                                <svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none; pointer-events: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span class="reaction-emoji-active text-xl leading-none mr-1" style="pointer-events: none;"><?= h($reactionEmojis[$post->user_reaction]) ?></span>
                            <?php else: ?>
                                <svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="pointer-events: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            <?php endif; ?>
                            <span class="reaction-label text-sm font-medium <?= $post->user_reaction === 'like' ? 'text-red-500' : 'text-gray-700' ?>" style="pointer-events: none;">
                                <?= $post->user_reaction ? ucfirst($post->user_reaction) : 'Like' ?>
                            </span>
                        </button>
                        <!-- Comment button - should trigger inline comment composer when clicked -->
                        <button class="comment-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" 
                                style="cursor: pointer; position: relative; z-index: 1;">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="pointer-events: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700" style="pointer-events: none;">Comment</span>
                        </button>
                    </div>
                </article>
            </section>
        </main>

        <!-- Right Sidebar -->
        <aside id="right-component" class="hidden xl:block w-80 flex-shrink-0 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto px-4">
            <?= $this->element('right_sidebar') ?>
        </aside>
    </div>
</div>

<?= $this->Html->script('dashboard') ?>
<?= $this->Html->script('mentions') ?>
<?= $this->Html->script('middle') ?>
<?= $this->Html->script('comments') ?>
<?= $this->Html->script('reactions') ?>
<?= $this->Html->script('gallery') ?>

<?php if (!empty($isMobileView)): ?>
<!-- Mobile bottom navigation -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t z-50">
    <div class="max-w-screen-2xl mx-auto px-4">
        <div class="grid grid-cols-5 gap-2 text-center py-2">
            <a href="/dashboard" class="flex flex-col items-center justify-center text-gray-700">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M4 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0h6"/>
                </svg>
                <span class="text-xs leading-tight">Home</span>
            </a>
            <a href="/reels" class="flex flex-col items-center justify-center text-gray-700">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0122 9.618v4.764a2 2 0 01-2.447 1.894L15 14M4 6h9v12H4z"/>
                </svg>
                <span class="text-xs leading-tight">Reels</span>
            </a>
            <a href="/friends" class="flex flex-col items-center justify-center text-gray-700">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-4a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="text-xs leading-tight">Friends</span>
            </a>
            <a href="/notifications" class="flex flex-col items-center justify-center text-gray-700">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1"/>
                </svg>
                <span class="text-xs leading-tight">Notifications</span>
            </a>
            <a href="/users/dashboard" class="flex flex-col items-center justify-center text-gray-700">
                <div class="w-6 h-6 rounded-full mb-0.5 overflow-hidden bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600 p-[1px]">
                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                        <img
                            data-avatar="current-user"
                            src="<?= $navHasPhoto ? h($currentUser->profile_photo_path) : '' ?>"
                            alt="<?= h($currentUser->username ?? 'Profile photo') ?>"
                            class="w-full h-full object-cover <?= $navHasPhoto ? '' : 'hidden' ?>"
                        >
                        <div
                            data-avatar-fallback="current-user"
                            class="w-full h-full rounded-full flex items-center justify-center text-white text-xs font-semibold <?= $navHasPhoto ? 'hidden' : '' ?>"
                        >
                            <span data-user-initial><?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?></span>
                        </div>
                    </div>
                </div>
                <span class="text-xs leading-tight">Menu</span>
            </a>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Load JS modules -->
<script src="/js/dashboard.js"></script>
<script src="/js/mentions.js"></script>
<script src="/js/middle.js"></script>
<script src="/js/comments.js"></script>
<script src="/js/reactions.js"></script>
<script src="/js/gallery.js"></script>
<script src="/js/composer-modal.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/post-composer.js"></script>
<script src="/js/search.js"></script>

