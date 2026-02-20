// comments.js — inline comment composer with attachments, delete, and time formatting
(function(){
  const csrfToken = window.csrfToken || document.querySelector('meta[name="csrfToken"]')?.content || '';
  
  // Check if we're on a page with posts
  setTimeout(() => {
    const posts = document.querySelectorAll('.post');
    const commentBtns = document.querySelectorAll('.comment-btn');
  }, 1000);

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
  }

  function showToast(message, type = 'info') {
    const colors = {
      success: 'bg-green-500',
      error: 'bg-red-500',
      warning: 'bg-yellow-500',
      info: 'bg-blue-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-y-full opacity-0`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    requestAnimationFrame(() => {
      toast.classList.remove('translate-y-full', 'opacity-0');
    });
    
    setTimeout(() => {
      toast.classList.add('translate-y-full', 'opacity-0');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  function formatCount(count) {
    return count === 1 ? '1 comment' : `${count} comments`;
  }

  function timeAgo(datetime) {
    if (!datetime) return 'Just now';
    
    // Handle both ISO format and MySQL datetime format (Y-m-d H:i:s)
    let then;
    if (typeof datetime === 'string') {
      // Replace space with 'T' for proper ISO parsing if needed
      // Add 'Z' to indicate UTC timezone if not already present
      let isoString = datetime.includes('T') ? datetime : datetime.replace(' ', 'T');
      if (!isoString.includes('Z') && !isoString.includes('+') && !isoString.includes('-', 10)) {
        isoString += 'Z';
      }
      then = new Date(isoString);
    } else {
      then = new Date(datetime);
    }
    
    const now = new Date();
    const diffMs = now - then;
    const diffSec = Math.floor(diffMs / 1000);
    
    // If difference is negative or very small, it's just now
    if (diffSec < 10) return 'Just now';
    
    const diffMin = Math.floor(diffSec / 60);
    const diffHour = Math.floor(diffMin / 60);
    const diffDay = Math.floor(diffHour / 24);
    const diffWeek = Math.floor(diffDay / 7);
    const diffMonth = Math.floor(diffDay / 30);
    const diffYear = Math.floor(diffDay / 365);

    if (diffSec < 60) return 'Just now';
    if (diffMin < 60) return `${diffMin}m ago`;
    if (diffHour < 24) return `${diffHour}h ago`;
    if (diffDay < 7) return `${diffDay}d ago`;
    if (diffWeek < 4) return `${diffWeek}w ago`;
    if (diffMonth < 12) return `${diffMonth}mo ago`;
    return `${diffYear}y ago`;
  }

  function handleCommentButtonClick(event) {
    const btn = event.target.closest('.comment-btn');
    if (!btn) {
      return;
    }
    
    // Check if already processed this event
    if (event._commentProcessed) {
      return;
    }
    event._commentProcessed = true;
    
    event.preventDefault();
    event.stopImmediatePropagation(); // Stop other handlers from firing
    const post = btn.closest('.post');
    if (!post) {
      return;
    }
    toggleComposer(post);
  }

  function toggleComposer(post) {
    let composer = post.querySelector('.comment-composer');
    if (!composer) {
      composer = buildComposer(post);
      post.appendChild(composer);
      loadExistingComments(post);
      // Focus the input after a short delay to ensure it's rendered
      setTimeout(() => {
        composer.querySelector('.comment-input')?.focus();
      }, 100);
    } else {
      // Toggle visibility on subsequent clicks
      if (composer.classList.contains('hidden')) {
        composer.classList.remove('hidden');
        composer.querySelector('.comment-input')?.focus();
      } else {
        composer.classList.add('hidden');
      }
    }
  }

  function buildComposer(post) {
    const wrap = document.createElement('div');
    wrap.className = 'comment-composer mt-4 border-t border-gray-100 pt-3';
    console.log('[comments.js] Creating composer WITHOUT hidden class');
    
    // Get current user info
    const userPhoto = window.currentUserPhoto || '';
    const userInitial = window.currentUserInitial || 'U';
    
    wrap.innerHTML = `
      <div class="comment-thread space-y-3 mb-3"></div>
      <div class="comment-input-wrapper">
        <div id="comment-attachment-preview-${post.dataset.postId}" class="mb-2 hidden"></div>
        <div class="flex items-center gap-2">
          <!-- Profile Photo -->
          <div class="flex-shrink-0">
            ${userPhoto 
              ? `<img src="${userPhoto}" alt="Profile" class="w-10 h-10 rounded-full object-cover">`
              : `<div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-semibold">${userInitial}</div>`
            }
          </div>
          <!-- Input Field -->
          <div class="flex-1 flex items-center gap-2 bg-gray-50 rounded-full px-4 py-2 border border-gray-200 focus-within:ring-1 focus-within:ring-blue-400">
            <textarea class="comment-input flex-1 bg-transparent border-0 focus:outline-none focus:ring-0 text-sm resize-none" rows="1" placeholder="Write a comment here" style="min-height: 24px; max-height: 120px;"></textarea>
            <!-- Paperclip Icon (Attachment) -->
            <label class="cursor-pointer inline-flex items-center justify-center hover:opacity-70 transition-opacity flex-shrink-0">
              <input type="file" class="comment-attachment-input hidden" accept="image/*,video/*">
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
              </svg>
            </label>
          </div>
          <!-- Send Button (Paper Plane) -->
          <button class="comment-submit bg-blue-500 text-white w-10 h-10 rounded-full text-sm font-semibold flex items-center justify-center hover:bg-blue-600 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(-35deg);">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
          </button>
        </div>
      </div>
    `;

    const input = wrap.querySelector('.comment-input');
    const submitBtn = wrap.querySelector('.comment-submit');
    const attachmentInput = wrap.querySelector('.comment-attachment-input');
    const attachmentPreview = wrap.querySelector(`#comment-attachment-preview-${post.dataset.postId}`);

    if (input && submitBtn) {
      input.addEventListener('keydown', function(ev){
        if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
          ev.preventDefault();
          submitBtn.click();
        }
      });
    }

    if (attachmentInput && attachmentPreview) {
      attachmentInput.addEventListener('change', function(e) {
        const file = e.target.files?.[0];
        if (!file) {
          attachmentPreview.classList.add('hidden');
          attachmentPreview.innerHTML = '';
          return;
        }

        // Validate file
        if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
          showToast('Please select an image or video file.', 'warning');
          attachmentInput.value = '';
          return;
        }
        if (file.size > 50 * 1024 * 1024) { // 50MB limit for comments
          showToast('File too large. Maximum 50MB for comment attachments.', 'warning');
          attachmentInput.value = '';
          return;
        }

        // Show preview
        const isImage = file.type.startsWith('image/');
        const tempUrl = URL.createObjectURL(file);
        attachmentPreview.innerHTML = `
          <div class="relative inline-block">
            ${isImage 
              ? `<img src="${tempUrl}" class="max-h-32 rounded-lg border border-gray-200">`
              : `<video src="${tempUrl}" class="max-h-32 rounded-lg border border-gray-200" muted loop playsinline></video>`
            }
            <button class="cancel-attachment absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        `;
        attachmentPreview.classList.remove('hidden');

        // Bind cancel button
        const cancelBtn = attachmentPreview.querySelector('.cancel-attachment');
        if (cancelBtn) {
          cancelBtn.addEventListener('click', function() {
            URL.revokeObjectURL(tempUrl);
            attachmentInput.value = '';
            attachmentPreview.classList.add('hidden');
            attachmentPreview.innerHTML = '';
          });
        }
      });
    }

    return wrap;
  }

  function loadExistingComments(post) {
    const postId = post.dataset.postId;
    if (!postId) return;

    const composer = post.querySelector('.comment-composer');
    const thread = composer?.querySelector('.comment-thread');
    if (!thread) return;

    thread.innerHTML = '<div class="text-center py-2 text-gray-400 text-sm">Loading comments...</div>';

    fetch(`/dashboard/comments/list?post_id=${postId}`, {
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(r => {
      console.log('[comments.js] Response status:', r.status);
      return r.json();
    })
    .then(json => {
      console.log('[comments.js] ========== FETCHED COMMENTS ==========');
      console.log('[comments.js] Full response:', json);
      console.log('[comments.js] Comments array:', json.comments);
      if (!json || !json.success) {
        thread.innerHTML = '';
        return;
      }
      thread.innerHTML = '';
      const comments = json.comments || [];
      console.log('[comments.js] Processing', comments.length, 'comments');
      if (comments.length === 0) {
        thread.innerHTML = '<div class="text-center py-2 text-gray-400 text-sm">No comments yet. Be the first!</div>';
        return;
      }
      
      // Store all comments for pagination
      thread.dataset.allComments = JSON.stringify(comments);
      thread.dataset.visibleCount = '4';
      
      // Show first 4 comments
      const initialComments = comments.slice(0, 4);
      initialComments.forEach((comment, index) => {
        console.log(`[comments.js] ========== COMMENT ${index + 1}/${comments.length} ==========`);
        console.log('[comments.js] Comment ID:', comment.id);
        console.log('[comments.js] Comment text:', comment.content_text);
        console.log('[comments.js] User reaction:', comment.user_reaction);
        console.log('[comments.js] Reaction counts:', comment.reaction_counts);
        console.log('[comments.js] Reaction counts type:', typeof comment.reaction_counts);
        console.log('[comments.js] Is array?:', Array.isArray(comment.reaction_counts));
        console.log('[comments.js] Keys:', comment.reaction_counts ? Object.keys(comment.reaction_counts) : 'null');
        console.log('[comments.js] Full comment object:', comment);
        renderComment(thread, comment, post);
      });
      
      // Show "See more comments" button if there are more than 4 comments
      if (comments.length > 4) {
        const seeMoreBtn = document.createElement('button');
        seeMoreBtn.className = 'see-more-comments-btn w-full text-gray-600 hover:text-blue-600 text-sm font-medium py-2 hover:bg-gray-50 rounded-lg transition-colors mt-2';
        seeMoreBtn.textContent = `See more comments (${comments.length - 4} remaining)`;
        thread.appendChild(seeMoreBtn);
      }
    })
    .catch(err => {
      console.error('[comments.js] Failed to load comments', err);
      thread.innerHTML = '<div class="text-center py-2 text-red-400 text-sm">Failed to load comments</div>';
    });
  }

  function renderComment(thread, comment, post) {
    console.log('[comments.js] renderComment called with comment:', comment);
    console.log('[comments.js] Comment reaction data:', {
      user_reaction: comment.user_reaction,
      reaction_counts: comment.reaction_counts
    });
    
    const user = comment?.user || {};
    const currentUserId = window.currentUserId || null;
    const canDelete = currentUserId && (currentUserId === comment.user_id || currentUserId === parseInt(post.dataset.userId));
    const canEdit = currentUserId && currentUserId === comment.user_id; // Only owner can edit
    
    const fullName = user.full_name || user.username || 'Anonymous';
    const username = user.username || '';
    const userId = user.id || comment.user_id;
    const profilePhoto = user.profile_photo_path || '';
    const initial = (fullName || 'A').charAt(0).toUpperCase();
    const createdAt = timeAgo(comment.created_at);
    const profileUrl = userId ? `/profile/${userId}` : '#';

    const item = document.createElement('div');
    item.className = 'comment-item flex items-start gap-3';
    item.dataset.commentId = comment.id;
    item.dataset.contentText = comment.content_text || '';
    item.dataset.attachmentUrl = comment.attachment_url || '';
    item.innerHTML = `
      <a href="${escapeHtml(profileUrl)}" class="w-9 h-9 rounded-full overflow-hidden bg-blue-500 text-white flex items-center justify-center font-semibold flex-shrink-0 hover:opacity-80 transition-opacity">
        ${profilePhoto ? `<img src="${escapeHtml(profilePhoto)}" alt="${escapeHtml(fullName)}" class="w-full h-full object-cover">` : escapeHtml(initial)}
      </a>
      <div class="flex-1 min-w-0">
        <div class="comment-content-wrapper bg-gray-50 rounded-2xl px-3 py-2 inline-block max-w-full">
          <div class="mb-1">
            <a href="${escapeHtml(profileUrl)}" class="text-sm font-semibold text-gray-900 hover:underline">${escapeHtml(fullName)}</a>
            <span class="text-xs text-gray-400 ml-1">@${escapeHtml(username)}</span>
          </div>
          <p class="text-xs text-gray-500 mb-2">${createdAt}</p>
          <p class="comment-text text-sm text-gray-700 break-words">${escapeHtml(comment.content_text || '')}</p>
          ${comment.attachment_url ? `
            <div class="comment-attachment mt-2">
              ${comment.attachment_url.match(/\.(jpg|jpeg|png|gif|webp)$/i) 
                ? `<img src="${escapeHtml(comment.attachment_url)}" class="max-h-64 rounded-lg">`
                : `<video src="${escapeHtml(comment.attachment_url)}" controls class="max-h-64 rounded-lg"></video>`
              }
            </div>
          ` : ''}
        </div>
        <div class="flex items-center gap-4 mt-2 px-2">
          <button class="comment-reaction-btn flex items-center gap-1.5 text-gray-500 hover:bg-gray-100 rounded-lg px-2 py-1 transition-colors text-sm" data-comment-id="${comment.id}" data-user-reaction="" data-target-type="comment" data-target-id="${comment.id}">
            <svg class="like-icon w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span class="reaction-label text-xs font-medium text-gray-700">Like</span>
            <span class="reaction-count text-xs" data-count="0" style="display:none">0</span>
          </button>
          ${canEdit ? `<button class="edit-comment-btn text-gray-500 hover:text-blue-600 transition-colors p-1" data-comment-id="${comment.id}" title="Edit comment">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>` : ''}
          ${canDelete ? `<button class="delete-comment-btn text-red-500 hover:text-red-600 transition-colors p-1" data-comment-id="${comment.id}" title="Delete comment">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>` : ''}
        </div>
        <!-- Reaction Summary -->
        <div class="comment-reaction-summary" style="display:none;margin-top:8px;padding-left:8px;">
          <div class="flex items-center gap-1.5 text-xs text-gray-600">
            <span class="comment-reaction-emojis" style="display:flex;align-items:center;"></span>
            <span class="comment-reaction-count">0</span>
          </div>
        </div>
      </div>
    `;
    thread.appendChild(item);
    
    // Initialize reaction button state if comment has reactions
    const reactionBtn = item.querySelector('.comment-reaction-btn');
    console.log('[comments.js] Found reaction button:', !!reactionBtn);
    
    if (reactionBtn) {
      // Normalize reaction_counts: convert array to object if needed
      let reactionCounts = comment.reaction_counts;
      if (Array.isArray(reactionCounts)) {
        console.warn('[comments.js] reaction_counts is an array, converting to object:', reactionCounts);
        reactionCounts = {};
      }
      
      console.log('[comments.js] Comment reaction data:', {
        user_reaction: comment.user_reaction,
        reaction_counts: reactionCounts,
        reaction_counts_type: typeof reactionCounts,
        reaction_counts_keys: reactionCounts ? Object.keys(reactionCounts) : null
      });
      
      // Update button appearance if current user has reacted
      if (comment.user_reaction) {
        console.log('[comments.js] Updating button with user reaction:', comment.user_reaction);
        updateCommentReactionButton(reactionBtn, comment.user_reaction);
      }
      
      // Always update reaction summary if it exists (even if current user hasn't reacted)
      if (reactionCounts && typeof reactionCounts === 'object' && Object.keys(reactionCounts).length > 0) {
        console.log('[comments.js] Updating reaction summary:', reactionCounts);
        updateCommentReactionSummary(item, reactionCounts);
      } else {
        console.log('[comments.js] No reaction counts to display (empty or null)');
      }
    }
  }

  function handleCommentSubmit(event) {
    const submitBtn = event.target.closest('.comment-submit');
    if (!submitBtn) return;
    
    // Check if already processed this event
    if (event._submitProcessed) {
      console.log('[comments.js] Submit event already processed, skipping');
      return;
    }
    event._submitProcessed = true;
    event.stopImmediatePropagation();
    
    event.preventDefault();
    
    const composer = submitBtn.closest('.comment-composer');
    const input = composer?.querySelector('.comment-input');
    const post = submitBtn.closest('.post');
    const attachmentInput = composer?.querySelector('.comment-attachment-input');
    const attachmentPreview = composer?.querySelector('[id^="comment-attachment-preview-"]');
    
    if (!composer || !input || !post) return;
    
    const text = input.value.trim();
    const file = attachmentInput?.files?.[0];
    
    if (!text && !file) {
      showToast('Please write a comment or attach a file.', 'warning');
      return;
    }

    submitBtn.disabled = true;
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>';
    
    submitComment(post, text, file)
      .then(function(comment){
        if (comment) {
          input.value = '';
          if (attachmentInput) attachmentInput.value = '';
          if (attachmentPreview) {
            attachmentPreview.classList.add('hidden');
            attachmentPreview.innerHTML = '';
          }
          input.focus();
        }
      })
      .finally(function(){
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
      });
  }

  async function submitComment(post, text, file) {
    const postId = post.dataset.postId;
    if (!postId) {
      showToast('Unable to identify post for comment.', 'error');
      return null;
    }

    try {
      let attachmentUrl = null;

      // Upload attachment first if exists
      if (file) {
        const formData = new FormData();
        formData.append('file', file);
        if (csrfToken) formData.append('_csrfToken', csrfToken);

        const uploadResponse = await fetch('/dashboard/upload?type=comment', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'X-CSRF-Token': csrfToken },
          body: formData
        });

        const uploadData = await uploadResponse.json();
        if (uploadData.files && uploadData.files[0]) {
          attachmentUrl = uploadData.files[0].url;
        }
      }

      // Submit comment
      const response = await fetch('/dashboard/comments/create', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
          post_id: postId,
          content_text: text,
          attachment_url: attachmentUrl
        })
      });

      const json = await response.json();
      if (!json || !json.success) {
        showToast(json?.message || 'Failed to add comment', 'error');
        return null;
      }

      const composer = post.querySelector('.comment-composer');
      const thread = composer?.querySelector('.comment-thread');
      if (thread) {
        // Remove "no comments" placeholder if exists
        const placeholder = thread.querySelector('.text-gray-400');
        if (placeholder) placeholder.remove();
        
        renderComment(thread, json.comment, post);
      }
      incrementCommentCount(post);
      return json.comment;
    } catch (err) {
      console.error('[comments.js] Unable to submit comment', err);
      showToast('Unable to add comment right now.', 'error');
      return null;
    }
  }

  function handleDeleteComment(event) {
    const deleteBtn = event.target.closest('.delete-comment-btn');
    if (!deleteBtn) return;
    
    // Check if already processed this event
    if (event._deleteProcessed) {
      console.log('[comments.js] Delete event already processed, skipping');
      return;
    }
    event._deleteProcessed = true;
    event.stopImmediatePropagation();
    
    event.preventDefault();

    const commentId = deleteBtn.dataset.commentId;
    const commentItem = deleteBtn.closest('.comment-item');
    const post = deleteBtn.closest('.post');

    if (!commentId || !commentItem) return;

    // Create confirmation modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/50';
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="p-6">
          <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Delete Comment</h3>
          <p class="text-gray-600 text-center text-sm">Are you sure you want to delete this comment? This action cannot be undone.</p>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
          <button type="button" class="cancel-delete-btn px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300">
            Cancel
          </button>
          <button type="button" class="confirm-delete-btn px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
            Delete
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    const cancelBtn = modal.querySelector('.cancel-delete-btn');
    const confirmBtn = modal.querySelector('.confirm-delete-btn');

    // Handle cancel
    cancelBtn.addEventListener('click', () => modal.remove());
    modal.addEventListener('click', (e) => {
      if (e.target === modal) modal.remove();
    });

    // Handle escape key
    const handleEscape = (e) => {
      if (e.key === 'Escape') {
        modal.remove();
        document.removeEventListener('keydown', handleEscape);
      }
    };
    document.addEventListener('keydown', handleEscape);

    // Handle confirm delete
    confirmBtn.addEventListener('click', async function() {
      confirmBtn.disabled = true;
      confirmBtn.innerHTML = '<div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> Deleting...';

      try {
        const response = await fetch('/dashboard/comments/delete', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({ comment_id: commentId })
        });

        const json = await response.json();
        if (json && json.success) {
          modal.remove();
          commentItem.style.transition = 'opacity 0.3s, transform 0.3s';
          commentItem.style.opacity = '0';
          commentItem.style.transform = 'translateX(-20px)';
          setTimeout(() => {
            commentItem.remove();
            decrementCommentCount(post);
          }, 300);
        } else {
          showToast(json?.message || 'Failed to delete comment', 'error');
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Delete';
        }
      } catch (err) {
        console.error('[comments.js] Delete failed', err);
        showToast('Failed to delete comment', 'error');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Delete';
      }
    });
  }

  function updateCommentReactionButton(btn, reactionKey) {
    if (!btn) return;
    
    const EMOJIS = [
      {key:'like', emoji:'❤️', label:'Like'},
      {key:'love', emoji:'🥰', label:'Love'},
      {key:'haha', emoji:'😂', label:'Haha'},
      {key:'wow', emoji:'😲', label:'Wow'},
      {key:'sad', emoji:'😢', label:'Sad'},
      {key:'angry', emoji:'😡', label:'Angry'}
    ];
    
    const icon = btn.querySelector('.like-icon');
    const label = btn.querySelector('.reaction-label');
    const emojiBadge = btn.querySelector('.reaction-emoji-active');
    
    if (!label) return;
    
    if (!reactionKey || reactionKey === '') {
      // No reaction
      if (emojiBadge) emojiBadge.remove();
      if (icon) {
        icon.style.display = '';
        icon.setAttribute('fill', 'none');
        icon.setAttribute('stroke', 'currentColor');
        icon.classList.remove('text-red-500');
        icon.classList.add('text-gray-500');
      }
      label.textContent = 'Like';
      label.classList.remove('text-red-500');
      label.classList.add('text-gray-700');
      btn.dataset.userReaction = '';
      return;
    }

    const reaction = EMOJIS.find(e => e.key === reactionKey);
    if (!reaction) return;

    if (reactionKey === 'like') {
      // Like reaction: show red heart icon
      if (emojiBadge) emojiBadge.remove();
      if (icon) {
        icon.style.display = '';
        icon.setAttribute('fill', 'currentColor');
        icon.setAttribute('stroke', 'none');
        icon.classList.remove('text-gray-500');
        icon.classList.add('text-red-500');
      }
      label.classList.remove('text-gray-700');
      label.classList.add('text-red-500');
    } else {
      // Other reactions: hide heart, show emoji badge
      if (icon) icon.style.display = 'none';
      let badge = emojiBadge;
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'reaction-emoji-active text-xl leading-none mr-1';
        btn.insertBefore(badge, label);
      }
      badge.textContent = reaction.emoji;
      label.classList.remove('text-red-500');
      label.classList.add('text-gray-700');
    }
    label.textContent = reaction.label;
    btn.dataset.userReaction = reactionKey;
  }

  function updateCommentReactionCount(btn, counts) {
    if (!btn) {
      console.log('[comments.js] updateCommentReactionCount: btn is null');
      return;
    }
    
    const countEl = btn.querySelector('.reaction-count');
    if (!countEl) {
      console.log('[comments.js] updateCommentReactionCount: .reaction-count element not found');
      return;
    }
    
    console.log('[comments.js] updateCommentReactionCount called with counts:', counts);
    const total = Object.values(counts || {}).reduce((sum, val) => sum + val, 0);
    console.log('[comments.js] Total reactions:', total);
    
    if (total > 0) {
      countEl.textContent = total;
      countEl.dataset.count = total;
      countEl.style.display = 'inline-block'; // Explicitly set to inline-block
      console.log('[comments.js] Showing reaction count:', total, 'display:', countEl.style.display);
    } else {
      countEl.textContent = '0';
      countEl.dataset.count = '0';
      countEl.style.display = 'none';
      console.log('[comments.js] Hiding reaction count (total is 0)');
    }
  }

  function updateCommentReactionSummary(commentEl, counts) {
    const EMOJIS = [
      {key:'like', emoji:'❤️'},
      {key:'love', emoji:'🥰'},
      {key:'haha', emoji:'😂'},
      {key:'wow', emoji:'😲'},
      {key:'sad', emoji:'😢'},
      {key:'angry', emoji:'😡'}
    ];
    
    const summary = commentEl.querySelector('.comment-reaction-summary');
    if (!summary) return;
    
    const emojisEl = summary.querySelector('.comment-reaction-emojis');
    const countEl = summary.querySelector('.comment-reaction-count');
    if (!emojisEl || !countEl) return;
    
    // Calculate total and find top 3
    let total = 0;
    const sorted = Object.entries(counts || {})
      .filter(([k, v]) => v > 0)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 3);
    
    sorted.forEach(([k, v]) => total += v);

    if (total === 0) {
      summary.style.display = 'none';
      emojisEl.textContent = '';
      countEl.textContent = '0';
      return;
    }
    
    summary.style.display = 'block';
    
    // Show top 3 reaction emojis with overlapping effect
    const topEmojis = sorted.map(([key]) => {
      const r = EMOJIS.find(e => e.key === key);
      return r ? r.emoji : '';
    }).filter(e => e);
    
    // Render emojis with overlap and white border
    emojisEl.innerHTML = topEmojis.map((emoji, idx) => 
      `<span class="reaction-emoji" style="display:inline-block;margin-left:${idx > 0 ? '-4px' : '0'};position:relative;z-index:${topEmojis.length - idx};text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white;font-size:14px;">${emoji}</span>`
    ).join('');
    countEl.textContent = total;
  }

  function incrementCommentCount(post) {
    const countEl = post.querySelector('.comments-count');
    if (!countEl) return;
    const current = parseInt(countEl.dataset.count || '0', 10);
    const next = current + 1;
    countEl.dataset.count = String(next);
    countEl.textContent = formatCount(next);
  }

  function decrementCommentCount(post) {
    const countEl = post.querySelector('.comments-count');
    if (!countEl) return;
    const current = parseInt(countEl.dataset.count || '0', 10);
    const next = Math.max(0, current - 1);
    countEl.dataset.count = String(next);
    countEl.textContent = formatCount(next);
  }

  function handleEditComment(event) {
    const editBtn = event.target.closest('.edit-comment-btn');
    if (!editBtn) return;
    
    // Check if already processed this event
    if (event._editProcessed) {
      console.log('[comments.js] Edit event already processed, skipping');
      return;
    }
    event._editProcessed = true;
    event.stopImmediatePropagation();
    
    event.preventDefault();

    const commentItem = editBtn.closest('.comment-item');
    if (!commentItem) return;

    // Check if already in edit mode
    if (commentItem.querySelector('.comment-edit-form')) return;

    const commentId = editBtn.dataset.commentId;
    const contentWrapper = commentItem.querySelector('.comment-content-wrapper');
    const textEl = commentItem.querySelector('.comment-text');
    const attachmentEl = commentItem.querySelector('.comment-attachment');
    
    const currentText = commentItem.dataset.contentText || textEl?.textContent || '';
    const hasAttachment = !!attachmentEl;

    // Create inline edit form
    const editForm = document.createElement('div');
    editForm.className = 'comment-edit-form mt-2';
    editForm.innerHTML = `
      <textarea class="comment-edit-input w-full bg-white rounded-lg px-3 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent resize-none" rows="2" placeholder="Edit your comment...">${escapeHtml(currentText)}</textarea>
      ${hasAttachment ? `
        <div class="comment-edit-attachment mt-2 relative inline-block">
          <div class="text-xs text-gray-500 mb-1">Current attachment:</div>
          ${attachmentEl.innerHTML}
          <button type="button" class="remove-attachment-btn absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors" title="Remove attachment">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      ` : ''}
      <div class="flex items-center gap-2 mt-2">
        <button type="button" class="comment-edit-save bg-blue-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-blue-600 transition-colors flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Save
        </button>
        <button type="button" class="comment-edit-cancel bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-300 transition-colors flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancel
        </button>
      </div>
    `;

    // Hide original content
    if (textEl) textEl.style.display = 'none';
    if (attachmentEl) attachmentEl.style.display = 'none';

    // Insert edit form
    contentWrapper.appendChild(editForm);

    // Focus the textarea
    const textarea = editForm.querySelector('.comment-edit-input');
    if (textarea) {
      textarea.focus();
      textarea.setSelectionRange(textarea.value.length, textarea.value.length);
    }

    // Track if attachment should be removed
    let removeAttachment = false;
    const removeBtn = editForm.querySelector('.remove-attachment-btn');
    if (removeBtn) {
      removeBtn.addEventListener('click', function() {
        const attachmentPreview = editForm.querySelector('.comment-edit-attachment');
        if (attachmentPreview) {
          attachmentPreview.innerHTML = '<div class="text-xs text-red-500 italic">Attachment will be removed on save</div>';
          removeAttachment = true;
        }
      });
    }

    // Handle save
    const saveBtn = editForm.querySelector('.comment-edit-save');
    saveBtn.addEventListener('click', async function() {
      const newText = textarea.value.trim();
      
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<div class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></div>';

      try {
        const response = await fetch('/dashboard/comments/edit', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          body: JSON.stringify({
            comment_id: commentId,
            content_text: newText,
            remove_attachment: removeAttachment
          })
        });

        const json = await response.json();
        if (json && json.success) {
          // Update display
          if (textEl) {
            textEl.textContent = newText;
            textEl.style.display = '';
          }
          commentItem.dataset.contentText = newText;

          // Handle attachment removal
          if (removeAttachment && attachmentEl) {
            attachmentEl.remove();
            commentItem.dataset.attachmentUrl = '';
          } else if (attachmentEl) {
            attachmentEl.style.display = '';
          }

          editForm.remove();
        } else {
          showToast(json?.message || 'Failed to update comment', 'error');
          saveBtn.disabled = false;
          saveBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save';
        }
      } catch (err) {
        console.error('[comments.js] Edit failed', err);
        showToast('Failed to update comment', 'error');
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save';
      }
    });

    // Handle cancel
    const cancelBtn = editForm.querySelector('.comment-edit-cancel');
    cancelBtn.addEventListener('click', function() {
      if (textEl) textEl.style.display = '';
      if (attachmentEl) attachmentEl.style.display = '';
      editForm.remove();
    });
  }

  // Event delegation with logging
  console.log('[comments.js] Setting up event listeners...');
  
  // Expose function for use by other modules (e.g., modals)
  window.updateCommentReactionButton = updateCommentReactionButton;
  window.updateCommentReactionSummary = updateCommentReactionSummary;
  
  // Test listener to see if any clicks are captured
  document.addEventListener('click', function(e) {
    const isCommentBtn = e.target.closest('.comment-btn');
    if (isCommentBtn) {
      console.log('[comments.js] GLOBAL CLICK on comment button detected!');
    }
  }, true); // Use capture phase
  
  // Handle "See more comments" button
  document.addEventListener('click', function(e) {
    const seeMoreBtn = e.target.closest('.see-more-comments-btn');
    if (!seeMoreBtn) return;
    
    e.preventDefault();
    const thread = seeMoreBtn.closest('.comment-thread');
    if (!thread) return;
    
    const post = thread.closest('.post');
    if (!post) return;
    
    try {
      const allComments = JSON.parse(thread.dataset.allComments || '[]');
      const visibleCount = parseInt(thread.dataset.visibleCount || '4', 10);
      
      // Show next 4 comments
      const nextBatch = allComments.slice(visibleCount, visibleCount + 4);
      
      // Remove the see more button temporarily
      seeMoreBtn.remove();
      
      // Render the next batch
      nextBatch.forEach(comment => {
        renderComment(thread, comment, post);
      });
      
      // Update visible count
      const newVisibleCount = visibleCount + nextBatch.length;
      thread.dataset.visibleCount = newVisibleCount;
      
      // Re-add see more button if there are still more comments
      if (newVisibleCount < allComments.length) {
        const newSeeMoreBtn = document.createElement('button');
        newSeeMoreBtn.className = 'see-more-comments-btn w-full text-gray-600 hover:text-blue-600 text-sm font-medium py-2 hover:bg-gray-50 rounded-lg transition-colors mt-2';
        newSeeMoreBtn.textContent = `See more comments (${allComments.length - newVisibleCount} remaining)`;
        thread.appendChild(newSeeMoreBtn);
      }
    } catch (err) {
      console.error('[comments.js] Failed to load more comments:', err);
    }
  });
  
  document.addEventListener('click', handleCommentButtonClick);
  document.addEventListener('click', handleCommentSubmit);
  document.addEventListener('click', handleDeleteComment);
  document.addEventListener('click', handleEditComment);
  
  console.log('[comments.js] Module fully initialized');
})();
