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
    console.log('[dashboard.js] 📦 Loading component:', { containerId, url, params });
    return fetch(url, { credentials: 'same-origin' })
      .then(function (r) { 
        console.log('[dashboard.js] ✅ Component fetch successful:', { containerId, status: r.status });
        return r.text(); 
      })
      .then(function (html) {
        const container = document.getElementById(containerId);
        if (!container) {
          console.error('[dashboard.js] ❌ Container not found:', containerId);
          return;
        }
        console.log('[dashboard.js] 🔄 Inserting HTML into container:', containerId, 'HTML length:', html.length);
        container.innerHTML = '';
        insertHtmlWithScripts(container, html);
        console.log('[dashboard.js] 🎉 Dispatching fragment:loaded event for:', containerId);
        container.dispatchEvent(new CustomEvent('fragment:loaded', { detail: { path: url, container: containerId } }));
        return html;
      })
      .catch(function (err) {
        console.error('[dashboard.js] ❌ Failed to load component', containerId, 'from', url, err);
        throw err;
      });
  }

  // Load more posts and append to existing posts list (for infinite scroll)
  function loadMorePosts(start, feed) {
    const url = '/dashboard/middle-column?' + new URLSearchParams({ start: start, feed: feed }).toString();
    console.log('[dashboard.js] 📜 Loading more posts:', { start, feed, url });
    
    return fetch(url, { credentials: 'same-origin' })
      .then(function (r) { 
        console.log('[dashboard.js] ✅ Posts fetch successful:', { status: r.status });
        return r.text(); 
      })
      .then(function (html) {
        const postsContainer = document.querySelector('#posts-list');
        if (!postsContainer) {
          console.warn('[dashboard.js] ⚠️ #posts-list not found - posts may not be loaded yet');
          return 0;
        }
        
        // Extract just the posts from the HTML (skip composer, etc.)
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newPosts = temp.querySelectorAll('#posts-list > .post');
        
        console.log('[dashboard.js] 📄 Appending', newPosts.length, 'new posts');
        
        let appended = 0;
        newPosts.forEach(function(post) {
          postsContainer.appendChild(post.cloneNode(true));
          appended++;
        });
        
        console.log('[dashboard.js] ✅ Appended', appended, 'posts to feed');
        return appended;
      })
      .catch(function (err) {
        console.error('[dashboard.js] ❌ Failed to load more posts:', err);
        throw err;
      });
  }

  // Load all three components
  function containerNeedsLoad(el) {
    if (!el) {
      console.log('[dashboard.js] containerNeedsLoad: element is null/undefined');
      return true;
    }
    const withoutComments = el.innerHTML.replace(/<!--[\s\S]*?-->/g, '').trim();
    const needsLoad = withoutComments.length === 0;
    console.log('[dashboard.js] containerNeedsLoad:', {
      elementId: el.id,
      htmlLength: el.innerHTML.length,
      withoutCommentsLength: withoutComments.length,
      needsLoad: needsLoad
    });
    return needsLoad;
  }

  function highlightPostFromHash() {
    const hash = window.location.hash;
    if (!hash || !hash.startsWith('#post-')) return;
    const target = document.querySelector(hash);
    if (!target) return;
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    target.classList.add('ring', 'ring-blue-400');
    setTimeout(() => target.classList.remove('ring', 'ring-blue-400'), 2500);
  }

  document.addEventListener('DOMContentLoaded', function () {
    console.log('[dashboard.js] ========================================');
    console.log('[dashboard.js] 📄 DOMContentLoaded event fired');
    console.log('[dashboard.js] ========================================');
    
    // Only fetch components if the container is empty. This preserves server-rendered HTML
    const left = document.getElementById('left-component');
    const middle = document.getElementById('middle-component');
    const right = document.getElementById('right-component');
    
    console.log('[dashboard.js] 🔍 Container detection:', {
      left: left ? '✅ exists' : '❌ not found',
      middle: middle ? '✅ exists' : '❌ not found',
      right: right ? '✅ exists' : '❌ not found'
    });
    
    const leftNeeds = containerNeedsLoad(left);
    const middleNeeds = containerNeedsLoad(middle);
    const rightNeeds = containerNeedsLoad(right);
    
    console.log('[dashboard.js] 📊 Container load status:', {
      leftNeedsLoad: leftNeeds,
      middleNeedsLoad: middleNeeds,
      rightNeedsLoad: rightNeeds
    });
    
    if (middle) {
      console.log('[dashboard.js] 📄 Middle container HTML length:', middle.innerHTML.length);
      console.log('[dashboard.js] 📄 Middle container HTML preview:', middle.innerHTML.substring(0, 200));
    }

    const promises = [];
    if (leftNeeds) {
      console.log('[dashboard.js] 📦 Queueing left-sidebar load');
      promises.push(loadComponent('/dashboard/left-sidebar', 'left-component'));
    }
    if (middleNeeds) {
      console.log('[dashboard.js] 📦 Queueing middle-column load');
      promises.push(loadComponent('/dashboard/middle-column', 'middle-component'));
    }
    if (rightNeeds) {
      console.log('[dashboard.js] 📦 Queueing right-sidebar load');
      promises.push(loadComponent('/dashboard/right-sidebar', 'right-component'));
    }
    
    console.log('[dashboard.js] 🚀 Starting', promises.length, 'component loads...');

    Promise.all(promises).then(function(){
      console.log('[dashboard.js] ✅ All component loads complete');
      // wire interactions whether we loaded via AJAX or used server HTML
      try { setupInteractions(); highlightPostFromHash(); } catch(e) { 
        console.error('[dashboard.js] ❌ setupInteractions error:', e);
      }
    }).catch(function(err){ 
      console.error('[dashboard.js] ❌ Component load error:', err);
      try { setupInteractions(); } catch(e){
        console.error('[dashboard.js] ❌ setupInteractions error in catch:', e);
      }
    });
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
        // Use posts.length as the next start offset (total posts loaded so far)
        const start = posts.length;
        const feed = postsContainer.getAttribute('data-feed') || 'friends';
        console.log('[dashboard.js] 📜 Infinite scroll triggered:', { currentPosts: posts.length, nextStart: start, feed: feed });
        
        loadMorePosts(start, feed)
          .then(function (appended) {
            console.log('[dashboard.js] ✅ Infinite scroll complete, appended:', appended);
            loading = false;
            
            // If no posts were appended, stop trying to load more
            if (appended === 0) {
              console.log('[dashboard.js] 🏁 No more posts available, disabling infinite scroll');
              window.removeEventListener('scroll', checkAndLoad);
            }
          })
          .catch(function (err) { 
            console.error('[dashboard.js] ❌ Infinite scroll load failed:', err);
            loading = false; 
          });
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
      if (!feed) {
        e.preventDefault();
        return;
      }
      
      e.preventDefault();
      
      // Check for unsaved changes before switching
      if (window.globalHasUnsavedChanges && window.globalShowUnsavedModal) {
        window.globalShowUnsavedModal().then(function(shouldDiscard) {
          if (shouldDiscard) {
            window.globalHasUnsavedChanges = false;
            switchFeed(tab, feed);
          }
        });
        return;
      }
      
      switchFeed(tab, feed);
    });
    
    function switchFeed(tab, feed) {
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
    }
  }

  // Expose setupInteractions for use after dynamic content loads
  window.setupDashboardInteractions = setupInteractions;

  document.addEventListener('fragment:loaded', function(event){
    if (event.detail && event.detail.container === 'middle-component') {
      highlightPostFromHash();
    }
  });
})();
