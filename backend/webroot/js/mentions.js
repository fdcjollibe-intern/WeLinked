/**
 * Mention autocomplete functionality
 * Shows dropdown when user types @ in textarea
 */

class MentionAutocomplete {
    constructor(textarea) {
        this.textarea = textarea;
        this.dropdown = null;
        this.currentQuery = '';
        this.selectedIndex = -1;
        this.users = [];
        this.searchTimeout = null;
        this.mentionStart = -1;
        
        this.init();
    }
    
    init() {
        if (!this.textarea) return;
        
        this.createDropdown();
        this.attachEventListeners();
    }
    
    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.id = 'mention-dropdown';
        this.dropdown.className = 'absolute bg-white border border-gray-200 rounded-lg shadow-xl z-50 hidden max-h-64 overflow-y-auto w-64';
        this.dropdown.style.bottom = '100%';
        this.dropdown.style.left = '0';
        this.dropdown.style.marginBottom = '8px';
        
        // Position relative to textarea container
        const container = this.textarea.closest('.composer') || this.textarea.parentElement;
        if (container) {
            container.style.position = 'relative';
            container.appendChild(this.dropdown);
        }
    }
    
    attachEventListeners() {
        // Listen for keyup to detect '@' mentions
        this.textarea.addEventListener('input', (e) => {
            this.handleInput();
        });
        
        // Handle keyboard navigation
        this.textarea.addEventListener('keydown', (e) => {
            if (!this.dropdown.classList.contains('hidden')) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.users.length - 1);
                    this.highlightSelected();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                    this.highlightSelected();
                } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                    e.preventDefault();
                    this.selectUser(this.users[this.selectedIndex]);
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    this.hideDropdown();
                }
            }
        });
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!this.textarea.contains(e.target) && 
                this.dropdown && 
                !this.dropdown.contains(e.target)) {
                this.hideDropdown();
            }
        });
        
        // Delegate click events for user selection
        this.dropdown.addEventListener('click', (e) => {
            const userItem = e.target.closest('.mention-user-item');
            if (userItem) {
                const userId = parseInt(userItem.dataset.userId);
                const user = this.users.find(u => u.id === userId);
                if (user) {
                    this.selectUser(user);
                }
            }
        });
    }
    
    handleInput() {
        const text = this.textarea.value;
        const cursorPos = this.textarea.selectionStart;
        
        // Find the last '@' before cursor
        let lastAtIndex = -1;
        for (let i = cursorPos - 1; i >= 0; i--) {
            if (text[i] === '@') {
                // Check if it's at the start or preceded by whitespace
                if (i === 0 || /\s/.test(text[i - 1])) {
                    lastAtIndex = i;
                    break;
                }
            } else if (/\s/.test(text[i])) {
                // Stop if we hit whitespace before finding '@'
                break;
            }
        }
        
        if (lastAtIndex === -1) {
            this.hideDropdown();
            return;
        }
        
        // Extract query after '@'
        const query = text.substring(lastAtIndex + 1, cursorPos);
        
        // Don't search if query has spaces or is too long
        if (query.includes(' ') || query.length > 30) {
            this.hideDropdown();
            return;
        }
        
        this.mentionStart = lastAtIndex;
        this.currentQuery = query;
        
        // Debounce search
        clearTimeout(this.searchTimeout);
        if (query.length === 0) {
            // Show recent/suggested users
            this.searchTimeout = setTimeout(() => this.performSearch(''), 100);
        } else {
            this.searchTimeout = setTimeout(() => this.performSearch(query), 300);
        }
    }
    
    async performSearch(query) {
        try {
            this.showLoading();
            
            const response = await fetch(`/mentions/search?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success && data.users) {
                this.users = data.users;
                this.selectedIndex = 0;
                this.renderResults();
            } else {
                this.hideDropdown();
            }
        } catch (error) {
            console.error('Mention search error:', error);
            this.hideDropdown();
        }
    }
    
    showLoading() {
        if (!this.dropdown) return;
        
        this.dropdown.innerHTML = `
            <div class="p-4 text-center">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <p class="text-xs text-gray-500 mt-2">Searching...</p>
            </div>
        `;
        this.dropdown.classList.remove('hidden');
    }
    
    renderResults() {
        if (!this.dropdown || this.users.length === 0) {
            this.hideDropdown();
            return;
        }
        
        const resultsHTML = this.users.map((user, index) => {
            const initials = user.username.charAt(0).toUpperCase();
            const isSelected = index === this.selectedIndex;
            
            return `
                <div class="mention-user-item flex items-center space-x-3 px-3 py-2 cursor-pointer transition-colors ${isSelected ? 'bg-blue-50' : 'hover:bg-gray-50'}" 
                     data-user-id="${user.id}">
                    ${user.profile_photo ? 
                        `<img src="${this.escapeHtml(user.profile_photo)}" alt="${this.escapeHtml(user.username)}" class="w-8 h-8 rounded-full object-cover">` :
                        `<div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold" style="background-color: ${user.color}">
                            ${initials}
                        </div>`
                    }
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">@${this.escapeHtml(user.username)}</p>
                        <p class="text-xs text-gray-500 truncate">${this.escapeHtml(user.full_name)}</p>
                    </div>
                </div>
            `;
        }).join('');
        
        this.dropdown.innerHTML = resultsHTML;
        this.dropdown.classList.remove('hidden');
    }
    
    highlightSelected() {
        const items = this.dropdown.querySelectorAll('.mention-user-item');
        items.forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('bg-blue-50');
                item.classList.remove('hover:bg-gray-50');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-blue-50');
                item.classList.add('hover:bg-gray-50');
            }
        });
    }
    
    selectUser(user) {
        if (!user || this.mentionStart === -1) return;
        
        const text = this.textarea.value;
        const cursorPos = this.textarea.selectionStart;
        
        // Replace from @ to cursor with @username
        const before = text.substring(0, this.mentionStart);
        const after = text.substring(cursorPos);
        const mention = `@${user.username} `;
        
        this.textarea.value = before + mention + after;
        
        // Set cursor after the mention
        const newCursorPos = this.mentionStart + mention.length;
        this.textarea.setSelectionRange(newCursorPos, newCursorPos);
        
        // Trigger input event to resize textarea if needed
        this.textarea.dispatchEvent(new Event('input', { bubbles: true }));
        
        // Store mentioned user for later use (when submitting post)
        if (!this.textarea.mentionedUsers) {
            this.textarea.mentionedUsers = [];
        }
        if (!this.textarea.mentionedUsers.find(u => u.id === user.id)) {
            this.textarea.mentionedUsers.push(user);
        }
        
        this.hideDropdown();
        this.textarea.focus();
    }
    
    hideDropdown() {
        if (this.dropdown) {
            this.dropdown.classList.add('hidden');
        }
        this.selectedIndex = -1;
        this.users = [];
    }
    
    showDropdown() {
        if (this.dropdown) {
            this.dropdown.classList.remove('hidden');
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Get mentioned user IDs from textarea
    getMentionedUserIds() {
        return this.textarea.mentionedUsers ? this.textarea.mentionedUsers.map(u => u.id) : [];
    }
}

// Export for use in other modules
window.MentionAutocomplete = MentionAutocomplete;
