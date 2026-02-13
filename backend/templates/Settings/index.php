
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
                
                <form id="account-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" 
                               name="username" 
                               value="<?= h($user->username) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Your unique username for the platform</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" 
                               name="full_name" 
                               value="<?= h($user->full_name) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                        <p class="text-xs text-gray-500 mt-1">Your display name that others will see</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" 
                               value="<?= h($user->email) ?>"
                               disabled
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 text-sm cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Email address cannot be changed for security reasons</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select name="gender" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                            <option value="Prefer not to say" <?= ($user->gender ?? 'Prefer not to say') === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                            <option value="Male" <?= ($user->gender ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($user->gender ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
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
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors text-sm shadow-sm hover:shadow-md">
                            Save Changes
                        </button>
                    </div>
                </form>
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
                    <form id="password-form" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" 
                                   name="current_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" 
                                   name="new_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" 
                                   name="confirm_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors">
                        </div>
                        
                        <div class="flex justify-end pt-2">
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors text-sm shadow-sm hover:shadow-md">
                                Update Password
                            </button>
                        </div>
                    </form>
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
                
                <form id="theme-form" class="space-y-3">
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
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors text-sm shadow-sm hover:shadow-md">
                            Save Theme
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        
        <?php if (!$isMobileView): ?>
            </div>
        </div>
        <?php endif; ?>
    </article>
</section>

<script>
function initSettingsHandlers(root = document) {
    root.getElementById?.('account-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const response = await fetch('/settings/update-account', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.fromEntries(formData))
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) location.reload();
        } catch (error) {
            alert('An error occurred');
        }
    });

    root.getElementById?.('password-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        if (data.new_password !== data.confirm_password) {
            alert('Passwords do not match');
            return;
        }
        try {
            const response = await fetch('/settings/update-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) e.target.reset();
        } catch (error) {
            alert('An error occurred');
        }
    });

    root.getElementById?.('theme-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const theme = formData.get('theme');
        try {
            const response = await fetch('/settings/update-theme', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ theme: theme })
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) {
                document.querySelectorAll('input[name="theme"]').forEach(radio => {
                    radio.checked = radio.value === theme;
                });
            }
        } catch (error) {
            alert('An error occurred while updating theme');
        }
    });

    root.getElementById?.('toggle-2fa')?.addEventListener('click', async (e) => {
        const button = e.target;
        const buttonText = button.textContent.trim();
        try {
            const action = buttonText === 'Enable 2FA' ? 'enable' : 'disable';
            const endpoint = action === 'enable' ? '/settings/enable-two-factor' : '/settings/disable-two-factor';
            const response = await fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' } });
            const result = await response.json();
            alert(result.message);
            if (result.success) {
                if (action === 'enable') {
                    button.textContent = 'Disable 2FA';
                    button.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                    button.classList.add('bg-green-500', 'text-white', 'hover:bg-green-600');
                } else {
                    button.textContent = 'Enable 2FA';
                    button.classList.remove('bg-green-500', 'text-white', 'hover:bg-green-600');
                    button.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                }
            }
        } catch (error) {
            alert('An error occurred while updating 2FA settings');
        }
    });
    
    // Profile photo upload handlers
    const uploadBtn = root.getElementById?.('upload-photo-btn');
    const photoInput = root.getElementById?.('profile-photo-input');
    const photoPreview = root.getElementById?.('profile-photo-preview');
    
    uploadBtn?.addEventListener('click', () => {
        photoInput?.click();
    });
    
    photoInput?.addEventListener('change', async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }
        
        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File too large. Maximum size is 10MB');
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = (e) => {
            if (photoPreview) {
                if (photoPreview.tagName === 'IMG') {
                    photoPreview.src = e.target.result;
                } else {
                    // Replace div with img
                    const img = document.createElement('img');
                    img.id = 'profile-photo-preview';
                    img.src = e.target.result;
                    img.alt = 'Profile Photo';
                    img.className = 'w-20 h-20 rounded-full object-cover border-2 border-gray-200';
                    photoPreview.parentNode?.replaceChild(img, photoPreview);
                }
            }
        };
        reader.readAsDataURL(file);
        
        // Upload to server
        const formData = new FormData();
        formData.append('profile_photo', file);
        
        try {
            const response = await fetch('/settings/upload-profile-photo', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) {
                // Update preview with Cloudinary URL
                const preview = document.getElementById('profile-photo-preview');
                if (preview && preview.tagName === 'IMG') {
                    preview.src = result.url;
                }
            }
        } catch (error) {
            alert('An error occurred while uploading the photo');
            console.error('Upload error:', error);
        }
    });
}

// Delegated handler for settings links so it works after fragment replaces
document.addEventListener('click', function (e) {
    const anchor = e.target.closest && e.target.closest('a[href^="/settings"]');
    if (!anchor) return;
    const middleColumn = document.getElementById('middle-component');
    if (!middleColumn) return; // fallback to normal navigation
    e.preventDefault();
    fetch(anchor.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            middleColumn.innerHTML = html;
            middleColumn.scrollTop = 0;
            // initialize any handlers inside the newly loaded fragment
            initSettingsHandlers(middleColumn);
        })
        .catch(err => {
            console.error('Failed to load settings section:', err);
            // fallback to full navigation on error
            window.location.href = anchor.href;
        });
});

// Initialize handlers for initial fragment load
initSettingsHandlers();
</script>