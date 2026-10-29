(() => {
  const projects = window.gsbProjects || [];
  if (!projects.length) return;
  const language = () => document.documentElement.dataset.language || 'en';
  const englishPpa = [
    ['Industrial rooftop energy system', 'Clean energy infrastructure for industrial operations.'],
    ['Factory rooftop solar', 'Reliable solar generation designed for manufacturing sites.'],
    ['Nonwoven production facility', 'Solar PPA for a high-demand production environment.'],
    ['Coastal commercial site', 'Practical clean energy for a commercial destination.'],
    ['Manufacturing rooftop system', 'Optimised rooftop solar for daily energy use.'],
    ['High-performance solar equipment', 'Solar equipment selected for dependable output.'],
    ['Urban industrial energy system', 'Solar infrastructure for an active industrial site.'],
    ['Market canopy solar', 'Solar canopy generation for a public-facing location.'],
    ['Packaging facility solar', 'A long-term clean-energy asset for packaging operations.'],
    ['Lighting manufacturing facility', 'Solar generation aligned with factory demand.'],
    ['Chemical industry solar', 'Reliable energy support for complex operations.'],
    ['Commercial rooftop solution', 'Solar PPA built around commercial energy needs.'],
    ['Wood processing solar', 'A practical rooftop system for production facilities.'],
    ['Service facility energy system', 'Clean power for an all-day service location.'],
    ['City-based solar infrastructure', 'An urban clean-energy installation with lasting value.'],
  ];
  const englishEpc = [
    ['Industrial rooftop solar', 'Engineering and delivery for an industrial energy asset.'],
    ['Commercial rooftop solar', 'A practical solar system for a commercial site.'],
    ['Manufacturing energy system', 'Designed for the demands of daily production.'],
    ['Large-scale rooftop solar', 'Coordinated delivery for a high-capacity rooftop system.'],
    ['Factory rooftop PV', 'Site-led design, procurement and construction.'],
    ['Commercial energy upgrade', 'Solar infrastructure built around business operations.'],
    ['Rooftop solar deployment', 'Disciplined project delivery from survey to handover.'],
    ['Industrial PV system', 'Reliable equipment and controlled site execution.'],
    ['Site-specific solar design', 'A system specified for the realities of the site.'],
    ['Business rooftop solution', 'Clear delivery for a long-term energy asset.'],
    ['Integrated solar delivery', 'One team accountable across every delivery stage.'],
    ['Operational solar asset', 'Commissioned infrastructure ready to perform.'],
  ];
  const value = (project, field, index = -1) => {
    if (type === 'ppa' && language() === 'en' && index >= 0 && englishPpa[index]) {
      if (field === 'title') return englishPpa[index][0];
      if (field === 'overview' || field === 'configuration') return englishPpa[index][1];
    }
    if (type === 'epc' && language() === 'en' && index >= 0 && englishEpc[index]) {
      if (field === 'title') return englishEpc[index][0];
      if (field === 'overview' || field === 'configuration') return englishEpc[index][1];
    }
    return project[`${field}_${language()}`] || project[`${field}_en`] || project[`${field}_th`] || project[`${field}_zh`] || '';
  };
  const type = projects[0].type;
  const gallery = document.querySelector(type === 'ppa' ? '.solar-gallery' : type === 'epc' ? '.epc-gallery' : '.ev-gallery');
  if (!gallery) return;
  const label = type === 'ppa' ? 'Solar PPA' : type === 'epc' ? 'Solar EPC' : 'EV CHARGING';
  const cardClass = type === 'ppa' ? 'solar-case' : type === 'epc' ? 'epc-card' : 'ev-card';
  const esc = (text) => String(text || '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
  const renderGallery = () => {
    gallery.innerHTML = projects.map((p, i) => `<article class="${cardClass}${i === 0 && type !== 'ev' ? ' ' + (type === 'ppa' ? 'solar-case-featured' : 'epc-featured') : ''}" data-project-index="${i}" tabindex="0" role="button"><img src="${esc(p.image_url || '')}" alt="${esc(value(p, 'title', i))}"><div><small>${String(i + 1).padStart(2, '0')} / ${label}</small><h3>${esc(value(p, 'title', i))}</h3><p>${esc(value(p, 'overview', i) || value(p, 'configuration', i))}</p>${type === 'ppa' ? `<span>${label} <b>↗</b></span>` : ''}</div></article>`).join('');
  };
  renderGallery();
  const modal = document.createElement('dialog');
  modal.className = type === 'ppa' ? 'project-modal' : 'epc-modal';
  const modalType = type === 'ppa' ? 'project-modal' : 'epc-modal';
  modal.innerHTML = `<div class="${modalType}-panel"><button class="${modalType}-close" type="button" aria-label="Close">×</button><div class="${modalType}-image"><img alt=""><div class="${modalType}-gallery project-modal-gallery" aria-label="Project photo gallery"></div></div><div class="${modalType}-content"><p class="modal-kicker"></p><h2></h2><p class="${modalType}-thai modal-summary"></p><div class="${modalType}-capacity"><span></span><strong></strong></div><div class="${modalType}-details modal-details"></div><a href="contact.html" class="${modalType}-cta">Contact us <span>→</span></a></div></div>`;
  document.body.append(modal);
  modal.querySelector('button').onclick = () => modal.close();
  modal.onclick = e => { if (e.target === modal) modal.close(); };
  const labels = { en: [type === 'ppa' ? 'Investment value' : 'Capacity', 'Location', 'Project type', 'Service model', 'Project status'], th: [type === 'ppa' ? 'มูลค่าโครงการ' : 'ขนาด/กำลัง', 'สถานที่', 'รูปแบบโครงการ', 'รูปแบบธุรกิจ', 'สถานะโครงการ'], zh: [type === 'ppa' ? '投资金额' : '规模/功率', '项目地点', '项目类型', '业务模式', '项目状态'] };
  const open = index => {
    const p = projects[index], l = labels[language()] || labels.en;
    const mainImage = modal.querySelector(`.${modalType}-image img`);
    const projectImages = [...new Set([p.image_url, ...(p.gallery_paths || [])].filter(Boolean))];
    mainImage.src = projectImages[0] || ''; mainImage.alt = value(p, 'title', index);
    const galleryElement = modal.querySelector(`.${modalType}-gallery`);
    galleryElement.innerHTML = projectImages.length > 1 ? projectImages.map((image, imageIndex) => `<button type="button" class="${imageIndex === 0 ? 'is-active' : ''}" aria-label="View project photo ${imageIndex + 1}" aria-pressed="${imageIndex === 0}"><img src="${esc(image)}" alt=""></button>`).join('') : '';
    galleryElement.querySelectorAll('button').forEach((button, imageIndex) => {
      button.onclick = () => {
        mainImage.src = projectImages[imageIndex];
        galleryElement.querySelectorAll('button').forEach((item, itemIndex) => {
          item.classList.toggle('is-active', itemIndex === imageIndex);
          item.setAttribute('aria-pressed', String(itemIndex === imageIndex));
        });
      };
    });
    modal.querySelector('.modal-kicker').textContent = `${label} / ${String(index + 1).padStart(2, '0')}`;
    modal.querySelector('h2').textContent = value(p, 'title', index); modal.querySelector('.modal-summary').textContent = value(p, 'overview', index);
    modal.querySelector(`.${modalType}-capacity span`).textContent = l[0];
    modal.querySelector(`.${modalType}-capacity strong`).textContent = value(p, 'scale');
    modal.querySelector('.modal-details').innerHTML = [[l[1], value(p,'location')],[l[2],value(p,'configuration')],[l[3],value(p,'model')],[l[4],value(p,'status')]].filter(([,v])=>v).map(([k,v])=>`<div><span>${esc(k)}</span><b>${esc(v)}</b></div>`).join('');
    modal.showModal();
  };
  const bindCards = () => gallery.querySelectorAll('[data-project-index]').forEach(card => { const openCard = () => open(Number(card.dataset.projectIndex)); card.onclick = openCard; card.onkeydown = e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openCard(); } }; });
  bindCards();
  window.addEventListener('gsb-language-change', () => { renderGallery(); bindCards(); });
})();
