
<!-- Settings Section -->
<section class="settings-section">
    <!-- Settings Navigation (Mobile) -->
    <?php if ($isMobileView): ?>
    <article class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-4">
        <h2 class="text-lg font-semibold mb-3">Settings</h2>
        <nav class="space-y-1">
            <a href="/settings?section=account" 
               class="flex items-center px-3 py-2 rounded-lg text-sm <?= $activeSection === 'account' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Account Information
            </a>
            <a href="/settings?section=security" 
               class="flex items-center px-3 py-2 rounded-lg text-sm <?= $activeSection === 'security' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Security & Privacy
            </a>
            <a href="/settings?section=theme" 
               class="flex items-center px-3 py-2 rounded-lg text-sm <?= $activeSection === 'theme' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                Theme
            </a>
        </nav>
    </article>
    <?php endif; ?>
    
    <article class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <?php if (!$isMobileView): ?>
        <!-- Desktop: Sidebar Navigation -->
        <div class="flex gap-6">
            <nav class="w-56 flex-shrink-0">
                <h2 class="text-lg font-semibold mb-4">Settings</h2>
                <div class="space-y-1">
                    <a href="/settings?section=account" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm <?= $activeSection === 'account' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Account Information
                    </a>
                    <a href="/settings?section=security" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm <?= $activeSection === 'security' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Security & Privacy
                    </a>
                    <a href="/settings?section=theme" 
                       class="flex items-center px-3 py-2 rounded-lg text-sm <?= $activeSection === 'theme' ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        Theme
                    </a>
                </div>
            </nav>
            
            <div class="flex-1 border-l pl-6">
        <?php endif; ?>
        
            <!-- Account Information Section -->
            <?php if ($activeSection === 'account'): ?>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold">Account Information</h3>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Secure
                    </div>
                </div>
                
                <?= $this->Form->create(null, [
                    'id' => 'account-form',
                    'type' => 'post',
                    'url' => ['controller' => 'Settings', 'action' => 'updateAccount'],
                    'class' => 'space-y-4'
                ]) ?>
                    <div>
                        <?= $this->Form->control('username', [
                            'label' => ['text' => 'Username', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                            'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                            'value' => h($user->username),
                            'required' => true
                        ]) ?>
                        <p class="text-xs text-gray-500 mt-1">Your unique username for the platform</p>
                    </div>
                    
                    <div>
                        <?= $this->Form->control('full_name', [
                            'label' => ['text' => 'Full Name', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                            'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                            'value' => h($user->full_name),
                            'required' => true
                        ]) ?>
                        <p class="text-xs text-gray-500 mt-1">Your display name that others will see</p>
                    </div>
                    
                    <div>
                        <?= $this->Form->control('email', [
                            'type' => 'email',
                            'label' => ['text' => 'Email Address', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                            'class' => 'w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 text-sm cursor-not-allowed',
                            'value' => h($user->email),
                            'disabled' => true
                        ]) ?>
                        <p class="text-xs text-gray-500 mt-1">Email address cannot be changed for security reasons</p>
                    </div>
                    
                    <div>
                        <?= $this->Form->control('gender', [
                            'type' => 'select',
                            'label' => ['text' => 'Gender', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                            'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                            'options' => [
                                'Prefer not to say' => 'Prefer not to say',
                                'Male' => 'Male',
                                'Female' => 'Female'
                            ],
                            'value' => h($user->gender ?? 'Prefer not to say'),
                            'empty' => false
                        ]) ?>
                        <p class="text-xs text-gray-500 mt-1">Your gender (optional)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                        <div class="flex items-center gap-4">
                            <?php if (!empty($user->profile_photo_path)): ?>
                            <img id="profile-photo-preview" 
                                 src="<?= h($user->profile_photo_path) ?>" 
                                 alt="Profile Photo" 
                                 class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                            <?php else: ?>
                            <div id="profile-photo-preview" class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-2 border-gray-200">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <input type="file" 
                                       id="profile-photo-input" 
                                       accept="image/*" 
                                       class="hidden">
                                <button type="button" 
                                        id="upload-photo-btn"
                                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm">
                                    Choose Photo
                                </button>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF or WEBP (Max 10MB)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-2">
                        <?= $this->Form->button('Save Changes', [
                            'type' => 'submit',
                            'class' => 'px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors text-sm shadow-sm hover:shadow-md'
                        ]) ?>
                    </div>
                <?= $this->Form->end() ?>
            </div>
            <?php endif; ?>
            
            <!-- Security & Privacy Section -->
            <?php if ($activeSection === 'security'): ?>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold">Security & Privacy</h3>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Enhanced Security
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h4 class="font-medium text-gray-900 mb-3">Change Password</h4>
                    <?= $this->Form->create(null, [
                        'id' => 'password-form',
                        'type' => 'post',
                        'url' => ['controller' => 'Settings', 'action' => 'updatePassword'],
                        'class' => 'space-y-3'
                    ]) ?>
                        <div>
                            <?= $this->Form->control('current_password', [
                                'type' => 'password',
                                'label' => ['text' => 'Current Password', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                                'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                                'required' => true
                            ]) ?>
                        </div>
                        
                        <div>
                            <?= $this->Form->control('new_password', [
                                'type' => 'password',
                                'label' => ['text' => 'New Password', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                                'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                                'required' => true
                            ]) ?>
                        </div>
                        
                        <div>
                            <?= $this->Form->control('confirm_password', [
                                'type' => 'password',
                                'label' => ['text' => 'Confirm New Password', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                                'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                                'required' => true
                            ]) ?>
                        </div>
                        
                        <div class="flex justify-end pt-2">
                            <?= $this->Form->button('Update Password', [
                                'type' => 'submit',
                                'class' => 'px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors text-sm shadow-sm hover:shadow-md'
                            ]) ?>
                        </div>
                    <?= $this->Form->end() ?>
                </div>
                
                <!-- Two-Factor Authentication -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Two-Factor Authentication</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Enable 2FA</p>
                                <p class="text-xs text-gray-600 mt-1">Add an extra layer of security to your account with two-factor authentication</p>
                            </div>
                            <button id="toggle-2fa" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                                Enable 2FA
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Note: This is a basic implementation. In production, you would need proper OTP verification with authenticator apps.</p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Theme Section -->
            <?php if ($activeSection === 'theme'): ?>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold">Theme</h3>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        Personalization
                    </div>
                </div>
                
                <?= $this->Form->create(null, [
                    'id' => 'theme-form',
                    'type' => 'post',
                    'url' => ['controller' => 'Settings', 'action' => 'updateTheme'],
                    'class' => 'space-y-3'
                ]) ?>
                    <div class="grid gap-3">
                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors hover:border-blue-300">
                            <input type="radio" name="theme" value="system" <?= $user->theme_preference === 'system' ? 'checked' : '' ?> class="w-4 h-4 text-blue-600">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">System Default</div>
                                <div class="text-xs text-gray-600">Match your device's system settings</div>
                            </div>
                            <div class="ml-auto text-xs text-gray-500">Auto</div>
                        </label>
                        
                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors hover:border-blue-300">
                            <input type="radio" name="theme" value="light" <?= $user->theme_preference === 'light' ? 'checked' : '' ?> class="w-4 h-4 text-blue-600">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">Light</div>
                                <div class="text-xs text-gray-600">Clean and bright interface</div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-6 h-4 bg-gray-200 rounded-full flex items-center px-1">
                                    <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                                </div>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors hover:border-blue-300">
                            <input type="radio" name="theme" value="dark" <?= $user->theme_preference === 'dark' ? 'checked' : '' ?> class="w-4 h-4 text-blue-600">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">Dark</div>
                                <div class="text-xs text-gray-600">Easy on the eyes, saves battery</div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-6 h-4 bg-gray-800 rounded-full flex items-center px-1">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <div class="flex justify-end pt-2">
                        <?= $this->Form->button('Save Theme', [
                            'type' => 'submit',
                            'class' => 'px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors text-sm shadow-sm hover:shadow-md'
                        ]) ?>
                    </div>
                <?= $this->Form->end() ?>
            </div>
            <?php endif; ?>
        
        <?php if (!$isMobileView): ?>
            </div>
        </div>
        <?php endif; ?>
    </article>
</section>

<!-- Cropper Modal -->
<div id="photo-cropper-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Adjust Profile Photo</h3>
            <button type="button" id="cropper-close" class="text-gray-400 hover:text-gray-600 transition-colors" aria-label="Close">
                ✕
            </button>
        </div>
        <div class="px-6 py-4 space-y-4">
            <div class="bg-gray-50 rounded-xl border border-dashed border-gray-200 p-4 min-h-[320px] flex items-center justify-center overflow-hidden">
                <img id="cropper-source" src="" alt="Crop preview" class="max-h-[70vh]">
            </div>
            <div class="flex flex-wrap gap-2" id="cropper-controls">
                <button type="button" data-crop-action="zoom-in" class="px-2 py-2 rounded-lg border border-gray-200 hover:bg-gray-100 group relative" title="Zoom In">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Zoom In</span>
                </button>
                <button type="button" data-crop-action="zoom-out" class="px-2 py-2 rounded-lg border border-gray-200 hover:bg-gray-100 group relative" title="Zoom Out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM7 10h6"/>
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Zoom Out</span>
                </button>
                <button type="button" data-crop-action="rotate-left" class="px-2 py-2 rounded-lg border border-gray-200 hover:bg-gray-100 group relative" title="Rotate Left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0H7.14M2.985 14.652a8.001 8.001 0 0115.682-4.482m0 0H9.88"/>
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Rotate Left</span>
                </button>
                <button type="button" data-crop-action="rotate-right" class="px-2 py-2 rounded-lg border border-gray-200 hover:bg-gray-100 group relative" title="Rotate Right">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.977 14.652a8.001 8.001 0 015.682 4.482m0 0H9.88m12.02-4.482v4.992m0 0H16.06a8.001 8.001 0 01-5.682-4.482m0 0H14.12"/>
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Rotate Right</span>
                </button>
                <button type="button" data-crop-action="flip-horizontal" class="px-2 py-2 rounded-lg border border-gray-200 hover:bg-gray-100 group relative" title="Flip Horizontal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0H3m4 0l8-3m0 0h4m-4 0l-8 3m0 0v12M19 3v12"/>
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Flip Horizontal</span>
                </button>
                <button type="button" data-crop-action="flip-vertical" class="px-2 py-2 rounded-lg border border-gray-200 hover:bg-gray-100 group relative" title="Flip Vertical">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16v4H4V7zm0 6h16v4H4v-4z"/>
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Flip Vertical</span>
                </button>
                <button type="button" data-crop-action="reset" class="px-2 py-2 rounded-lg border border-gray-200 hover:bg-gray-100 group relative" title="Reset">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Reset</span>
                </button>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" id="cropper-cancel" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
            <button type="button" id="cropper-apply" class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700">Save &amp; Upload</button>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.__settingsScriptInitialized) {
        window.initSettingsHandlers?.(document);
        return;
    }
    window.__settingsScriptInitialized = true;

    const SETTINGS_USER_ID = <?= (int)$user->id ?>;
    const INLINE_CSRF_TOKEN = '<?= $this->request->getAttribute('csrfToken') ?>';
    if (INLINE_CSRF_TOKEN) {
        if (!window.csrfToken) {
            window.csrfToken = INLINE_CSRF_TOKEN;
        }
        if (!window.CSRF_TOKEN) {
            window.CSRF_TOKEN = INLINE_CSRF_TOKEN;
        }
    }

    function getCsrfToken() {
        return (
            window.CSRF_TOKEN ||
            window.csrfToken ||
            document.querySelector('meta[name="csrfToken"]')?.content ||
            document.querySelector('meta[name="csrf-token"]')?.content ||
            ''
        );
    }

    function buildJsonRequestOptions(payload = {}) {
        const headers = { 'Content-Type': 'application/json' };
        const token = getCsrfToken();
        if (token) {
            headers['X-CSRF-Token'] = token;
        }
        return {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        };
    }

    function buildFormDataRequestOptions(formData) {
        const headers = {};
        const token = getCsrfToken();
        if (token) {
            headers['X-CSRF-Token'] = token;
        }
        return {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: formData,
        };
    }

    const CROPPER_CSS = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css';
    const CROPPER_JS = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js';
    let cropperAssetsPromise = null;
    let cropperInstance = null;
    let currentScaleX = 1;
    let currentScaleY = 1;
    let pendingFileName = '';

    const cropperState = {
        modal: null,
        image: null,
        apply: null,
        cancel: null,
        close: null,
        controls: null,
    };

    function loadAsset(tagName, attributes) {
        return new Promise((resolve, reject) => {
            const node = document.createElement(tagName);
            Object.assign(node, attributes);
            node.onload = () => resolve();
            node.onerror = reject;
            document.head.appendChild(node);
        });
    }

    function ensureCropperAssets() {
        if (window.Cropper) {
            return Promise.resolve();
        }
        if (!cropperAssetsPromise) {
            cropperAssetsPromise = Promise.all([
                document.querySelector(`link[href="${CROPPER_CSS}"]`) ? Promise.resolve() : loadAsset('link', { rel: 'stylesheet', href: CROPPER_CSS }),
                loadAsset('script', { src: CROPPER_JS })
            ]);
        }
        return cropperAssetsPromise;
    }

    function getCropperElements(root) {
        cropperState.modal = document.getElementById('photo-cropper-modal') || root.querySelector('#photo-cropper-modal');
        if (!cropperState.modal) {
            return null;
        }
        cropperState.image = document.getElementById('cropper-source') || root.querySelector('#cropper-source');
        cropperState.apply = document.getElementById('cropper-apply') || root.querySelector('#cropper-apply');
        cropperState.cancel = document.getElementById('cropper-cancel') || root.querySelector('#cropper-cancel');
        cropperState.close = document.getElementById('cropper-close') || root.querySelector('#cropper-close');
        cropperState.controls = document.getElementById('cropper-controls') || root.querySelector('#cropper-controls');
        return cropperState;
    }

    function showToast(message, variant = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-6 right-6 z-50 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium flex items-center gap-3 ${variant === 'success' ? 'bg-white border-green-200 text-gray-900' : 'bg-white border-red-200 text-gray-900'}`;
        toast.innerHTML = `<span class="w-2 h-2 rounded-full ${variant === 'success' ? 'bg-green-500' : 'bg-red-500'}"></span><span>${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3200);
    }

    function dispatchUserUpdate(detail) {
        const enriched = { ...detail };
        if (!enriched.userId && SETTINGS_USER_ID) {
            enriched.userId = SETTINGS_USER_ID;
        }
        window.dispatchEvent(new CustomEvent('user:profile-updated', { detail: enriched }));
    }

    function toggleButtonLoading(button, isLoading, label = 'Saving...') {
        if (!button) return;
        if (!button.dataset.originalText) {
            button.dataset.originalText = button.textContent.trim();
        }
        button.disabled = isLoading;
        button.classList.toggle('opacity-60', isLoading);
        button.textContent = isLoading ? label : button.dataset.originalText;
    }

    function destroyCropper() {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        currentScaleX = 1;
        currentScaleY = 1;
    }

    function hideCropperModal() {
        if (cropperState.modal) {
            cropperState.modal.classList.add('hidden');
            cropperState.modal.classList.remove('flex');
        }
        destroyCropper();
    }

    async function openCropper(file, root) {
        if (!file) {
            return;
        }
        if (!file.type.startsWith('image/')) {
            showToast('Please select an image file', 'error');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showToast('File too large. Maximum size is 10MB.', 'error');
            return;
        }

        pendingFileName = file.name || 'profile-photo.png';
        await ensureCropperAssets();
        const elements = getCropperElements(root);
        if (!elements || !elements.image) {
            showToast('Unable to open cropper', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            elements.image.src = reader.result;
            elements.modal.classList.remove('hidden');
            elements.modal.classList.add('flex');
            destroyCropper();
            cropperInstance = new window.Cropper(elements.image, {
                aspectRatio: 1,
                viewMode: 2,
                autoCropArea: 1,
                background: false,
                movable: true,
                zoomOnWheel: true,
            });
        };
        reader.readAsDataURL(file);
    }

    function bindCropperControls(root) {
        const elements = getCropperElements(root);
        if (!elements || !elements.controls) {
            return;
        }

        if (!elements.controls.dataset.bound) {
            elements.controls.dataset.bound = 'true';
            elements.controls.addEventListener('click', (event) => {
                const button = event.target.closest('[data-crop-action]');
                if (!button || !cropperInstance) {
                    return;
                }
                const action = button.getAttribute('data-crop-action');
                switch (action) {
                    case 'zoom-in':
                        cropperInstance.zoom(0.1);
                        break;
                    case 'zoom-out':
                        cropperInstance.zoom(-0.1);
                        break;
                    case 'rotate-left':
                        cropperInstance.rotate(-90);
                        break;
                    case 'rotate-right':
                        cropperInstance.rotate(90);
                        break;
                    case 'flip-horizontal':
                        currentScaleX = currentScaleX * -1;
                        cropperInstance.scaleX(currentScaleX);
                        break;
                    case 'flip-vertical':
                        currentScaleY = currentScaleY * -1;
                        cropperInstance.scaleY(currentScaleY);
                        break;
                    case 'reset':
                        currentScaleX = 1;
                        currentScaleY = 1;
                        cropperInstance.reset();
                        break;
                }
            });
        }

        [elements.cancel, elements.close].forEach((btn) => {
            if (!btn || btn.dataset.bound) {
                return;
            }
            btn.dataset.bound = 'true';
            btn.addEventListener('click', () => hideCropperModal());
        });

        if (elements.modal && !elements.modal.dataset.boundBackdrop) {
            elements.modal.dataset.boundBackdrop = 'true';
            elements.modal.addEventListener('click', (event) => {
                if (event.target === elements.modal) {
                    hideCropperModal();
                }
            });
        }

        if (elements.apply && !elements.apply.dataset.bound) {
            elements.apply.dataset.bound = 'true';
            elements.apply.addEventListener('click', async () => {
                if (!cropperInstance) {
                    return;
                }
                toggleButtonLoading(elements.apply, true, 'Uploading...');
                cropperInstance.getCroppedCanvas({ width: 640, height: 640, imageSmoothingQuality: 'high' }).toBlob(async (blob) => {
                    if (!blob) {
                        toggleButtonLoading(elements.apply, false);
                        showToast('Unable to process image', 'error');
                        return;
                    }
                    try {
                        const formData = new FormData();
                        formData.append('profile_photo', blob, pendingFileName);
                        const response = await fetch('/settings/upload-profile-photo', buildFormDataRequestOptions(formData));
                        const result = await response.json();
                        toggleButtonLoading(elements.apply, false);
                        if (!response.ok || !result.success) {
                            showToast(result.message || 'Upload failed', 'error');
                            return;
                        }
                        updateProfilePreview(result.url);
                        dispatchUserUpdate({ photoUrl: result.url, userId: SETTINGS_USER_ID });
                        showToast('Profile photo updated');
                        hideCropperModal();
                    } catch (error) {
                        toggleButtonLoading(elements.apply, false);
                        console.error('Upload error', error);
                        showToast('An error occurred while uploading', 'error');
                    }
                }, 'image/png');
            });
        }
    }

    function updateProfilePreview(url) {
        let preview = document.getElementById('profile-photo-preview');
        if (!preview) {
            return;
        }
        if (preview.tagName !== 'IMG') {
            const img = document.createElement('img');
            img.id = 'profile-photo-preview';
            img.alt = 'Profile Photo';
            img.className = 'w-20 h-20 rounded-full object-cover border-2 border-gray-200';
            preview.parentNode?.replaceChild(img, preview);
            preview = img;
        }
        preview.src = url;
    }

    function serializeForm(form) {
        const formData = new FormData(form);
        return Object.fromEntries(formData.entries());
    }

    window.initSettingsHandlers = function initSettingsHandlers(root = document) {
        const scope = root?.querySelector ? root : document;

        const accountForm = scope.querySelector('#account-form');
        if (accountForm && !accountForm.dataset.bound) {
            accountForm.dataset.bound = 'true';
            accountForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const submitBtn = accountForm.querySelector('button[type="submit"]');
                toggleButtonLoading(submitBtn, true);
                try {
                    const response = await fetch('/settings/update-account', buildJsonRequestOptions(serializeForm(accountForm)));
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        showToast(result.message || 'Failed to update profile', 'error');
                    } else {
                        showToast(result.message || 'Profile updated');
                        if (result.user) {
                            const { username, full_name: fullName } = result.user;
                            if (username) {
                                accountForm.querySelector('input[name="username"]').value = username;
                            }
                            if (fullName) {
                                accountForm.querySelector('input[name="full_name"]').value = fullName;
                            }
                            if (result.user.gender) {
                                const select = accountForm.querySelector('select[name="gender"]');
                                if (select) {
                                    select.value = result.user.gender;
                                }
                            }
                            dispatchUserUpdate({ username, fullName, userId: SETTINGS_USER_ID });
                        }
                    }
                } catch (error) {
                    console.error('Update account error', error);
                    showToast('An error occurred while saving', 'error');
                } finally {
                    toggleButtonLoading(submitBtn, false);
                }
            });
        }

        const passwordForm = scope.querySelector('#password-form');
        if (passwordForm && !passwordForm.dataset.bound) {
            passwordForm.dataset.bound = 'true';
            passwordForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const data = serializeForm(passwordForm);
                if ((data.new_password || '').trim() === '' || (data.current_password || '').trim() === '') {
                    showToast('Please fill out all password fields', 'error');
                    return;
                }
                if (data.new_password !== data.confirm_password) {
                    showToast('Passwords do not match', 'error');
                    return;
                }
                const submitBtn = passwordForm.querySelector('button[type="submit"]');
                toggleButtonLoading(submitBtn, true, 'Updating...');
                try {
                    const response = await fetch('/settings/update-password', buildJsonRequestOptions(data));
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        showToast(result.message || 'Unable to update password', 'error');
                    } else {
                        passwordForm.reset();
                        showToast(result.message || 'Password updated');
                    }
                } catch (error) {
                    console.error('Password update error', error);
                    showToast('An error occurred while updating password', 'error');
                } finally {
                    toggleButtonLoading(submitBtn, false);
                }
            });
        }

        const themeForm = scope.querySelector('#theme-form');
        if (themeForm && !themeForm.dataset.bound) {
            themeForm.dataset.bound = 'true';
            themeForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const submitBtn = themeForm.querySelector('button[type="submit"]');
                toggleButtonLoading(submitBtn, true, 'Saving...');
                const formData = new FormData(themeForm);
                const theme = formData.get('theme');
                try {
                    const response = await fetch('/settings/update-theme', buildJsonRequestOptions({ theme }));
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        showToast(result.message || 'Failed to save theme', 'error');
                    } else {
                        showToast(result.message || 'Theme preference saved');
                    }
                } catch (error) {
                    console.error('Theme update error', error);
                    showToast('An error occurred while updating theme', 'error');
                } finally {
                    toggleButtonLoading(submitBtn, false);
                }
            });
        }

        const twoFaBtn = scope.querySelector('#toggle-2fa');
        if (twoFaBtn && !twoFaBtn.dataset.bound) {
            twoFaBtn.dataset.bound = 'true';
            twoFaBtn.addEventListener('click', async () => {
                const enabling = twoFaBtn.textContent.trim() === 'Enable 2FA';
                toggleButtonLoading(twoFaBtn, true, enabling ? 'Enabling...' : 'Disabling...');
                try {
                    const endpoint = enabling ? '/settings/enable-two-factor' : '/settings/disable-two-factor';
                    const response = await fetch(endpoint, buildJsonRequestOptions({}));
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        showToast(result.message || 'Unable to update 2FA', 'error');
                    } else {
                        showToast(result.message || (enabling ? 'Two-factor enabled' : 'Two-factor disabled'));
                        if (enabling) {
                            twoFaBtn.textContent = 'Disable 2FA';
                            twoFaBtn.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                            twoFaBtn.classList.add('bg-green-500', 'text-white', 'hover:bg-green-600');
                        } else {
                            twoFaBtn.textContent = 'Enable 2FA';
                            twoFaBtn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                            twoFaBtn.classList.remove('bg-green-500', 'text-white', 'hover:bg-green-600');
                        }
                    }
                } catch (error) {
                    console.error('2FA toggle error', error);
                    showToast('An error occurred while updating 2FA', 'error');
                } finally {
                    toggleButtonLoading(twoFaBtn, false, enabling ? 'Enable 2FA' : 'Disable 2FA');
                }
            });
        }

        const uploadBtn = scope.querySelector('#upload-photo-btn');
        const photoInput = scope.querySelector('#profile-photo-input');
        if (uploadBtn && photoInput && !photoInput.dataset.bound) {
            photoInput.dataset.bound = 'true';
            uploadBtn.addEventListener('click', () => photoInput.click());
            photoInput.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (!file) {
                    return;
                }
                // Reset input so selecting the same file again still triggers change
                event.target.value = '';
                bindCropperControls(scope);
                openCropper(file, scope);
            });
        }

        // Ensure delegated anchors keep working when loaded outside dashboard
        if (!document.__settingsLinkHandlerBound) {
            document.__settingsLinkHandlerBound = true;
            document.addEventListener('click', (event) => {
                const anchor = event.target.closest && event.target.closest('a[href^="/settings"]');
                if (!anchor) {
                    return;
                }
                if (window.loadMiddleColumn) {
                    event.preventDefault();
                    window.loadMiddleColumn(anchor.getAttribute('href'));
                }
            });
        }
    };

    const runInitializers = () => window.initSettingsHandlers(document);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runInitializers, { once: true });
    } else {
        runInitializers();
    }
})();
// Initialize handlers for initial fragment load
</script>