// middle.js — handles composer, file uploads with progress, and post creation
(function () {
  function el(id) { return document.getElementById(id); }

  function createProgressBar(filename) {
    const wrap = document.createElement('div');
    wrap.className = 'upload-item';
    const label = document.createElement('div');
    label.textContent = filename;
    const bar = document.createElement('div');
    bar.className = 'progress-bar';
    const fill = document.createElement('div');
    fill.className = 'progress-fill';
    fill.style.width = '0%';
    bar.appendChild(fill);
    wrap.appendChild(label);
    wrap.appendChild(bar);
    return { wrap, fill };
  }

  function uploadFile(file, type, onProgress) {
    return new Promise(function (resolve, reject) {
      const xhr = new XMLHttpRequest();
      const url = '/dashboard/upload?type=' + encodeURIComponent(type);
      const fd = new FormData();
      fd.append('file', file, file.name);
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
    const preview = document.createElement('div');
    preview.id = 'attachment-preview';
    const composer = document.querySelector('.composer');
    if (composer) composer.appendChild(preview);

    const submit = el('post-submit');
    const postInput = el('post-input');

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

      // Upload files sequentially with progress
      const uploadedUrls = [];
      const items = preview.querySelectorAll('.upload-item');

      (function uploadNext(i) {
        if (i >= files.length) {
          // create post with uploadedUrls
          fetch('/dashboard/posts/create', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body: body, attachments: uploadedUrls })
          }).then(function (r) { return r.json(); })
            .then(function (json) {
              // show simple modal / toast
              const modal = document.createElement('div');
              modal.className = 'simple-modal';
              modal.textContent = 'Thread posted';
              document.body.appendChild(modal);
              setTimeout(function () { modal.remove(); }, 1500);

              // prepend new post fragment to posts-list if possible
              const postsList = document.getElementById('posts-list');
              if (postsList) {
                const article = document.createElement('article');
                article.className = 'post';
                article.innerHTML = '<div class="post-header">' + (json.post.user.username || 'you') + '</div>' +
                  '<div class="post-body">' + (json.post.body || '') + '</div>';
                postsList.insertBefore(article, postsList.firstChild);
              }

              // clear composer
              postInput.value = '';
              input.value = '';
              preview.innerHTML = '';
            });
          return;
        }

        const file = files[i];
        const item = items[i];
        const fill = item ? item.querySelector('.progress-fill') : null;
        uploadFile(file, 'post', function (pct) {
          if (fill) fill.style.width = pct + '%';
        }).then(function (res) {
          if (res && res.url) uploadedUrls.push(res.url);
          uploadNext(i + 1);
        }).catch(function (err) { alert('Upload failed: ' + err.message); });
      })(0);
    });
  });
})();
