// reactions.js — Enhanced reaction system with hover/long-press, fill states, and compact number formatting
(function(){
  const csrfToken = window.csrfToken || document.querySelector('meta[name="csrfToken"]')?.content || '';
  const EMOJIS = [
    {key:'like', emoji:'❤️', src:'/assets/reactions/like.webm', label:'Like', color:'#f91880'},
    {key:'love', emoji:'🥰', src:'/assets/reactions/loveit.webm', label:'Love', color:'#f33e5b'},
    {key:'haha', emoji:'😂', src:'/assets/reactions/laugh.webm', label:'Haha', color:'#f7b125'},
    {key:'wow', emoji:'😲', src:'/assets/reactions/wow.webm', label:'Wow', color:'#f7b125'},
    {key:'sad', emoji:'😢', src:'/assets/reactions/sad.webm', label:'Sad', color:'#5890ff'},
    {key:'angry', emoji:'😡', src:'/assets/reactions/angry.webm', label:'Angry', color:'#e9710f'}
  ];
  const HEART_SVG = '<svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>';

  // Format numbers: 120, 1k, 120k, 2.1m
  function formatCount(n) {
    if (n < 1000) return n.toString();
    if (n < 1000000) return (n / 1000).toFixed(n % 1000 === 0 ? 0 : 1).replace(/\.0$/, '') + 'k';
    return (n / 1000000).toFixed(n % 1000000 === 0 ? 0 : 1).replace(/\.0$/, '') + 'm';
  }

  function ensureHeartIcon(btn) {
    let icon = btn.querySelector('.like-icon');
    if (!icon) {
      btn.insertAdjacentHTML('afterbegin', HEART_SVG);
      icon = btn.querySelector('.like-icon');
    }
    return icon;
  }

  // Update reaction button state based on user's reaction
  function updateButtonState(btn, reactionKey) {
    const icon = ensureHeartIcon(btn);
    const label = btn.querySelector('.reaction-label');
    if (!label) return;
    const emojiBadge = btn.querySelector('.reaction-emoji-active');

    if (!reactionKey || reactionKey === '') {
      if (emojiBadge) emojiBadge.remove();
      icon.style.display = '';
      icon.setAttribute('fill', 'none');
      icon.setAttribute('stroke', 'currentColor');
      icon.classList.remove('text-red-500');
      icon.classList.add('text-gray-500');
      label.textContent = 'Like';
      label.classList.remove('text-red-500');
      label.classList.add('text-gray-700');
      btn.dataset.userReaction = '';
      return;
    }

    const reaction = EMOJIS.find(e => e.key === reactionKey);
    if (!reaction) return;

    if (reactionKey === 'like') {
      if (emojiBadge) emojiBadge.remove();
      icon.style.display = '';
      icon.setAttribute('fill', 'currentColor');
      icon.setAttribute('stroke', 'none');
      icon.classList.remove('text-gray-500');
      icon.classList.add('text-red-500');
      label.classList.remove('text-gray-700');
      label.classList.add('text-red-500');
    } else {
      icon.style.display = 'none';
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

  // Update reaction summary display
  function updateReactionSummary(postEl, counts, userReaction) {
    const summary = postEl.querySelector('.reaction-summary');
    if (!summary) return;
    
    const emojisEl = summary.querySelector('.reaction-emojis');
    const countEl = summary.querySelector('.reaction-count');
    if (!emojisEl || !countEl) return;
    
    // Ensure emojis container has flex display for overlapping
    emojisEl.style.cssText = 'display:flex;align-items:center';

    // Calculate total and find top 3
    let total = 0;
    const sorted = Object.entries(counts || {})
      .filter(([k, v]) => v > 0) // Only include reactions with count > 0
      .sort((a, b) => b[1] - a[1])
      .slice(0, 3);
    
    sorted.forEach(([k, v]) => total += v);

    if (total === 0) {
      summary.style.display = 'none';
      emojisEl.textContent = '';
      countEl.textContent = '0';
      return;
    }
    
    summary.style.display = 'flex';
    summary.dataset.total = total;
    
    // Show top 3 reaction emojis with overlapping effect
    const topEmojis = sorted.map(([key]) => {
      const r = EMOJIS.find(e => e.key === key);
      return r ? r.emoji : '';
    }).filter(e => e);
    
    // Render emojis with overlap and white border
    emojisEl.innerHTML = topEmojis.map((emoji, idx) => 
      `<span class="reaction-emoji" style="display:inline-block;margin-left:${idx > 0 ? '-4px' : '0'};position:relative;z-index:${topEmojis.length - idx};text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white">${emoji}</span>`
    ).join('');
    countEl.textContent = formatCount(total);
  }

  function createPicker() {
    const wrap = document.createElement('div');
    wrap.className = 'reaction-picker';
    wrap.style.cssText = 'position:fixed;visibility:hidden;z-index:9999;background:#fff;border-radius:50px;box-shadow:0 8px 24px rgba(0,0,0,0.2);padding:12px 16px;display:flex;align-items:center;gap:10px;border:1px solid #e5e7eb;pointer-events:auto';
    
    // Disable right-click on entire picker
    wrap.addEventListener('contextmenu', (e) => e.preventDefault());
    
    EMOJIS.forEach(e => {
      const b = document.createElement('div');
      b.dataset.reactionKey = e.key;
      b.style.cssText = 'display:flex;flex-direction:column;align-items:center;cursor:pointer;padding:8px;border-radius:12px;transition:all 0.15s ease;position:relative';
      
      const vid = document.createElement('video');
      vid.src = e.src;
      vid.width = 40; vid.height = 40; vid.muted = true; vid.loop = true; vid.playsInline = true;
      vid.style.cssText = 'transition:transform 0.15s ease;margin-bottom:4px;display:block;pointer-events:none'; // prevent video controls and interactions
      vid.autoplay = false; // Start paused, play when picker opens
      
      const label = document.createElement('div');
      label.textContent = e.label;
      label.style.cssText = 'font-size:11px;color:#6b7280;font-weight:600;white-space:nowrap';
      
      b.appendChild(vid);
      b.appendChild(label);
      
      b.addEventListener('mouseenter', ()=> {
        vid.style.transform = 'scale(1.3)';
        b.style.background = '#f3f4f6';
      });
      b.addEventListener('mouseleave', ()=> {
        vid.style.transform = 'scale(1)';
        b.style.background = 'transparent';
      });
      
      wrap.appendChild(b);
    });
    document.body.appendChild(wrap);
    return wrap;
  }

  function hidePicker(picker) {
    picker.style.visibility = 'hidden';
    picker.style.pointerEvents = 'none';
    // Pause all videos
    picker.querySelectorAll('video').forEach(v => { v.pause(); v.currentTime = 0; });
  }

  function showPicker(picker, x, y, element, btn) {
    picker.style.left = x + 'px';
    picker.style.top = y + 'px';
    picker.style.visibility = 'visible';
    picker.style.pointerEvents = 'auto';
    picker._element = element;
    picker._btn = btn;
    // Play all videos when picker opens
    picker.querySelectorAll('video').forEach(v => v.play());
  }

  function performReaction(element, reactionKey, btn) {
    // Check if it's a comment or post
    const isComment = element.classList.contains('comment-item');
    const targetType = isComment ? 'comment' : 'post';
    const targetId = isComment ? element.dataset.commentId : element.dataset.postId;
    
    if (!btn) {
      btn = element.querySelector(isComment ? '.comment-reaction-btn' : '.reaction-btn');
    }
    if (!btn || !targetId) return;

    const currentReaction = btn.dataset.userReaction || '';
    const togglingOff = currentReaction === reactionKey;

    updateButtonState(btn, togglingOff ? '' : reactionKey);

    console.log('[reactions.js] Performing reaction:', {
      targetType,
      targetId,
      reactionKey,
      currentReaction,
      togglingOff
    });
    
    fetch('/dashboard/posts/react', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ 
        target_type: targetType, 
        target_id: targetId, 
        reaction_type: reactionKey 
      })
    })
    .then(r => {
      console.log('[reactions.js] Response status:', r.status);
      return r.json();
    })
    .then(json => {
      console.log('[reactions.js] Response data:', json);
      if (json && json.success) {
        // Update button state with server response
        updateButtonState(btn, json.user_reaction || '');
        // Update reaction summary for posts
        if (!isComment) {
          updateReactionSummary(element, json.counts || {}, json.user_reaction);
        } else {
          // Update reaction count for comments
          updateCommentReactionCount(btn, json.counts || {});
        }
      } else {
        console.error('[reactions.js] Reaction failed:', json);
        alert('Failed to save reaction: ' + (json.error || json.message || 'Unknown error'));
        updateButtonState(btn, currentReaction);
      }
    })
    .catch(err => {
      console.error('[reactions.js] Reaction error:', err);
      alert('Error sending reaction: ' + err.message);
      updateButtonState(btn, currentReaction);
    });
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

  // Initialize reaction buttons on page load
  function initializeReactionButtons() {
    console.log('[reactions.js] Initializing reaction buttons on page load');
    document.querySelectorAll('.post').forEach(post => {
      const btn = post.querySelector('.reaction-btn');
      if (!btn) return;
      
      const userReaction = btn.dataset.userReaction || '';
      if (userReaction) {
        console.log('[reactions.js] Setting initial state for post', post.dataset.postId, ':', userReaction);
        updateButtonState(btn, userReaction);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const picker = createPicker();
    
    // Initialize existing posts
    initializeReactionButtons();
    let hoverTimer = null;
    let longPressTimer = null;
    let isDragging = false;

    // Desktop: hover to show picker after 800ms (for both posts and comments)
    document.body.addEventListener('mouseover', (e) => {
      const btn = e.target.closest('.reaction-btn, .comment-reaction-btn');
      if (btn && !('ontouchstart' in window)) {
        const postOrComment = btn.closest('.post, .comment-item');
        if (!postOrComment) return;
        
        hoverTimer = setTimeout(() => {
          const rect = btn.getBoundingClientRect();
          const x = rect.left + (rect.width / 2) - 250;
          const y = rect.top - 90;
          showPicker(picker, Math.max(10, x), Math.max(10, y), postOrComment, btn);
        }, 800);
      }
    });

    document.body.addEventListener('mouseout', (e) => {
      const btn = e.target.closest('.reaction-btn, .comment-reaction-btn');
      if (btn) clearTimeout(hoverTimer);
    });

    // Desktop: click reaction in picker
    picker.addEventListener('click', (e) => {
      const reactionBtn = e.target.closest('[data-reaction-key]');
      if (reactionBtn && picker._element) {
        const key = reactionBtn.dataset.reactionKey;
        performReaction(picker._element, key, picker._btn);
        hidePicker(picker);
      }
    });

    // Desktop: quick like on button click (when picker not visible)
    document.body.addEventListener('click', (e) => {
      const btn = e.target.closest('.reaction-btn, .comment-reaction-btn');
      if (btn && picker.style.visibility === 'hidden') {
        const element = btn.closest('.post, .comment-item');
        if (element) performReaction(element, 'like', btn);
      }
      
      // Hide picker if clicking outside
      if (!e.target.closest('.reaction-picker') && !e.target.closest('.reaction-btn, .comment-reaction-btn')) {
        hidePicker(picker);
      }
    });

    // Mobile: long-press to show picker
    document.body.addEventListener('touchstart', (e) => {
      const btn = e.target.closest('.reaction-btn, .comment-reaction-btn');
      if (btn) {
        const element = btn.closest('.post, .comment-item');
        if (!element) return;
        
        longPressTimer = setTimeout(() => {
          const rect = btn.getBoundingClientRect();
          const x = rect.left + (rect.width / 2) - 250;
          const y = rect.top - 90;
          showPicker(picker, Math.max(10, x), Math.max(10, y), element, btn);
          isDragging = true;
        }, 500);
      }
    }, { passive: true });

    document.body.addEventListener('touchend', (e) => {
      clearTimeout(longPressTimer);
      
      if (isDragging) {
        isDragging = false;
        // Check if touch ended on a reaction
        const touch = e.changedTouches[0];
        const el = document.elementFromPoint(touch.clientX, touch.clientY);
        const reactionBtn = el && el.closest('[data-reaction-key]');
        if (reactionBtn && picker._element) {
          const key = reactionBtn.dataset.reactionKey;
          performReaction(picker._element, key, picker._btn);
        }
        hidePicker(picker);
      } else {
        // Quick tap = like
        const btn = e.target.closest('.reaction-btn, .comment-reaction-btn');
        if (btn && picker.style.visibility === 'hidden') {
          const element = btn.closest('.post, .comment-item');
          if (element) performReaction(element, 'like', btn);
        }
      }
    }, { passive: true });

    document.body.addEventListener('touchcancel', () => {
      clearTimeout(longPressTimer);
      isDragging = false;
      hidePicker(picker);
    }, { passive: true });

    // Mobile: drag to select reaction
    document.body.addEventListener('touchmove', (e) => {
      if (isDragging && picker.style.visibility === 'visible') {
        const touch = e.touches[0];
        const el = document.elementFromPoint(touch.clientX, touch.clientY);
        const reactionBtn = el && el.closest('[data-reaction-key]');
        
        // Highlight hovered reaction
        picker.querySelectorAll('[data-reaction-key]').forEach(b => {
          const vid = b.querySelector('video');
          if (b === reactionBtn) {
            vid.style.transform = 'scale(1.3)';
            b.style.background = '#f3f4f6';
          } else {
            vid.style.transform = 'scale(1)';
            b.style.background = 'transparent';
          }
        });
      }
    }, { passive: true });

    // Hide picker when hovering away from picker area (desktop)
    picker.addEventListener('mouseleave', () => {
      setTimeout(() => hidePicker(picker), 200);
    });
  });
})();
