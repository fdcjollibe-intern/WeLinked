<?php
/**
 * Settings Dashboard View
 * Renders the full three-column layout with the settings panel in the middle column.
 */
?>
<?= $this->Html->css('dashboard') ?>
<script>
    (function(){
        const token = '<?= $this->request->getAttribute('csrfToken') ?>';
        if (token && !window.csrfToken) {
            window.csrfToken = token;
        }
        if (window.csrfToken && !window.CSRF_TOKEN) {
            window.CSRF_TOKEN = window.csrfToken;
        }
    })();
</script>

<?php $navHasPhoto = !empty($currentUser->profile_photo_path); ?>
<?php if (empty($isMobileView)): ?>
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
                <input type="search" id="global-search-input" placeholder="Search users & posts..." class="bg-transparent border-0 focus:ring-0 focus:outline-none focus:border-transparent text-sm w-64 placeholder-gray-400" autocomplete="off" />
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
            <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </button>
            <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
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
                            class="w-full h-full rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm <?= $navHasPhoto ? 'hidden' : '' ?>"
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
            <button aria-label="Create" class="p-2 rounded-md text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <button aria-label="Search" class="p-2 rounded-md text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <button aria-label="Messages" class="p-2 rounded-md text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8-1.45 0-2.83-.27-4.065-.76L3 21l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </button>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php $mobilePad = (!empty($isMobileView) ? 'pt-14 pb-20' : 'pt-20'); ?>
<div class="bg-gray-50 min-h-screen <?= $mobilePad ?>">
    <div class="max-w-screen-2xl mx-auto flex">
        <aside id="left-component" class="hidden lg:block w-72 flex-shrink-0 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto px-4">
            <?= $this->element('left_sidebar') ?>
        </aside>

        <main id="middle-component" class="flex-1 min-w-0 px-4 lg:px-8">
            <?= $this->element('Settings/settings_panel', [
                'user' => $user,
                'activeSection' => $activeSection,
                'isMobileView' => $isMobileView,
            ]) ?>
        </main>

        <aside id="right-component" class="hidden xl:block w-80 flex-shrink-0 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto px-4">
            <?= $this->element('right_sidebar') ?>
        </aside>
    </div>
</div>

<?= $this->Html->script('dashboard') ?>
<?= $this->Html->script('middle') ?>
<?= $this->Html->script('reactions') ?>
<?= $this->Html->script('gallery') ?>

<?php if (!empty($isMobileView)): ?>
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
            <a href="/settings" class="flex flex-col items-center justify-center text-gray-900">
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
                            class="w-full h-full rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-semibold <?= $navHasPhoto ? 'hidden' : '' ?>"
                        >
                            <span data-user-initial><?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?></span>
                        </div>
                    </div>
                </div>
                <span class="text-xs leading-tight">Settings</span>
            </a>
        </div>
    </div>
</nav>
<?php endif; ?>

<script src="/js/dashboard.js"></script>
<script src="/js/mentions.js"></script>
<script src="/js/middle.js"></script>
<script src="/js/reactions.js"></script>
<script src="/js/gallery.js"></script>
<script src="/js/composer-modal.js"></script>
<script src="/js/post-composer.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/search.js"></script>
