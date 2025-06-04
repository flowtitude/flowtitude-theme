// Flowtitude Notify utility
(function(global){
  const COLORS = {
    success: 'var(--ft-sem-success, #059669)',
    error:   'var(--ft-sem-error, #DC2626)',
    warning: 'var(--ft-sem-warning, #F59E0B)',
    info:    'var(--ft-sem-info, #2563EB)'
  };

  const ICONS = {
    success: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 1em; height: 1em; color: white;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
    error: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 1em; height: 1em; color: white;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
    warning: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 1em; height: 1em; color: white;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" /></svg>',
    info: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 1em; height: 1em; color: white;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" /></svg>'
  };

  function createContainer(){
    let c = document.getElementById('flowtitude-toast-container');
    if(!c){
      c = document.createElement('div');
      c.id = 'flowtitude-toast-container';
      Object.assign(c.style, {
        position:'fixed',
        right:'1.5rem',
        bottom:'1.5rem',
        display:'flex',
        flexDirection:'column',
        gap:'10px',
        zIndex:99999,
        pointerEvents:'none'
      });
      document.body.appendChild(c);
    }
    return c;
  }

  function show(message,type='info',duration=3000){
    const container = createContainer();
    const toast = document.createElement('div');
    toast.className = 'flowtitude-toast';
    const bg = COLORS[type] || COLORS.info;
    Object.assign(toast.style, {
      background:bg,
      color:'#fff',
      padding:'10px 14px',
      borderRadius:'6px',
      minWidth:'200px',
      maxWidth:'320px',
      boxShadow:'0 4px 12px rgba(0,0,0,0.15)',
      display:'flex',
      alignItems:'center',
      gap:'8px',
      fontSize:'14px',
      pointerEvents:'auto',
      opacity:'0',
      transform:'translateY(10px)',
      transition:'opacity .25s ease, transform .25s ease'
    });
    toast.innerHTML = `<span style="font-size:16px;">${ICONS[type]}</span><span style="flex:1;">${message}</span>`;
    container.appendChild(toast);
    requestAnimationFrame(()=>{
      toast.style.opacity='1';
      toast.style.transform='translateY(0)';
    });
    setTimeout(()=>{
      toast.style.opacity='0';
      toast.style.transform='translateY(10px)';
      setTimeout(()=> toast.remove(),250);
    },duration);
  }

  global.FlowtitudeNotify = { show };
})(window); 