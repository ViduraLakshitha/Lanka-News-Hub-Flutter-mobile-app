document.addEventListener('DOMContentLoaded', () => {
  const menuButton=document.querySelector('[data-menu-toggle]');
  const siteMenu=document.getElementById('site-navigation');
  menuButton?.addEventListener('click',()=>{
    const open=menuButton.getAttribute('aria-expanded')!=='true';
    menuButton.setAttribute('aria-expanded',String(open));
    menuButton.setAttribute('aria-label',open?'Close navigation':'Open navigation');
    siteMenu?.classList.toggle('menu-open',open);
  });
  document.addEventListener('keydown',event=>{if(event.key==='Escape' && menuButton?.getAttribute('aria-expanded')==='true'){menuButton.setAttribute('aria-expanded','false');menuButton.setAttribute('aria-label','Open navigation');siteMenu?.classList.remove('menu-open');menuButton.focus();}});
  const placement=document.querySelector('[data-banner-position]');
  const dimCard=document.getElementById('ad-dimension-card');
  let currentUploadedImage = null;
  let bannerObjectUrl = null;
  let placementSpecs={};
  if(dimCard && dimCard.dataset.specs){
    try { placementSpecs = JSON.parse(dimCard.dataset.specs); } catch(e){}
  }

  function updateDimensionCard(pos){
    if(!dimCard) return;
    const spec = placementSpecs[pos];
    if(spec){
      const badge = document.getElementById('ad-dim-badge');
      const ratio = document.getElementById('ad-dim-ratio');
      const name = document.getElementById('ad-dim-name');
      const desc = document.getElementById('ad-dim-desc');
      const tmplLink = document.getElementById('ad-dim-template-link');
      if(badge) badge.textContent = spec.dimensions;
      if(ratio) ratio.textContent = 'Ratio: ' + spec.aspect;
      if(name) name.textContent = spec.name;
      if(desc) desc.textContent = spec.description;
      if(tmplLink && spec.image){
        tmplLink.href = '/' + spec.image.replace(/^\//,'');
        tmplLink.textContent = 'Check banner size (' + spec.dimensions + ') ↗';
      }
      dimCard.hidden = false;
    }
  }

  function markPlacement(){
    const current = placement?.value;
    document.querySelectorAll('[data-placement]').forEach(slot=>{
      const isChosen = slot.dataset.placement === current;
      slot.classList.toggle('chosen', isChosen);
      slot.setAttribute('aria-selected', isChosen ? 'true' : 'false');
    });
    if(current) {
      updateDimensionCard(current);
      validateUploadedDimensions();
    }
  }
  placement?.addEventListener('change',markPlacement); markPlacement();

  document.querySelectorAll('.placement-map [data-placement]').forEach(slot=>{
    const selectSlot = () => {
      if(placement && slot.dataset.placement){
        placement.value = slot.dataset.placement;
        placement.dispatchEvent(new Event('change', { bubbles: true }));
        markPlacement();
        placement.focus();
      }
    };
    slot.addEventListener('click', selectSlot);
    slot.addEventListener('keydown', e => {
      if(e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectSlot(); }
    });
  });

  document.querySelectorAll('[data-choose-placement]').forEach(btn => {
    btn.addEventListener('click', () => {
      const slot = btn.dataset.choosePlacement;
      if(placement && slot){
        placement.value = slot;
        placement.dispatchEvent(new Event('change', { bubbles: true }));
        markPlacement();
        document.getElementById('ad-editor')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        placement.focus();
      }
    });
  });

  const adFormat=document.querySelector('[data-ad-format]');
  function showAdFields(){
    const format=adFormat?.value;
    document.querySelectorAll('[data-ad-fields]').forEach(group=>{group.hidden=!group.dataset.adFields.split(' ').includes(format);});
    const upload=document.querySelector('[data-banner-upload]');
    if(upload) upload.accept=format==='video' ? 'video/mp4,video/webm' : 'image/jpeg,image/png,image/webp,image/gif';
    const preview=document.querySelector('[data-banner-preview]');
    if(preview){preview.querySelector('[data-preview-image]').hidden=format==='video';preview.querySelector('[data-preview-video]').hidden=format!=='video';}
  }
  adFormat?.addEventListener('change',showAdFields);showAdFields();
  document.querySelectorAll('[data-ad-preset]').forEach(button=>button.addEventListener('click',()=>{if(adFormat){adFormat.value=button.dataset.adPreset;showAdFields();document.getElementById('ad-editor')?.scrollIntoView({block:'start'});adFormat.focus();}}));

  function validateUploadedDimensions(){
    const inspector = document.getElementById('upload-dim-inspector');
    const badge = document.getElementById('inspector-badge');
    const details = document.getElementById('inspector-details');
    if(!inspector || !currentUploadedImage) return;

    const pos = placement?.value;
    const spec = placementSpecs[pos];
    const w = currentUploadedImage.width;
    const h = currentUploadedImage.height;
    const sizeMb = (currentUploadedImage.fileSize / 1024 / 1024).toFixed(2);
    const sizeKb = Math.round(currentUploadedImage.fileSize / 1024);
    const sizeStr = currentUploadedImage.fileSize < 1048576 ? sizeKb + ' KB' : sizeMb + ' MB';

    inspector.className = 'upload-dim-inspector';
    inspector.style.display = 'flex';

    if(!spec){
      inspector.classList.add('match');
      if(badge) badge.textContent = 'Uploaded: ' + w + ' × ' + h + ' px (' + sizeStr + ')';
      if(details) details.textContent = 'Image loaded ready for publication.';
      return;
    }

    const targetW = spec.width;
    const targetH = spec.height;
    const targetRatio = targetW / targetH;
    const currentRatio = w / h;
    const isExact = (w === targetW && h === targetH);
    const ratioDelta = Math.abs(currentRatio - targetRatio) / targetRatio;
    const isRatioMatch = ratioDelta < 0.08;

    if(isExact){
      inspector.classList.add('match');
      if(badge) badge.textContent = '✓ Exact size match: ' + w + ' × ' + h + ' px';
      if(details) details.textContent = 'Matches ' + spec.name + ' (' + spec.dimensions + ') perfectly · ' + sizeStr;
    } else if(isRatioMatch){
      inspector.classList.add('ratio-match');
      if(badge) badge.textContent = '✓ Aspect ratio matches: ' + w + ' × ' + h + ' px';
      if(details) details.textContent = 'Proportional match to recommended ' + spec.dimensions + ' (Ratio: ' + spec.aspect + '). Scales smoothly · ' + sizeStr;
    } else {
      inspector.classList.add('warn');
      if(badge) badge.textContent = 'ℹ Uploaded size: ' + w + ' × ' + h + ' px';
      if(details) details.textContent = 'Recommended for ' + spec.name + ' is ' + spec.dimensions + ' (Ratio: ' + spec.aspect + '). The image will fit responsively · ' + sizeStr;
    }
  }

  document.querySelector('[data-banner-upload]')?.addEventListener('change',event=>{
    const file=event.target.files[0],preview=document.querySelector('[data-banner-preview]');
    if(!file || !preview) return;
    const inspector=document.getElementById('upload-dim-inspector');
    if(inspector) {
      inspector.className='upload-dim-inspector';
      inspector.style.display='flex';
      const b=document.getElementById('inspector-badge');
      if(b) b.textContent='Reading file dimensions...';
    }
    const reader=new FileReader();
    reader.onload=e=>{
      const dataUrl=e.target.result;
      const img=preview.querySelector('[data-preview-image]'),video=preview.querySelector('[data-preview-video]');
      if(adFormat?.value==='video') {
        video.src=dataUrl;
        if(inspector) {
          inspector.className='upload-dim-inspector match';
          const b=document.getElementById('inspector-badge');
          const d=document.getElementById('inspector-details');
          if(b) b.textContent='▶ Video: ' + file.name;
          if(d) d.textContent=(file.size/1024/1024).toFixed(2)+' MB · MP4/WebM video';
        }
        preview.querySelector('[data-banner-dimensions]').textContent=file.name+' · '+(file.size/1024/1024).toFixed(2)+' MB';
      } else {
        const probe=new Image();
        probe.onload=()=>{
          currentUploadedImage={ width: probe.naturalWidth, height: probe.naturalHeight, fileSize: file.size, name: file.name };
          if(img) img.src=dataUrl;
          preview.querySelector('[data-banner-dimensions]').textContent=probe.naturalWidth+' × '+probe.naturalHeight+' px · '+file.name+' · '+(file.size/1024/1024).toFixed(2)+' MB';
          validateUploadedDimensions();
        };
        probe.onerror=()=>{
          if(inspector) {
            inspector.className='upload-dim-inspector warn';
            const b=document.getElementById('inspector-badge');
            if(b) b.textContent='Cannot preview this file. Choose a supported image.';
          }
        };
        probe.src=dataUrl;
      }
      preview.hidden=false;
      showAdFields();
    };
    reader.readAsDataURL(file);
  });
  const storage = {get: key => { try { return localStorage.getItem(key); } catch { return null; } }, set: (key,value) => { try { localStorage.setItem(key,value); } catch {} }};
  if (!document.body.classList.contains('admin-body')) document.documentElement.dataset.theme = storage.get('reading-theme') || 'light';
  document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = theme; storage.set('reading-theme',theme);
  });
  document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', e => { if (!confirm(form.dataset.confirm)) e.preventDefault(); }));
  document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
    const original = button.textContent;
    try { await navigator.clipboard.writeText(button.dataset.copy); button.textContent = 'Copied ✓'; }
    catch { button.textContent = 'Copy unavailable'; }
    setTimeout(() => button.textContent = original, 2500);
  }));
  document.querySelector('[data-close-ad]')?.addEventListener('click', e => e.currentTarget.closest('.sticky-ad').remove());
  const popup=document.querySelector('[data-ad-popup]');
  if(popup){
    const isPreview = window.location.search.includes('preview_ads=1');
    const close=()=>{popup.hidden=true;try{sessionStorage.setItem('ad-popup-seen','1')}catch{}};
    popup.querySelector('[data-close-popup]')?.addEventListener('click',close);
    popup.addEventListener('click',event=>{if(event.target===popup)close()});
    document.addEventListener('keydown',event=>{if(event.key==='Escape' && !popup.hidden)close()});
    try{if(isPreview || !sessionStorage.getItem('ad-popup-seen'))setTimeout(()=>{popup.hidden=false;popup.querySelector('[data-close-popup]')?.focus()},1200)}catch{setTimeout(()=>popup.hidden=false,1200)}
  }
  document.querySelectorAll('.ad-slot.has-loop').forEach(slot => {
    const slides = Array.from(slot.querySelectorAll('[data-ad-slide]'));
    const dots = Array.from(slot.querySelectorAll('[data-ad-dot]'));
    const counter = slot.querySelector('[data-ad-counter]');
    const sponsorDisplay = slot.querySelector('[data-sponsor-display]');
    if (slides.length <= 1) return;
    let currentIndex = 0;
    let timer = null;
    let isHovered = false;
    const intervalMs = parseInt(slot.dataset.adLoop, 10) || 5000;
    function goToSlide(index) {
      currentIndex = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => {
        const active = i === currentIndex;
        slide.classList.toggle('is-active', active);
        slide.setAttribute('aria-hidden', String(!active));
        if (active && sponsorDisplay) sponsorDisplay.textContent = slide.dataset.adSponsor || '';
      });
      dots.forEach((dot, i) => dot.classList.toggle('is-active', i === currentIndex));
      if (counter) counter.textContent = `${currentIndex + 1}/${slides.length}`;
    }
    function startLoop() {
      stopLoop();
      if (isHovered) return;
      timer = setInterval(() => {
        if (!isHovered) goToSlide(currentIndex + 1);
      }, intervalMs);
    }
    function stopLoop() { if (timer) { clearInterval(timer); timer = null; } }
    slot.addEventListener('mouseenter', () => { isHovered = true; stopLoop(); });
    slot.addEventListener('mouseleave', () => { isHovered = false; startLoop(); });
    slot.addEventListener('focusin', () => { isHovered = true; stopLoop(); });
    slot.addEventListener('focusout', () => { isHovered = false; startLoop(); });
    dots.forEach((dot, i) => {
      dot.addEventListener('click', e => { e.preventDefault(); goToSlide(i); if (!isHovered) startLoop(); });
    });
    startLoop();
  });
  let size = 18;
  document.querySelectorAll('[data-font]').forEach(button => button.addEventListener('click', () => { size = Math.min(28,Math.max(14,size+Number(button.dataset.font))); document.querySelector('[data-article-body]').style.fontSize = size+'px'; }));
  const tabs = [...document.querySelectorAll('[data-tab]')];
  function showTab(id) {
    tabs.forEach(tab => { tab.setAttribute('aria-selected',String(tab.dataset.tab===id)); tab.tabIndex = tab.dataset.tab===id ? 0 : -1; });
    document.querySelectorAll('[data-edition]').forEach(panel => panel.hidden=panel.dataset.edition!==id);
  }
  tabs.forEach((tab,index) => {
    tab.addEventListener('click',()=>showTab(tab.dataset.tab));
    tab.addEventListener('keydown',event=>{ if (!['ArrowLeft','ArrowRight'].includes(event.key)) return; event.preventDefault(); const next=tabs[(index+(event.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length]; showTab(next.dataset.tab); next.focus(); });
  });
  if(tabs.length) showTab(tabs[0].dataset.tab);
  document.querySelectorAll('[data-rich-editor]').forEach(textarea => {
    const toolbar=document.createElement('div'); toolbar.className='rich-toolbar'; toolbar.setAttribute('role','toolbar'); toolbar.setAttribute('aria-label','Text formatting');
    const editor=document.createElement('div'); editor.className='rich-editor'; editor.contentEditable='true'; editor.setAttribute('role','textbox'); editor.setAttribute('aria-multiline','true'); editor.setAttribute('aria-label',textarea.getAttribute('aria-label')); editor.innerHTML=textarea.value;
    const commands=[['Bold','bold'],['Italic','italic'],['Heading','formatBlock','h2'],['Paragraph','formatBlock','p'],['Bullet list','insertUnorderedList'],['Numbered list','insertOrderedList'],['Quote','formatBlock','blockquote'],['Link','createLink'],['Clear','removeFormat']];
    commands.forEach(([label,command,value])=>{
      const button=document.createElement('button'); button.type='button'; button.textContent=label; button.title=label;
      button.addEventListener('mousedown',e=>e.preventDefault());
      button.addEventListener('click',()=>{
        if(command==='createLink'){value=prompt('Enter an https:// or http:// link'); if(!value || !/^https?:\/\//i.test(value)) return;}
        editor.focus(); document.execCommand(command,false,value || null); textarea.value=editor.innerHTML;
      }); toolbar.append(button);
    });
    // Paste as plain text. Server-side allowlisting remains authoritative.
    editor.addEventListener('paste',event=>{event.preventDefault();document.execCommand('insertText',false,event.clipboardData.getData('text/plain'));});
    editor.addEventListener('input',()=>textarea.value=editor.innerHTML);
    textarea.before(toolbar,editor); textarea.hidden=true;
    textarea.form.addEventListener('submit',()=>textarea.value=editor.innerHTML);
  });
  const consent=document.querySelector('[data-consent]');
  let analyticsLoaded=false;
  function analytics(){
    if(!consent || analyticsLoaded) return; analyticsLoaded=true;
    window.dataLayer=window.dataLayer || [];
    const ga=consent.dataset.ga,gtm=consent.dataset.gtm;
    if(/^G-[A-Z0-9]+$/.test(ga)){
      const script=document.createElement('script');script.async=true;script.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(ga);document.head.append(script);
      window.gtag=function(){window.dataLayer.push(arguments)};window.gtag('js',new Date());window.gtag('config',ga);
    }
    if(/^GTM-[A-Z0-9]+$/.test(gtm)){
      window.dataLayer.push({'gtm.start':Date.now(),event:'gtm.js'});
      const script=document.createElement('script');script.async=true;script.src='https://www.googletagmanager.com/gtm.js?id='+encodeURIComponent(gtm);document.head.append(script);
    }
  }
  if(consent){const choice=storage.get('analytics-consent'); if(choice){consent.hidden=true;if(choice==='yes')analytics();} consent.querySelectorAll('[data-consent-value]').forEach(button=>button.addEventListener('click',()=>{storage.set('analytics-consent',button.dataset.consentValue);consent.hidden=true;if(button.dataset.consentValue==='yes')analytics();}));}
});
