(() => {
  const language = localStorage.getItem('gsb-language') || 'en';
  const serviceCopy = {
    en: {
      solar: ['Select solar installation service', 'Solar installation', ['Factory installation', 'Commercial building installation', 'Investment and operating model consultation']],
      ev: ['Select EV charging service', 'EV charging station', ['Propose a site for GSB investment', 'Invest in EV charging installation', 'Ask about EV charging station usage']],
      general: ['Select your topic', 'General enquiry', ['Business partnership enquiry', 'Other enquiry']],
    },
    th: {
      solar: ['เลือกบริการด้านโซลาร์เซลล์', 'บริการโซลาร์เซลล์', ['ติดตั้งระบบสำหรับโรงงาน', 'ติดตั้งระบบสำหรับอาคารพาณิชย์', 'ปรึกษาด้านการลงทุนและการดำเนินงาน']],
      ev: ['เลือกบริการสถานีชาร์จ EV', 'สถานีชาร์จ EV', ['เสนอพื้นที่ให้ GSB พิจารณาลงทุน', 'ลงทุนติดตั้งสถานีชาร์จ EV', 'สอบถามการใช้งานสถานีชาร์จ EV']],
      general: ['เลือกหัวข้อที่ต้องการ', 'สอบถามทั่วไป', ['สอบถามความร่วมมือทางธุรกิจ', 'สอบถามข้อมูลอื่น ๆ']],
    },
    zh: {
      solar: ['选择太阳能服务', '太阳能解决方案', ['工厂安装', '商业建筑安装', '投资与运营咨询']],
      ev: ['选择电动汽车充电服务', '电动汽车充电站', ['推荐场地供 GSB 投资', '投资建设电动汽车充电站', '咨询充电站使用方式']],
      general: ['选择咨询主题', '一般咨询', ['商务合作咨询', '其他咨询']],
    },
  }[language] || {};
  const services = Object.fromEntries(Object.entries(serviceCopy).map(([key, [label, category, options]]) => [key, { label, category, options }]));
  const panel = document.querySelector('[data-gx-panel]');
  const heroLink = document.querySelector('[data-gx-link]');
  const heroOptions = document.querySelector('[data-gx-options]');
  const consultButtons = document.querySelectorAll('[data-gx-consult]');
  const consultDialog = document.querySelector('[data-gx-dialog]');
  const formCategory = document.querySelector('[data-gx-form-category]');
  const formValue = document.querySelector('[data-gx-form-value]');
  const formService = document.querySelector('[data-gx-form-service]');
  let selectedService = services.solar;
  let selectedOption = services.solar.options[0];
  document.querySelectorAll('[data-gx-service]').forEach((button) => {
    button.addEventListener('click', () => {
      const service = services[button.dataset.gxService];
      selectedService = service;
      selectedOption = service.options[0];
      document.querySelectorAll('[data-gx-service]').forEach((item) => { item.classList.remove('is-active'); item.setAttribute('aria-selected', 'false'); });
      button.classList.add('is-active'); button.setAttribute('aria-selected', 'true');
      if (panel && service) panel.innerHTML = '<p>' + service.text + '</p><a href="' + service.href + '">' + service.label + ' <span>↗</span></a>';
      if (heroLink && service) heroLink.textContent = service.label;
      if (heroOptions && service) heroOptions.innerHTML = service.options.map((option) => '<button type="button" data-gx-option>' + option + '</button>').join('');
    });
  });
  document.addEventListener('click', (event) => {
    const option = event.target.closest('[data-gx-option]');
    if (!option || !heroLink) return;
    heroLink.textContent = option.textContent;
    selectedOption = option.textContent;
    option.closest('.gx-service-select')?.removeAttribute('open');
  });
  consultButtons.forEach((consultButton) => consultButton.addEventListener('click', () => {
    if (!consultDialog || !selectedService) return;
    if (formCategory) formCategory.textContent = selectedService.category;
    if (formValue) formValue.value = Object.keys(services).find((key) => services[key] === selectedService) || 'general';
    if (formService) formService.value = selectedOption;
    consultDialog.showModal();
  }));
  document.querySelector('[data-gx-close]')?.addEventListener('click', () => consultDialog?.close());

  const slides = Array.from(document.querySelectorAll('[data-gx-slider] article'));
  const count = document.querySelector('[data-gx-count]');
  let current = 0;
  const show = (index) => {
    if (!slides.length) return;
    current = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => slide.classList.toggle('is-current', i === current));
    if (count) count.textContent = String(current + 1).padStart(2, '0') + ' / ' + String(slides.length).padStart(2, '0');
  };
  document.querySelector('[data-gx-prev]')?.addEventListener('click', () => show(current - 1));
  document.querySelector('[data-gx-next]')?.addEventListener('click', () => show(current + 1));
  if (slides.length) setInterval(() => show(current + 1), 6500);

  const reviewStage = document.querySelector('[data-gx-reviews]');
  const reviewDotsRoot = document.querySelector('.gx-review-dots');
  const managedTestimonials = Array.isArray(window.gsbTestimonials) ? window.gsbTestimonials : [];
  const escapeReview = (value) => String(value || '').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character]));
  if (reviewStage && managedTestimonials.length) {
    const pages = [];
    for (let index = 0; index < managedTestimonials.length; index += 3) {
      const items = managedTestimonials.slice(index, index + 3).map((testimonial) => `<article><p lang="th">${escapeReview(testimonial.quote_th || testimonial.quote_en || testimonial.quote_zh)}</p><span class="gx-stars" aria-label="${testimonial.rating} stars">${'★'.repeat(Number(testimonial.rating || 5))}</span><footer><b>${escapeReview(testimonial.name)}</b>${testimonial.image_path ? `<img class="gx-review-avatar" src="/${escapeReview(testimonial.image_path)}" alt="${escapeReview(testimonial.name)}">` : ''}</footer></article>`).join('');
      pages.push(`<div class="gx-review-page${index === 0 ? ' is-current' : ''}" data-gx-review-page>${items}</div>`);
    }
    reviewStage.innerHTML = pages.join('');
    if (reviewDotsRoot) reviewDotsRoot.innerHTML = pages.map((_, index) => `<button class="${index === 0 ? 'is-active' : ''}" type="button" data-gx-review-dot aria-label="Show review page ${index + 1}"></button>`).join('');
  }
  const reviewPages = Array.from(document.querySelectorAll('[data-gx-review-page]'));
  const reviewDots = Array.from(document.querySelectorAll('[data-gx-review-dot]'));
  let reviewCurrent = 0;
  const showReviews = (index) => {
    if (!reviewPages.length) return;
    reviewCurrent = (index + reviewPages.length) % reviewPages.length;
    reviewPages.forEach((page, pageIndex) => page.classList.toggle('is-current', pageIndex === reviewCurrent));
    reviewDots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === reviewCurrent));
  };
  reviewDots.forEach((dot, index) => dot.addEventListener('click', () => showReviews(index)));
  if (reviewPages.length > 1) setInterval(() => showReviews(reviewCurrent + 1), 5800);

  const languageMenu = document.querySelector('.gx-language');
  const hasPublishedBlogContent = () => window.gsbHasPublishedBlogContent !== false;
  const primaryNavigation = document.querySelector('.gx-navline nav');
  if (primaryNavigation) primaryNavigation.innerHTML = `<a href="/about.html">About us</a>${hasPublishedBlogContent() ? '<a href="/blog.html">Blog</a>' : ''}<a href="/solutions.html">Solutions</a>`;
  document.querySelector('.gx-intro-link')?.setAttribute('href', 'solutions.html');
  const languageLabel = document.querySelector('[data-gx-language-label]');
  const translations = {
    EN: { benefits: { eyebrow: 'WHY GSB SUNPOWER', titleOne: 'Built around', titleTwo: 'what matters', summary: 'Clean energy projects should be simple to understand, dependable to run and ready to grow with your business', boiTitle: 'BOI-supported investment', boiText: 'Eligible projects can access investment incentives and tax benefits', teamTitle: 'Experienced specialist team', teamText: 'Practical guidance from the first conversation to every next step', deliveryTitle: 'End-to-end delivery', deliveryText: 'Site survey, design, installation, project management and maintenance', smartTitle: 'Smart energy management', smartText: 'Real-time system visibility through an application designed for your site', cta: 'Explore GSB SunPower <span>↗</span>' } },
    TH: { benefits: { eyebrow: 'ทำไมต้อง GSB SUNPOWER', titleOne: 'ออกแบบเพื่อ', titleTwo: 'สิ่งที่สำคัญ', summary: 'โครงการพลังงานสะอาดควรเข้าใจง่าย เดินระบบได้อย่างมั่นใจ และรองรับการเติบโตของธุรกิจคุณ', boiTitle: 'โครงการที่ได้รับการส่งเสริมจาก BOI', boiText: 'โครงการที่เข้าเกณฑ์อาจได้รับสิทธิประโยชน์ด้านการลงทุนและภาษี', teamTitle: 'ทีมผู้เชี่ยวชาญมากประสบการณ์', teamText: 'ให้คำแนะนำที่นำไปใช้ได้จริง ตั้งแต่วันแรกจนถึงทุกขั้นตอนต่อไป', deliveryTitle: 'บริการครบวงจร', deliveryText: 'สำรวจ ออกแบบ ติดตั้ง บริหารโครงการ และบำรุงรักษา', smartTitle: 'การจัดการพลังงานอัจฉริยะ', smartText: 'ติดตามการทำงานของระบบแบบเรียลไทม์ผ่านแอปพลิเคชันที่ออกแบบมาสำหรับหน้างานของคุณ', cta: 'รู้จัก GSB SunPower <span>↗</span>' } },
    ZH: { benefits: { eyebrow: '为什么选择 GSB SUNPOWER', titleOne: '为真正重要的', titleTwo: '事情而打造', summary: '清洁能源项目应当易于理解、稳定可靠，并随您的业务共同成长', boiTitle: 'BOI 投资支持', boiText: '符合条件的项目可获得投资激励和税务优惠', teamTitle: '经验丰富的专业团队', teamText: '从首次沟通到每一个后续环节，提供务实指导', deliveryTitle: '端到端交付', deliveryText: '现场勘察、设计、安装、项目管理及维护', smartTitle: '智能能源管理', smartText: '通过为您的场地设计的应用程序实时查看系统状态', cta: '了解 GSB SunPower <span>↗</span>' } },
  };
  const translatedValue = (dictionary, path) => path.split('.').reduce((value, key) => value && value[key], dictionary);
  const applyLanguage = (language) => {
    const dictionary = translations[language];
    if (!dictionary) return;
    const navCopy = {
      EN: { about: 'About us', blog: 'Blog', solutions: 'Solutions', contact: 'Contact us' },
      TH: { about: 'เกี่ยวกับเรา', blog: 'บทความ', solutions: 'โซลูชัน', contact: 'ติดต่อเรา' },
      ZH: { about: '关于我们', blog: '博客', solutions: '解决方案', contact: '联系我们' },
    }[language];
    const navigation = document.querySelector('.gx-navline nav');
    if (navigation && navCopy) {
      const isBlogPage = window.location.pathname.endsWith('/blog.html');
      navigation.innerHTML = `<a href="/about.html">${navCopy.about}</a>${hasPublishedBlogContent() ? `<a class="${isBlogPage ? 'is-current' : ''}" href="/blog.html">${navCopy.blog}</a>` : ''}<a href="/solutions.html">${navCopy.solutions}</a>`;
    }
    document.querySelector('.gx-navline > .gx-contact')?.replaceChildren(navCopy.contact);
    document.querySelectorAll('[data-gx-i18n]').forEach((element) => {
      const value = translatedValue(dictionary, element.dataset.gxI18n);
      if (value) element.textContent = value;
    });
    document.querySelectorAll('[data-gx-i18n-html]').forEach((element) => {
      const value = translatedValue(dictionary, element.dataset.gxI18nHtml);
      if (value) element.innerHTML = value;
    });
  };
  document.querySelectorAll('[data-gx-language]').forEach((button) => {
    button.addEventListener('click', () => {
      const language = button.dataset.gxLanguage;
      if (!language) return;
      localStorage.setItem('gsb-language', language.toLowerCase());
      window.location.reload();
    });
  });

  const savedLanguage = (localStorage.getItem('gsb-language') || 'en').toUpperCase();
  if (languageLabel && translations[savedLanguage]) languageLabel.textContent = savedLanguage;
  if (translations[savedLanguage]) applyLanguage(savedLanguage);
  window.addEventListener('gsb:blog-availability', () => applyLanguage((localStorage.getItem('gsb-language') || 'en').toUpperCase()));

  const enquirySuccessDialog = document.querySelector('[data-gx-enquiry-success]');
  if (enquirySuccessDialog) {
    const enquiryCopy = {
      EN: { kicker: 'MESSAGE RECEIVED', title: 'Your enquiry is on its way.', copy: 'Thank you for getting in touch. Our team will review your details and contact you shortly.', status: 'Saved securely in our system', button: 'Continue' },
      TH: { kicker: 'รับข้อมูลเรียบร้อยแล้ว', title: 'เราได้รับข้อมูลของคุณแล้ว', copy: 'ขอบคุณที่ติดต่อ GSB SunPower ทีมงานจะตรวจสอบรายละเอียดและติดต่อกลับโดยเร็วที่สุด', status: 'บันทึกข้อมูลเข้าสู่ระบบเรียบร้อย', button: 'ดำเนินการต่อ' },
      ZH: { kicker: '信息已收到', title: '您的咨询已成功发送', copy: '感谢您联系 GSB SunPower。我们的团队将审核您的信息并尽快与您联系。', status: '信息已安全保存至系统', button: '继续' },
    }[savedLanguage] || {};
    Object.entries(enquiryCopy).forEach(([key, value]) => document.querySelector(`[data-gx-enquiry-success-${key}]`)?.replaceChildren(value));
    requestAnimationFrame(() => enquirySuccessDialog.showModal());
    document.querySelector('[data-gx-enquiry-success-close]')?.addEventListener('click', () => enquirySuccessDialog.close());
    enquirySuccessDialog.addEventListener('click', (event) => {
      if (event.target === enquirySuccessDialog) enquirySuccessDialog.close();
    });
  }

  const observed = document.querySelectorAll('.gx-intro, .gx-services, .gx-work, .gx-proof, .gx-testimonial, .gx-faq');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => entry.isIntersecting && entry.target.classList.add('is-visible')), { threshold: .12 });
    observed.forEach((section) => { section.classList.add('gx-reveal'); observer.observe(section); });
  }
})();
