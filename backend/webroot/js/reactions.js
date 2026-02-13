// reactions.js — Enhanced reaction system with hover/long-press, fill states, and compact number formatting
(function(){
  const EMOJIS = [
    {key:'like', emoji:'❤️', src:'/assets/reactions/like.webm', label:'Like', color:'#f91880'},
    {key:'love', emoji:'🥰', src:'/assets/reactions/loveit.webm', label:'Love', color:'#f33e5b'},
    {key:'haha', emoji:'😂', src:'/assets/reactions/laugh.webm', label:'Haha', color:'#f7b125'},
    {key:'wow', emoji:'😲', src:'/assets/reactions/wow.webm', label:'Wow', color:'#f7b125'},
    {key:'sad', emoji:'😢', src:'/assets/reactions/sad.webm', label:'Sad', color:'#5890ff'},
    {key:'angry', emoji:'😡', src:'/assets/reactions/angry.webm', label:'Angry', color:'#e9710f'}
  ];

  // Format numbers: 120, 1k, 120k, 2.1m
  function formatCount(n) {
    if (n < 1000) return n.toString();
    if (n < 1000000) return (n / 1000).toFixed(n % 1000 === 0 ? 0 : 1).replace(/\.0$/, '') + 'k';
    return (n / 1000000).toFixed(n % 1000000 === 0 ? 0 : 1).replace(/\.0$/, '') + 'm';
  }

  // Update reaction button state based on user's reaction
  function updateButtonState(btn, reactionKey) {
    const icon = btn.querySelector('.like-icon');
    const label = btn.querySelector('.reaction-label');
    if (!icon || !label) return;

    if (!reactionKey || reactionKey === '') {
      // No reaction - show outline heart
      icon.setAttribute('fill', 'none');
      icon.setAttribute('stroke', 'currentColor');
      icon.classList.remove('text-red-500');
      icon.classList.add('text-gray-500');
      label.textContent = 'Like';
      label.classList.remove('text-red-500');
      label.classList.add('text-gray-700');
      btn.dataset.userReaction = '';
    } else {
      const reaction = EMOJIS.find(e => e.key === reactionKey);
      if (reaction) {
        // Show filled/colored state based on reaction type
        if (reactionKey === 'like') {
          // Red filled heart for Like
          icon.setAttribute('fill', 'currentColor');
          icon.setAttribute('stroke', 'none');
          icon.classList.remove('text-gray-500');
          icon.classList.add('text-red-500');
          label.classList.remove('text-gray-700');
          label.classList.add('text-red-500');
        } else {
          // Replace with emoji for other reactions
          icon.outerHTML = `<span class="text-xl leading-none">${reaction.emoji}</span>`;
        }
        label.textContent = reaction.label;
        btn.dataset.userReaction = reactionKey;
      }
    }
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

  function showPicker(picker, x, y, postEl) {
    picker.style.left = x + 'px';
    picker.style.top = y + 'px';
    picker.style.visibility = 'visible';
    picker.style.pointerEvents = 'auto';
    picker._postEl = postEl;
    // Play all videos when picker opens
    picker.querySelectorAll('video').forEach(v => v.play());
  }

  function performReaction(postEl, reactionKey) {
    const postId = postEl.dataset.postId;
    const btn = postEl.querySelector('.reaction-btn');
    if (!btn) return;

    const currentReaction = btn.dataset.userReaction || '';
    
    // Toggle: if same reaction, remove it
    const finalReaction = (currentReaction === reactionKey) ? '' : reactionKey;

    // Optimistic update for button
    updateButtonState(btn, finalReaction);

    // Send to server
    if (!postId) return;
    
    fetch('/dashboard/posts/react', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        target_type: 'post', 
        target_id: postId, 
        reaction_type: finalReaction 
      })
    })
    .then(r => r.json())
    .then(json => {
      if (json && json.success) {
        // Update button state with server response
        updateButtonState(btn, json.user_reaction || '');
        // Update reaction summary with counts from server
        updateReactionSummary(postEl, json.counts || {}, json.user_reaction);
      }
    })
    .catch(() => {
      // On error, revert optimistic update
      updateButtonState(btn, currentReaction);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const picker = createPicker();
    let hoverTimer = null;
    let longPressTimer = null;
    let isDragging = false;

    // Desktop: hover to show picker after 800ms
    document.body.addEventListener('mouseover', (e) => {
      const btn = e.target.closest('.reaction-btn');
      if (btn && !('ontouchstart' in window)) {
        const postEl = btn.closest('.post');
        if (!postEl) return;
        hoverTimer = setTimeout(() => {
          const rect = btn.getBoundingClientRect();
          const x = rect.left + (rect.width / 2) - 250;
          const y = rect.top - 90;
          showPicker(picker, Math.max(10, x), Math.max(10, y), postEl);
        }, 800);
      }
    });

    document.body.addEventListener('mouseout', (e) => {
      const btn = e.target.closest('.reaction-btn');
      if (btn) clearTimeout(hoverTimer);
    });

    // Desktop: click reaction in picker
    picker.addEventListener('click', (e) => {
      const reactionBtn = e.target.closest('[data-reaction-key]');
      if (reactionBtn && picker._postEl) {
        const key = reactionBtn.dataset.reactionKey;
        performReaction(picker._postEl, key);
        hidePicker(picker);
      }
    });

    // Desktop: quick like on button click (when picker not visible)
    document.body.addEventListener('click', (e) => {
      const btn = e.target.closest('.reaction-btn');
      if (btn && picker.style.visibility === 'hidden') {
        const postEl = btn.closest('.post');
        if (postEl) performReaction(postEl, 'like');
      }
      
      // Hide picker if clicking outside
      if (!e.target.closest('.reaction-picker') && !e.target.closest('.reaction-btn')) {
        hidePicker(picker);
      }
    });

    // Mobile: long-press to show picker
    document.body.addEventListener('touchstart', (e) => {
      const btn = e.target.closest('.reaction-btn');
      if (btn) {
        const postEl = btn.closest('.post');
        if (!postEl) return;
        
        longPressTimer = setTimeout(() => {
          const rect = btn.getBoundingClientRect();
          const x = rect.left + (rect.width / 2) - 250;
          const y = rect.top - 90;
          showPicker(picker, Math.max(10, x), Math.max(10, y), postEl);
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
        if (reactionBtn && picker._postEl) {
          const key = reactionBtn.dataset.reactionKey;
          performReaction(picker._postEl, key);
        }
        hidePicker(picker);
      } else {
        // Quick tap = like
        const btn = e.target.closest('.reaction-btn');
        if (btn && picker.style.visibility === 'hidden') {
          const postEl = btn.closest('.post');
          if (postEl) performReaction(postEl, 'like');
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
