<?php
/**
 * User Not Found - Dashboard View
 * This renders the user not found error within the dashboard layout
 * @var \App\View\AppView $this
 * @var string $username
 * @var object $currentUser
 * @var object $identity
 */

$this->set('title', 'User Not Found');
?>

<!-- Main Dashboard Container -->
<div class="flex min-h-screen bg-gray-50">
    <!-- Left Sidebar -->
    <aside id="left-component" class="hidden lg:block w-64 fixed top-16 left-0 bottom-0 overflow-y-auto bg-white border-r border-gray-200">
        <?= $this->element('left_sidebar', ['currentUser' => $currentUser]) ?>
    </aside>

    <!-- Middle Column -->
    <main id="middle-component" class="flex-1 lg:ml-64 xl:mr-80 mt-16 max-w-2xl mx-auto px-4 py-6">
        <?= $this->element('Profile/user_not_found', ['username' => $username]) ?>
    </main>

    <!-- Right Sidebar -->
    <aside id="right-component" class="hidden xl:block w-80 fixed top-16 right-0 bottom-0 overflow-y-auto bg-white border-l border-gray-200">
        <?= $this->element('right_sidebar', ['currentUser' => $currentUser]) ?>
    </aside>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40">
    <div class="flex justify-around items-center h-16">
        <a href="<?= $this->Url->build('/') ?>" class="flex flex-col items-center justify-center flex-1 text-blue-500">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
            <span class="text-xs mt-1">Home</span>
        </a>
        <a href="<?= $this->Url->build('/search') ?>" class="flex flex-col items-center justify-center flex-1 text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-xs mt-1">Search</span>
        </a>
        <a href="<?= $this->Url->build('/friends') ?>" class="flex flex-col items-center justify-center flex-1 text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-xs mt-1">Friends</span>
        </a>
        <a href="<?= $this->Url->build('/profile/' . ($currentUser->username ?? 'user')) ?>" class="flex flex-col items-center justify-center flex-1 text-gray-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="text-xs mt-1">Profile</span>
        </a>
    </div>
</nav>
