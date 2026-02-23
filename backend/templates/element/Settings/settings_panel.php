
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
                    <!-- Profile Photo (Centered at Top) -->
                    <div class="flex flex-col items-center justify-center py-4 border-b border-gray-100 mb-4">
                        <?php if (!empty($user->profile_photo_path)): ?>
                        <img id="profile-photo-preview" 
                             src="<?= h($user->profile_photo_path) ?>" 
                             alt="Profile Photo" 
                             class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 shadow-md mb-4">
                        <?php else: ?>
                        <div id="profile-photo-preview" class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-4 border-gray-200 shadow-md mb-4">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <input type="file" 
                               id="profile-photo-input" 
                               accept="image/*" 
                               class="hidden">
                        <button type="button" 
                                id="upload-photo-btn"
                                class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors text-sm shadow-sm hover:shadow-md">
                            Change Photo
                        </button>
                        <p class="text-xs text-gray-500 mt-2">JPG, PNG, GIF or WEBP (Max 10MB)</p>
                    </div>
                    
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
                    
                    <!-- Birthday and Gender in one row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <?= $this->Form->control('birthdate', [
                                'type' => 'date',
                                'label' => ['text' => 'Birthday', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                                'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                                'value' => $user->birthdate ? $user->birthdate->format('Y-m-d') : '',
                                'required' => false
                            ]) ?>
                            <p class="text-xs text-gray-500 mt-1">Your date of birth (optional)</p>
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
                    </div>
                    
                    <div>
                        <div class="flex items-center">
                            <?= $this->Form->control('is_birthday_public', [
                                'type' => 'checkbox',
                                'label' => false,
                                'class' => 'w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500',
                                'checked' => $user->is_birthday_public ?? false
                            ]) ?>
                            <label for="is-birthday-public" class="ml-2 text-sm font-medium text-gray-700">
                                Make my birthday publicly visible
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">If enabled, your birthday will be visible to other users in the birthday list</p>
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
                
                <!-- View Logged In Devices -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">View Logged In Devices</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Device Management</p>
                                <p class="text-xs text-gray-600 mt-1">See all devices where you're currently logged in and manage your sessions</p>
                            </div>
                            <button id="view-devices-btn" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors text-sm font-medium">
                                View Devices
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">You can logout from specific devices or all other devices for security purposes.</p>
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
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900">Adjust Profile Photo</h3>
            <button type="button" id="cropper-close" class="text-gray-400 hover:text-gray-600 transition-colors" aria-label="Close">
                ✕
            </button>
        </div>
        <div class="px-5 py-3 space-y-3">
            <div class="bg-gray-50 rounded-xl border border-dashed border-gray-200 p-3 min-h-[280px] max-h-[380px] flex items-center justify-center overflow-hidden">
                <img id="cropper-source" src="" alt="Crop preview" class="max-h-[350px] max-w-full">
            </div>
            <div class="flex flex-wrap gap-1.5 justify-center" id="cropper-controls">
                <button type="button" data-crop-action="zoom-in" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 hover:border-blue-500 transition-all text-xs font-medium flex items-center gap-1" title="Zoom In">
                    <span class="material-symbols-outlined" style="font-size:16px">zoom_in</span>
                    <span class="hidden sm:inline">Zoom In</span>
                </button>
                <button type="button" data-crop-action="zoom-out" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 hover:border-blue-500 transition-all text-xs font-medium flex items-center gap-1" title="Zoom Out">
                    <span class="material-symbols-outlined" style="font-size:16px">zoom_out</span>
                    <span class="hidden sm:inline">Zoom Out</span>
                </button>
                <button type="button" data-crop-action="rotate-left" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 hover:border-blue-500 transition-all text-xs font-medium flex items-center gap-1" title="Rotate Left">
                    <span class="material-symbols-outlined" style="font-size:16px">rotate_left</span>
                    <span class="hidden sm:inline">Rotate</span>
                </button>
                <button type="button" data-crop-action="rotate-right" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 hover:border-blue-500 transition-all text-xs font-medium flex items-center gap-1" title="Rotate Right">
                    <span class="material-symbols-outlined" style="font-size:16px">rotate_right</span>
                    <span class="hidden sm:inline">Rotate</span>
                </button>
                <button type="button" data-crop-action="flip-horizontal" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 hover:border-blue-500 transition-all text-xs font-medium flex items-center gap-1" title="Flip Horizontal">
                    <span class="material-symbols-outlined" style="font-size:16px">flip</span>
                    <span class="hidden sm:inline">Flip H</span>
                </button>
                <button type="button" data-crop-action="flip-vertical" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 hover:border-blue-500 transition-all text-xs font-medium flex items-center gap-1" title="Flip Vertical">
                    <span class="material-symbols-outlined" style="font-size:16px;transform:rotate(90deg)">flip</span>
                    <span class="hidden sm:inline">Flip V</span>
                </button>
                <button type="button" data-crop-action="reset" class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-100 hover:border-red-500 transition-all text-xs font-medium flex items-center gap-1" title="Reset">
                    <span class="material-symbols-outlined" style="font-size:16px">restart_alt</span>
                    <span class="hidden sm:inline">Reset</span>
                </button>
            </div>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 flex justify-end gap-2">
            <button type="button" id="cropper-cancel" class="px-4 py-1.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
            <button type="button" id="cropper-apply" class="px-4 py-1.5 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700">Save &amp; Upload</button>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-[60] hidden items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div id="confirm-icon" class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                    </div>
                </div>
                <h3 id="confirm-title" class="text-lg font-semibold text-gray-900">Confirm Action</h3>
            </div>
        </div>
        <div class="px-6 py-4">
            <p id="confirm-message" class="text-gray-600"></p>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" id="confirm-cancel-btn" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button type="button" id="confirm-ok-btn" class="px-4 py-2 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- Device Management Modal -->
<div id="device-management-modal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Logged In Devices</h3>
                <p class="text-sm text-gray-500 mt-1">Manage your active sessions across devices</p>
            </div>
            <button type="button" id="devices-modal-close" class="text-gray-400 hover:text-gray-600 transition-colors text-xl" aria-label="Close">
                ✕
            </button>
        </div>
        <div class="flex-1 px-6 py-4 overflow-y-auto">
            <div id="devices-loading" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="ml-3 text-gray-600">Loading devices...</span>
            </div>
            <div id="devices-list" class="hidden space-y-4">
                <!-- Device items will be populated here -->
            </div>
            <div id="devices-error" class="hidden text-center py-12">
                <div class="text-red-500 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <p class="text-gray-600">Failed to load devices</p>
                <button id="retry-devices-btn" class="mt-3 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm">
                    Try Again
                </button>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            <div class="flex justify-between items-center">
                <p class="text-xs text-gray-500">
                    Sessions expire after 30 days of inactivity
                </p>
                <div class="flex gap-3">
                    <button type="button" id="logout-all-btn" class="px-4 py-2 text-sm font-medium border-2 border-red-500 text-red-600 rounded-lg hover:bg-red-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Logout All Other Devices
                    </button>
                </div>
            </div>
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
        toast.className = `fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 px-8 py-6 rounded-xl shadow-xl bg-white flex flex-col items-center gap-3`;
        const iconColor = variant === 'success' ? 'bg-green-500' : 'bg-red-500';
        const iconSvg = variant === 'success' 
            ? '<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
            : '<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>';
        toast.innerHTML = `
            <div class="w-16 h-16 rounded-full ${iconColor} flex items-center justify-center">
                ${iconSvg}
            </div>
            <span class="text-gray-900 font-bold text-base">${message}</span>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
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

        const viewDevicesBtn = scope.querySelector('#view-devices-btn');
        if (viewDevicesBtn && !viewDevicesBtn.dataset.bound) {
            viewDevicesBtn.dataset.bound = 'true';
            viewDevicesBtn.addEventListener('click', () => {
                openDeviceManagementModal();
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
    };

    // Custom Confirmation Modal Functions
    const showConfirmModal = (message, title = 'Confirm Action') => {
        return new Promise((resolve) => {
            const modal = document.getElementById('confirm-modal');
            const titleEl = document.getElementById('confirm-title');
            const messageEl = document.getElementById('confirm-message');
            const okBtn = document.getElementById('confirm-ok-btn');
            const cancelBtn = document.getElementById('confirm-cancel-btn');
            
            titleEl.textContent = title;
            messageEl.textContent = message;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            const handleOk = () => {
                cleanup();
                resolve(true);
            };
            
            const handleCancel = () => {
                cleanup();
                resolve(false);
            };
            
            const cleanup = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                okBtn.removeEventListener('click', handleOk);
                cancelBtn.removeEventListener('click', handleCancel);
            };
            
            okBtn.addEventListener('click', handleOk);
            cancelBtn.addEventListener('click', handleCancel);
            
            // Close on Escape key
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    handleCancel();
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);
        });
    };

    // Device Management Functions
    const openDeviceManagementModal = () => {
        const modal = document.getElementById('device-management-modal');
        const loadingDiv = document.getElementById('devices-loading');
        const listDiv = document.getElementById('devices-list');
        const errorDiv = document.getElementById('devices-error');
        
        // Reset modal state
        loadingDiv.classList.remove('hidden');
        listDiv.classList.add('hidden');
        errorDiv.classList.add('hidden');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Load devices
        loadDevices();
    };

    const closeDeviceManagementModal = () => {
        const modal = document.getElementById('device-management-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    const loadDevices = async () => {
        try {
            const response = await fetch('/api/device-sessions', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken || window.CSRF_TOKEN
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load devices');
            }

            const result = await response.json();
            
            if (result.success) {
                displayDevices(result.sessions);
            } else {
                throw new Error(result.message || 'Failed to load devices');
            }
        } catch (error) {
            console.error('Error loading devices:', error);
            showDevicesError();
        }
    };

    const displayDevices = (sessions) => {
        const loadingDiv = document.getElementById('devices-loading');
        const listDiv = document.getElementById('devices-list');
        const errorDiv = document.getElementById('devices-error');
        
        loadingDiv.classList.add('hidden');
        errorDiv.classList.add('hidden');
        
        // Get current browser's session cookie
        const currentSessionId = getCookie('welinked_session');
        console.log('[Device Display] Current browser session ID:', currentSessionId);
        
        // Mark current device based on session ID match
        sessions = sessions.map(session => {
            const isCurrentDevice = session.session_id === currentSessionId;
            console.log('[Device Display] Comparing:', {
                sessionId: session.session_id,
                currentSessionId: currentSessionId,
                matches: isCurrentDevice,
                deviceName: session.device_name
            });
            return {
                ...session,
                is_current: isCurrentDevice
            };
        });
        
        // Sort: Current device first, then by last activity
        sessions.sort((a, b) => {
            if (a.is_current && !b.is_current) return -1;
            if (!a.is_current && b.is_current) return 1;
            return new Date(b.last_activity) - new Date(a.last_activity);
        });
        
        console.log('[Device Display] Sorted sessions with current device first:', sessions);
        
        if (sessions.length === 0) {
            listDiv.innerHTML = '<div class="text-center py-8 text-gray-500">No active sessions found.</div>';
        } else {
            listDiv.innerHTML = sessions.map(session => {
                console.log('[Device Display] Rendering device:', session.device_name, 'is_current:', session.is_current);
                return createDeviceItem(session);
            }).join('');
        }
        
        listDiv.classList.remove('hidden');
        
        // Bind logout buttons
        bindDeviceLogoutButtons();
    };
    
    // Helper function to get cookie value
    const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            const cookieValue = parts.pop().split(';').shift();
            console.log(`[Cookie] Found ${name}:`, cookieValue);
            return cookieValue;
        }
        console.log(`[Cookie] ${name} not found`);
        return null;
    };

    const createDeviceItem = (session) => {
        const lastActivity = new Date(session.last_activity).toLocaleString();
        const createdAt = new Date(session.created_at).toLocaleString();
        
        return `
            <div class="border rounded-lg p-4 ${session.is_current ? 'bg-green-50 border-green-400 border-2' : 'bg-white border-gray-200'}">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <i class="${session.icon} ${session.is_current ? 'text-green-600' : 'text-gray-400'} text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <h4 class="font-medium text-gray-900">${session.device_name}</h4>
                                ${session.is_current ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">This Device</span>' : ''}
                            </div>
                            <div class="mt-1 text-sm text-gray-600 space-y-1">
                                ${session.browser_name ? `<p><span class="font-medium">Browser:</span> ${session.browser_name} ${session.browser_version || ''}</p>` : ''}
                                ${session.os_name ? `<p><span class="font-medium">Operating System:</span> ${session.os_name} ${session.os_version || ''}</p>` : ''}
                                <p><span class="font-medium">Location:</span> ${session.location}</p>
                                <p><span class="font-medium">Last Activity:</span> ${lastActivity}</p>
                                <p><span class="font-medium">Signed In:</span> ${createdAt}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        ${!session.is_current ? 
                            `<button type="button" class="logout-device-btn px-3 py-1 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded border border-red-200 hover:border-red-300 transition-colors" data-session-id="${session.session_id}">
                                <i class="fas fa-sign-out-alt mr-1"></i>Logout
                            </button>` : 
                            `<button type="button" class="logout-current-device-btn px-3 py-1 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded border border-red-200 hover:border-red-300 transition-colors" data-session-id="${session.session_id}">
                                <i class="fas fa-sign-out-alt mr-1"></i>Logout This Device
                            </button>`
                        }
                    </div>
                </div>
            </div>
        `;
    };

    const bindDeviceLogoutButtons = () => {
        // Bind individual device logout buttons
        document.querySelectorAll('.logout-device-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const sessionId = e.target.closest('button').dataset.sessionId;
                const confirmed = await showConfirmModal(
                    'Are you sure you want to logout from this device? This will end the session on that device.',
                    'Logout Device'
                );
                if (confirmed) {
                    await logoutDevice(sessionId);
                }
            });
        });

        // Bind current device logout button
        document.querySelectorAll('.logout-current-device-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const sessionId = e.target.closest('button').dataset.sessionId;
                const confirmed = await showConfirmModal(
                    'Are you sure you want to logout from this device? You will be redirected to the login page.',
                    'Logout This Device'
                );
                if (confirmed) {
                    await logoutCurrentDevice(sessionId);
                }
            });
        });

        // Bind logout all devices button
        const logoutAllBtn = document.getElementById('logout-all-btn');
        if (logoutAllBtn) {
            logoutAllBtn.onclick = async () => {
                const confirmed = await showConfirmModal(
                    'Are you sure you want to logout from all other devices? You will remain logged in on this device only.',
                    'Logout All Devices'
                );
                if (confirmed) {
                    await logoutAllDevices();
                }
            };
        }
    };

    const logoutCurrentDevice = async (sessionId) => {
        try {
            const response = await fetch('/api/device-sessions/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken || window.CSRF_TOKEN
                },
                body: JSON.stringify({ session_id: sessionId, is_current: true })
            });

            const result = await response.json();
            
            if (result.success) {
                showToast('Logging out...');
                // Close modal and redirect to login
                closeDeviceManagementModal();
                setTimeout(() => {
                    window.location.href = '/login';
                }, 500);
            } else {
                showToast(result.message || 'Failed to logout', 'error');
            }
        } catch (error) {
            console.error('Error logging out current device:', error);
            showToast('An error occurred while logging out', 'error');
        }
    };

    const logoutDevice = async (sessionId) => {
        try {
            const response = await fetch('/api/device-sessions/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken || window.CSRF_TOKEN
                },
                body: JSON.stringify({ session_id: sessionId })
            });

            const result = await response.json();
            
            if (result.success) {
                showToast('Device logged out successfully');
                loadDevices(); // Reload the list
            } else {
                showToast(result.message || 'Failed to logout device', 'error');
            }
        } catch (error) {
            console.error('Error logging out device:', error);
            showToast('An error occurred while logging out device', 'error');
        }
    };

    const logoutAllDevices = async () => {
        try {
            const response = await fetch('/api/device-sessions/logout-all', {
                method: 'POST', 
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken || window.CSRF_TOKEN
                }
            });

            const result = await response.json();
            
            if (result.success) {
                showToast('Logged out from all other devices successfully');
                loadDevices(); // Reload the list
            } else {
                showToast(result.message || 'Failed to logout all devices', 'error');
            }
        } catch (error) {
            console.error('Error logging out all devices:', error);
            showToast('An error occurred while logging out all devices', 'error');
        }
    };

    const showDevicesError = () => {
        const loadingDiv = document.getElementById('devices-loading');
        const listDiv = document.getElementById('devices-list');
        const errorDiv = document.getElementById('devices-error');
        
        loadingDiv.classList.add('hidden');
        listDiv.classList.add('hidden');
        errorDiv.classList.remove('hidden');
        
        // Bind retry button
        const retryBtn = document.getElementById('retry-devices-btn');
        if (retryBtn) {
            retryBtn.onclick = () => {
                loadingDiv.classList.remove('hidden');
                errorDiv.classList.add('hidden');
                loadDevices();
            };
        }
    };

    // Bind modal close button
    document.addEventListener('DOMContentLoaded', () => {
        const closeBtn = document.getElementById('devices-modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                closeDeviceManagementModal();
            });
        }
        
        // Close modal when clicking outside
        const modal = document.getElementById('device-management-modal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeDeviceManagementModal();
                }
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('device-management-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeDeviceManagementModal();
                }
            }
        });

        // Listen for WebSocket session invalidation
        if (window.socket) {
            window.socket.on('session_invalidated', (data) => {
                showToast(data.message || 'Your session has been terminated.', 'error');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            });
        }
    });

    const runInitializers = () => window.initSettingsHandlers(document);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runInitializers, { once: true });
    } else {
        runInitializers();
    }
})();
// Initialize handlers for initial fragment load
</script>