<div class="flex flex-col h-full">
    <!-- User Profile Section -->
    <div class="text-center py-6 px-4">
        <div class="relative inline-block mb-3">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-pink-400 via-purple-400 to-blue-400 p-1">
                <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                    <div class="w-full h-full rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                        <?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?>
                    </div>
                </div>
            </div>
        </div>
        <h3 class="font-semibold text-gray-900"><?= h($currentUser->fullname ?? $currentUser->username ?? 'Your Name') ?></h3>
        <p class="text-sm text-gray-500">@<?= h($currentUser->username ?? 'username') ?></p>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4">
        <ul class="space-y-1">
            <li>
                <a href="#" class="flex items-center px-4 py-3 bg-blue-500 text-white rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24"><path d="M19 20H5V9l7-5.25L19 9v11z"/></svg>
                    <span>News Feed</span>
                </a>
            </li>
            <li>
                <a href="#" class="group relative flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Messages</span>
                    <span class="ml-auto bg-blue-100 text-blue-600 text-xs font-semibold px-2 py-0.5 rounded-full">6</span>
                    <span class="absolute right-0 -top-8 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">Coming Soon</span>
                </a>
            </li>
            <li>
                <a href="#" class="group relative flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                    <span>Forums</span>
                    <span class="absolute right-0 -top-8 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">Coming Soon</span>
                </a>
            </li>
            <li>
                <a href="#" class="group relative flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Friends</span>
                    <span class="ml-auto bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-0.5 rounded-full">3</span>
                    <span class="absolute right-0 -top-8 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">Coming Soon</span>
                </a>
            </li>
            <li>
                <a href="#" class="group relative flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Media</span>
                    <span class="absolute right-0 -top-8 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">Coming Soon</span>
                </a>
            </li>
            <li>
                <a href="#" class="group relative flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-full font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Settings</span>
                    <span class="absolute right-0 -top-8 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none">Coming Soon</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Download App Section -->
    <div class="p-4 mt-auto">
        <div class="relative">
            <div class="absolute -top-8 left-4 z-10">
                <div class="w-12 h-12 rounded-full bg-gray-200 border-2 border-white shadow-lg overflow-hidden">
                    <div class="w-full h-full bg-gradient-to-br from-pink-300 to-purple-400 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C17.5 2 22 6.5 22 12S17.5 22 12 22 2 17.5 2 12 6.5 2 12 2M12 4C7.58 4 4 7.58 4 12S7.58 20 12 20 20 16.42 20 12 16.42 4 12 4M12 6C9.79 6 8 7.79 8 10S9.79 14 12 14 16 12.21 16 10 14.21 6 12 6Z"/></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-4 pt-8">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-400 to-purple-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M7.07,18.28C7.5,17.38 10.12,16.5 12,16.5C13.88,16.5 16.5,17.38 16.93,18.28C15.57,19.36 13.86,20 12,20C10.14,20 8.43,19.36 7.07,18.28M18.36,16.83C16.93,15.09 13.46,14.5 12,14.5C10.54,14.5 7.07,15.09 5.64,16.83C4.62,15.5 4,13.82 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,13.82 19.38,15.5 18.36,16.83M12,6C10.06,6 8.5,7.56 8.5,9.5C8.5,11.44 10.06,13 12,13C13.94,13 15.5,11.44 15.5,9.5C15.5,7.56 13.94,6 12,6M12,8A1.5,1.5 0 0,1 13.5,9.5A1.5,1.5 0 0,1 12,11A1.5,1.5 0 0,1 10.5,9.5A1.5,1.5 0 0,1 12,8Z"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Download the App</span>
                </div>
            </div>
        </div>
    </div>
</div>
