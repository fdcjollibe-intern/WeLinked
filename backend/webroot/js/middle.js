// middle.js — handles composer, file uploads with progress, and post creation
(function () {
  const csrfToken = window.csrfToken || document.querySelector('meta[name="csrfToken"]')?.content || '';
  
  console.log('[middle.js] 🔐 Module loaded, CSRF token available:', !!csrfToken);

  function el(id) { return document.getElementById(id); }

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
  }

  function createAttachmentPreview(file) {
    const wrap = document.createElement('div');
    wrap.className = 'upload-item relative rounded-xl border border-gray-200 bg-white p-3 flex items-center gap-3 shadow-sm';

    const mediaShell = document.createElement('div');
    mediaShell.className = 'w-16 h-16 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center flex-shrink-0';

    const isImage = file.type.startsWith('image/');
    const mediaEl = document.createElement(isImage ? 'img' : 'video');
    mediaEl.className = 'w-full h-full object-cover';
    const tempUrl = URL.createObjectURL(file);
    mediaEl.src = tempUrl;
    const revoke = () => URL.revokeObjectURL(tempUrl);
    mediaEl.addEventListener(isImage ? 'load' : 'loadeddata', revoke, { once: true });
    if (!isImage) {
      mediaEl.muted = true;
      mediaEl.loop = true;
      mediaEl.playsInline = true;
    }
    mediaShell.appendChild(mediaEl);

    const meta = document.createElement('div');
    meta.className = 'flex-1 min-w-0';
    meta.innerHTML = `
      <p class="text-sm font-semibold text-gray-800 truncate">${file.name}</p>
      <p class="text-xs text-gray-500">${(file.size / (1024 * 1024)).toFixed(1)} MB • ${file.type || 'binary'}</p>
    `;

    const badge = document.createElement('div');
    badge.className = 'text-xs font-medium text-blue-600';
    badge.textContent = 'Uploading…';

    // Cancel/Remove button (red X circle)
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors shadow-md z-10';
    cancelBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>';
    cancelBtn.title = 'Remove file';

    const ring = document.createElement('div');
    ring.className = 'absolute inset-0 bg-white/70 flex items-center justify-center rounded-xl transition-opacity';
    const ringText = document.createElement('div');
    ringText.className = 'text-sm font-semibold text-gray-700';
    ringText.textContent = '0%';
    ring.appendChild(ringText);

    wrap.appendChild(mediaShell);
    wrap.appendChild(meta);
    wrap.appendChild(badge);
    wrap.appendChild(cancelBtn);
    wrap.appendChild(ring);

    return {
      wrap,
      cancelBtn,
      setProgress(percent) {
        const clamped = Math.max(0, Math.min(100, Math.round(percent)));
        ringText.textContent = clamped + '%';
      },
      markUploaded(url) {
        ring.style.opacity = '0';
        setTimeout(() => ring.remove(), 200);
        badge.textContent = 'Uploaded';
        badge.className = 'text-xs font-medium text-emerald-600';
        if (url) {
          mediaEl.src = url;
        }
      },
      markFailed() {
        ringText.textContent = 'Failed';
        ring.classList.add('bg-red-50');
        badge.textContent = 'Failed';
        badge.className = 'text-xs font-medium text-red-600';
      },
      markRemoving() {
        ring.style.opacity = '1';
        ring.className = 'absolute inset-0 bg-white/90 flex items-center justify-center rounded-xl transition-opacity';
        ringText.textContent = 'Removing...';
        ringText.className = 'text-sm font-semibold text-orange-600';
        badge.textContent = 'Removing';
        badge.className = 'text-xs font-medium text-orange-600';
        cancelBtn.disabled = true;
        cancelBtn.style.opacity = '0.5';
        cancelBtn.style.cursor = 'not-allowed';
      },
      remove() {
        wrap.remove();
      }
    };
  }

  function uploadFile(file, type, onProgress) {
    return new Promise(function (resolve, reject) {
      console.log('[middle.js] 📤 Starting upload:', {
        fileName: file.name,
        fileType: file.type,
        fileSize: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
        uploadType: type,
        timestamp: new Date().toISOString()
      });

      const xhr = new XMLHttpRequest();
      const url = '/dashboard/upload?type=' + encodeURIComponent(type);
      const fd = new FormData();
      fd.append('file', file, file.name);
      
      // Add CSRF token
      if (csrfToken) {
        fd.append('_csrfToken', csrfToken);
        console.log('[middle.js] ✓ CSRF token added to upload request');
      } else {
        console.warn('[middle.js] ⚠️ No CSRF token available for upload');
      }
      
      xhr.open('POST', url, true);
      xhr.withCredentials = true;
      if (csrfToken) {
        xhr.setRequestHeader('X-CSRF-Token', csrfToken);
      }
      
      console.log('[middle.js] 🌐 XHR request opened:', {
        url: url,
        method: 'POST',
        withCredentials: true
      });

      xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
          const pct = Math.round((e.loaded / e.total) * 100);
          console.log('[middle.js] 📊 Upload progress:', {
            fileName: file.name,
            progress: pct + '%',
            loaded: (e.loaded / (1024 * 1024)).toFixed(2) + ' MB',
            total: (e.total / (1024 * 1024)).toFixed(2) + ' MB'
          });
          onProgress(pct);
        }
      };

      xhr.onload = function () {
        console.log('[middle.js] 📥 Upload response received:', {
          fileName: file.name,
          status: xhr.status,
          statusText: xhr.statusText,
          responseLength: xhr.responseText.length
        });

        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            const json = JSON.parse(xhr.responseText || '{}');
            console.log('[middle.js] ✅ Upload successful:', {
              fileName: file.name,
              response: json
            });
            resolve(json.files ? json.files[0] : null);
          } catch (err) {
            console.error('[middle.js] ❌ Failed to parse upload response:', {
              fileName: file.name,
              error: err.message,
              responseText: xhr.responseText
            });
            resolve(null);
          }
        } else {
          console.error('[middle.js] ❌ Upload failed with status:', {
            fileName: file.name,
            status: xhr.status,
            statusText: xhr.statusText,
            response: xhr.responseText
          });
          reject(new Error('Upload failed: ' + xhr.status));
        }
      };

      xhr.onerror = function () {
        console.error('[middle.js] ❌ Upload network error:', {
          fileName: file.name,
          timestamp: new Date().toISOString()
        });
        reject(new Error('Upload network error'));
      };

      console.log('[middle.js] 🚀 Sending XHR request...');
      xhr.send(fd);
    });
  }

  function initializeMiddleColumn() {
    console.log('[middle.js] ==========================================');
    console.log('[middle.js] 🚀 INITIALIZING MIDDLE COLUMN');
    console.log('[middle.js] ==========================================');
    console.log('[middle.js] Timestamp:', new Date().toISOString());
    console.log('[middle.js] Page URL:', window.location.href);
    
    const input = el('attachment-input');
    const submit = el('post-submit-btn');
    const postInput = el('post-composer-textarea');
    
    console.log('[middle.js] 🔍 Element detection:', {
      'attachment-input': input ? '✅ Found' : '❌ Not found',
      'post-submit-btn': submit ? '✅ Found' : '❌ Not found',
      'post-composer-textarea': postInput ? '✅ Found' : '❌ Not found'
    });
    
    if (!input || !submit || !postInput) {
      console.warn('[middle.js] ⚠️ Missing required elements, exiting initialization');
      console.log('[middle.js] Available IDs in document:', 
        Array.from(document.querySelectorAll('[id]')).map(el => el.id).slice(0, 20)
      );
      return;
    }
    
    console.log('[middle.js] ✅ All required elements found, continuing initialization...');
    
    // reuse existing preview container if present, otherwise create and append to composer
    let preview = document.getElementById('attachment-preview');
    if (!preview) {
      preview = document.createElement('div');
      preview.id = 'attachment-preview';
      const composer = document.querySelector('.composer');
      if (composer) composer.appendChild(preview);
      else document.body.appendChild(preview);
    }

    let previewItems = [];
    let uploadedFilesData = []; // Store successfully uploaded files
    let uploadingCount = 0; // Track number of files currently uploading
    
    // Function to update submit button state based on uploads
    function updateSubmitButtonState() {
      if (uploadingCount > 0) {
        submit.disabled = true;
        submit.classList.add('opacity-50', 'cursor-not-allowed');
        submit.classList.remove('hover:bg-blue-600');
        submit.title = 'Attachment uploading... Please wait';
      } else {
        submit.disabled = false;
        submit.classList.remove('opacity-50', 'cursor-not-allowed');
        submit.classList.add('hover:bg-blue-600');
        submit.title = '';
      }
    }
    
    console.log('[middle.js] 📦 Preview container ready:', {
      previewId: preview.id,
      previewExists: !!preview
    });
    
    // Initialize mention autocomplete
    let mentionAutocomplete = null;
    if (window.MentionAutocomplete && postInput) {
      mentionAutocomplete = new window.MentionAutocomplete(postInput);
      console.log('[middle.js] ✅ Mention autocomplete initialized');
    } else {
      console.log('[middle.js] ℹ️ Mention autocomplete not available:', {
        MentionAutocomplete: !!window.MentionAutocomplete,
        postInput: !!postInput
      });
    }
    
    console.log('[middle.js] 🎯 Attaching event listeners to submit button...');
    console.log('[middle.js] Submit button element:', submit);
    console.log('[middle.js] Submit button tag:', submit.tagName);
    console.log('[middle.js] Submit button id:', submit.id);
    
    // Fetch and update current user's profile photo
    const composer = document.getElementById('post-composer');
    const userPhotoEl = document.getElementById('composer-user-photo');
    if (composer && userPhotoEl) {
      fetch('/users/current-profile')
        .then(r => r.json())
        .then(data => {
          if (data.success && data.user && data.user.profile_photo_path) {
            const currentSrc = userPhotoEl.src || '';
            const newSrc = data.user.profile_photo_path;
            if (!currentSrc.includes(newSrc)) {
              if (userPhotoEl.tagName === 'IMG') {
                userPhotoEl.src = newSrc;
              } else {
                const img = document.createElement('img');
                img.id = 'composer-user-photo';
                img.src = newSrc;
                img.alt = 'Profile';
                img.className = 'w-10 h-10 rounded-full object-cover';
                userPhotoEl.parentNode.replaceChild(img, userPhotoEl);
              }
              console.log('[middle.js] ✅ Updated composer profile photo');
            }
          }
        })
        .catch(err => console.warn('[middle.js] Could not fetch user profile:', err));
    }
    
    // Drag & Drop functionality
    const dropOverlay = document.getElementById('composer-drop-overlay');
    if (composer && dropOverlay) {
      let dragCounter = 0;
      
      composer.addEventListener('dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dragCounter++;
        if (dragCounter === 1) {
          dropOverlay.classList.remove('hidden');
        }
      });
      
      composer.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
      });
      
      composer.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dragCounter--;
        if (dragCounter === 0) {
          dropOverlay.classList.add('hidden');
        }
      });
      
      composer.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dragCounter = 0;
        dropOverlay.classList.add('hidden');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
          console.log('[middle.js] 📥 Files dropped:', files.length);
          // Create a new DataTransfer to add files to input
          const dataTransfer = new DataTransfer();
          // Add existing files from input
          if (input.files) {
            for (let i = 0; i < input.files.length; i++) {
              dataTransfer.items.add(input.files[i]);
            }
          }
          // Add dropped files
          for (let i = 0; i < files.length; i++) {
            dataTransfer.items.add(files[i]);
          }
          input.files = dataTransfer.files;
          // Trigger change event
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
      
      console.log('[middle.js] ✅ Drag & drop enabled');
    }
    
    // Unsaved changes warning
    let hasUnsavedChanges = false;
    
    function checkUnsavedChanges() {
      const hasText = postInput.value.trim().length > 0;
      const hasAttachments = uploadedFilesData.length > 0;
      hasUnsavedChanges = hasText || hasAttachments;
      window.globalHasUnsavedChanges = hasUnsavedChanges; // Update global flag
    }
    
    postInput.addEventListener('input', checkUnsavedChanges);
    
    window.addEventListener('beforeunload', function(e) {
      if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
      }
    });
    
    // Create unsaved changes modal
    function showUnsavedChangesModal() {
      return new Promise(function(resolve) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
          <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
            <div class="p-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-amber-100 rounded-full">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Leave page?</h3>
              <p class="text-gray-600 text-center text-sm">This page is asking you to confirm that you want to leave — information you've entered may not be saved.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
              <button id="modal-cancel" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                Stay on page
              </button>
              <button id="modal-discard" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                Leave page
              </button>
            </div>
          </div>
        `;
        
        document.body.appendChild(modal);
        
        // Handle escape key
        const handleEscape = (e) => {
          if (e.key === 'Escape') {
            modal.remove();
            document.removeEventListener('keydown', handleEscape);
            resolve(false);
          }
        };
        document.addEventListener('keydown', handleEscape);
        
        // Handle click outside modal
        modal.addEventListener('click', function(e) {
          if (e.target === modal) {
            modal.remove();
            document.removeEventListener('keydown', handleEscape);
            resolve(false);
          }
        });
        
        modal.querySelector('#modal-cancel').addEventListener('click', function() {
          modal.remove();
          document.removeEventListener('keydown', handleEscape);
          resolve(false);
        });
        
        modal.querySelector('#modal-discard').addEventListener('click', async function() {
          modal.remove();
          document.removeEventListener('keydown', handleEscape);
          // Delete uploaded files from Cloudinary
          for (const file of uploadedFilesData) {
            if (file.public_id) {
              try {
                await fetch('/dashboard/upload', {
                  method: 'DELETE',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                  },
                  body: JSON.stringify({
                    public_id: file.public_id,
                    resource_type: file.resource_type || 'image'
                  })
                });
                console.log('[middle.js] 🗑️ Cleaned up:', file.public_id);
              } catch (err) {
                console.error('[middle.js] Failed to cleanup:', err);
              }
            }
          }
          
          // Clear composer UI
          postInput.value = '';
          if (postInput && postInput.tagName && postInput.tagName.toLowerCase() === 'textarea') {
            postInput.style.height = '';
            if (typeof autoResizeTextarea === 'function') {
              autoResizeTextarea(postInput);
            }
          }
          preview.innerHTML = '';
          previewItems = [];
          uploadedFilesData = [];
          uploadingCount = 0;
          updateSubmitButtonState();
          
          resolve(true);
        });
      });
    }
    
    // Connect to global navigation guard
    window.globalShowUnsavedModal = showUnsavedChangesModal;
    
    console.log('[middle.js] ✅ Unsaved changes warning enabled');
    
    // Auto-resize textarea: keep single-line height until text wraps or user adds lines
    function autoResizeTextarea(t){
      if(!t) return;
      t.style.height = 'auto';
      const max = 400; // px
      const newH = Math.min(t.scrollHeight, max);
      t.style.height = newH + 'px';
    }
    // initialize and bind
    if(postInput && postInput.tagName && postInput.tagName.toLowerCase()==='textarea'){
      // set invisible measurement then resize once
      setTimeout(()=> autoResizeTextarea(postInput), 0);
      postInput.addEventListener('input', function(){ autoResizeTextarea(postInput); });
      // Ensure Enter inserts a newline (do not submit). Use Ctrl/Cmd+Enter to submit quickly.
      postInput.addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
          // Ctrl/Cmd+Enter -> submit
          if(e.ctrlKey || e.metaKey){
            e.preventDefault();
            submit.click();
            return;
          }
          // Allow Enter to create a newline; stop propagation to avoid outer handlers
          e.stopPropagation();
        }
      });
    }

    input.addEventListener('change', function () {
      console.log('[middle.js] 📂 Files selected, starting immediate upload...');
      
      const selected = Array.from(input.files || []);
      console.log('[middle.js] Selected attachments:', selected.map(f => ({ name: f.name, type: f.type, size: f.size })));
      
      if (!selected.length) {
        if (previewItems.length === 0) {
          preview.innerHTML = '<p class="text-sm text-gray-400">No attachments selected</p>';
        }
        return;
      }
      
      // Remove placeholder text if it exists
      const placeholder = preview.querySelector('p.text-gray-400');
      if (placeholder) {
        placeholder.remove();
      }
      
      // Validate files first
      const maxSize = 250 * 1024 * 1024;
      for (let f of selected) {
        if (f.size > maxSize) {
          alert('File too large: ' + f.name + ' (max 250 MB)');
          input.value = '';
          return;
        }
        if (!f.type.startsWith('image/') && !f.type.startsWith('video/')) {
          alert('Invalid file type: ' + f.name + ' (only images and videos)');
          input.value = '';
          return;
        }
      }
      
      // Create preview cards and start uploading immediately
      selected.forEach(function (file, idx) {
        const card = createAttachmentPreview(file);
        previewItems.push(card);
        preview.appendChild(card.wrap);
        
        // Cancel button handler
        card.cancelBtn.addEventListener('click', async function() {
          console.log('[middle.js] ❌ Removing file:', file.name);
          
          // Show removing indicator
          card.markRemoving();
          
          // Remove from uploaded data and delete from Cloudinary if it was uploaded
          const uploadedIdx = uploadedFilesData.findIndex(f => f.original === file.name);
          if (uploadedIdx !== -1) {
            const uploadedFile = uploadedFilesData[uploadedIdx];
            console.log('[middle.js] 🗑️ Deleting from Cloudinary:', uploadedFile.public_id);
            
            // Delete from Cloudinary
            try {
              const response = await fetch('/dashboard/upload', {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                  public_id: uploadedFile.public_id,
                  resource_type: uploadedFile.resource_type
                })
              });
              
              const result = await response.json();
              if (result.success) {
                console.log('[middle.js] ✅ Deleted from Cloudinary successfully');
              } else {
                console.error('[middle.js] ⚠️ Failed to delete from Cloudinary:', result);
              }
            } catch (err) {
              console.error('[middle.js] ❌ Error deleting from Cloudinary:', err);
            }
            
            uploadedFilesData.splice(uploadedIdx, 1);
          }
          
          // Find and remove from previewItems array
          const cardIdx = previewItems.findIndex(c => c === card);
          if (cardIdx !== -1) {
            previewItems.splice(cardIdx, 1);
          }
          
          card.remove();
          
          // If no more files, clear the input
          if (previewItems.length === 0) {
            input.value = '';
            preview.innerHTML = '<p class="text-sm text-gray-400">No attachments selected</p>';
          }
          
          checkUnsavedChanges();
        });
        
        // Start upload immediately
        console.log('[middle.js] 🚀 Starting immediate upload for:', file.name);
        uploadingCount++;
        updateSubmitButtonState();
        
        uploadFile(file, 'post', function(pct) {
          card.setProgress(pct);
        }).then(function(result) {
          uploadingCount--;
          updateSubmitButtonState();
          
          if (result && result.url) {
            console.log('[middle.js] ✅ File uploaded:', file.name, '->', result.url);
            card.markUploaded(result.url);
            uploadedFilesData.push(result);
            checkUnsavedChanges();
          } else {
            console.error('[middle.js] ❌ Upload returned no URL for:', file.name);
            card.markFailed();
          }
        }).catch(function(err) {
          uploadingCount--;
          updateSubmitButtonState();
          
          console.error('[middle.js] ❌ Upload failed for:', file.name, err);
          card.markFailed();
          alert('Upload failed for ' + file.name + ': ' + err.message);
        });
      });
      
      // Clear the file input so the same files can be selected again
      input.value = '';
    });

    submit.addEventListener('click', function () {
      console.log('[middle.js] ==========================================');
      console.log('[middle.js] 🖱️ SUBMIT BUTTON CLICKED');
      console.log('[middle.js] ==========================================');
      
      // Check if uploads are still in progress
      if (uploadingCount > 0) {
        console.warn('[middle.js] ⚠️ Cannot post while attachments are uploading');
        return;
      }
      
      const body = postInput.value || '';
      
      console.log('[middle.js] 📝 Post data gathered:', {
        bodyLength: body.length,
        uploadedFilesCount: uploadedFilesData.length,
        files: uploadedFilesData.map(f => ({ url: f.url, original: f.original }))
      });

      if (uploadedFilesData.length === 0 && body.trim() === '') {
        console.warn('[middle.js] ⚠️ Post validation failed: empty content');
        alert('Write something or add an attachment.');
        return;
      }
      
      // Check if any uploads failed
      const hasFailedUploads = previewItems.some(function(card) {
        return card.wrap.querySelector('.text-red-600');
      });
      
      if (hasFailedUploads) {
        console.warn('[middle.js] ⚠️ Some uploads failed');
        alert('Some files failed to upload. Please remove them and try again.');
        return;
      }
      
      console.log('[middle.js] ✓ Post validation passed, submitting...');

      // Check for single video -> Reels
      const videos = uploadedFilesData.filter(f => f.url && (f.url.includes('.mp4') || f.url.includes('.webm') || f.url.includes('.mov')));
      const images = uploadedFilesData.filter(f => f.url && !videos.find(v => v.url === f.url));
      
      if (videos.length === 1 && images.length === 0 && window.showReelsConfirmation) {
        window.showReelsConfirmation(function(confirmed) {
          if (confirmed) {
            submitPost(body, uploadedFilesData, true); // Mark as reel
          }
        });
        return;
      }

      // Proceed with post submission using already-uploaded files
      submitPost(body, uploadedFilesData, false);
    });

    function proceedWithUpload(files, body, isReel) {
      console.log('[middle.js] 🎬 Starting upload process:', {
        fileCount: files.length,
        bodyLength: body.length,
        isReel: isReel
      });

      const queue = Array.from(files || []);

      if (queue.length === 0) {
        console.log('[middle.js] 📮 No files to upload, submitting post immediately');
        submitPost(body, [], isReel);
        return;
      }

      const cards = queue.map(function (file, idx) {
        const existing = previewItems[idx];
        if (existing) return existing;
        const fallback = createAttachmentPreview(file);
        preview.appendChild(fallback.wrap);
        return fallback;
      });

      const uploadedFiles = [];

      (function uploadNext(i) {
        if (i >= queue.length) {
          console.log('[middle.js] ✅ All files uploaded successfully:', {
            totalFiles: uploadedFiles.length,
            files: uploadedFiles.map(f => ({ url: f.url, original: f.original }))
          });
          submitPost(body, uploadedFiles, isReel);
          return;
        }

        const file = queue[i];
        const card = cards[i];
        
        console.log('[middle.js] 📤 Uploading file ' + (i + 1) + '/' + queue.length + ':', file.name);
        
        uploadFile(file, 'post', function (pct) {
          if (card && card.setProgress) card.setProgress(pct);
        }).then(function (res) {
          if (res && res.url) {
            console.log('[middle.js] ✅ File uploaded successfully:', {
              fileIndex: i + 1,
              fileName: file.name,
              url: res.url
            });
            uploadedFiles.push(res);
            if (card && card.markUploaded) {
              card.markUploaded(res.url);
            }
          } else {
            console.error('[middle.js] ❌ Upload returned no URL:', {
              fileIndex: i + 1,
              fileName: file.name,
              response: res
            });
            if (card && card.markFailed) {
              card.markFailed();
            }
          }
          uploadNext(i + 1);
        }).catch(function (err) {
          console.error('[middle.js] ❌ Upload error:', {
            fileIndex: i + 1,
            fileName: file.name,
            error: err.message,
            stack: err.stack
          });
          if (card && card.markFailed) card.markFailed();
          alert('Upload failed: ' + err.message);
        });
      })(0);
    }

    function submitPost(body, uploadedFiles, isReel) {
      const mentionedUserIds = mentionAutocomplete ? mentionAutocomplete.getMentionedUserIds() : [];
      const payload = {
        body: body,
        content_text: body,
        attachments: uploadedFiles,
        media: uploadedFiles,
        mentions: mentionedUserIds,
        is_reel: isReel
      };

      console.log('[middle.js] 📮 Submitting post to backend:', {
        endpoint: '/dashboard/posts/create',
        payload: payload,
        payloadSize: JSON.stringify(payload).length + ' bytes',
        timestamp: new Date().toISOString()
      });

      fetch('/dashboard/posts/create', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(payload)
      })
      .then(function (r) {
        console.log('[middle.js] 📥 Post creation response received:', {
          status: r.status,
          statusText: r.statusText,
          ok: r.ok
        });
        
        // Clone the response to read it as text for debugging
        return r.clone().text().then(function(text) {
          console.log('[middle.js] 📄 Raw response body (first 500 chars):', text.substring(0, 500));
          console.log('[middle.js] 📊 Response body length:', text.length, 'bytes');
          console.log('[middle.js] 🔍 First character code:', text.charCodeAt(0));
          
          try {
            return JSON.parse(text);
          } catch(parseErr) {
            console.error('[middle.js] ⚠️ JSON Parse Error Details:', {
              error: parseErr.message,
              firstChars: text.substring(0, 100),
              lastChars: text.substring(text.length - 100)
            });
            throw parseErr;
          }
        });
      })
      .then(function (json) {
        console.log('[middle.js] 📋 Post creation response data:', json);
        
        if (!json || !json.success) {
          console.error('[middle.js] ❌ Post creation failed:', {
            success: json?.success,
            message: json?.message,
            error: json?.error,
            fullResponse: json
          });
          alert(json?.message || 'Failed to create post');
          return;
        }
        
        console.log('[middle.js] ✅ Post created successfully:', {
          postId: json.post?.id,
          timestamp: new Date().toISOString()
        });

          const modal = document.createElement('div');
          modal.className = 'thread-toast fixed top-6 right-6 bg-white shadow-lg border rounded-lg px-4 py-3 z-60 flex items-center gap-3';
          const txt = document.createElement('div');
          txt.textContent = isReel ? 'Reel successfully posted' : 'Post successfully created';
          const close = document.createElement('button'); close.className = 'ml-2 text-gray-500'; close.textContent = '✕';
          close.addEventListener('click', ()=> modal.remove());
          modal.appendChild(txt); modal.appendChild(close);
          document.body.appendChild(modal);
          setTimeout(function () { if (modal.parentNode) modal.parentNode.removeChild(modal); }, 3000);

          appendNewPost(json.post || {}, body, uploadedFiles);

          // Clear unsaved changes flag
          hasUnsavedChanges = false;
          window.globalHasUnsavedChanges = false;

          postInput.value = '';
          if(postInput && postInput.tagName && postInput.tagName.toLowerCase()==='textarea'){
            postInput.style.height = '';
            autoResizeTextarea(postInput);
          }
          postInput.mentionedUsers = [];
          input.value = '';
          preview.innerHTML = '';
          previewItems = [];
          uploadedFilesData = [];
          uploadingCount = 0;
          updateSubmitButtonState();
        })
        .catch(function (err) {
          console.error('[middle.js] ❌ Post creation network error:', {
            error: err.message,
            stack: err.stack,
            timestamp: new Date().toISOString()
          });
          alert('Failed to publish post. Please try again.');
        });
    }

    function appendNewPost(post, body, uploadedFiles) {
      const postsList = document.getElementById('posts-list');
      if (!postsList) return;
      const article = document.createElement('article');
      article.className = 'post bg-white rounded-2xl shadow-sm border border-gray-100 p-5';
      article.dataset.postId = post.id || Date.now();

      const user = post.user || {};
      const username = user.username || 'You';
      const fullName = user.full_name || username;
      const profilePhoto = user.profile_photo_path || user.profile_photo || '';
      const safeName = escapeHtml(fullName);
      const safeUsername = escapeHtml(username);
      const initial = (username || 'Y').charAt(0).toUpperCase();
      const safePhoto = profilePhoto ? escapeHtml(profilePhoto) : '';

      let html = '<div class="flex items-start justify-between mb-4">';
      html += '<div class="flex items-center space-x-3">';
      html += '<a href="/profile/' + safeUsername + '" class="flex-shrink-0">';
      if (profilePhoto) {
        html += '<div class="w-10 h-10 rounded-full overflow-hidden"><img src="' + safePhoto + '" alt="' + safeName + '" class="w-full h-full object-cover"></div>';
      } else {
        html += '<div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">' + escapeHtml(initial) + '</div>';
      }
      html += '</a>';
      html += '<div>';
      html += '<a href="/profile/' + safeUsername + '" class="hover:underline">';
      html += '<h3 class="font-semibold text-gray-900">' + safeName + '</h3>';
      html += '</a>';
      html += '<p class="text-xs text-gray-400">Just now</p></div></div></div>';

      if (body) {
        html += '<p class="text-gray-700 mb-4">' + escapeHtml(body) + '</p>';
      }

      // Separate videos and images
      const allAttachments = (post.attachments && post.attachments.length ? post.attachments : uploadedFiles || []);
      const videoAttachments = [];
      const imageAttachments = [];
      
      allAttachments.forEach(function(att) {
        const url = typeof att === 'string' ? att : (att.url || '');
        const resourceType = att.resource_type || '';
        const isVideo = resourceType === 'video' || url.match(/\.(mp4|webm|mov|avi|mkv)$/i);
        
        if (isVideo) {
          videoAttachments.push(url);
        } else if (url) {
          imageAttachments.push(url);
        }
      });

      // Render videos
      if (videoAttachments.length > 0) {
        videoAttachments.forEach(function(videoUrl) {
          html += '<div class="post-video mt-3 mb-3 rounded-xl bg-black flex items-center justify-center overflow-hidden" style="max-height: 500px;">';
          html += '<video class="w-full h-auto object-contain" style="max-height: 500px;" src="' + escapeHtml(videoUrl) + '" controls preload="metadata" playsinline>';
          html += 'Your browser does not support the video tag.';
          html += '</video></div>';
        });
      }

      // Render images using collage
      if (imageAttachments.length && window.createPhotoCollage) {
        html += window.createPhotoCollage(imageAttachments, post.id || Date.now(), videoAttachments.length > 0);
      }

      html += `
        <div class="flex items-center justify-between text-sm text-gray-500 mb-2 px-1">
          <div class="reaction-summary hidden" data-total="0">
            <span class="reaction-emojis" style="display:flex;align-items:center"></span>
            <span class="reaction-count">0</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="comments-count" data-count="0">0 comments</span>
            <span class="shares-count">0 shares</span>
          </div>
        </div>
        <div class="border-t border-gray-100 pt-2 flex items-center justify-around">
          <button class="reaction-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors" data-user-reaction="">
            <svg class="like-icon w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span class="reaction-label text-sm font-medium text-gray-700">Like</span>
          </button>
          <button class="comment-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Comment</span>
          </button>
          <button class="share-btn flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Share</span>
          </button>
        </div>`;

      article.innerHTML = html;

      const composer = postsList.querySelector('.composer');
      if (composer && composer.nextSibling) {
        postsList.insertBefore(article, composer.nextSibling);
      } else {
        postsList.insertBefore(article, postsList.firstChild);
      }
    }

    // Infinite scroll: fetch next fragments from /dashboard/middle-column?start=N&feed=...
    (function initInfiniteScroll(){
      const postsList = document.getElementById('posts-list');
      if(!postsList) return;
      let loading = false;
      let limit = 8;
      let hasMorePosts = true;
      let currentObserver = null;
      
      function loadMore(){
        if(loading || !hasMorePosts) return; 
        loading = true;
        const start = parseInt(postsList.getAttribute('data-start')||0,10) + postsList.querySelectorAll('.post').length;
        const feed = postsList.getAttribute('data-feed') || 'friends';
        
        console.log('[Infinite Scroll] Loading more posts...', { start, feed, currentPosts: postsList.querySelectorAll('.post').length });
        console.log('[Infinite Scroll] Feed type being requested:', feed);
        
        fetch('/dashboard/middle-column?start=' + start + '&feed=' + encodeURIComponent(feed))
          .then(r => r.text())
          .then(html => {
            // parse returned fragment and extract posts
            const tmp = document.createElement('div'); tmp.innerHTML = html;
            const newList = tmp.querySelector('#posts-list');
            if(!newList) { loading=false; return; }
            
            // Verify the feed type in the loaded content
            const loadedFeed = newList.getAttribute('data-feed');
            console.log('[Infinite Scroll] Loaded content has feed type:', loadedFeed);
            if(loadedFeed !== feed) {
              console.warn('[Infinite Scroll] WARNING: Feed mismatch! Expected:', feed, 'Got:', loadedFeed);
            }
            
            const children = newList.children;
            let appended = 0;
            Array.from(children).forEach(ch => { postsList.appendChild(ch); appended++; });
            
            console.log('[Infinite Scroll] Loaded', appended, 'new posts');
            
            // update start
            const newStart = start + appended;
            postsList.setAttribute('data-start', newStart);
            // if less than limit returned, stop further loads
            if(appended < limit){ 
              hasMorePosts = false;
              console.log('[Infinite Scroll] No more posts to load');
            } else {
              // Set up observer for the new 5th post from end
              setupScrollMarker();
            }
            loading=false;
          }).catch((err)=>{ 
            console.error('[Infinite Scroll] Load failed:', err);
            loading=false; 
          });
      }
      
      function setupScrollMarker(){
        // Clean up previous observer
        if(currentObserver) {
          currentObserver.disconnect();
          currentObserver = null;
        }
        
        const posts = postsList.querySelectorAll('.post');
        if(posts.length < 5) return;
        
        // Target the 5th post from the end as our trigger marker
        const marker = posts[posts.length - 5];
        if(!marker) return;
        
        // Use Intersection Observer with threshold to trigger only when marker is 50% visible
        currentObserver = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            // Trigger when 50% of the marker element is visible AND scrolling down
            if(entry.isIntersecting && entry.intersectionRatio >= 0.5) {
              console.log('[Infinite Scroll] Marker reached (5th from end)');
              loadMore();
            }
          });
        }, {
          threshold: 0.5, // Trigger when 50% of the marker is visible
          rootMargin: '0px' // No margin - must actually scroll to it
        });
        
        currentObserver.observe(marker);
        console.log('[Infinite Scroll] Marker set at post', posts.length - 5, 'of', posts.length);
      }
      
      // Initial setup
      setupScrollMarker();
      
      // Fallback check for initial load if not enough content
      setTimeout(() => {
        if(document.body.offsetHeight <= window.innerHeight + 100 && hasMorePosts && !loading) {
          console.log('[Infinite Scroll] Initial content too short, loading more...');
          loadMore();
        }
      }, 500);
    })();
  }

  // Global navigation interceptor for unsaved changes
  window.globalHasUnsavedChanges = false;
  window.globalShowUnsavedModal = null;
  
  function setupGlobalNavigationGuard() {
    // Intercept all link clicks
    document.addEventListener('click', function(e) {
      if (!window.globalHasUnsavedChanges) return;
      
      const link = e.target.closest('a');
      if (!link) return;
      
      // Skip if it's an external link or javascript: link
      const href = link.getAttribute('href');
      if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || link.target === '_blank') {
        return;
      }
      
      // Intercept navigation
      e.preventDefault();
      e.stopPropagation();
      
      if (window.globalShowUnsavedModal) {
        window.globalShowUnsavedModal().then(function(shouldDiscard) {
          if (shouldDiscard) {
            window.globalHasUnsavedChanges = false;
            // Navigate to the link
            if (link.onclick) {
              link.onclick.call(link, e);
            } else {
              window.location.href = href;
            }
          }
        });
      }
    }, true); // Use capture phase to intercept before other handlers
    
    // Intercept form submissions (like search)
    document.addEventListener('submit', function(e) {
      if (!window.globalHasUnsavedChanges) return;
      
      const form = e.target;
      if (!form || form.tagName !== 'FORM') return;
      
      e.preventDefault();
      e.stopPropagation();
      
      if (window.globalShowUnsavedModal) {
        window.globalShowUnsavedModal().then(function(shouldDiscard) {
          if (shouldDiscard) {
            window.globalHasUnsavedChanges = false;
            // Submit the form
            form.submit();
          }
        });
      }
    }, true);
    
    console.log('[middle.js] ✅ Global navigation guard enabled');
  }
  
  // Initialize global guard on load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupGlobalNavigationGuard);
  } else {
    setupGlobalNavigationGuard();
  }

  // Handle page refresh/close with unsaved changes
  window.addEventListener('beforeunload', function(e) {
    if (window.globalHasUnsavedChanges) {
      // Standard way to trigger browser's native confirmation dialog
      e.preventDefault();
      e.returnValue = '';
      return '';
    }
  });

  // Initialize on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', function () {
    console.log('[middle.js] 📄 DOMContentLoaded event fired');
    initializeMiddleColumn();
  });

  // Also initialize when middle column fragment is loaded dynamically
  document.addEventListener('fragment:loaded', function(e) {
    console.log('[middle.js] ==========================================');
    console.log('[middle.js] 🔄 fragment:loaded EVENT RECEIVED');
    console.log('[middle.js] ==========================================');
    console.log('[middle.js] Event detail:', e.detail);
    console.log('[middle.js] Container:', e.detail?.container);
    console.log('[middle.js] Path:', e.detail?.path);
    
    if (e.detail && e.detail.container === 'middle-component') {
      console.log('[middle.js] ✨ Middle column fragment loaded, scheduling re-initialization in 50ms...');
      setTimeout(function() {
        console.log('[middle.js] ⏰ 50ms delay complete, calling initializeMiddleColumn...');
        initializeMiddleColumn();
      }, 50); // Small delay to ensure DOM is ready
    } else {
      console.log('[middle.js] ⏭️ Fragment is not for middle-component, ignoring');
    }
  });

  // Expose globally for manual initialization if needed
  window.initializeMiddleColumn = initializeMiddleColumn;
})();