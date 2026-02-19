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

if (!function_exists('profile_time_ago')) {
    function profile_time_ago($datetime) {
        if (!$datetime) {
            return 'Just now';
        }
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        }
        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        }
        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        return 'Just now';
    }
}

$reactionEmojis = [
    'like' => '❤️',
    'haha' => '😆',
    'love' => '🥰',
    'wow' => '😮',
    'sad' => '😢',
    'angry' => '😡',
];
?>
<!-- Profile Section -->
<section class="profile-section" data-profile-user-id="<?= (int) $user->id ?>">
    <!-- Profile Header Card -->
    <article class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 pl-11 mb-4">
        <div class="flex <?= $isMobileView ? 'items-start text-left' : 'items-start' ?> gap-6">
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
                <div class="flex <?= $isMobileView ? 'flex-col' : 'items-center' ?> gap-4 mb-1 pt-1">
                    <h1 class="<?= $isMobileView ? 'text-xl' : 'text-2xl' ?> font-extrabold text-gray-900" data-profile-username><?= h($user->full_name) ?></h1>
                    <?php if ($identity->id === $user->id && !$isMobileView): ?>
                        <button data-action="edit-profile" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors">
                            Edit Profile
                        </button>
                    <?php elseif ($identity->id !== $user->id): ?>
                        <?php if (isset($isFollowing) && $isFollowing): ?>
                            <button class="profile-header-unfollow-btn px-4 py-2 bg-gray-100 hover:bg-red-50 border border-gray-200 hover:border-red-300 text-gray-600 hover:text-red-600 rounded-lg text-sm font-medium transition-colors"
                                    data-user-id="<?= $user->id ?>"
                                    data-username="<?= h($user->username) ?>">
                                Following
                            </button>
                        <?php else: ?>
                            <button class="profile-header-follow-btn px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors"
                                    data-user-id="<?= $user->id ?>"
                                    data-username="<?= h($user->username) ?>">
                                Follow
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <!-- username -->
                <div class="text-gray-600 mb-2 pr-12">
                    <div class="text-sm" data-profile-full-name>@<?= h($user->username) ?></div>
                </div>
                

            <!-- bio  -->
                <?php if (!empty($user->bio)): ?>
                <div class="text-gray-700 mb-2 pr-12" id="bio-container">
                    <div data-profile-bio><?= h($user->bio) ?></div>
                </div>
                <?php else: ?>
                <div class="text-gray-700 mb-2 pr-12" id="bio-container" style="display: none;">
                    <div data-profile-bio></div>
                </div>
                <?php endif; ?>

            <!-- website -->
                <?php if (!empty($user->website)): ?>
                <div class="text-blue-600 mb-2" id="website-container">
                    <a href="<?= h($user->website) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 hover:underline" data-profile-website-link>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <span data-profile-website><?= h($user->website) ?></span>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-blue-600 mb-2" id="website-container" style="display: none;">
                    <a href="#" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 hover:underline" data-profile-website-link>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <span data-profile-website></span>
                    </a>
                </div>
                <?php endif; ?>


                <!-- Stats -->
                <div class="flex <?= $isMobileView ? 'justify-start' : '' ?> gap-6 mb-3">
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

        <?php if (!empty($posts)): ?>
            <div class="space-y-6">
                <?php foreach ($posts as $index => $post): ?>
                    <?php
                        $postUser = $post->user ?? $user;
                        $postUsername = $postUser->username ?? 'unknown';
                        $postFullName = $postUser->full_name ?? $postUsername;
                        $postPhoto = $postUser->profile_photo_path ?? null;
                        $postInitial = strtoupper(substr($postUsername, 0, 1));
                        
                        // Separate video and image attachments
                        $videoAttachments = [];
                        $imageAttachments = [];
                        
                        // Check post_attachments relationship first
                        if (!empty($post->post_attachments)) {
                            foreach ($post->post_attachments as $attachment) {
                                if ($attachment->file_type === 'video') {
                                    $videoAttachments[] = $attachment;
                                } else {
                                    $imageAttachments[] = $attachment;
                                }
                            }
                        }
                        
                        // Fallback to old attachments array if no PostAttachments
                        if (empty($imageAttachments) && empty($videoAttachments)) {
                            foreach (($post->attachments ?? []) as $attachment) {
                                if (is_string($attachment) && $attachment !== '') {
                                    $imageAttachments[] = $attachment;
                                    continue;
                                }
                                if (is_array($attachment) && !empty($attachment['url'])) {
                                    $imageAttachments[] = $attachment['url'];
                                }
                            }
                        }
                        
                        $reactionCounts = $post->reaction_counts ?? [];
                        arsort($reactionCounts);
                        $topReactions = array_slice(array_keys($reactionCounts), 0, 3);
                        $totalReactions = $post->total_reactions ?? 0;
                        $commentTotal = (int)($post->comments_count ?? 0);
                        $userReaction = $post->user_reaction ?? '';
                    ?>
                    <article id="post-<?= h($post->id ?? ($index + 1)) ?>" class="post bg-white rounded-2xl shadow-sm border border-gray-100 p-5" data-index="<?= $index + 1 ?>" data-post-id="<?= h($post->id ?? ($index + 1)) ?>">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden">
                                    <?php if ($postPhoto): ?>
                                        <img src="<?= h($postPhoto) ?>" alt="<?= h($postUsername) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                            <?= h($postInitial) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900"><?= h($postFullName) ?></h3>
                                    <p class="text-xs text-gray-400"><?= h(profile_time_ago($post->created_at)) ?></p>
                                </div>
                            </div>
                            <?php 
                                $isPostOwner = isset($identity->id) && isset($post->user_id) && (int)$identity->id === (int)$post->user_id;
                            ?>
                            <?php if ($isPostOwner): ?>
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

                        <div class="flex items-center justify-between text-sm text-gray-500 mb-2 px-1">
                            <?php if ($totalReactions > 0): ?>
                                <div class="flex items-center space-x-1 reaction-summary" data-total="<?= (int)$totalReactions ?>">
                                    <span class="reaction-emojis" style="display:flex;align-items:center">
                                        <?php foreach ($topReactions as $reactionKeyIndex => $reactionKey): ?>
                                            <?php if (isset($reactionEmojis[$reactionKey])): ?>
                                                <span class="reaction-emoji" style="display:inline-block;<?= $reactionKeyIndex > 0 ? 'margin-left:-4px;' : '' ?>position:relative;z-index:<?= 3 - $reactionKeyIndex ?>;text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white">
                                                    <?= $reactionEmojis[$reactionKey] ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </span>
                                    <span class="reaction-count"><?= (int)$totalReactions ?></span>
                                </div>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>
                            <div class="flex items-center space-x-4">
                                <span class="comments-count" data-count="<?= $commentTotal ?>">
                                    <?= h(__n('{0} comment', '{0} comments', $commentTotal, $commentTotal)) ?>
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-2 flex items-center justify-around">
                            <button class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" data-user-reaction="<?= h($userReaction) ?>">
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
            </div>
        <?php elseif ($postCount > 0): ?>
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
<!-- Unfollow Confirmation Modal -->
<div id="unfollow-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl transform transition-all">
        <h3 class="text-xl font-semibold text-center mb-2">Unfollow User</h3>
        <p class="text-gray-600 text-center mb-6" id="unfollow-message">Are you sure you want to unfollow this user?</p>
        <div class="flex gap-3">
            <button id="unfollow-cancel" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                Cancel
            </button>
            <button id="unfollow-confirm" class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors">
                Unfollow
            </button>
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
    const currentUserId = <?= (int)($identity->id ?? $identity['id'] ?? 0) ?>;
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

    // Ensure GIF avatars autoplay on the profile page by forcing a fresh load
    (function ensureGifAvatarPlays() {
        if (!profileRoot) return;
        try {
            const avatarNode = profileRoot.querySelector('[data-profile-avatar]');
            if (!avatarNode) return;
            if (avatarNode.tagName === 'IMG') {
                const src = avatarNode.getAttribute('src') || '';
                if (/\.gif(\?|$)/i.test(src)) {
                    // Force reload with cache-busting query so animation starts
                    const base = src.split('?')[0];
                    avatarNode.src = base + '?_=' + Date.now();
                }
            }
        } catch (err) {
            console.error('ensureGifAvatarPlays error', err);
        }
    })();

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
    
    // Handle follow button in profile header
    document.addEventListener('click', function(e) {
        const followBtn = e.target.closest('.profile-header-follow-btn');
        if (!followBtn) return;
        
        const userId = followBtn.dataset.userId;
        const username = followBtn.dataset.username;
        
        followBtn.disabled = true;
        followBtn.textContent = 'Following...';
        
        fetch('/friends/follow', buildProfileJsonRequest({ user_id: userId }))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Change button to "Following" state
                    followBtn.textContent = 'Following';
                    followBtn.className = 'profile-header-unfollow-btn px-4 py-2 bg-gray-100 hover:bg-red-50 border border-gray-200 hover:border-red-300 text-gray-600 hover:text-red-600 rounded-lg text-sm font-medium transition-colors';
                    followBtn.disabled = false;
                    showToast(`You are now following @${username}`);
                } else {
                    showToast(data.message || 'Failed to follow user');
                    followBtn.disabled = false;
                    followBtn.textContent = 'Follow';
                }
            })
            .catch(error => {
                console.error('Follow error:', error);
                showToast('An error occurred');
                followBtn.disabled = false;
                followBtn.textContent = 'Follow';
            });
    });
    
    // Handle unfollow button in profile header
    document.addEventListener('click', function(e) {
        const unfollowBtn = e.target.closest('.profile-header-unfollow-btn');
        if (!unfollowBtn) return;
        
        const userId = unfollowBtn.dataset.userId;
        const username = unfollowBtn.dataset.username;
        
        showUnfollowModal(userId, username, unfollowBtn);
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
        
        modalContent.innerHTML = data.map(user => {
            const isOwnAccount = currentUserId && Number(user.id) === currentUserId;
            let actionButton = '';
            
            if (!isOwnAccount) {
                actionButton = user.is_following 
                    ? `<button class="unfollow-user-btn px-4 py-1.5 text-xs font-semibold border border-gray-300 rounded-lg hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-colors" data-user-id="${user.id}">Unfollow</button>`
                    : `<button class="follow-user-btn px-4 py-1.5 text-xs font-semibold bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors" data-user-id="${user.id}">Follow</button>`;
            }
            
            return `
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
                    ${actionButton}
                </div>
            `;
        }).join('');
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
    
    // Unfollow confirmation modal
    const unfollowModal = document.getElementById('unfollow-modal');
    const unfollowMessage = document.getElementById('unfollow-message');
    const unfollowConfirmBtn = document.getElementById('unfollow-confirm');
    const unfollowCancelBtn = document.getElementById('unfollow-cancel');
    let pendingUnfollowUserId = null;
    let pendingUnfollowBtn = null;

    function showUnfollowModal(userId, username, btnElement) {
        pendingUnfollowUserId = userId;
        pendingUnfollowBtn = btnElement;
        unfollowMessage.textContent = username ? `Are you sure you want to unfollow @${username}?` : 'Are you sure you want to unfollow this user?';
        unfollowModal.classList.remove('hidden');
    }

    function hideUnfollowModal() {
        unfollowModal.classList.add('hidden');
        pendingUnfollowUserId = null;
        pendingUnfollowBtn = null;
    }

    unfollowCancelBtn?.addEventListener('click', hideUnfollowModal);
    
    unfollowModal?.addEventListener('click', function(e) {
        if (e.target === unfollowModal) {
            hideUnfollowModal();
        }
    });

    unfollowConfirmBtn?.addEventListener('click', function() {
        if (!pendingUnfollowUserId || !pendingUnfollowBtn) return;
        
        const userId = pendingUnfollowUserId;
        const unfollowBtn = pendingUnfollowBtn;
        const isHeaderButton = unfollowBtn.classList.contains('profile-header-unfollow-btn');
        
        hideUnfollowModal();
        
        unfollowBtn.disabled = true;
        unfollowBtn.textContent = 'Unfollowing...';
        
        fetch('/friends/unfollow', buildProfileJsonRequest({ user_id: userId }))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (isHeaderButton) {
                    // Change header button back to "Follow" state
                    unfollowBtn.textContent = 'Follow';
                    unfollowBtn.className = 'profile-header-follow-btn px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors';
                    unfollowBtn.disabled = false;
                    showToast('Unfollowed successfully');
                } else {
                    // Reload modal data for modal list buttons
                    loadData(currentTab);
                }
            } else {
                showToast(data.message || 'Failed to unfollow user');
                unfollowBtn.disabled = false;
                unfollowBtn.textContent = isHeaderButton ? 'Following' : 'Unfollow';
            }
        })
        .catch(error => {
            console.error('Unfollow error:', error);
            showToast('An error occurred while unfollowing');
            unfollowBtn.disabled = false;
            unfollowBtn.textContent = isHeaderButton ? 'Following' : 'Unfollow';
        });
    });

    document.addEventListener('click', function(e) {
        const unfollowBtn = e.target.closest('.unfollow-user-btn');
        if (!unfollowBtn) return;
        
        const userId = unfollowBtn.dataset.userId;
        // Find username from the parent container
        const userContainer = unfollowBtn.closest('[data-modal-user-id]');
        const usernameElement = userContainer?.querySelector('[data-username-display]');
        const username = usernameElement?.textContent?.replace(/^@/, '').trim() || '';
        
        showUnfollowModal(userId, username, unfollowBtn);
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
    
    function showToast(text) {
        let t = document.getElementById('profile-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'profile-toast';
            t.className = 'fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300';
            t.style.opacity = '0';
            document.body.appendChild(t);
        }
        t.textContent = text;
        t.style.opacity = '1';
        setTimeout(() => { if (t) t.style.opacity = '0'; }, 3000);
    }
})();
</script>

<?php if ($identity->id === $user->id): ?>
    <?= $this->element('Profile/edit_profile_modal', ['user' => $user]) ?>
<?php endif; ?>

<?php if ($identity->id === $user->id && $isMobileView): ?>
    <div class="px-4 mt-3 md:hidden">
        <button data-action="edit-profile" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors">
            Edit Profile
        </button>
    </div>
<?php endif; ?>
