// reactions.js — reaction picker UI with 800ms hover delay and simple selection
(function(){
  const EMOJIS = [
    {key:'like', img:'https://fonts.gstatic.com/s/e/notoemoji/latest/2764_fe0f/512.gif', label:'Like'},
    {key:'haha', img:'https://fonts.gstatic.com/s/e/notoemoji/latest/1f602/512.gif', label:'Laughing'},
    {key:'love', img:'https://fonts.gstatic.com/s/e/notoemoji/latest/1f970/512.gif', label:'Love It'},
    {key:'wow', img:'https://fonts.gstatic.com/s/e/notoemoji/latest/1f62f/512.gif', label:'Surprised'},
    {key:'sad', img:'https://fonts.gstatic.com/s/e/notoemoji/latest/1f622/512.gif', label:'Sad'},
    {key:'angry', img:'https://fonts.gstatic.com/s/e/notoemoji/latest/1f620/512.gif', label:'Angry'}
  ];

  function createPicker() {
    const wrap = document.createElement('div');
    wrap.className = 'reaction-picker';
    wrap.style.cssText = 'position:absolute;visibility:hidden;z-index:60;background:#fff;border-radius:50px;box-shadow:0 4px 20px rgba(0,0,0,0.15);padding:12px 16px;display:flex;align-items:center;gap:10px;border:1px solid #e5e7eb';
    
    EMOJIS.forEach(e => {
      const b = document.createElement('div');
      b.style.cssText = 'display:flex;flex-direction:column;align-items:center;cursor:pointer;padding:8px;border-radius:12px;transition:all 0.15s ease';
      
      const img = document.createElement('img'); 
      img.src = e.img; 
      img.alt = e.label; 
      img.width = 36; 
      img.height = 36;
      img.style.cssText = 'transition:transform 0.15s ease;margin-bottom:4px';
      
      const label = document.createElement('div');
      label.textContent = e.label;
      label.style.cssText = 'font-size:10px;color:#6b7280;font-weight:500;white-space:nowrap';
      
      b.appendChild(img);
      b.appendChild(label);
      
      b.addEventListener('mouseenter', ()=> {
        img.style.transform = 'scale(1.25)';
        b.style.background = '#f3f4f6';
      });
      b.addEventListener('mouseleave', ()=> {
        img.style.transform = 'scale(1)';
        b.style.background = 'transparent';
      });
      b.addEventListener('click', (ev)=>{
        const postEl = wrap._postEl;
        if (!postEl) return;
        hidePicker(wrap);
        const postId = postEl.getAttribute('data-post-id') || postEl.getAttribute('data-id') || null;
        if (postId) {
          fetch('/dashboard/posts/react', {
            method:'POST',
            credentials:'same-origin',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({ target_type: 'post', target_id: postId, reaction_type: e.key })
          }).then(resp => resp.json().catch(()=>null)).then(json => {
            if (!json || !json.success) return;
            postEl.dataset.userReaction = json.user_reaction || '';
            let countsEl = postEl.querySelector('.reaction-counts');
            if (!countsEl) {
              countsEl = document.createElement('div');
              countsEl.className = 'reaction-counts text-xs text-gray-500 ml-2';
              const actions = postEl.querySelector('.mt-3') || postEl.querySelector('.post-header');
              if (actions) actions.appendChild(countsEl);
            }
            const parts = [];
            for (const k in json.counts) {
              parts.push(k + ':' + json.counts[k]);
            }
            countsEl.textContent = parts.join(' • ');
          }).catch(()=>{});
        }
      });
      wrap.appendChild(b);
    });
    document.body.appendChild(wrap);
    return wrap;
  }

  function hidePicker(p){ p.style.visibility='hidden'; }
  function showPicker(p,x,y,postEl){ p.style.left = x+'px'; p.style.top = y+'px'; p.style.visibility='visible'; p._postEl = postEl; }

  document.addEventListener('DOMContentLoaded', ()=>{
    const picker = createPicker();
    let hoverTimer = null;
    document.body.addEventListener('mouseover', (e)=>{
      const btn = e.target.closest && e.target.closest('.reaction-btn');
      if (btn) {
        const postEl = btn.closest('.post');
        hoverTimer = setTimeout(()=>{
          const rect = btn.getBoundingClientRect();
          const x = rect.left + (rect.width/2) - 220;
          const y = rect.top - 100;
          showPicker(picker, Math.max(8,x), Math.max(8,y), postEl);
        }, 800);
      }
    });
    document.body.addEventListener('mouseout',(e)=>{
      const btn = e.target.closest && e.target.closest('.reaction-btn');
      if (btn) { clearTimeout(hoverTimer); }
    });
    document.addEventListener('click',(e)=>{
      if (!e.target.closest || !e.target.closest('.reaction-picker')) {
        hidePicker(picker);
      }
    });
  });
})();
