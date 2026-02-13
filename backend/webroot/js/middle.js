// middle.js — handles composer, file uploads with progress, and post creation
(function () {
  function el(id) { return document.getElementById(id); }

  function createProgressBar(filename) {
    const wrap = document.createElement('div');
    wrap.className = 'upload-item relative rounded-lg overflow-hidden bg-gray-50 p-2 flex items-center gap-3';
    const label = document.createElement('div');
    label.textContent = filename;
    label.className = 'text-sm text-gray-700 flex-1';

    // circular progress using conic-gradient
    const circle = document.createElement('div');
    circle.className = 'progress-circle w-12 h-12 rounded-full flex items-center justify-center bg-white shadow';
    circle.style.position = 'relative';
    circle.dataset.pct = '0';
    const pct = document.createElement('div');
    pct.className = 'progress-pct text-xs font-semibold text-gray-700';
    pct.textContent = '0%';
    circle.appendChild(pct);

    wrap.appendChild(circle);
    wrap.appendChild(label);

    return {
      wrap,
      setProgress: function (n) {
        const clamped = Math.max(0, Math.min(100, Math.round(n)));
        circle.style.background = 'conic-gradient(#3b82f6 ' + clamped + '%, #e6e6e6 ' + clamped + '%)';
        pct.textContent = clamped + '%';
      }
    };
  }

  function uploadFile(file, type, onProgress) {
    return new Promise(function (resolve, reject) {
      const xhr = new XMLHttpRequest();
      const url = '/dashboard/upload?type=' + encodeURIComponent(type);
      const fd = new FormData();
      fd.append('file', file, file.name);
      
      // Add CSRF token
      const csrfToken = window.csrfToken || '';
      if (csrfToken) {
        fd.append('_csrfToken', csrfToken);
      }
      
      xhr.open('POST', url, true);
      xhr.withCredentials = true;
      xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
          const pct = Math.round((e.loaded / e.total) * 100);
          onProgress(pct);
        }
      };
      xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            const json = JSON.parse(xhr.responseText || '{}');
            resolve(json.files ? json.files[0] : null);
          } catch (err) {
            resolve(null);
          }
        } else {
          reject(new Error('Upload failed: ' + xhr.status));
        }
      };
      xhr.onerror = function () { reject(new Error('Upload network error')); };
      xhr.send(fd);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const input = el('attachment-input');
    // reuse existing preview container if present, otherwise create and append to composer
    let preview = document.getElementById('attachment-preview');
    if (!preview) {
      preview = document.createElement('div');
      preview.id = 'attachment-preview';
      const composer = document.querySelector('.composer');
      if (composer) composer.appendChild(preview);
      else document.body.appendChild(preview);
    }

    const submit = el('post-submit');
    const postInput = el('post-input');

    // Exit early if required elements don't exist (not on a page with composer)
    if (!input || !submit || !postInput) {
      return;
    }
    
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
      preview.innerHTML = '';
      Array.from(input.files || []).forEach(function (f) {
        const node = createProgressBar(f.name);
        preview.appendChild(node.wrap);
      });
    });

    submit.addEventListener('click', function () {
      const files = Array.from(input.files || []);
      const body = postInput.value || '';
      if (files.length === 0 && body.trim() === '') {
        alert('Write something or add an attachment.');
        return;
      }

      // Validate files
      const maxSize = 250 * 1024 * 1024;
      for (let f of files) {
        if (f.size > maxSize) { alert('File too large: ' + f.name); return; }
        if (!f.type.startsWith('image/') && !f.type.startsWith('video/')) { alert('Invalid file type: ' + f.name); return; }
      }

      // Check for single video -> Reels
      const videos = files.filter(f => f.type.startsWith('video/'));
      const images = files.filter(f => f.type.startsWith('image/'));
      
      if (videos.length === 1 && images.length === 0 && window.showReelsConfirmation) {
        window.showReelsConfirmation(function(confirmed) {
          if (confirmed) {
            proceedWithUpload(files, body, true); // Mark as reel
          }
        });
        return;
      }

      // Proceed normally
      proceedWithUpload(files, body, false);
    });

    function proceedWithUpload(files, body, isReel) {
      // Upload files sequentially with progress
      const uploadedUrls = [];
      const items = [];

      Array.from(files).forEach(function (f) {
        const node = createProgressBar(f.name);
        preview.appendChild(node.wrap);
        items.push(node);
      });

      (function uploadNext(i) {
        if (i >= files.length) {
          // create post with uploadedUrls
          fetch('/dashboard/posts/create', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body: body, attachments: uploadedUrls, is_reel: isReel })
          }).then(function (r) { return r.json(); })
            .then(function (json) {
              // show modal with close button
              const modal = document.createElement('div');
              modal.className = 'thread-toast fixed top-6 right-6 bg-white shadow-lg border rounded-lg px-4 py-3 z-60 flex items-center gap-3';
              const txt = document.createElement('div'); 
              txt.textContent = isReel ? 'Reel successfully posted' : 'Post successfully created';
              const close = document.createElement('button'); close.className = 'ml-2 text-gray-500'; close.textContent = '✕';
              close.addEventListener('click', ()=> modal.remove());
              modal.appendChild(txt); modal.appendChild(close);
              document.body.appendChild(modal);
              setTimeout(function () { if (modal.parentNode) modal.parentNode.removeChild(modal); }, 3000);

              // prepend new post fragment to posts-list if possible
              const postsList = document.getElementById('posts-list');
              if (postsList && json && json.post) {
                const article = document.createElement('article');
                article.className = 'post bg-white rounded-2xl shadow-sm border border-gray-100 p-5';
                article.dataset.postId = json.post.id || Date.now();
                
                const user = (json.post.user && (json.post.user.username || json.post.user)) || 'You';
                let html = '<div class="flex items-start justify-between mb-4">';
                html += '<div class="flex items-center space-x-3">';
                html += '<div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">' + user.charAt(0).toUpperCase() + '</div>';
                html += '<div><h3 class="font-semibold text-gray-900">' + user + '</h3>';
                html += '<p class="text-xs text-gray-400">Just now</p></div></div></div>';
                
                if (json.post.body) {
                  html += '<p class="text-gray-700 mb-4">' + json.post.body + '</p>';
                }
                
                // Add photo collage if images exist
                if (json.post.attachments && json.post.attachments.length > 0 && window.createPhotoCollage) {
                  const imageUrls = json.post.attachments.map(a => a.url || a);
                  html += window.createPhotoCollage(imageUrls, json.post.id);
                }
                
                article.innerHTML = html;
                
                // Insert after composer
                const composer = postsList.querySelector('.composer');
                if (composer && composer.nextSibling) {
                  postsList.insertBefore(article, composer.nextSibling);
                } else {
                  postsList.insertBefore(article, postsList.firstChild);
                }
              }

              // clear composer and reset height
              postInput.value = '';
              if(postInput && postInput.tagName && postInput.tagName.toLowerCase()==='textarea'){
                postInput.style.height = '';
                autoResizeTextarea(postInput);
              }
              input.value = '';
              preview.innerHTML = '';
            });
          return;
        }

        const file = files[i];
        const node = items[i];
        uploadFile(file, 'post', function (pct) {
          if (node && node.setProgress) node.setProgress(pct);
        }).then(function (res) {
          if (res && res.url) uploadedUrls.push(res.url);
          uploadNext(i + 1);
        }).catch(function (err) { alert('Upload failed: ' + err.message); });
      })(0);
    }

    // Infinite scroll: fetch next fragments from /dashboard/middle-column?start=N&feed=...
    (function initInfiniteScroll(){
      const postsList = document.getElementById('posts-list');
      if(!postsList) return;
      let loading = false;
      let limit = 20;
      function loadMore(){
        if(loading) return; loading=true;
        const start = parseInt(postsList.getAttribute('data-start')||0,10) + postsList.querySelectorAll('.post').length;
        const feed = postsList.getAttribute('data-feed') || 'friends';
        fetch('/dashboard/middle-column?start=' + start + '&feed=' + encodeURIComponent(feed))
          .then(r => r.text())
          .then(html => {
            // parse returned fragment and extract posts
            const tmp = document.createElement('div'); tmp.innerHTML = html;
            const newList = tmp.querySelector('#posts-list');
            if(!newList) { loading=false; return; }
            const children = newList.children;
            let appended = 0;
            Array.from(children).forEach(ch => { postsList.appendChild(ch); appended++; });
            // update start
            const newStart = start + appended;
            postsList.setAttribute('data-start', newStart);
            // if less than limit returned, stop further loads
            if(appended < limit){ window.removeEventListener('scroll', onScroll); }
            loading=false;
          }).catch(()=>{ loading=false; });
      }
      function onScroll(){
        const nearBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 800;
        if(nearBottom) loadMore();
      }
      window.addEventListener('scroll', onScroll);
    })();
  });
})();

