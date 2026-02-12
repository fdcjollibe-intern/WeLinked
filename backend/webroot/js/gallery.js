// gallery.js — simple carousel with arrows and drag-to-pan
(function(){
  function initGallery(g){
    const wrapper = g.querySelector('.gallery-track');
    if(!wrapper) return;
    const imgs = wrapper.querySelectorAll('img');
    let index = 0;
    const left = g.querySelector('.gallery-left');
    const right = g.querySelector('.gallery-right');
    function show(i){ index = (i + imgs.length) % imgs.length; wrapper.style.transform = 'translateX(' + (-index*100) + '%)'; }
    if(left) left.addEventListener('click', ()=> show(index-1));
    if(right) right.addEventListener('click', ()=> show(index+1));

    // drag to pan
    let startX=0, current=0, dragging=false;
    wrapper.addEventListener('pointerdown',(e)=>{ dragging=true; startX=e.clientX; wrapper.setPointerCapture(e.pointerId); });
    wrapper.addEventListener('pointermove',(e)=>{ if(!dragging) return; const dx=e.clientX-startX; wrapper.style.transform = 'translateX(' + ((-index*100) + (dx / g.clientWidth * 100)) + '%)'; });
    wrapper.addEventListener('pointerup',(e)=>{ if(!dragging) return; dragging=false; const dx=e.clientX-startX; if(Math.abs(dx) > g.clientWidth*0.15){ if(dx<0) show(index+1); else show(index-1); } else show(index); });
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('.post-gallery').forEach(initGallery);
  });
})();
