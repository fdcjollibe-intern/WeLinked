<?php
/**
 * User Not Found Element - Profile section
 * @var \App\View\AppView $this
 * @var string $username
 */
?>
<section class="profile-section flex items-center justify-center py-16">
    <div class="text-center max-w-md mx-auto px-4">
        <!-- 404 Icon -->
        <div class="mb-6">
            <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        
        <!-- Error Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-3">User Not Found</h1>
        <p class="text-gray-600 mb-2">
            The user <span class="font-semibold text-gray-900">@<?= h($username ?? 'unknown') ?></span> doesn't exist.
        </p>
        <p class="text-gray-500 text-sm mb-8">
            This account may have been deleted, or the username might be incorrect.
        </p>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/dashboard" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors">
                Go to Dashboard
            </a>
            <a href="/search" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                Search Users
            </a>
        </div>
        
        <!-- Additional Help -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500">
                Looking for someone? Try using the search feature to find users by name or username.
            </p>
        </div>
    </div>
</section>
