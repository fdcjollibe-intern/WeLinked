// composer-modal.js — Composer modal, photo collages, and photo viewer
(function() {
  const isMobile = () => window.innerWidth < 768;

  // Privacy toggle functionality
  function initPrivacyToggle() {
    const toggle = document.getElementById('privacy-toggle');
    const label = document.getElementById('privacy-label');
    if (!toggle || !label) return;

    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const current = toggle.dataset.privacy;
      const next = current === 'public' ? 'private' : 'public';
      toggle.dataset.privacy = next;
      label.textContent = next.charAt(0).toUpperCase() + next.slice(1);
    });
  }

  // Create photo collage for posts
  function createPhotoCollage(images, postId) {
    if (!images || images.length === 0) return '';
    
    const total = images.length;
    let html = '';

    if (total === 1) {
      // Single photo: full width
      html = `<div class="photo-collage single-photo cursor-pointer" data-images='${JSON.stringify(images)}' data-index="0">
        <img src="${images[0]}" alt="Photo" class="w-full h-auto rounded-xl object-cover max-h-[500px]">
      </div>`;
    } else if (total === 2) {
      // Two photos: side by side
      html = `<div class="photo-collage two-photos grid grid-cols-2 gap-2 rounded-xl overflow-hidden cursor-pointer" data-images='${JSON.stringify(images)}'>
        <img src="${images[0]}" alt="Photo 1" class="w-full h-64 object-cover" data-index="0">
        <img src="${images[1]}" alt="Photo 2" class="w-full h-64 object-cover" data-index="1">
      </div>`;
    } else if (total === 3) {
      // Three photos: 1 large left, 2 stacked right
      html = `<div class="photo-collage three-photos grid grid-cols-2 gap-2 rounded-xl overflow-hidden cursor-pointer" data-images='${JSON.stringify(images)}'>
        <img src="${images[0]}" alt="Photo 1" class="w-full h-full object-cover row-span-2" data-index="0">
        <div class="grid grid-rows-2 gap-2">
          <img src="${images[1]}" alt="Photo 2" class="w-full h-full object-cover" data-index="1">
          <img src="${images[2]}" alt="Photo 3" class="w-full h-full object-cover" data-index="2">
        </div>
      </div>`;
    } else {
      // 4+ photos: show first 3, with 3rd having "+N" overlay
      const remaining = total - 3;
      html = `<div class="photo-collage multi-photos grid grid-cols-2 gap-2 rounded-xl overflow-hidden cursor-pointer" data-images='${JSON.stringify(images)}'>
        <img src="${images[0]}" alt="Photo 1" class="w-full h-full object-cover row-span-2" data-index="0">
        <div class="grid grid-rows-2 gap-2">
          <img src="${images[1]}" alt="Photo 2" class="w-full h-full object-cover" data-index="1">
          <div class="relative">
            <img src="${images[2]}" alt="Photo 3" class="w-full h-full object-cover" data-index="2">
            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
              <span class="text-white text-3xl font-bold">+${remaining}</span>
            </div>
          </div>
        </div>
      </div>`;
    }

    return html;
  }

  // Create photo viewer modal
  function createPhotoViewerModal() {
    const modal = document.createElement('div');
    modal.id = 'photo-viewer-modal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-90 z-[100] hidden items-center justify-center';
    modal.innerHTML = `
      <div class="photo-viewer-container w-full h-full max-w-7xl mx-auto flex ${isMobile() ? 'flex-col' : 'flex-row'} p-4">
        <!-- Photo Section -->
        <div class="photo-section ${isMobile() ? 'w-full h-1/2' : 'w-2/3 h-full'} flex items-center justify-center relative">
          <button id="close-photo-viewer" class="absolute top-4 right-4 text-white text-3xl z-10 hover:text-gray-300">&times;</button>
          <button id="prev-photo" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-40 text-white p-3 rounded-full z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <img id="photo-viewer-img" src="" alt="Photo" class="max-w-full max-h-full object-contain">
          <button id="next-photo" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-40 text-white p-3 rounded-full z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
        
        <!-- Details Section -->
        <div class="details-section ${isMobile() ? 'w-full h-1/2 overflow-y-auto' : 'w-1/3 h-full overflow-y-auto'} bg-white ${isMobile() ? 'mt-4 rounded-t-xl' : 'ml-4 rounded-xl'}">
          <div class="p-4">
            <!-- Post Header -->
            <div class="flex items-center space-x-3 mb-4 pb-4 border-b">
              <div id="viewer-user-avatar" class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">U</div>
              <div>
                <h3 id="viewer-username" class="font-semibold text-gray-900">Username</h3>
                <p id="viewer-timestamp" class="text-xs text-gray-400">Just now</p>
              </div>
            </div>
            
            <!-- Caption -->
            <p id="viewer-caption" class="text-gray-700 mb-4"></p>
            
            <!-- Reactions Summary -->
            <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b">
              <div class="flex items-center space-x-1">
                <span id="viewer-reactions" style="text-shadow:-1px -1px 0 white,1px -1px 0 white,-1px 1px 0 white,1px 1px 0 white,0 -1px 0 white,0 1px 0 white,-1px 0 0 white,1px 0 0 white;display:flex;align-items:center">❤️</span>
                <span id="viewer-reaction-count">0</span>
              </div>
              <div class="flex items-center space-x-4">
                <span id="viewer-comments-count">0 comments</span>
                <span id="viewer-shares-count">0 shares</span>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center justify-around py-2 border-b mb-4">
              <button class="flex items-center space-x-2 px-3 py-1 hover:bg-gray-50 rounded">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <span class="text-sm font-medium">Like</span>
              </button>
              <button class="flex items-center space-x-2 px-3 py-1 hover:bg-gray-50 rounded">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span class="text-sm font-medium">Comment</span>
              </button>
              <button class="flex items-center space-x-2 px-3 py-1 hover:bg-gray-50 rounded">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span class="text-sm font-medium">Share</span>
              </button>
            </div>
            
            <!-- Comments Section -->
            <div id="viewer-comments" class="space-y-3">
              <p class="text-sm text-gray-500 text-center py-4">No comments yet. Be the first to comment!</p>
            </div>
            
            <!-- Comment Input -->
            <div class="mt-4 pt-4 border-t sticky bottom-0 bg-white">
              <div class="flex items-center space-x-2">
                <input type="text" placeholder="Write a comment..." class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-blue-500">
                <button class="bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-semibold hover:bg-blue-600">Post</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    return modal;
  }

  // Open photo viewer
  function openPhotoViewer(images, startIndex, postEl) {
    let modal = document.getElementById('photo-viewer-modal');
    if (!modal) {
      modal = createPhotoViewerModal();
    }

    let currentIndex = startIndex || 0;
    const img = document.getElementById('photo-viewer-img');
    const prevBtn = document.getElementById('prev-photo');
    const nextBtn = document.getElementById('next-photo');
    const closeBtn = document.getElementById('close-photo-viewer');

    function updatePhoto() {
      img.src = images[currentIndex];
      prevBtn.style.display = currentIndex > 0 ? 'block' : 'none';
      nextBtn.style.display = currentIndex < images.length - 1 ? 'block' : 'none';
    }

    // Populate post details from postEl if available
    if (postEl) {
      const username = postEl.querySelector('h3')?.textContent || 'User';
      const timestamp = postEl.querySelector('.text-xs.text-gray-400')?.textContent || 'Just now';
      const caption = postEl.querySelector('.text-gray-700')?.textContent || '';
      const reactions = postEl.querySelector('.reaction-emojis')?.innerHTML || '';
      const reactionCount = postEl.querySelector('.reaction-count')?.textContent || '0';
      const commentsCount = postEl.querySelector('.comments-count')?.textContent || '0 comments';
      const sharesCount = postEl.querySelector('.shares-count')?.textContent || '0 shares';

      document.getElementById('viewer-username').textContent = username;
      document.getElementById('viewer-timestamp').textContent = timestamp;
      document.getElementById('viewer-caption').textContent = caption;
      document.getElementById('viewer-reactions').innerHTML = reactions;
      document.getElementById('viewer-reaction-count').textContent = reactionCount;
      document.getElementById('viewer-comments-count').textContent = commentsCount;
      document.getElementById('viewer-shares-count').textContent = sharesCount;

      // Set avatar
      const avatarEl = postEl.querySelector('.w-10.h-10.rounded-full > div');
      if (avatarEl) {
        document.getElementById('viewer-user-avatar').innerHTML = avatarEl.innerHTML;
        document.getElementById('viewer-user-avatar').className = avatarEl.className;
      }
    }

    updatePhoto();
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    prevBtn.onclick = () => {
      if (currentIndex > 0) {
        currentIndex--;
        updatePhoto();
      }
    };

    nextBtn.onclick = () => {
      if (currentIndex < images.length - 1) {
        currentIndex++;
        updatePhoto();
      }
    };

    closeBtn.onclick = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };

    modal.onclick = (e) => {
      if (e.target === modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    };

    // Keyboard navigation
    const handleKey = (e) => {
      if (modal.classList.contains('hidden')) return;
      if (e.key === 'ArrowLeft') prevBtn.click();
      if (e.key === 'ArrowRight') nextBtn.click();
      if (e.key === 'Escape') closeBtn.click();
    };
    document.addEventListener('keydown', handleKey);
  }

  // Initialize photo collage click handlers
  function initPhotoCollageHandlers() {
    document.body.addEventListener('click', (e) => {
      const collage = e.target.closest('.photo-collage');
      if (collage) {
        const images = JSON.parse(collage.dataset.images || '[]');
        let startIndex = 0;
        
        // Check if clicked on specific image
        const clickedImg = e.target.closest('img');
        if (clickedImg && clickedImg.dataset.index) {
          startIndex = parseInt(clickedImg.dataset.index, 10);
        }
        
        const postEl = collage.closest('.post');
        openPhotoViewer(images, startIndex, postEl);
      }
    });
  }

  // Reels detection modal
  function showReelsConfirmation(callback) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-[100] flex items-center justify-center';
    modal.innerHTML = `
      <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-2">Post as Reel?</h3>
        <p class="text-gray-600 mb-6">This video will be posted as a Reel and appear in the Reels section.</p>
        <div class="flex space-x-3">
          <button id="cancel-reel" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button id="confirm-reel" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Post as Reel</button>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    
    document.getElementById('confirm-reel').onclick = () => {
      document.body.removeChild(modal);
      callback(true);
    };
    
    document.getElementById('cancel-reel').onclick = () => {
      document.body.removeChild(modal);
      callback(false);
    };
    
    modal.onclick = (e) => {
      if (e.target === modal) {
        document.body.removeChild(modal);
        callback(false);
      }
    };
  }

  // Initialize everything
  document.addEventListener('DOMContentLoaded', () => {
    initPrivacyToggle();
    initPhotoCollageHandlers();
    
    // Expose functions globally for middle.js integration
    window.createPhotoCollage = createPhotoCollage;
    window.showReelsConfirmation = showReelsConfirmation;
  });
})();
