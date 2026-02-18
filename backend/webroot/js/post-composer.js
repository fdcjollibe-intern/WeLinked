/**
 * Post Composer with @Mentions, Location, and Edit/Delete functionality
 * Handles post CRUD operations with real-time feed updates
 */

class PostComposer {
    constructor() {
        this.mentionsList = [];
        this.selectedMentions = []; // Store {userId, username, color}
        this.selectedMedia = []; // {url, original, resource_type, public_id}
        this.currentMentionSearch = '';
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.initPostActions();
    }

    /**
     * Get CSRF token for requests
     */
    getCsrfToken() {
        // Check window first (set in template)
        if (window.csrfToken) return window.csrfToken;
        // Check meta tag
        const meta = document.querySelector('meta[name="csrfToken"]');
        if (meta) return meta.getAttribute('content');
        // Check cookie
        const cookie = document.cookie.match(/csrfToken=([^;]+)/);
        return cookie ? cookie[1] : null;
    }

    /**
     * Attach event listeners for post composer
     */
    attachEventListeners() {
        // Post composer textarea
        const textarea = document.getElementById('post-composer-textarea');
        if (textarea) {
            textarea.addEventListener('input', (e) => this.handleTextareaInput(e));
            textarea.addEventListener('keydown', (e) => this.handleKeydown(e));
        }

        // Submit button
        const submitBtn = document.getElementById('post-submit-btn');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => this.submitPost());
        }

        // Location toggle button
        const toggleLocationBtn = document.getElementById('toggle-location-btn');
        if (toggleLocationBtn) {
            toggleLocationBtn.addEventListener('click', () => this.toggleLocationInput());
        }

        // Remove location button
        const removeLocationBtn = document.getElementById('remove-location-btn');
        if (removeLocationBtn) {
            removeLocationBtn.addEventListener('click', () => this.hideLocationInput());
        }

        // Location input
        const locationInput = document.getElementById('post-location-input');
        if (locationInput) {
            locationInput.addEventListener('input', (e) => this.handleLocationInput(e));
        }

        // Close mention dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('mention-dropdown');
            if (dropdown && !dropdown.contains(e.target) && e.target !== textarea) {
                this.hideMentionDropdown();
            }
        });

        // Attachment input
        const attachmentInput = document.getElementById('attachment-input');
        if (attachmentInput) {
            attachmentInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
        }

        // Drag & drop handlers on composer
        const composer = document.getElementById('post-composer') || document.querySelector('.composer');
        if (composer) {
            composer.addEventListener('dragenter', (e) => this.showDropOverlay(e));
            composer.addEventListener('dragover', (e) => this.onDragOver(e));
            composer.addEventListener('dragleave', (e) => this.hideDropOverlay(e));
            composer.addEventListener('drop', (e) => this.onDrop(e));
        }
    }

    /**
     * Handle textarea input for @mention detection
     */
    handleTextareaInput(e) {
        const textarea = e.target;
        const value = textarea.value;
        const cursorPos = textarea.selectionStart;

        // Get text before cursor
        const textBeforeCursor = value.substring(0, cursorPos);
        
        // Find last @ symbol
        const lastAtIndex = textBeforeCursor.lastIndexOf('@');
        
        if (lastAtIndex !== -1) {
            // Check if @ is at start or preceded by whitespace
            const charBeforeAt = lastAtIndex > 0 ? textBeforeCursor[lastAtIndex - 1] : ' ';
            if (charBeforeAt === ' ' || charBeforeAt === '\n' || lastAtIndex === 0) {
                // Extract search term after @
                const searchTerm = textBeforeCursor.substring(lastAtIndex + 1);
                
                // Check if there's no space after @ (still typing mention)
                if (!searchTerm.includes(' ') && !searchTerm.includes('\n')) {
                    this.currentMentionSearch = searchTerm;
                    this.fetchMentionSuggestions(searchTerm);
                    return;
                }
            }
        }
        
        // Hide dropdown if no active mention
        this.hideMentionDropdown();

        // Autosize textarea
        this.autosizeTextarea(textarea);
    }

    autosizeTextarea(textarea) {
        if (!textarea) return;
        const maxHeight = 320; // match inline style max-height
        textarea.style.height = 'auto';
        textarea.style.height = '72px'; // reset to min-height first
        const newHeight = Math.min(textarea.scrollHeight, maxHeight);
        textarea.style.height = newHeight + 'px';
        
        // Also adjust overflow based on whether we hit max height
        textarea.style.overflowY = textarea.scrollHeight > maxHeight ? 'auto' : 'hidden';
    }

    /**
     * Handle keyboard navigation in mention dropdown
     */
    handleKeydown(e) {
        const dropdown = document.getElementById('mention-dropdown');
        if (!dropdown || dropdown.classList.contains('hidden')) {
            return;
        }

        const items = dropdown.querySelectorAll('.mention-item');
        let activeIndex = -1;
        
        items.forEach((item, index) => {
            if (item.classList.contains('active')) {
                activeIndex = index;
            }
        });

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextIndex = (activeIndex + 1) % items.length;
            this.setActiveMentionItem(items, nextIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevIndex = activeIndex === 0 ? items.length - 1 : activeIndex - 1;
            this.setActiveMentionItem(items, prevIndex);
        } else if (e.key === 'Enter' && activeIndex !== -1) {
            e.preventDefault();
            items[activeIndex].click();
        } else if (e.key === 'Escape') {
            this.hideMentionDropdown();
        }
    }

    /**
     * Set active item in mention dropdown
     */
    setActiveMentionItem(items, index) {
        items.forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
    }

    /**
     * Fetch mention suggestions from API
     */
    async fetchMentionSuggestions(query) {
        try {
            const response = await fetch(`/api/mentions/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success && data.users) {
                this.mentionsList = data.users;
                this.showMentionDropdown(data.users);
            }
        } catch (error) {
            console.error('Fetch mentions error:', error);
        }
    }

    /**
     * Show mention dropdown with user suggestions
     */
    showMentionDropdown(users) {
        let dropdown = document.getElementById('mention-dropdown');
        
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.id = 'mention-dropdown';
            dropdown.className = 'absolute bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto z-50 hidden';
            dropdown.style.width = '300px';
            document.body.appendChild(dropdown);
        }

        if (users.length === 0) {
            this.hideMentionDropdown();
            return;
        }

        // Build dropdown HTML
        dropdown.innerHTML = users.map(user => `
            <div class="mention-item flex items-center px-4 py-2 hover:bg-gray-50 cursor-pointer transition-colors"
                 data-user-id="${user.id}"
                 data-username="${user.username}"
                 data-color="${user.color}">
                ${user.profile_photo ? 
                    `<img src="${user.profile_photo}" alt="${user.username}" class="w-8 h-8 rounded-full mr-3">` :
                    `<div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-3 text-sm font-semibold text-gray-600">
                        ${user.username.charAt(0).toUpperCase()}
                    </div>`
                }
                <div class="flex-1">
                    <div class="font-medium text-sm text-gray-900">@${user.username}</div>
                    <div class="text-xs text-gray-500">${user.full_name}</div>
                </div>
                <div class="w-3 h-3 rounded-full ${this.getColorClass(user.color)}"></div>
            </div>
        `).join('');

        // Position dropdown near textarea
        const textarea = document.getElementById('post-composer-textarea');
        if (textarea) {
            const rect = textarea.getBoundingClientRect();
            dropdown.style.top = `${rect.bottom + window.scrollY + 5}px`;
            dropdown.style.left = `${rect.left + window.scrollX}px`;
        }

        dropdown.classList.remove('hidden');

        // Add click handlers
        dropdown.querySelectorAll('.mention-item').forEach(item => {
            item.addEventListener('click', () => {
                const userId = parseInt(item.dataset.userId);
                const username = item.dataset.username;
                const color = item.dataset.color;
                this.insertMention(userId, username, color);
            });
        });
    }

    /**
     * Hide mention dropdown
     */
    hideMentionDropdown() {
        const dropdown = document.getElementById('mention-dropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }

    /**
     * Insert mention into textarea
     */
    insertMention(userId, username, color) {
        const textarea = document.getElementById('post-composer-textarea');
        if (!textarea) return;

        const value = textarea.value;
        const cursorPos = textarea.selectionStart;
        const textBeforeCursor = value.substring(0, cursorPos);
        const lastAtIndex = textBeforeCursor.lastIndexOf('@');
        
        // Replace from @ to cursor with @username
        const before = value.substring(0, lastAtIndex);
        const after = value.substring(cursorPos);
        const newValue = before + `@${username} ` + after;
        
        textarea.value = newValue;
        textarea.focus();
        
        // Set cursor after mention
        const newCursorPos = lastAtIndex + username.length + 2;
        textarea.setSelectionRange(newCursorPos, newCursorPos);
        
        // Track selected mention with color
        this.selectedMentions.push({userId, username, color});
        
        this.hideMentionDropdown();
        this.autosizeTextarea(textarea);
    }

    /**
     * Get CSS color class based on gender color
     */
    getColorClass(color) {
        const colors = {
            'blue': 'bg-blue-500',
            'pink': 'bg-pink-500',
            'green': 'bg-green-500'
        };
        return colors[color] || 'bg-gray-500';
    }

    /**
     * Handle location input
     */
    handleLocationInput(e) {
        // Could implement location autocomplete here
        // For now, just free text input
    }

    /**
     * Toggle location input visibility
     */
    toggleLocationInput() {
        const container = document.getElementById('location-input-container');
        const input = document.getElementById('post-location-input');
        
        if (container) {
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                if (input) input.focus();
            } else {
                container.classList.add('hidden');
                if (input) input.value = '';
            }
        }
    }

    /**
     * Hide location input
     */
    hideLocationInput() {
        const container = document.getElementById('location-input-container');
        const input = document.getElementById('post-location-input');
        
        if (container) {
            container.classList.add('hidden');
        }
        if (input) {
            input.value = '';
        }
    }

    /**
     * Submit new post
     */
    async submitPost() {
        const textarea = document.getElementById('post-composer-textarea');
        const locationInput = document.getElementById('post-location-input');
        
        if (!textarea) return;

        const contentText = textarea.value.trim();
        if (!contentText) {
            this.showToast('Please write something to post', 'warning');
            return;
        }

        const location = locationInput ? locationInput.value.trim() : null;

        try {
            const csrfToken = this.getCsrfToken();
            const response = await fetch('/dashboard/posts/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken || ''
                },
                body: JSON.stringify({
                    content_text: contentText,
                    location: location,
                    mentions: this.selectedMentions.map(m => typeof m === 'object' ? m.userId : m),
                    media: this.selectedMedia
                })
            });

            const result = await response.json();
            
            if (result.success) {
                // Clear form
                textarea.value = '';
                if (locationInput) locationInput.value = '';
                this.selectedMentions = [];
                this.selectedMedia = [];
                
                // Reset textarea height
                this.autosizeTextarea(textarea);
                
                // Clear previews
                const preview = document.getElementById('attachment-preview');
                if (preview) preview.innerHTML = '';
                
                // Reload feed
                if (typeof window.reloadFeed === 'function') {
                    window.reloadFeed();
                } else {
                    window.location.reload();
                }
                
                this.showToast('Post created successfully!', 'success');
            } else {
                this.showToast('Failed to create post: ' + (result.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Post submit error:', error);
            this.showToast('An error occurred while creating post', 'error');
        }
    }

    /**
     * Handle files from input or drop
     */
    async handleFiles(fileList) {
        if (!fileList || fileList.length === 0) return;
        const files = Array.from(fileList);
        // upload via uploads endpoint
        const form = new FormData();
        files.forEach((f) => form.append('files[]', f, f.name));

        try {
            const csrfToken = this.getCsrfToken();
            if (csrfToken) {
                form.append('_csrfToken', csrfToken);
            }
            const resp = await fetch('/dashboard/upload?type=post', {
                method: 'POST',
                body: form
            });
            const data = await resp.json();
            if (data.files && Array.isArray(data.files)) {
                data.files.forEach(f => {
                    this.selectedMedia.push(f);
                });
                this.renderAttachmentPreviews();
            }
        } catch (err) {
            console.error('Upload error', err);
            this.showToast('Failed to upload attachments', 'error');
        } finally {
            this.hideDropOverlay();
        }
    }

    renderAttachmentPreviews() {
        const container = document.getElementById('attachment-preview');
        if (!container) return;
        container.innerHTML = '';
        this.selectedMedia.forEach((m, idx) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'inline-block mr-2 mb-2 relative';
            wrapper.style.width = '120px';
            wrapper.style.height = '80px';

            if ((m.resource_type || '').startsWith('video') || (m.url||'').match(/\.mp4|\.webm|\.ogg/)) {
                wrapper.innerHTML = `<video src="${m.url}" class="w-full h-full object-cover rounded-md" muted playsinline></video>`;
            } else {
                wrapper.innerHTML = `<img src="${m.url}" class="w-full h-full object-cover rounded-md">`;
            }

            const del = document.createElement('button');
            del.className = 'absolute top-1 right-1 bg-white rounded-full p-1 shadow';
            del.innerHTML = '✕';
            del.addEventListener('click', () => {
                this.selectedMedia.splice(idx, 1);
                this.renderAttachmentPreviews();
            });
            wrapper.appendChild(del);
            container.appendChild(wrapper);
        });
    }

    showDropOverlay(e) {
        e.preventDefault();
        const overlay = document.getElementById('composer-drop-overlay');
        if (overlay) overlay.classList.remove('hidden');
    }

    onDragOver(e) {
        e.preventDefault();
    }

    hideDropOverlay() {
        const overlay = document.getElementById('composer-drop-overlay');
        if (overlay) overlay.classList.add('hidden');
    }

    onDrop(e) {
        e.preventDefault();
        this.hideDropOverlay();
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length) {
            this.handleFiles(dt.files);
        }
    }

    /**
     * Initialize post action buttons (edit, delete) and dropdown menus
     */
    initPostActions() {
        // Use event delegation for dynamically loaded posts
        document.addEventListener('click', (e) => {
            // Handle post menu button click (three dots)
            const menuBtn = e.target.closest('.post-menu-btn');
            if (menuBtn) {
                e.stopPropagation();
                const dropdown = menuBtn.nextElementSibling;
                const isVisible = dropdown && !dropdown.classList.contains('hidden');
                
                // Close all other dropdowns first
                document.querySelectorAll('.post-menu-dropdown').forEach(d => {
                    d.classList.add('hidden');
                });
                
                // Toggle this dropdown
                if (dropdown && !isVisible) {
                    dropdown.classList.remove('hidden');
                }
                return;
            }
            
            // Handle edit button click
            const editBtn = e.target.closest('.post-edit-btn');
            if (editBtn) {
                e.stopPropagation();
                const postId = editBtn.dataset.postId;
                this.closeAllPostMenus();
                this.editPost(postId);
                return;
            }
            
            // Handle delete button click
            const deleteBtn = e.target.closest('.post-delete-btn');
            if (deleteBtn) {
                e.stopPropagation();
                const postId = deleteBtn.dataset.postId;
                this.closeAllPostMenus();
                this.deletePost(postId);
                return;
            }
            
            // Close menus when clicking outside
            if (!e.target.closest('.post-menu-container')) {
                this.closeAllPostMenus();
            }
        });
    }

    /**
     * Close all post menu dropdowns
     */
    closeAllPostMenus() {
        document.querySelectorAll('.post-menu-dropdown').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }

    /**
     * Edit post - shows a modal with post content and attachments
     */
    async editPost(postId) {
        const postElement = document.querySelector(`[data-post-id="${postId}"]`);
        if (!postElement) return;

        // Find the post content
        const contentElement = postElement.querySelector('p.text-gray-700');
        const currentContent = contentElement ? contentElement.textContent.trim() : '';
        
        // Find existing attachments (images and videos)
        const galleryEl = postElement.querySelector('.post-gallery');
        const videoEl = postElement.querySelector('.post-video video');
        const attachments = [];
        
        // Collect image attachments
        if (galleryEl) {
            const images = galleryEl.querySelectorAll('img');
            images.forEach(img => {
                if (img.src) {
                    attachments.push({ type: 'image', url: img.src });
                }
            });
        }
        
        // Collect video attachments
        if (videoEl && videoEl.src) {
            attachments.push({ type: 'video', url: videoEl.src });
        }

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'edit-post-modal fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Post</h3>
                    <button class="edit-modal-close text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4 max-h-[60vh] overflow-y-auto">
                    <textarea class="edit-post-textarea w-full bg-gray-50 rounded-xl px-4 py-3 text-gray-700 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent resize-none" rows="4" placeholder="What's on your mind?">${this.escapeHtml(currentContent)}</textarea>
                    
                    <div class="edit-post-attachments mt-4 ${attachments.length === 0 ? 'hidden' : ''}">
                        <div class="text-sm font-medium text-gray-700 mb-2">Attachments</div>
                        <div class="edit-attachments-grid grid grid-cols-2 gap-2">
                            ${attachments.map((att, idx) => `
                                <div class="edit-attachment-item relative rounded-lg overflow-hidden bg-gray-100" data-index="${idx}" data-url="${this.escapeHtml(att.url)}" data-type="${att.type}">
                                    ${att.type === 'image' 
                                        ? `<img src="${this.escapeHtml(att.url)}" class="w-full h-32 object-cover">`
                                        : `<video src="${this.escapeHtml(att.url)}" class="w-full h-32 object-cover"></video>`
                                    }
                                    <button class="remove-edit-attachment absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors shadow-md" data-index="${idx}" title="Remove attachment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <div class="attachment-removed-overlay hidden absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                                        <span class="text-white text-xs font-medium">Will be removed</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Click × to remove attachments. New attachments cannot be added while editing.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-4 py-3 border-t border-gray-100 bg-gray-50">
                    <button class="edit-modal-cancel px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button class="edit-modal-save bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Focus textarea
        const textarea = modal.querySelector('.edit-post-textarea');
        if (textarea) {
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        }

        // Track which attachments to remove
        const attachmentsToRemove = new Set();

        // Handle remove attachment clicks
        modal.querySelectorAll('.remove-edit-attachment').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const idx = parseInt(btn.dataset.index);
                const item = modal.querySelector(`.edit-attachment-item[data-index="${idx}"]`);
                const overlay = item?.querySelector('.attachment-removed-overlay');
                
                if (attachmentsToRemove.has(idx)) {
                    // Un-mark for removal
                    attachmentsToRemove.delete(idx);
                    if (overlay) overlay.classList.add('hidden');
                    btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
                } else {
                    // Mark for removal
                    attachmentsToRemove.add(idx);
                    if (overlay) overlay.classList.remove('hidden');
                    btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>`;
                }
            });
        });

        // Close modal on background click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Close button
        modal.querySelector('.edit-modal-close').addEventListener('click', () => modal.remove());
        modal.querySelector('.edit-modal-cancel').addEventListener('click', () => modal.remove());

        // Save button
        modal.querySelector('.edit-modal-save').addEventListener('click', async () => {
            const newContent = textarea.value.trim();
            const saveBtn = modal.querySelector('.edit-modal-save');
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> Saving...';

            try {
                // Build removed attachment URLs
                const removedUrls = [];
                attachmentsToRemove.forEach(idx => {
                    if (attachments[idx]) {
                        removedUrls.push(attachments[idx].url);
                    }
                });

                const response = await fetch(`/dashboard/posts/edit/${postId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content_text: newContent,
                        remove_attachments: removedUrls
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    // Update UI - update text content
                    if (contentElement) {
                        contentElement.innerHTML = this.highlightMentions(newContent);
                    }
                    
                    // Remove attachments from UI if any were marked
                    if (attachmentsToRemove.size > 0) {
                        // If all attachments removed, remove the gallery/video container
                        if (attachmentsToRemove.size === attachments.length) {
                            if (galleryEl) galleryEl.remove();
                            if (videoEl) videoEl.closest('.post-video')?.remove();
                        } else {
                            // Partial removal - update the gallery
                            // This is simplified - in real implementation you'd rebuild the gallery
                            attachmentsToRemove.forEach(idx => {
                                const att = attachments[idx];
                                if (att.type === 'image' && galleryEl) {
                                    const img = galleryEl.querySelector(`img[src="${att.url}"]`);
                                    if (img) img.closest('div')?.remove();
                                } else if (att.type === 'video' && videoEl) {
                                    videoEl.closest('.post-video')?.remove();
                                }
                            });
                        }
                    }
                    
                    modal.remove();
                } else {
                    this.showToast('Failed to update post: ' + (result.message || 'Unknown error'), 'error');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Changes';
                }
            } catch (error) {
                console.error('Post edit error:', error);
                this.showToast('An error occurred while updating post', 'error');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save Changes';
            }
        });

        // Handle escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    /**
     * Delete post - shows confirmation modal
     */
    async deletePost(postId) {
        // Create confirm modal
        const modal = document.createElement('div');
        modal.className = 'delete-post-modal fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden">
                <div class="p-6 text-center">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Post</h3>
                    <p class="text-sm text-gray-500 mb-6">Are you sure you want to delete this post? This action cannot be undone.</p>
                    <div class="flex items-center justify-center gap-3">
                        <button class="delete-modal-cancel px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button class="delete-modal-confirm bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal on background click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Cancel button
        modal.querySelector('.delete-modal-cancel').addEventListener('click', () => modal.remove());

        // Confirm delete button
        modal.querySelector('.delete-modal-confirm').addEventListener('click', async () => {
            const confirmBtn = modal.querySelector('.delete-modal-confirm');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> Deleting...';

            try {
                const response = await fetch(`/dashboard/posts/delete/${postId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    modal.remove();
                    // Remove post from UI with animation
                    const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                    if (postElement) {
                        postElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        postElement.style.opacity = '0';
                        postElement.style.transform = 'scale(0.95)';
                        setTimeout(() => postElement.remove(), 300);
                    }
                } else {
                    modal.remove();
                    this.showToast('Failed to delete post: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Post delete error:', error);
                modal.remove();
                this.showToast('An error occurred while deleting post', 'error');
            }
        });

        // Handle escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    }

    /**
     * Show a toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const bgColor = type === 'error' ? 'bg-red-500' : type === 'success' ? 'bg-green-500' : 'bg-gray-800';
        toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2 animate-fade-in`;
        toast.innerHTML = `
            ${type === 'error' ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' : ''}
            <span class="text-sm">${message}</span>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /**
     * Highlight @mentions with gender-based colors
     */
    highlightMentions(text) {
        // Match @username patterns and apply colors from selectedMentions
        return text.replace(/@(\w+)/g, (match, username) => {
            // Find if this mention has color data
            const mention = this.selectedMentions.find(m => 
                (typeof m === 'object' && m.username === username) || m === username
            );
            
            let color = '#3b82f6'; // default blue
            if (mention && typeof mention === 'object' && mention.color) {
                // Map color names to hex values
                const colorMap = {
                    'blue': '#3b82f6',
                    'pink': '#ec4899',
                    'green': '#10b981',
                };
                color = colorMap[mention.color] || color;
            }
            
            return `<span class="mention-highlight" data-username="${username}" style="color: ${color}; font-weight: 500;">${match}</span>`;
        });
    }
}

// Initialize post composer when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.postComposer = new PostComposer();
});

/**
 * Utility: Render mentions in post content with gender-based colors
 * Call this after posts are loaded to colorize @mentions
 * @param {string} text - The post content text
 * @param {Array} mentions - Array of mention objects with {username, color}
 * @returns {string} HTML with colored mentions
 */
window.renderMentionsInPost = function(text, mentions = []) {
    if (!text) return '';
    
    return text.replace(/@(\w+)/g, (match, username) => {
        // Find mention color
        const mention = mentions.find(m => m.username === username);
        let color = '#3b82f6'; // default blue
        
        if (mention && mention.color) {
            const colorMap = {
                'blue': '#3b82f6',
                'pink': '#ec4899',
                'green': '#10b981',
            };
            color = colorMap[mention.color] || color;
        }
        
        return `<span class="mention-highlight" style="color: ${color}; font-weight: 500;">${match}</span>`;
    });
};