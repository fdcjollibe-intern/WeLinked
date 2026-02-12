<section class="space-y-4">
    <!-- Stories Section (Facebook style) -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex space-x-3 overflow-x-auto pb-2">
            <!-- Create Story -->
            <div class="flex-shrink-0 w-28 h-44 bg-gray-100 rounded-lg cursor-pointer hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="h-32 bg-gradient-to-b from-transparent to-black/20"></div>
                <div class="absolute bottom-0 left-0 right-0 p-3 text-center">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white mb-2 mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-900">Create Story</p>
                </div>
            </div>
            
            <!-- Story Items -->
            <div class="flex-shrink-0 w-28 h-44 rounded-lg cursor-pointer hover:shadow-md transition-shadow relative overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=200&h=350&fit=crop" alt="Story" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute top-3 left-3">
                    <div class="w-8 h-8 rounded-full bg-blue-500 border-2 border-white flex items-center justify-center text-white font-semibold text-sm">J</div>
                </div>
                <div class="absolute bottom-3 left-3 right-3">
                    <p class="text-white text-xs font-semibold">Tom Russo</p>
                </div>
            </div>
            
            <div class="flex-shrink-0 w-28 h-44 rounded-lg cursor-pointer hover:shadow-md transition-shadow relative overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=350&fit=crop&crop=face" alt="Story" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute top-3 left-3">
                    <div class="w-8 h-8 rounded-full bg-green-500 border-2 border-white flex items-center justify-center text-white font-semibold text-sm">B</div>
                </div>
                <div class="absolute bottom-3 left-3 right-3">
                    <p class="text-white text-xs font-semibold">Betty Chen</p>
                </div>
            </div>
            
            <div class="flex-shrink-0 w-28 h-44 rounded-lg cursor-pointer hover:shadow-md transition-shadow relative overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=350&fit=crop&crop=face" alt="Story" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute top-3 left-3">
                    <div class="w-8 h-8 rounded-full bg-purple-500 border-2 border-white flex items-center justify-center text-white font-semibold text-sm">D</div>
                </div>
                <div class="absolute bottom-3 left-3 right-3">
                    <p class="text-white text-xs font-semibold">Dennis Han</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Post Composer -->
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-start space-x-3">
            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold flex-shrink-0"><?= strtoupper(substr($currentUser->username ?? 'Y', 0, 1)) ?></div>
            <div class="flex-1">
                <textarea id="post-input" placeholder="What's on your mind, <?= h($currentUser->username ?? 'there') ?>?" class="w-full resize-none border-0 focus:ring-0 text-gray-600 bg-gray-50 rounded-lg px-4 py-3 placeholder-gray-400" rows="3"></textarea>
            </div>
        </div>
        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
            <div class="flex items-center space-x-2">
                <label class="px-3 py-2 text-sm text-gray-600 cursor-pointer flex items-center hover:bg-gray-50 rounded-lg">
                    <input id="attachment-input" type="file" multiple accept="image/*,video/*" class="hidden">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Photo/video
                </label>
                <button class="px-3 py-2 text-sm text-gray-600 flex items-center hover:bg-gray-50 rounded-lg">
                    <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    Feeling/activity
                </button>
            </div>
            <button id="post-submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600 disabled:bg-gray-300">Post</button>
        </div>
        <div id="attachment-preview" class="mt-3"></div>
    </div>

    <div id="posts-list" data-start="0" class="space-y-4">
        <!-- Static test post with reactions -->
        <article class="bg-white rounded-lg shadow post" data-post-id="1" data-index="1">
            <div class="p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center text-white font-semibold flex-shrink-0">J</div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">johndoe</h3>
                                <p class="text-xs text-gray-500">2 hours ago • <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4Z"/></svg></p>
                            </div>
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><path d="M16,12A2,2 0 0,1 18,10A2,2 0 0,1 20,12A2,2 0 0,1 18,14A2,2 0 0,1 16,12M10,12A2,2 0 0,1 12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12M4,12A2,2 0 0,1 6,10A2,2 0 0,1 8,12A2,2 0 0,1 6,14A2,2 0 0,1 4,12Z"/></svg>
                            </button>
                        </div>
                        <p class="text-gray-900 text-sm leading-relaxed mb-3">
                            Just launched our new feature! So excited to share this with the community. What do you all think? 🚀
                        </p>
                    </div>
                </div>
                <div class="rounded-lg overflow-hidden mt-3">
                    <img src="https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=600&h=400&fit=crop" alt="Post image" class="w-full h-auto object-cover">
                </div>
            </div>
            
            <!-- Reactions and Comments Section -->
            <div class="px-4 pb-4">
                <div class="flex items-center justify-between py-2 text-sm text-gray-500 border-b border-gray-100">
                    <div class="flex items-center space-x-1">
                        <div class="flex -space-x-1">
                            <div class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center border border-white">
                                <span class="text-xs text-white">👍</span>
                            </div>
                            <div class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center border border-white">
                                <span class="text-xs text-white">❤️</span>
                            </div>
                        </div>
                        <span class="ml-2">12</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span>3 comments</span>
                        <span>1 share</span>
                    </div>
                </div>
                
                <div class="flex items-center justify-center space-x-1 pt-2">
                    <button class="reaction-btn flex-1 px-3 py-2 text-sm flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V18m-7-8a2 2 0 01-2-2V4a2 2 0 012-2h2.343M11 7L9 5l2-2m0 4l2 2-2 2m0-4h6"/></svg>
                        Like
                    </button>
                    <button class="comment-btn flex-1 px-3 py-2 text-sm flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Comment
                    </button>
                    <button class="flex-1 px-3 py-2 text-sm flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        Share
                    </button>
                </div>
            </div>
        </article>
    </div>
</section>
