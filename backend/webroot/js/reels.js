/**
 * Reels JavaScript
 * Handles auto-play, infinite scroll, preloading, and interactions for Reels
 */

let currentReelIndex = 0;
let isLoadingMore = false;
let hasMoreReels = true;
let preloadedVideos = new Set();

/**
 * Initialize Reels functionality
 */
function initializeReels() {
  console.log('[reels.js] Initializing Reels');
  
  // Set up Intersection Observer for auto-play
  setupIntersectionObserver();
  
  // Set up infinite scroll
  setupInfiniteScroll();
  
  // Set up event listeners
  setupEventListeners();
  
  // Preload first few videos
  preloadNextVideos();
}

/**
 * Set up Intersection Observer to auto-play/pause videos
 */
function setupIntersectionObserver() {
  const options = {
    root: document.querySelector('#reels-feed'),
    threshold: 0.5 // Video is considered "in view" when 50% visible
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      const video = entry.target.querySelector('video');
      if (!video) return;
      
      if (entry.isIntersecting) {
        // Video is in view - play it
        console.log('[reels.js] 🎬 Playing video:', video.src);
        video.play().catch(err => {
          console.error('[reels.js] ❌ Error playing video:', err);
        });
        
        // Update current index
        const reelItems = Array.from(document.querySelectorAll('.reel-item'));
        currentReelIndex = reelItems.indexOf(entry.target);
        
        // Preload next videos
        preloadNextVideos();
      } else {
        // Video is out of view - pause it
        console.log('[reels.js] ⏸️ Pausing video:', video.src);
        video.pause();
      }
    });
  }, options);
  
  // Observe all reel items
  document.querySelectorAll('.reel-item').forEach(item => {
    observer.observe(item);
  });
  
  // Store observer for later use when adding new reels
  window.reelsObserver = observer;
}

/**
 * Set up infinite scroll to load more reels
 */
function setupInfiniteScroll() {
  const feed = document.querySelector('#reels-feed');
  
  feed.addEventListener('scroll', () => {
    const reelItems = document.querySelectorAll('.reel-item');
    if (reelItems.length === 0) return;
    
    const secondToLast = reelItems[reelItems.length - 2];
    if (!secondToLast) return;
    
    const rect = secondToLast.getBoundingClientRect();
    const isVisible = rect.top >= 0 && rect.bottom <= window.innerHeight;
    
    if (isVisible && !isLoadingMore && hasMoreReels) {
      loadMoreReels();
    }
  });
}

/**
 * Load more reels via AJAX
 */
async function loadMoreReels() {
  if (isLoadingMore || !hasMoreReels) return;
  
  isLoadingMore = true;
  console.log('[reels.js] 📥 Loading more reels...');
  
  // Show loading indicator
  const loadingEl = document.querySelector('#reels-loading');
  loadingEl.classList.remove('hidden');
  
  try {
    const reelCount = document.querySelectorAll('.reel-item').length;
    const response = await fetch(`/reels?start=${reelCount}&limit=5`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    const data = await response.json();
    
    if (data.success && data.reels && data.reels.length > 0) {
      console.log('[reels.js] ✅ Loaded', data.reels.length, 'more reels');
      
      // Append new reels to feed
      const feed = document.querySelector('#reels-feed');
      data.reels.forEach(reel => {
        const reelHtml = createReelElement(reel);
        feed.insertAdjacentHTML('beforeend', reelHtml);
      });
      
      // Observe new reel items
      const newReels = feed.querySelectorAll('.reel-item:not([data-observed])');
      newReels.forEach(item => {
        item.setAttribute('data-observed', 'true');
        window.reelsObserver.observe(item);
      });
      
      // If we got fewer than requested, we've reached the end
      if (data.reels.length < 5) {
        hasMoreReels = false;
        console.log('[reels.js] 📭 No more reels to load');
      }
    } else {
      hasMoreReels = false;
      console.log('[reels.js] 📭 No more reels available');
    }
  } catch (err) {
    console.error('[reels.js] ❌ Error loading more reels:', err);
  } finally {
    isLoadingMore = false;
    loadingEl.classList.add('hidden');
  }
}

/**
 * Create HTML for a single reel element
 */
function createReelElement(reel) {
  const userInitial = reel.user.full_name ? reel.user.full_name.charAt(0).toUpperCase() : '?';
  const profilePhoto = reel.user.profile_photo_path 
    ? `<img src="${escapeHtml(reel.user.profile_photo_path)}" alt="${escapeHtml(reel.user.full_name)}" class="w-12 h-12 rounded-full border-2 border-white">`
    : `<div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold border-2 border-white">${userInitial}</div>`;
  
  const userReactionFill = reel.user_reaction ? 'fill-red-500' : 'fill-none';
  const caption = reel.content_text ? `
    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
      <p class="text-white text-sm line-clamp-3">${escapeHtml(reel.content_text)}</p>
      ${reel.location ? `
        <p class="text-gray-300 text-xs mt-1">
          <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          ${escapeHtml(reel.location)}
        </p>
      ` : ''}
    </div>
  ` : '';
  
  return `
    <div class="reel-item snap-start h-screen w-full relative flex items-center justify-center" 
         data-reel-id="${reel.id}" 
         data-video-url="${escapeHtml(reel.video_url)}">
      
      <video 
        class="reel-video absolute inset-0 w-full h-full object-contain bg-black"
        src="${escapeHtml(reel.video_url)}"
        loop
        playsinline
        muted
      ></video>
      
      <div class="absolute right-4 bottom-20 flex flex-col gap-4 z-10">
        <a href="/profile/${escapeHtml(reel.user.username)}" class="flex flex-col items-center gap-1">
          ${profilePhoto}
          <span class="text-white text-xs font-semibold">${escapeHtml(reel.user.username)}</span>
        </a>
        
        <button class="reel-reaction-btn w-14 h-14 rounded-full bg-gray-800 bg-opacity-75 hover:bg-opacity-100 flex flex-col items-center justify-center text-white transition-all hover:scale-110"
                data-post-id="${reel.id}"
                data-user-reaction="${reel.user_reaction || ''}">
          <svg class="w-7 h-7 ${userReactionFill}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
          <span class="text-xs mt-1">${reel.total_reactions || 0}</span>
        </button>
        
        <button class="reel-comment-btn w-14 h-14 rounded-full bg-gray-800 bg-opacity-75 hover:bg-opacity-100 flex flex-col items-center justify-center text-white transition-all hover:scale-110"
                data-post-id="${reel.id}">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          <span class="text-xs mt-1">${reel.comment_count || 0}</span>
        </button>
        
        <button class="reel-share-btn w-14 h-14 rounded-full bg-gray-800 bg-opacity-75 hover:bg-opacity-100 flex items-center justify-center text-white transition-all hover:scale-110">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
          </svg>
        </button>
      </div>
      
      ${caption}
    </div>
  `;
}

/**
 * Preload next 3 videos for smooth playback
 */
function preloadNextVideos() {
  const reelItems = Array.from(document.querySelectorAll('.reel-item'));
  const startIndex = currentReelIndex + 1;
  const endIndex = Math.min(startIndex + 3, reelItems.length);
  
  for (let i = startIndex; i < endIndex; i++) {
    const videoUrl = reelItems[i].dataset.videoUrl;
    if (videoUrl && !preloadedVideos.has(videoUrl)) {
      console.log('[reels.js] 📦 Preloading video:', videoUrl);
      const video = document.createElement('video');
      video.src = videoUrl;
      video.preload = 'auto';
      preloadedVideos.add(videoUrl);
    }
  }
}

/**
 * Set up event listeners for buttons
 */
function setupEventListeners() {
  // Close button - go back to dashboard
  document.querySelector('#close-reels')?.addEventListener('click', () => {
    window.location.href = '/dashboard';
  });
  
  // Reaction button - use event delegation
  document.querySelector('#reels-feed').addEventListener('click', async (e) => {
    const reactionBtn = e.target.closest('.reel-reaction-btn');
    if (reactionBtn) {
      await handleReactionClick(reactionBtn);
    }
    
    const commentBtn = e.target.closest('.reel-comment-btn');
    if (commentBtn) {
      handleCommentClick(commentBtn);
    }
    
    const shareBtn = e.target.closest('.reel-share-btn');
    if (shareBtn) {
      handleShareClick(shareBtn);
    }
  });
}

/**
 * Handle reaction button click
 */
async function handleReactionClick(btn) {
  const postId = btn.dataset.postId;
  const currentReaction = btn.dataset.userReaction;
  
  console.log('[reels.js] 💖 Toggling reaction for post:', postId);
  
  try {
    const response = await fetch('/dashboard/posts/react', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        post_id: parseInt(postId),
        reaction_type: currentReaction ? null : 'like' // Toggle: remove if exists, add 'like' if doesn't
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Update UI
      const svg = btn.querySelector('svg');
      const countSpan = btn.querySelector('span');
      
      if (data.action === 'added') {
        svg.classList.add('fill-red-500');
        svg.classList.remove('fill-none');
        btn.dataset.userReaction = 'like';
        countSpan.textContent = parseInt(countSpan.textContent || 0) + 1;
      } else if (data.action === 'removed') {
        svg.classList.remove('fill-red-500');
        svg.classList.add('fill-none');
        btn.dataset.userReaction = '';
        countSpan.textContent = Math.max(0, parseInt(countSpan.textContent || 0) - 1);
      }
      
      console.log('[reels.js] ✅ Reaction updated');
    }
  } catch (err) {
    console.error('[reels.js] ❌ Error toggling reaction:', err);
  }
}

/**
 * Handle comment button click
 */
function handleCommentClick(btn) {
  const postId = btn.dataset.postId;
  console.log('[reels.js] 💬 Opening comments for post:', postId);
  
  // TODO: Open comments modal or navigate to post detail
  alert('Comments feature coming soon!');
}

/**
 * Handle share button click
 */
function handleShareClick(btn) {
  console.log('[reels.js] 📤 Share clicked');
  
  // Copy current URL to clipboard
  const currentUrl = window.location.href;
  navigator.clipboard.writeText(currentUrl).then(() => {
    // TODO: Show toast notification
    alert('Link copied to clipboard!');
  }).catch(err => {
    console.error('[reels.js] ❌ Error copying link:', err);
  });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeReels);
} else {
  initializeReels();
}
