<section class="flex flex-col h-full py-4">
    <!-- Header with Tabs -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Feeds</h1>
        <div class="flex items-center space-x-4 text-sm" id="feed-tabs">
            <a href="#" class="feed-tab text-gray-400 hover:text-gray-600" data-feed="foryou">For You</a>
            <a href="#" class="feed-tab text-blue-500 font-medium border-b-2 border-blue-500 pb-1" data-feed="friends">Friends</a>
            <a href="#" class="feed-tab text-gray-400 hover:text-gray-600 opacity-50 cursor-not-allowed" data-feed="reels">Reels</a>
        </div>
    </div>

    <!-- Posts Feed -->
    <div id="posts-list" data-start="0" data-feed="friends" class="space-y-4 pb-8">
        <!-- Composer Card (moved to top) -->
        <div id="post-composer" class="composer bg-white rounded-2xl shadow-sm border border-gray-200 p-4 relative">
            <div class="flex items-start space-x-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm">
                    <?= strtoupper(substr($currentUser->username ?? 'Y', 0, 1)) ?>
                </div>
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

        <!-- Post 1 -->
        <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 post" data-post-id="1" data-index="1">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-pink-400 flex items-center justify-center text-white font-bold">G</div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">George Lobko</h3>
                        <p class="text-xs text-gray-400">2 hours ago</p>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"/></svg>
                </button>
            </div>
            
            <p class="text-gray-700 mb-4">
                Hi everyone, today I was on the most beautiful mountain in the world 😍, I also want to say hi to 
                <span class="text-blue-500">@Silena</span>, 
                <span class="text-orange-500">@Olya</span> and 
                <span class="text-yellow-500">@Davis</span>!
            </p>
            
            <!-- Photo Collage (3 photos) -->
            <div class="photo-collage three-photos grid grid-cols-2 gap-2 rounded-xl overflow-hidden cursor-pointer mb-4" data-images='["https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=600&h=400&fit=crop","https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=600&h=400&fit=crop","https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=600&h=400&fit=crop"]'>
                <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=600&h=400&fit=crop" alt="Photo 1" class="w-full h-full object-cover row-span-2" data-index="0">
                <div class="grid grid-rows-2 gap-2">
                    <img src="https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=600&h=400&fit=crop" alt="Photo 2" class="w-full h-full object-cover" data-index="1">
                    <img src="https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=600&h=400&fit=crop" alt="Photo 3" class="w-full h-full object-cover" data-index="2">
                </div>
            </div>
            
            <!-- Reactions Summary & Counts -->
            <div class="flex items-center justify-between text-sm text-gray-500 mb-2 px-1">
                <div class="flex items-center space-x-1 reaction-summary" data-total="120">
                    <span class="reaction-emojis" style="display:flex;align-items:center"><span class="reaction-emoji" style="display:inline-block;position:relative;z-index:1;text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white">❤️</span></span>
                    <span class="reaction-count">120</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="comments-count">23 comments</span>
                    <span class="shares-count">12 shares</span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="border-t border-gray-100 pt-2 flex items-center justify-around">
                <button class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" data-user-reaction="">
                    <svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span class="reaction-label text-sm font-medium text-gray-700">Like</span>
                </button>
                <button class="comment-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span class="text-sm font-medium text-gray-700">Comment</span>
                </button>
                <button class="share-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span class="text-sm font-medium text-gray-700">Share</span>
                </button>
            </div>
        </article>

        <!-- Post 2: 7 Photos Travel Album -->
        <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 post" data-post-id="2" data-index="2">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-purple-500 flex items-center justify-center text-white font-bold">S</div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Sarah Mitchell</h3>
                        <p class="text-xs text-gray-400">1 hour ago</p>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"/></svg>
                </button>
            </div>
            
            <p class="text-gray-700 mb-4">
                Just got back from an incredible week exploring Iceland! 🇮🇸✨ From stunning waterfalls to black sand beaches, northern lights dancing in the sky, and the most breathtaking glaciers... This trip was absolutely magical! Already planning my next adventure 🌍💙 #TravelDiaries #Iceland #Wanderlust
            </p>
            
            <!-- Photo Collage (7 photos - shows 3 with +4 overlay) -->
            <div class="photo-collage four-plus-photos grid grid-cols-2 gap-2 rounded-xl overflow-hidden cursor-pointer mb-4" data-images='["https://images.unsplash.com/photo-1483347756197-71ef80e95f73?w=600&h=400&fit=crop","https://images.unsplash.com/photo-1504198453319-5ce911bafcde?w=600&h=400&fit=crop","https://images.unsplash.com/photo-1519681393784-d120267933ba?w=600&h=400&fit=crop","https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&h=400&fit=crop","https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=600&h=400&fit=crop","https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=600&h=400&fit=crop","https://images.unsplash.com/photo-1482938289607-e9573fc25ebb?w=600&h=400&fit=crop"]'>
                <img src="https://images.unsplash.com/photo-1483347756197-71ef80e95f73?w=600&h=400&fit=crop" alt="Photo 1" class="w-full h-full object-cover row-span-2" data-index="0">
                <div class="grid grid-rows-2 gap-2">
                    <img src="https://images.unsplash.com/photo-1504198453319-5ce911bafcde?w=600&h=400&fit=crop" alt="Photo 2" class="w-full h-full object-cover" data-index="1">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=600&h=400&fit=crop" alt="Photo 3" class="w-full h-full object-cover" data-index="2">
                        <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                            <span class="text-white text-4xl font-bold">+4</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reactions Summary & Counts -->
            <div class="flex items-center justify-between text-sm text-gray-500 mb-2 px-1">
                <div class="flex items-center space-x-1 reaction-summary" data-total="342">
                    <span class="reaction-emojis" style="display:flex;align-items:center"><span class="reaction-emoji" style="display:inline-block;position:relative;z-index:3;text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white">❤️</span><span class="reaction-emoji" style="display:inline-block;margin-left:-4px;position:relative;z-index:2;text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white">😆</span><span class="reaction-emoji" style="display:inline-block;margin-left:-4px;position:relative;z-index:1;text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white">😮</span></span>
                    <span class="reaction-count">342</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="comments-count">67 comments</span>
                    <span class="shares-count">34 shares</span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="border-t border-gray-100 pt-2 flex items-center justify-around">
                <button class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" data-user-reaction="">
                    <svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span class="reaction-label text-sm font-medium text-gray-700">Like</span>
                </button>
                <button class="comment-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span class="text-sm font-medium text-gray-700">Comment</span>
                </button>
                <button class="share-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span class="text-sm font-medium text-gray-700">Share</span>
                </button>
            </div>
        </article>

        <!-- Post 3 -->
        <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 post" data-post-id="3" data-index="3">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-blue-500 flex items-center justify-center text-white font-bold">V</div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Vitaliy Boyko</h3>
                        <p class="text-xs text-gray-400">3 hours ago</p>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"/></svg>
                </button>
            </div>
            
            <p class="text-gray-700 mb-4">
                I chose a wonderful coffee today, I wanted to tell you what product they have in stock - it's a latte with coconut 🥥 milk... delicious... it's really incredibly tasty!!! 😋
            </p>
            
            <!-- Reactions Summary & Counts -->
            <div class="flex items-center justify-between text-sm text-gray-500 mb-2 px-1">
                <div class="flex items-center space-x-1 reaction-summary" data-total="89">
                    <span class="reaction-emojis" style="display:flex;align-items:center"><span class="reaction-emoji" style="display:inline-block;position:relative;z-index:1;text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white">❤️</span></span>
                    <span class="reaction-count">89</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="comments-count">15 comments</span>
                    <span class="shares-count">8 shares</span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="border-t border-gray-100 pt-2 flex items-center justify-around">
                <button class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" data-user-reaction="">
                    <svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span class="reaction-label text-sm font-medium text-gray-700">Like</span>
                </button>
                <button class="comment-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span class="text-sm font-medium text-gray-700">Comment</span>
                </button>
                <button class="share-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span class="text-sm font-medium text-gray-700">Share</span>
                </button>
            </div>
        </article>

        
    </div>
</section>
