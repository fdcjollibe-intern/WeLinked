// dashboard.js — loads components and implements basic infinite-loading trigger
(function () {
  function insertHtmlWithScripts(container, html) {
    const temp = document.createElement('div');
    temp.innerHTML = html;

    Array.from(temp.childNodes).forEach(function (node) {
      if (node.nodeName && node.nodeName.toLowerCase() === 'script') return;
      container.appendChild(node.cloneNode(true));
    });

    Array.from(temp.querySelectorAll('script')).forEach(function (oldScript) {
      const script = document.createElement('script');
      if (oldScript.src) {
        script.src = oldScript.src;
      } else {
        script.textContent = oldScript.textContent;
      }
      script.async = false;
      document.head.appendChild(script);
      document.head.removeChild(script);
    });
  }

  function loadComponent(path, containerId, params) {
    let url = path;
    if (params) url += '?' + new URLSearchParams(params).toString();
    console.debug('Loading component', containerId, 'from', url);
    return fetch(url, { credentials: 'same-origin' })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        insertHtmlWithScripts(container, html);
        container.dispatchEvent(new CustomEvent('fragment:loaded', { detail: { path: url, container: containerId } }));
        return html;
      })
      .catch(function (err) {
        console.error('Failed to load component', containerId, 'from', url, err);
        throw err;
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
    // Handle feed tab switching
    setupFeedTabs();
    
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
        const feed = postsContainer.getAttribute('data-feed') || 'friends';
        loadComponent('/dashboard/middle-column', 'middle-component', { start: start, feed: feed })
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

  function setupFeedTabs() {
    const middleComponent = document.getElementById('middle-component');
    if (!middleComponent) return;

    // Use event delegation for tab clicks
    middleComponent.addEventListener('click', function(e) {
      const tab = e.target.closest('.feed-tab');
      if (!tab) return;
      
      const feed = tab.getAttribute('data-feed');
      if (!feed || feed === 'reels') {
        e.preventDefault();
        return;
      }
      
      e.preventDefault();
      
      // Update active tab styling
      const allTabs = middleComponent.querySelectorAll('.feed-tab');
      allTabs.forEach(function(t) {
        t.className = 'feed-tab text-gray-400 hover:text-gray-600';
      });
      tab.className = 'feed-tab text-blue-500 font-medium border-b-2 border-blue-500 pb-1';
      
      // Load posts for selected feed
      loadComponent('/dashboard/middle-column', 'middle-component', { feed: feed, start: 0 })
        .then(function() {
          setupInteractions();
        });
    });
  }

  // Expose setupInteractions for use after dynamic content loads
  window.setupDashboardInteractions = setupInteractions;
})();
