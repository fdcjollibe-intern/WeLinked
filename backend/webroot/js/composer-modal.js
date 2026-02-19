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
  function createPhotoCollage(images, postId, hasVideo) {
    if (!images || images.length === 0) return '';
    
    const total = images.length;
    const topMargin = hasVideo ? 'mt-2' : 'mt-3';
    let html = '';

    if (total === 1) {
      // Single photo
      html = `<div class="photo-collage cursor-pointer ${topMargin} mb-4 rounded-xl overflow-hidden" data-images='${JSON.stringify(images)}' data-index="0" style="max-height: 450px;">
        <img src="${images[0]}" alt="Post image" class="w-full h-auto object-cover" style="max-height: 450px;">
      </div>`;
    } else if (total === 2) {
      // Two photos
      html = `<div class="photo-collage grid grid-cols-2 gap-2 cursor-pointer ${topMargin} mb-4 rounded-xl overflow-hidden" data-images='${JSON.stringify(images)}' style="max-height: 350px;">
        <img src="${images[0]}" alt="Post image" class="w-full h-full object-cover" data-index="0">
        <img src="${images[1]}" alt="Post image" class="w-full h-full object-cover" data-index="1">
      </div>`;
    } else if (total === 3) {
      // Three photos: show 2 with +1 overlay on second
      html = `<div class="photo-collage grid grid-cols-2 gap-2 cursor-pointer ${topMargin} mb-4 rounded-xl overflow-hidden" data-images='${JSON.stringify(images)}' style="max-height: 350px;">
        <img src="${images[0]}" alt="Post image" class="w-full h-full object-cover" data-index="0">
        <div class="relative overflow-hidden">
          <img src="${images[1]}" alt="Post image" class="w-full h-full object-cover" data-index="1">
          <div class="absolute inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center pointer-events-none">
            <span class="text-white text-4xl font-bold">+1</span>
          </div>
        </div>
      </div>`;
    } else {
      // 4+ photos: 2x2 grid with overlay on bottom-right
      const remaining = total - 4;
      html = `<div class="photo-collage grid grid-cols-2 grid-rows-2 gap-2 cursor-pointer ${topMargin} mb-4 rounded-xl overflow-hidden" data-images='${JSON.stringify(images)}' style="max-height: 400px;">
        <img src="${images[0]}" alt="Post image" class="w-full h-full object-cover" data-index="0">
        <img src="${images[1]}" alt="Post image" class="w-full h-full object-cover" data-index="1">
        <img src="${images[2]}" alt="Post image" class="w-full h-full object-cover" data-index="2">
        <div class="relative overflow-hidden">
          <img src="${images[3]}" alt="Post image" class="w-full h-full object-cover" data-index="3">`;
      
      if (remaining > 0) {
        html += `
          <div class="absolute inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center pointer-events-none">
            <span class="text-white text-4xl font-bold">+${remaining}</span>
          </div>`;
      }
      
      html += `
        </div>
      </div>`;
    }

    return html;
  }

  // Create photo viewer modal
  function createPhotoViewerModal() {
    const modal = document.createElement('div');
    modal.id = 'photo-viewer-modal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-95 z-[100] hidden items-center justify-center';
    modal.innerHTML = `
      <div class="photo-viewer-container w-full h-full flex items-center justify-center relative p-4">
        <!-- Close Button -->
        <button id="close-photo-viewer" class="absolute top-4 right-4 text-white text-3xl z-20 hover:text-gray-300 transition-colors">&times;</button>
        
        <!-- Previous Button -->
        <button id="prev-photo" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-40 text-white p-3 rounded-full z-10 transition-all">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        
        <!-- Image -->
        <img id="photo-viewer-img" src="" alt="Photo" class="max-w-full max-h-full object-contain">
        
        <!-- Next Button -->
        <button id="next-photo" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-20 hover:bg-opacity-40 text-white p-3 rounded-full z-10 transition-all">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
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

    // Store images in modal dataset for keyboard navigation
    modal.dataset.allImages = JSON.stringify(images);
    let currentIndex = startIndex || 0;
    
    const img = document.getElementById('photo-viewer-img');
    const prevBtn = document.getElementById('prev-photo');
    const nextBtn = document.getElementById('next-photo');
    const closeBtn = document.getElementById('close-photo-viewer');

    function updatePhoto() {
      // Get images from modal dataset to ensure consistency
      const allImages = JSON.parse(modal.dataset.allImages || '[]');
      img.src = allImages[currentIndex];
      prevBtn.style.display = currentIndex > 0 ? 'block' : 'none';
      nextBtn.style.display = currentIndex < allImages.length - 1 ? 'block' : 'none';
    }

    updatePhoto();
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // Remove old event listeners by cloning elements
    const newPrevBtn = prevBtn.cloneNode(true);
    const newNextBtn = nextBtn.cloneNode(true);
    const newCloseBtn = closeBtn.cloneNode(true);
    prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
    nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
    closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);

    newPrevBtn.onclick = () => {
      if (currentIndex > 0) {
        currentIndex--;
        updatePhoto();
      }
    };

    newNextBtn.onclick = () => {
      const allImages = JSON.parse(modal.dataset.allImages || '[]');
      if (currentIndex < allImages.length - 1) {
        currentIndex++;
        updatePhoto();
      }
    };

    const closeModal = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      // Remove keyboard listener when closing
      document.removeEventListener('keydown', handleKey);
    };

    newCloseBtn.onclick = closeModal;

    modal.onclick = (e) => {
      if (e.target === modal) {
        closeModal();
      }
    };

    // Keyboard navigation
    const handleKey = (e) => {
      if (modal.classList.contains('hidden')) return;
      if (e.key === 'ArrowLeft') newPrevBtn.click();
      if (e.key === 'ArrowRight') newNextBtn.click();
      if (e.key === 'Escape') newCloseBtn.click();
    };
    
    // Remove any existing keyboard listeners before adding new one
    document.removeEventListener('keydown', handleKey);
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
