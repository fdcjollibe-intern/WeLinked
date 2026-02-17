<?php
/**
 * Edit Profile Modal Element
 * @var \App\View\AppView $this
 * @var object $user
 */
?>
<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] flex flex-col shadow-xl">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900">Edit Profile</h3>
            <button class="close-edit-modal text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto p-6">
            <?= $this->Form->create(null, [
                'id' => 'edit-profile-form',
                'type' => 'post',
                'url' => ['controller' => 'Profile', 'action' => 'update'],
                'class' => 'space-y-4'
            ]) ?>
            
                <!-- Full Name -->
                <div>
                    <?= $this->Form->control('full_name', [
                        'label' => ['text' => 'Full Name', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                        'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                        'value' => h($user->full_name ?? ''),
                        'required' => true,
                        'maxlength' => 150
                    ]) ?>
                    <p class="text-xs text-gray-500 mt-1">Your display name that others will see</p>
                </div>
                
                <!-- Username -->
                <div>
                    <?= $this->Form->control('username', [
                        'label' => ['text' => 'Username', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                        'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                        'value' => h($user->username ?? ''),
                        'required' => true,
                        'maxlength' => 50,
                        'pattern' => '[a-zA-Z0-9_]+',
                        'title' => 'Username can only contain letters, numbers, and underscores'
                    ]) ?>
                    <p class="text-xs text-gray-500 mt-1">Your unique username for the platform</p>
                </div>
                
                <!-- Bio -->
                <div>
                    <?= $this->Form->control('bio', [
                        'type' => 'textarea',
                        'label' => ['text' => 'Bio', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                        'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors resize-none',
                        'value' => h($user->bio ?? ''),
                        'rows' => 3,
                        'maxlength' => 180,
                        'placeholder' => 'Tell us about yourself...'
                    ]) ?>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="bio-counter">0</span>/180 characters
                    </p>
                </div>
                
                <!-- Website -->
                <div>
                    <?= $this->Form->control('website', [
                        'type' => 'url',
                        'label' => ['text' => 'Website', 'class' => 'block text-sm font-medium text-gray-700 mb-1'],
                        'class' => 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors',
                        'value' => h($user->website ?? ''),
                        'maxlength' => 180,
                        'placeholder' => 'https://example.com'
                    ]) ?>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="website-counter">0</span>/180 characters
                    </p>
                </div>
                
                <!-- Gender -->
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
                
            <?= $this->Form->end() ?>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
            <button type="button" 
                    class="close-edit-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm">
                Cancel
            </button>
            <button type="submit" 
                    form="edit-profile-form"
                    class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors text-sm shadow-sm hover:shadow-md">
                Save Changes
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('edit-profile-modal');
    const form = document.getElementById('edit-profile-form');
    const bioTextarea = form?.querySelector('[name="bio"]');
    const bioCounter = document.getElementById('bio-counter');
    const websiteInput = form?.querySelector('[name="website"]');
    const websiteCounter = document.getElementById('website-counter');
    
    // Character counters
    function updateCharCounter(input, counter) {
        if (input && counter) {
            const length = input.value.length;
            counter.textContent = length;
            // Warning color if near limit
            if (length > 150) {
                counter.classList.add('text-orange-500', 'font-semibold');
            } else {
                counter.classList.remove('text-orange-500', 'font-semibold');
            }
        }
    }
    
    if (bioTextarea && bioCounter) {
        updateCharCounter(bioTextarea, bioCounter);
        bioTextarea.addEventListener('input', () => updateCharCounter(bioTextarea, bioCounter));
    }
    
    if (websiteInput && websiteCounter) {
        updateCharCounter(websiteInput, websiteCounter);
        websiteInput.addEventListener('input', () => updateCharCounter(websiteInput, websiteCounter));
    }
    
    // Open modal
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('[data-action="edit-profile"]');
        if (!editBtn) return;
        
        modal?.classList.remove('hidden');
        // Update counters when opening
        updateCharCounter(bioTextarea, bioCounter);
        updateCharCounter(websiteInput, websiteCounter);
    });
    
    // Close modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.close-edit-modal') || e.target === modal) {
            modal?.classList.add('hidden');
        }
    });
    
    // Handle form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn?.textContent;
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            }
            
            const formData = new FormData(form);
            const csrfToken = window.csrfToken || window.CSRF_TOKEN || '<?= $this->request->getAttribute('csrfToken') ?>';
            
            // Store original username to detect changes
            const originalUsername = form.querySelector('[name="username"]')?.value;
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showToast('Profile updated successfully!', 'success');
                    
                    // Check if username changed - redirect to new profile URL
                    if (data.user && data.user.username && originalUsername !== data.user.username) {
                        showToast('Redirecting to updated profile...', 'success');
                        setTimeout(() => {
                            window.location.href = `/profile/${data.user.username}`;
                        }, 1000);
                        return;
                    }
                    
                    // Update the profile display
                    if (data.user) {
                        // Update username display
                        const usernameDisplay = document.querySelector('[data-profile-full-name]');
                        if (usernameDisplay && data.user.username) {
                            usernameDisplay.textContent = '@' + data.user.username;
                        }
                        
                        // Update full name display
                        const fullNameDisplay = document.querySelector('[data-profile-username]');
                        if (fullNameDisplay && data.user.full_name) {
                            fullNameDisplay.textContent = data.user.full_name;
                        }
                        
                        // Update bio display
                        const bioDisplay = document.querySelector('[data-profile-bio]');
                        const bioContainer = document.getElementById('bio-container');
                        if (bioDisplay && bioContainer) {
                            bioDisplay.textContent = data.user.bio || '';
                            bioContainer.style.display = data.user.bio ? 'block' : 'none';
                        }
                        
                        // Update website display
                        const websiteDisplay = document.querySelector('[data-profile-website]');
                        const websiteLink = document.querySelector('[data-profile-website-link]');
                        const websiteContainer = document.getElementById('website-container');
                        if (websiteDisplay && websiteLink && websiteContainer) {
                            websiteDisplay.textContent = data.user.website || '';
                            websiteLink.href = data.user.website || '#';
                            websiteContainer.style.display = data.user.website ? 'block' : 'none';
                        }
                    }
                    
                    // Close modal
                    modal?.classList.add('hidden');
                } else {
                    // Show error message
                    showToast(data.message || 'Failed to update profile', 'error');
                }
            })
            .catch(error => {
                console.error('Profile update error:', error);
                showToast('An error occurred while updating profile', 'error');
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    }
    
    function showToast(message, variant = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-6 right-6 z-50 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium flex items-center gap-3 ${variant === 'success' ? 'bg-white border-green-200 text-gray-900' : 'bg-white border-red-200 text-gray-900'}`;
        toast.innerHTML = `<span class="w-2 h-2 rounded-full ${variant === 'success' ? 'bg-green-500' : 'bg-red-500'}"></span><span>${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3200);
    }
})();
</script>
