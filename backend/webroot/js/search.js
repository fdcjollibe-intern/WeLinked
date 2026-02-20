/**
 * Search functionality for navigation bar
 * Provides instant search suggestions for users and posts
 */

class SearchManager {
    constructor() {
        this.searchInput = null;
        this.searchDropdown = null;
        this.searchTimeout = null;
        this.currentQuery = '';
        this.currentType = 'users';
        this.init();
    }

    init() {
        this.searchInput = document.getElementById('global-search-input');
        if (!this.searchInput) return;
        
        this.attachEventListeners();
        this.createDropdown();
    }

    attachEventListeners() {
        // Input event with debouncing
        this.searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            if (query.length === 0) {
                this.hideDropdown();
                return;
            }
            
            // Debounce search
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.performSearch(query);
            }, 300);
        });

        // Focus event
        this.searchInput.addEventListener('focus', () => {
            const query = this.searchInput.value.trim();
            if (query.length > 0) {
                this.performSearch(query);
            }
        });

        // Enter key to go to full search page
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.searchInput.value.trim();
                if (query) {
                    window.location.href = `/search?q=${encodeURIComponent(query)}&type=${this.currentType}`;
                }
            }
        });

        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && 
                this.searchDropdown && 
                !this.searchDropdown.contains(e.target)) {
                this.hideDropdown();
            }
        });

        // Tab switching in dropdown
        document.addEventListener('click', (e) => {
            const tabBtn = e.target.closest('.search-tab-btn');
            if (tabBtn) {
                const type = tabBtn.dataset.type;
                this.switchTab(type);
            }
        });
    }

    createDropdown() {
        this.searchDropdown = document.createElement('div');
        this.searchDropdown.id = 'search-dropdown';
        this.searchDropdown.className = 'absolute top-full left-0 mt-2 w-full md:w-96 bg-white border border-gray-200 rounded-lg shadow-xl z-50 hidden max-h-200 overflow-hidden';
        
        const searchContainer = this.searchInput.closest('.flex.items-center');
        if (searchContainer) {
            searchContainer.style.position = 'relative';
            searchContainer.appendChild(this.searchDropdown);
        }
    }

    async performSearch(query) {
        this.currentQuery = query;
        
        // Show loading state
        this.showLoading();
        
        try {
            const response = await fetch(`/api/search/suggest?q=${encodeURIComponent(query)}&type=${this.currentType}&limit=6`);
            const data = await response.json();
            
            if (data.success) {
                this.renderResults(data.results, query);
            }
        } catch (error) {
            console.error('Search error:', error);
            this.hideLoading();
        }
    }

    switchTab(type) {
        this.currentType = type;
        this.performSearch(this.currentQuery);
    }

    renderResults(results, query) {
        if (!this.searchDropdown) return;
        
        const tabs = `
            <div class="flex border-b border-gray-200 px-2 py-2 flex-shrink-0">
                <button class="search-tab-btn flex-1 px-3 py-2 text-sm font-medium rounded-lg ${this.currentType === 'users' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'}" data-type="users">
                    Users
                </button>
                <button class="search-tab-btn flex-1 px-3 py-2 text-sm font-medium rounded-lg ${this.currentType === 'posts' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'}" data-type="posts">
                    Posts
                </button>
            </div>
        `;
        
        if (results.length === 0) {
            this.searchDropdown.innerHTML = `
                <div class="flex flex-col h-full">
                    ${tabs}
                    <div class="p-6 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm">No results found</p>
                    </div>
                </div>
            `;
            this.showDropdown();
            return;
        }
        
        let resultsHTML = '';
        
        if (this.currentType === 'users') {
            resultsHTML = results.map(user => `
                <a href="/profile/${encodeURIComponent(user.username)}" 
                   class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                    ${user.profile_photo_path ? 
                        `<img src="${this.escapeHtml(user.profile_photo_path)}" alt="${this.escapeHtml(user.username)}" class="w-10 h-10 rounded-full object-cover">` :
                        `<div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-semibold">
                            ${user.username.charAt(0).toUpperCase()}
                        </div>`
                    }
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">${this.escapeHtml(user.full_name)}</p>
                        <p class="text-xs text-gray-500 truncate">@${this.escapeHtml(user.username)}</p>
                        ${user.mutual_count > 0 ? 
                            `<p class="text-xs text-gray-400">${user.mutual_count} mutual ${user.mutual_count === 1 ? 'friend' : 'friends'}</p>` : 
                            ''
                        }
                    </div>
                </a>
            `).join('');
        } else {
            resultsHTML = results.map(post => {
                const excerpt = this.truncateText(post.content_text, 80);
                return `
                    <a href="/search?q=${encodeURIComponent(query)}&type=posts" 
                       class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start space-x-3">
                            ${post.user.profile_photo_path ? 
                                `<img src="${this.escapeHtml(post.user.profile_photo_path)}" alt="${this.escapeHtml(post.user.username)}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">` :
                                `<div class="w-8 h-8 rounded-full bg-pink-400 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                    ${post.user.username.charAt(0).toUpperCase()}
                                </div>`
                            }
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-900">${this.escapeHtml(post.user.full_name)}</p>
                                <p class="text-sm text-gray-700 mt-1 line-clamp-2">${this.escapeHtml(excerpt)}</p>
                                ${post.location ? 
                                    `<p class="text-xs text-gray-400 mt-1">📍 ${this.escapeHtml(post.location)}</p>` : 
                                    ''
                                }
                            </div>
                        </div>
                    </a>
                `;
            }).join('');
        }
        
        const viewAllBtn = `
            <a href="/search?q=${encodeURIComponent(query)}&type=${this.currentType}" 
               class="block px-4 py-3 text-center text-sm font-medium text-blue-600 hover:bg-blue-50 border-t border-gray-200 flex-shrink-0">
                View all results
            </a>
        `;
        
        const resultsContainer = `
            <div class="flex-1 overflow-y-auto">
                ${resultsHTML}
            </div>
        `;
        
        this.searchDropdown.innerHTML = `
            <div class="flex flex-col h-full">
                ${tabs}
                ${results.length > 0 ? resultsContainer : ''}
                ${results.length > 0 ? viewAllBtn : ''}
            </div>
        `;
        this.showDropdown();
    }

    showDropdown() {
        if (this.searchDropdown) {
            this.searchDropdown.classList.remove('hidden');
        }
    }

    hideDropdown() {
        if (this.searchDropdown) {
            this.searchDropdown.classList.add('hidden');
        }
    }

    showLoading() {
        if (!this.searchDropdown) return;
        
        const loadingHTML = `
            <div class="p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-sm text-gray-500 mt-3">Searching...</p>
            </div>
        `;
        this.searchDropdown.innerHTML = loadingHTML;
        this.showDropdown();
    }

    hideLoading() {
        // Loading is hidden when results are rendered or dropdown is hidden
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    truncateText(text, maxLength) {
        if (!text || text.length <= maxLength) return text || '';
        return text.substring(0, maxLength) + '...';
    }
}

// Initialize search manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
});
