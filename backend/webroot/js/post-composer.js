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
            alert('Please write something to post');
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
                
                alert('Post created successfully!');
            } else {
                alert('Failed to create post: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Post submit error:', error);
            alert('An error occurred while creating post');
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
            alert('Failed to upload attachments');
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
     * Initialize post action buttons (edit, delete)
     */
    initPostActions() {
        // Use event delegation for dynamically loaded posts
        document.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.post-edit-btn');
            const deleteBtn = e.target.closest('.post-delete-btn');
            
            if (editBtn) {
                const postId = editBtn.dataset.postId;
                this.editPost(postId);
            }
            
            if (deleteBtn) {
                const postId = deleteBtn.dataset.postId;
                this.deletePost(postId);
            }
        });
    }

    /**
     * Edit post
     */
    async editPost(postId) {
        const postElement = document.querySelector(`[data-post-id="${postId}"]`);
        if (!postElement) return;

        const contentElement = postElement.querySelector('.post-content');
        const currentContent = contentElement.textContent.trim();
        
        const newContent = prompt('Edit your post:', currentContent);
        if (newContent === null || newContent === currentContent) return;

        try {
            const response = await fetch(`/dashboard/posts/edit/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content_text: newContent
                })
            });

            const result = await response.json();
            
            if (result.success) {
                // Update UI
                contentElement.innerHTML = this.highlightMentions(newContent);
                alert('Post updated successfully!');
            } else {
                alert('Failed to update post: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Post edit error:', error);
            alert('An error occurred while updating post');
        }
    }

    /**
     * Delete post
     */
    async deletePost(postId) {
        if (!confirm('Are you sure you want to delete this post?')) {
            return;
        }

        try {
            const response = await fetch(`/dashboard/posts/delete/${postId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();
            
            if (result.success) {
                // Remove post from UI
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                if (postElement) {
                    postElement.style.opacity = '0';
                    postElement.style.transform = 'scale(0.95)';
                    setTimeout(() => postElement.remove(), 300);
                }
                alert('Post deleted successfully!');
            } else {
                alert('Failed to delete post: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Post delete error:', error);
            alert('An error occurred while deleting post');
        }
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