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
                    <textarea id="post-composer-textarea" placeholder="What's on your mind? Use @ to mention friends..." rows="3" class="w-full mt-2 bg-transparent border-0 focus:ring-0 focus:outline-none text-gray-600 placeholder-gray-400 resize-none overflow-y-hidden" style="min-height:72px;max-height:320px;line-height:1.4;transition:height 140ms ease"></textarea>
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
                </div>
                <button id="post-submit-btn" class="bg-blue-500 text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-blue-600 transition-colors">Post</button>
            </div>
            <div id="attachment-preview" class="mt-3"></div>
            <div id="composer-drop-overlay" class="hidden absolute inset-0 bg-white bg-opacity-80 rounded-2xl flex items-center justify-center text-gray-600 text-lg font-medium border-2 border-dashed border-gray-300" style="pointer-events:none">Drop files here to attach</div>
        </div>


        
        <!-- Posts will be loaded by dashboard.js automatically -->
    </div>
</section>
