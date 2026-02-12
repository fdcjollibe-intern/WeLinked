// dashboard.js — loads components and implements basic infinite-loading trigger
(function () {
  function loadComponent(path, containerId, params) {
    let url = path;
    if (params) url += '?' + new URLSearchParams(params).toString();
    return fetch(url, { credentials: 'same-origin' })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        document.getElementById(containerId).innerHTML = html;
      });
  }

  // Load all three components
  document.addEventListener('DOMContentLoaded', function () {
    // Only fetch components if the container is empty. This preserves server-rendered HTML
    const left = document.getElementById('left-component');
    const middle = document.getElementById('middle-component');
    const right = document.getElementById('right-component');

    const needsLoad = (el)=> !el || el.innerHTML.trim().length === 0;

    const promises = [];
    if (needsLoad(left)) promises.push(loadComponent('/dashboard/left-sidebar', 'left-component'));
    if (needsLoad(middle)) promises.push(loadComponent('/dashboard/middle-column', 'middle-component'));
    if (needsLoad(right)) promises.push(loadComponent('/dashboard/right-sidebar', 'right-component'));

    Promise.all(promises).then(function(){
      // wire interactions whether we loaded via AJAX or used server HTML
      try { setupInteractions(); } catch(e) { /* ignore */ }
    }).catch(function(){ try { setupInteractions(); } catch(e){} });
  });

  function setupInteractions() {
    // basic infinite load: when the 15th post is visible, fetch next batch
    const postsContainer = document.getElementById('middle-component')?.querySelector('#posts-list');
    if (!postsContainer) return;

    let loading = false;
    function checkAndLoad() {
      if (loading) return;
      const posts = postsContainer.querySelectorAll('.post');
      if (posts.length === 0) return;
      // find the post with data-index == 15 + multiples
      const last = posts[posts.length - 1];
      const idx = parseInt(last.getAttribute('data-index') || '0', 10);
      // if user has scrolled past the 15th-post threshold from the current batch
      const threshold = 15;
      const visibleCount = Array.from(posts).filter(function (p) {
        const rect = p.getBoundingClientRect();
        return rect.top >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight);
      }).length;

      if (visibleCount >= threshold) {
        loading = true;
        const start = parseInt(postsContainer.getAttribute('data-start') || '0', 10) + posts.length;
        loadComponent('/dashboard/middle-column', 'middle-component', { start: start })
          .then(function () {
            // update start attribute for next fetch
            postsContainer.setAttribute('data-start', start);
            loading = false;
          })
          .catch(function () { loading = false; });
      }
    }

    window.addEventListener('scroll', function () {
      checkAndLoad();
    });
  }
})();
