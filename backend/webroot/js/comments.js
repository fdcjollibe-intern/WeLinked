// comments.js — inline comment composer with attachments, delete, and time formatting
(function(){
  const csrfToken = window.csrfToken || document.querySelector('meta[name="csrfToken"]')?.content || '';

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
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
      const isoString = datetime.includes('T') ? datetime : datetime.replace(' ', 'T');
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
    if (!btn) return;
    event.preventDefault();
    const post = btn.closest('.post');
    if (!post) return;
    toggleComposer(post);
  }

  function toggleComposer(post) {
    let composer = post.querySelector('.comment-composer');
    if (!composer) {
      composer = buildComposer(post);
      post.appendChild(composer);
      loadExistingComments(post);
    }
    if (composer.classList.contains('hidden')) {
      composer.classList.remove('hidden');
      composer.querySelector('.comment-input')?.focus();
    } else {
      composer.classList.add('hidden');
    }
  }

  function buildComposer(post) {
    const wrap = document.createElement('div');
    wrap.className = 'comment-composer mt-4 border-t border-gray-100 pt-3 hidden';
    wrap.innerHTML = `
      <div class="comment-thread space-y-3 mb-3"></div>
      <div class="comment-input-wrapper">
        <div id="comment-attachment-preview-${post.dataset.postId}" class="mb-2 hidden"></div>
        <div class="flex items-center gap-2">
          <label class="cursor-pointer inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors flex-shrink-0">
            <input type="file" class="comment-attachment-input hidden" accept="image/*,video/*">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </label>
          <textarea class="comment-input flex-1 bg-gray-50 rounded-xl px-3 py-2 text-sm border border-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-400" rows="1" placeholder="Write a comment..."></textarea>
          <button class="comment-submit bg-blue-500 text-white w-10 h-10 rounded-lg text-sm font-semibold flex items-center justify-center hover:bg-blue-600 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          alert('Please select an image or video file.');
          attachmentInput.value = '';
          return;
        }
        if (file.size > 50 * 1024 * 1024) { // 50MB limit for comments
          alert('File too large. Maximum 50MB for comment attachments.');
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
    .then(r => r.json())
    .then(json => {
      if (!json || !json.success) {
        thread.innerHTML = '';
        return;
      }
      thread.innerHTML = '';
      const comments = json.comments || [];
      if (comments.length === 0) {
        thread.innerHTML = '<div class="text-center py-2 text-gray-400 text-sm">No comments yet. Be the first!</div>';
        return;
      }
      comments.forEach(comment => renderComment(thread, comment, post));
    })
    .catch(err => {
      console.error('[comments.js] Failed to load comments', err);
      thread.innerHTML = '<div class="text-center py-2 text-red-400 text-sm">Failed to load comments</div>';
    });
  }

  function renderComment(thread, comment, post) {
    const user = comment?.user || {};
    const currentUserId = window.currentUserId || null;
    const canDelete = currentUserId && (currentUserId === comment.user_id || currentUserId === parseInt(post.dataset.userId));
    
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
    item.innerHTML = `
      <a href="${escapeHtml(profileUrl)}" class="w-9 h-9 rounded-full overflow-hidden bg-blue-500 text-white flex items-center justify-center font-semibold flex-shrink-0 hover:opacity-80 transition-opacity">
        ${profilePhoto ? `<img src="${escapeHtml(profilePhoto)}" alt="${escapeHtml(fullName)}" class="w-full h-full object-cover">` : escapeHtml(initial)}
      </a>
      <div class="flex-1 min-w-0">
        <div class="bg-gray-50 rounded-2xl px-3 py-2 inline-block max-w-full">
          <div class="mb-1">
            <a href="${escapeHtml(profileUrl)}" class="text-sm font-semibold text-gray-900 hover:underline">${escapeHtml(fullName)}</a>
            <span class="text-xs text-gray-400 ml-1">@${escapeHtml(username)}</span>
          </div>
          <p class="text-xs text-gray-500 mb-2">${createdAt}</p>
          <p class="text-sm text-gray-700 break-words">${escapeHtml(comment.content_text || '')}</p>
          ${comment.attachment_url ? `
            <div class="mt-2">
              ${comment.attachment_url.match(/\.(jpg|jpeg|png|gif|webp)$/i) 
                ? `<img src="${escapeHtml(comment.attachment_url)}" class="max-h-64 rounded-lg">`
                : `<video src="${escapeHtml(comment.attachment_url)}" controls class="max-h-64 rounded-lg"></video>`
              }
            </div>
          ` : ''}
        </div>
        <div class="flex items-center gap-4 mt-2 px-2">
          <button class="comment-reaction-btn flex items-center gap-1.5 text-gray-500 hover:bg-gray-100 rounded-lg px-2 py-1 transition-colors text-sm" data-comment-id="${comment.id}" data-user-reaction="" data-target-type="comment" data-target-id="${comment.id}">
            <svg class="like-icon w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span class="reaction-label text-xs font-medium">Like</span>
            <span class="reaction-count text-xs" data-count="0" style="display:none">0</span>
          </button>
          ${canDelete ? `<button class="delete-comment-btn text-red-500 hover:text-red-600 transition-colors p-1" data-comment-id="${comment.id}" title="Delete comment">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>` : ''}
        </div>
      </div>
    `;
    thread.appendChild(item);
    
    // Initialize reaction button state if comment has reactions
    if (comment.user_reaction || comment.reaction_counts) {
      const reactionBtn = item.querySelector('.comment-reaction-btn');
      if (reactionBtn && comment.user_reaction) {
        updateCommentReactionButton(reactionBtn, comment.user_reaction);
      }
      if (comment.reaction_counts) {
        updateCommentReactionCount(reactionBtn, comment.reaction_counts);
      }
    }
  }

  function handleCommentSubmit(event) {
    const submitBtn = event.target.closest('.comment-submit');
    if (!submitBtn) return;
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
      alert('Please write a comment or attach a file.');
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
      alert('Unable to identify post for comment.');
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
        alert(json?.message || 'Failed to add comment');
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
      alert('Unable to add comment right now.');
      return null;
    }
  }

  function handleDeleteComment(event) {
    const deleteBtn = event.target.closest('.delete-comment-btn');
    if (!deleteBtn) return;
    event.preventDefault();

    if (!confirm('Delete this comment?')) return;

    const commentId = deleteBtn.dataset.commentId;
    const commentItem = deleteBtn.closest('.comment-item');
    const post = deleteBtn.closest('.post');

    if (!commentId || !commentItem) return;

    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';

    fetch('/dashboard/comments/delete', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ comment_id: commentId })
    })
    .then(r => r.json())
    .then(json => {
      if (json && json.success) {
        commentItem.remove();
        decrementCommentCount(post);
      } else {
        alert(json?.message || 'Failed to delete comment');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
      }
    })
    .catch(err => {
      console.error('[comments.js] Delete failed', err);
      alert('Failed to delete comment');
      deleteBtn.disabled = false;
      deleteBtn.textContent = 'Delete';
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
    if (!btn) return;
    
    const countEl = btn.querySelector('.reaction-count');
    if (!countEl) return;
    
    const total = Object.values(counts).reduce((sum, val) => sum + val, 0);
    
    if (total > 0) {
      countEl.textContent = total;
      countEl.dataset.count = total;
      countEl.style.display = '';
    } else {
      countEl.textContent = '0';
      countEl.dataset.count = '0';
      countEl.style.display = 'none';
    }
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

  // Event delegation
  document.addEventListener('click', handleCommentButtonClick);
  document.addEventListener('click', handleCommentSubmit);
  document.addEventListener('click', handleDeleteComment);
})();
