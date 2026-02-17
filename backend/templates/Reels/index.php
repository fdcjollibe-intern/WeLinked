<!-- Reels Template - Full screen vertical video feed -->
<div id="reels-container" class="fixed inset-0 bg-black z-50">
    <!-- Close button -->
    <button id="close-reels" class="fixed top-4 left-4 z-50 w-10 h-10 bg-gray-800 bg-opacity-75 hover:bg-opacity-100 rounded-full flex items-center justify-center text-white transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    
    <!-- Reels feed container -->
    <div id="reels-feed" class="h-full overflow-y-scroll snap-y snap-mandatory">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $index => $post): ?>
                <div class="reel-item snap-start h-screen w-full relative flex items-center justify-center" 
                     data-reel-id="<?= h($post->id) ?>" 
                     data-video-url="<?= h($post->video_url) ?>">
                    
                    <!-- Video element -->
                    <video 
                        class="reel-video absolute inset-0 w-full h-full object-contain bg-black"
                        src="<?= h($post->video_url) ?>"
                        loop
                        playsinline
                        muted
                        <?= $index === 0 ? 'autoplay' : '' ?>
                    ></video>
                    
                    <!-- Overlay controls on right side -->
                    <div class="absolute right-4 bottom-20 flex flex-col gap-4 z-10">
                        <!-- Author info -->
                        <a href="/profile/<?= h($post->user->username) ?>" class="flex flex-col items-center gap-1">
                            <?php if (!empty($post->user->profile_photo_path)): ?>
                                <img src="<?= h($post->user->profile_photo_path) ?>" 
                                     alt="<?= h($post->user->full_name) ?>" 
                                     class="w-12 h-12 rounded-full border-2 border-white">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold border-2 border-white">
                                    <?= strtoupper(substr($post->user->full_name, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <span class="text-white text-xs font-semibold"><?= h($post->user->username) ?></span>
                        </a>
                        
                        <!-- Like button -->
                        <button class="reel-reaction-btn w-14 h-14 rounded-full bg-gray-800 bg-opacity-75 hover:bg-opacity-100 flex flex-col items-center justify-center text-white transition-all hover:scale-110"
                                data-post-id="<?= h($post->id) ?>"
                                data-user-reaction="<?= h($post->user_reaction ?? '') ?>">
                            <svg class="w-7 h-7 <?= !empty($post->user_reaction) ? 'fill-red-500' : 'fill-none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span class="text-xs mt-1"><?= h($post->total_reactions ?? 0) ?></span>
                        </button>
                        
                        <!-- Comment button -->
                        <button class="reel-comment-btn w-14 h-14 rounded-full bg-gray-800 bg-opacity-75 hover:bg-opacity-100 flex flex-col items-center justify-center text-white transition-all hover:scale-110"
                                data-post-id="<?= h($post->id) ?>">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span class="text-xs mt-1"><?= h($post->comment_count ?? 0) ?></span>
                        </button>
                        
                        <!-- Share button -->
                        <button class="reel-share-btn w-14 h-14 rounded-full bg-gray-800 bg-opacity-75 hover:bg-opacity-100 flex items-center justify-center text-white transition-all hover:scale-110">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Caption overlay at bottom -->
                    <?php if (!empty($post->content_text)): ?>
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                            <p class="text-white text-sm line-clamp-3"><?= h($post->content_text) ?></p>
                            <?php if (!empty($post->location)): ?>
                                <p class="text-gray-300 text-xs mt-1">
                                    <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <?= h($post->location) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="h-screen flex items-center justify-center text-white">
                <div class="text-center">
                    <svg class="w-20 h-20 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-400 text-lg">No reels available yet</p>
                    <p class="text-gray-500 text-sm mt-2">Check back later for video content</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Loading indicator for infinite scroll -->
    <div id="reels-loading" class="hidden fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-gray-800 bg-opacity-75 text-white px-4 py-2 rounded-full">
        <span class="text-sm">Loading more reels...</span>
    </div>
</div>

<!-- Include reels JavaScript -->
<script src="/js/reels.js"></script>
