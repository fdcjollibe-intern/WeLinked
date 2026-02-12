<?php
/**
 * Dashboard merging three components: left, middle, right
 */
?>
<?= $this->Html->css('dashboard') ?>

<!-- Facebook-style full-width navbar -->
<nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-50 h-16">
    <div class="flex items-center justify-between px-4 h-full">
        <!-- Left: Logo -->
        <div class="flex items-center w-80">
            <a href="<?= $this->Url->build('/') ?>" class="flex items-center space-x-2">
                <picture>
                    <source srcset="/assets/logo.avif" type="image/avif">
                    <img src="/assets/logo.png" alt="eLinked logo" class="w-10 h-10" />
                </picture>
            </a>
            <!-- Search on larger screens -->
            <div class="ml-4 flex-1 max-w-xs hidden lg:block">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" placeholder="Search WeLinked" class="bg-gray-100 border-0 rounded-full pl-10 pr-4 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>
        </div>

        <!-- Center: Navigation tabs (Facebook style) -->
        <div class="hidden md:flex items-center space-x-2">
            <a href="#" class="group relative flex items-center justify-center w-28 h-12 border-b-2 border-blue-500 text-blue-500">
                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50">For You Page</span>
            </a>
            <a href="#" class="group relative flex items-center justify-center w-28 h-12 text-gray-500 hover:bg-gray-100 rounded-lg">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50">Friends</span>
            </a>
            <a href="#" class="group relative flex items-center justify-center w-28 h-12 text-gray-500 hover:bg-gray-100 rounded-lg">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50">Reels</span>
            </a>
        </div>

        <!-- Right: User actions -->
        <div class="flex items-center space-x-3 w-80 justify-end">
            <button class="p-2.5 bg-gray-100 rounded-full hover:bg-gray-200">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </button>
            <button class="p-2.5 bg-gray-100 rounded-full hover:bg-gray-200">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </button>
            <button class="p-2.5 bg-gray-100 rounded-full hover:bg-gray-200">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </button>
            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center cursor-pointer">
                <span class="text-white font-semibold text-base"><?= strtoupper(substr($currentUser->username ?? 'U', 0, 1)) ?></span>
            </div>
        </div>
    </div>
</nav>

<!-- Main layout with Facebook-style structure -->
<div class="bg-gray-100 min-h-screen pt-16">
    <!-- Left Sidebar (Facebook style) -->
    <aside id="left-component" class="hidden lg:block fixed left-0 top-16 w-96 h-full overflow-y-auto">
        <?= $this->element('left_sidebar') ?>
    </aside>

    <!-- Right Sidebar (Facebook style) -->
    <aside id="right-component" class="hidden xl:block fixed right-0 top-16 w-96 h-full overflow-y-auto">
        <?= $this->element('right_sidebar') ?>
    </aside>

    <!-- Center Content Area -->
    <main id="middle-component" class="lg:ml-96 xl:mr-96">
        <div class="max-w-3xl mx-auto px-4 py-6">
            <?= $this->element('middle_column') ?>
        </div>
    </main>
</div>

<?= $this->Html->script('dashboard') ?>
<?= $this->Html->script('middle') ?>
<?= $this->Html->script('reactions') ?>
<?= $this->Html->script('gallery') ?>
