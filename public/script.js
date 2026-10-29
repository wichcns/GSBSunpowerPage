const menuButton = document.querySelector(".menu-button");
const menu = document.querySelector(".site-menu");

// Quiet interaction layer: communicates page position and hierarchy without decorative motion.
const progress = document.createElement("div");
progress.className = "page-progress";
progress.setAttribute("aria-hidden", "true");
document.body.prepend(progress);
const updatePageProgress = () => {
  const total = document.documentElement.scrollHeight - window.innerHeight;
  progress.style.transform = `scaleX(${total > 0 ? Math.min(window.scrollY / total, 1) : 0})`;
  document.body.classList.toggle("has-scrolled", window.scrollY > 12);
};
updatePageProgress();
window.addEventListener("scroll", updatePageProgress, { passive: true });

const currentPage = window.location.pathname.split("/").pop() || "index.html";
document.querySelectorAll(".site-menu a").forEach((link) => {
  if (link.getAttribute("href") === currentPage) link.classList.add("is-current");
});

const revealItems = document.querySelectorAll(".catalog-intro-copy, .catalog-intro-image, .catalog-metrics, .catalog-service, .catalog-register-grid, .catalog-steps li, .catalog-contact .shell");
if ("IntersectionObserver" in window) {
  const revealObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
    if (entry.isIntersecting) { entry.target.classList.add("is-visible"); revealObserver.unobserve(entry.target); }
  }), { threshold: 0.12 });
  revealItems.forEach((item) => { item.classList.add("reveal-item"); revealObserver.observe(item); });
}

// These pages remain available by URL, but are not part of the public navigation.
menu?.querySelectorAll('a[href="approach.html"], a[href="projects.html"]').forEach((link) => link.remove());
menu?.querySelectorAll('a[href="insights.html"]').forEach((link) => { link.href = "contact.html"; link.textContent = "Contact Us"; });

document.querySelectorAll(".brand").forEach((brand) => {
  if (brand.querySelector(".brand-logo")) return;

  const logo = document.createElement("img");
  logo.className = "brand-logo";
  logo.src = "/image/GSBSUNPOWER-Edit.png";
  logo.alt = "GSB SunPower Group";
  brand.replaceChildren(logo);
});

menuButton?.addEventListener("click", () => {
  const opened = menu.classList.toggle("is-open");
  menuButton.setAttribute("aria-expanded", String(opened));
});

menu?.querySelectorAll("a").forEach((link) => {
  link.addEventListener("click", () => {
    menu.classList.remove("is-open");
    menuButton?.setAttribute("aria-expanded", "false");
  });
});

// Site language controller. Content elements can opt in with data-i18n="key".
const languageFiles = { en: "en.js", th: "th.js", zh: "zh.js" };
const savedLanguage =
  localStorage.getItem("gsb-language") || document.documentElement.lang || "en";
const activeLanguage = languageFiles[savedLanguage] ? savedLanguage : "en";
const languageScript = document.createElement("script");
languageScript.src = languageFiles[activeLanguage];
languageScript.onload = () => initialiseLanguage(activeLanguage);
document.head.append(languageScript);

function initialiseLanguage(language) {
  const copy = window.GSBTranslations?.[language]?.nav;
  const common = window.GSBTranslations?.[language]?.common;
  if (!copy) return;
  document.documentElement.lang = language;
  document.documentElement.dataset.language = language;
  document
    .querySelectorAll(".utility-content > span, .footer-top > p")
    .forEach((element) => {
      element.textContent = common?.tagline || "Clean energy, made practical.";
    });
  document.querySelectorAll(".back-top").forEach((element) => {
    element.textContent = common?.backTop || "Back to top ↑";
  });
  document
    .querySelectorAll(".footer-bottom > span:last-child")
    .forEach((element) => {
      element.textContent = common?.location || "Bangkok, Thailand";
    });
  document.querySelectorAll(".skip-link").forEach((element) => {
    element.textContent = common?.skip || "Skip to main content";
  });
  document.querySelectorAll(".menu-button .sr-only").forEach((element) => {
    element.textContent = common?.openMenu || "Open menu";
  });
  document.querySelector(".utility-content a")?.childNodes.forEach((node) => {
    if (node.nodeType === Node.TEXT_NODE) node.nodeValue = `${copy.contact} `;
  });
  const navLabels = {
    "about.html": copy.about,
    "solutions.html": copy.solutions,
    "approach.html": copy.approach,
    "projects.html": copy.projects,
    "insights.html": copy.insights,
    "ev-charging.html": copy.evCharging || "EV Charging",
    "contact.html": copy.contact,
  };
  menu?.querySelectorAll("a:not(.nav-button)").forEach((link) => {
    const file = link.getAttribute("href");
    if (navLabels[file]) link.textContent = navLabels[file];
  });
  menu
    ?.querySelector(".nav-button")
    ?.replaceWith(createLanguageMenu(language, copy));
  const page = window.location.pathname.split("/").pop() || "index.html";
  const pageCopy = window.GSBTranslations?.[language]?.pages?.[page] || {};
  Object.entries(pageCopy).forEach(([selector, value]) =>
    document.querySelectorAll(selector).forEach((element) => {
      element.innerHTML = value;
    }),
  );
  const backText = {
    en: "Back to Solutions",
    th: "กลับไปหน้าโซลูชัน",
    zh: "返回解决方案",
  };
  document.querySelector(".solar-back")?.childNodes.forEach((node) => {
    if (node.nodeType === Node.TEXT_NODE)
      node.nodeValue = ` ${backText[language] || backText.en}`;
  });
  document.querySelector(".epc-back")?.childNodes.forEach((node) => {
    if (node.nodeType === Node.TEXT_NODE)
      node.nodeValue = ` ${backText[language] || backText.en}`;
  });
  document.querySelectorAll("[data-i18n]").forEach((element) => {
    const value =
      window.GSBTranslations?.[language]?.content?.[element.dataset.i18n];
    if (value) element.innerHTML = value;
  });
  localiseDynamicCards(language);
  localiseSolutionCards(language);
  localiseContactForm(language);
  window.dispatchEvent(new CustomEvent("gsb-language-change", { detail: { language } }));
}

function localiseSolutionCards(language) {
  document.querySelectorAll("[data-solution-card]").forEach((card) => {
    const suffix = `${language[0].toUpperCase()}${language.slice(1)}`;
    const title = card.dataset[`title${suffix}`] || card.dataset.titleEn;
    const summary = card.dataset[`summary${suffix}`] || card.dataset.summaryEn;
    card.querySelector("[data-solution-title]")?.replaceChildren(title);
    card.querySelector("[data-solution-summary]")?.replaceChildren(summary);
  });
}

function localiseContactForm(language) {
  const form = document.querySelector(".contact-form");
  const contactHeading = document.querySelector(".contact-modern-heading");
  const headingCopy = {
    en: ["Let’s make energy<br><mark>work harder.</mark>", "Build a partnership"],
    th: ["เปลี่ยนพลังงานให้ <mark>สร้างผลลัพธ์มากขึ้น</mark>", "สร้างความร่วมมือทางธุรกิจ"],
    zh: ["让能源<br><mark>创造更大价值。</mark>", "建立商业合作"]
  }[language];
  if (contactHeading && headingCopy) { contactHeading.querySelector("h1").innerHTML = headingCopy[0]; contactHeading.querySelector(".kicker").childNodes.forEach((node) => { if (node.nodeType === Node.TEXT_NODE) node.nodeValue = ` ${headingCopy[1]}`; }); }
  const details = document.querySelector(".contact-details");
  const detailCopy = { th:["ติดต่อเรา","สำนักงาน","อาคาร BHIRAJ TOWER at BITEC ชั้น 22 ห้อง 2209, 4345 ถนนสุขุมวิท แขวงบางนาใต้ เขตบางนา กรุงเทพมหานคร 10260 ประเทศไทย","เรายินดีนัดหมายเพื่อพูดคุยรายละเอียดโครงการของคุณ","อีเมล","โทรศัพท์","บอกเราเกี่ยวกับพื้นที่ของคุณ แล้วเราจะช่วยวางขั้นตอนถัดไปที่เหมาะสม"], zh:["联系我们","办公室","BITEC 会展中心 BHIRAJ TOWER，22 层 2209 室，泰国曼谷挽那泰分区素坤逸路 4345 号，挽那区，邮编 10260","欢迎预约沟通您的项目需求。","电子邮箱","电话","告诉我们您的场地，我们将帮助规划下一步。"], en:["GET IN TOUCH","OFFICE","BHIRAJ TOWER at BITEC, 22nd Floor, Room 2209, 4345 Sukhumvit Road, Bang Na Tai Subdistrict, Bang Na District, Bangkok 10260, Thailand","We’re happy to discuss your project.","EMAIL","PHONE","Tell us about your site, and we’ll help map the next practical step."] }[language];
  if (details && detailCopy) { details.querySelector(".contact-label").textContent=detailCopy[0]; const groups=details.querySelectorAll("div"); groups[0]?.querySelector("small")?.replaceChildren(detailCopy[1]); groups[0]?.querySelector("strong")?.replaceChildren(detailCopy[2]); groups[0]?.querySelector("p")?.replaceChildren(detailCopy[3]); groups[1]?.querySelector("small")?.replaceChildren(detailCopy[4]); groups[2]?.querySelector("small")?.replaceChildren(detailCopy[5]); details.querySelector(".contact-note p")?.replaceChildren(detailCopy[6]); }
  if (!form) return;
  const copy = {
    en: ["START A CONVERSATION", "Tell us what you’re planning.", "Your name", "Company name", "Email", "Project details", "Send enquiry", "We’ll respond with the right person for your enquiry."],
    th: ["เริ่มต้นการสนทนา", "บอกเราเกี่ยวกับแผนของคุณ", "ชื่อของคุณ", "ชื่อบริษัท", "อีเมล", "รายละเอียดโครงการ", "ส่งข้อมูลติดต่อ", "เราจะประสานงานกับผู้เชี่ยวชาญที่เหมาะสมสำหรับโครงการของคุณ"],
    zh: ["开启对话", "告诉我们您的计划", "您的姓名", "公司名称", "电子邮箱", "项目详情", "提交咨询", "我们将安排合适的专家回复您的咨询。"]
  }[language] || [];
  form.querySelector(".contact-form-kicker").textContent = copy[0];
  form.querySelector("h2").textContent = copy[1];
  form.querySelectorAll("label").forEach((label, index) => { const control = label.querySelector("input,textarea"); label.childNodes.forEach((node) => { if (node.nodeType === Node.TEXT_NODE) node.nodeValue = copy[index + 2]; }); control?.setAttribute("aria-label", copy[index + 2]); });
  form.querySelector("button").childNodes[0].nodeValue = `${copy[6]} `;
  form.querySelector(".form-footnote").textContent = copy[7];
}

function localiseDynamicCards(language) {
  if (language === "en") return;
  const thaiPpaTitles = [
    "โซลาร์เซลล์รูฟท็อปโรงงานอุตสาหกรรม สมุทรสาคร",
    "โซลาร์เซลล์รูฟท็อปโรงงาน อยุธยา",
    "โซลาร์เซลล์สำหรับโรงงานผ้าไม่ถักทอ สมุทรสาคร",
    "โซลาร์เซลล์สำหรับพื้นที่พาณิชย์ หัวหิน",
    "โซลาร์เซลล์รูฟท็อปโรงงาน ระยอง",
    "ระบบโซลาร์เซลล์ประสิทธิภาพสูง ฉะเชิงเทรา",
    "โซลาร์เซลล์สำหรับพื้นที่อุตสาหกรรม พระราม 2",
    "โซลาร์เซลล์หลังคาตลาด ระยอง",
    "โซลาร์เซลล์สำหรับโรงงานบรรจุภัณฑ์ ระยอง",
    "โซลาร์เซลล์สำหรับโรงงานอุปกรณ์แสงสว่าง ระยอง",
    "โซลาร์เซลล์สำหรับอุตสาหกรรมเคมี ระยอง",
    "โซลาร์เซลล์รูฟท็อปธุรกิจ เพชรบุรี",
    "โซลาร์เซลล์สำหรับโรงงานแปรรูปไม้ ประจวบคีรีขันธ์",
    "โซลาร์เซลล์สำหรับศูนย์บริการ ประจวบคีรีขันธ์",
    "โครงสร้างพื้นฐานพลังงานสะอาด พระราม 3",
  ];
  const chinesePpaTitles = [
    "北榄府工业厂房屋顶太阳能",
    "大城府工厂屋顶太阳能",
    "北榄府无纺布工厂太阳能",
    "华欣商业屋顶太阳能",
    "罗勇府工厂屋顶太阳能",
    "北柳府高性能太阳能系统",
    "拉玛二路工业区太阳能",
    "罗勇府市场车棚太阳能",
    "罗勇府包装工厂太阳能",
    "罗勇府照明设备工厂太阳能",
    "罗勇府化工厂太阳能",
    "佛丕府商业屋顶太阳能",
    "班武里府木材加工厂太阳能",
    "班武里府服务中心太阳能",
    "拉玛三路清洁能源基础设施",
  ];
  const thaiEpcTitles = [
    "Solar EPC สำหรับโรงงานอุตสาหกรรม",
    "Solar EPC สำหรับอาคารพาณิชย์",
    "Solar EPC สำหรับภาคการผลิต",
    "Solar EPC รูฟท็อปขนาดใหญ่",
    "Solar EPC สำหรับโรงงาน",
    "Solar EPC เพื่อยกระดับพลังงานธุรกิจ",
    "Solar EPC ระบบรูฟท็อป",
    "Solar EPC สำหรับอุตสาหกรรม",
    "Solar EPC ออกแบบตามพื้นที่",
    "Solar EPC สำหรับธุรกิจ",
    "Solar EPC ส่งมอบครบวงจร",
    "Solar EPC สินทรัพย์พลังงานสะอาด",
  ];
  const chineseEpcTitles = [
    "工业厂房 Solar EPC",
    "商业建筑 Solar EPC",
    "制造业 Solar EPC",
    "大型屋顶 Solar EPC",
    "工厂屋顶 Solar EPC",
    "企业能源升级 Solar EPC",
    "屋顶太阳能 Solar EPC",
    "工业太阳能 Solar EPC",
    "定制化太阳能 Solar EPC",
    "商业屋顶 Solar EPC",
    "一体化交付 Solar EPC",
    "清洁能源资产 Solar EPC",
  ];
  document
    .querySelectorAll(".solar-gallery .solar-case")
    .forEach((card, index) => {
      const number = String(index + 1).padStart(2, "0");
      const thai = language === "th";
      card
        .querySelector("small")
        ?.replaceChildren(
          document.createTextNode(
            thai ? "โครงการ Solar PPA" : "太阳能购电协议项目",
          ),
        );
      card
        .querySelector("h3")
        ?.replaceChildren(
          document.createTextNode(
            thai ? thaiPpaTitles[index] : chinesePpaTitles[index],
          ),
        );
      card.dataset.localTitle = thai
        ? thaiPpaTitles[index]
        : chinesePpaTitles[index];
      card
        .querySelector("p")
        ?.replaceChildren(
          document.createTextNode(
            thai
              ? "ระบบพลังงานสะอาดสำหรับภาคธุรกิจและอุตสาหกรรม"
              : "面向商业与工业场景的清洁能源系统",
          ),
        );
      card
        .querySelector("span")
        ?.replaceChildren(
          document.createTextNode(
            thai ? "โซลาร์เซลล์ พีพีเอ ↗" : "太阳能购电协议 ↗",
          ),
        );
    });
  document
    .querySelectorAll(".epc-project-gallery .epc-card")
    .forEach((card, index) => {
      const number = String(index + 1).padStart(2, "0");
      const thai = language === "th";
      card
        .querySelector("small")
        ?.replaceChildren(
          document.createTextNode(
            thai ? "โครงการ Solar EPC" : "太阳能 EPC 项目",
          ),
        );
      card
        .querySelector("h3")
        ?.replaceChildren(
          document.createTextNode(
            thai ? thaiEpcTitles[index] : chineseEpcTitles[index],
          ),
        );
      card.dataset.localTitle = thai
        ? thaiEpcTitles[index]
        : chineseEpcTitles[index];
    });
  document.querySelectorAll(".ev-gallery .ev-card").forEach((card) => {
    card
      .querySelector("small")
      ?.replaceChildren(
        document.createTextNode(thai ? "สถานีชาร์จ EV" : "电动车充电站"),
      );
    card
      .querySelector("p")
      ?.replaceChildren(
        document.createTextNode(
          thai ? "จุดติดตั้งสถานีชาร์จรถยนต์ไฟฟ้า" : "电动车充电服务点",
        ),
      );
  });
}

function createLanguageMenu(language, copy) {
  const wrapper = document.createElement("div");
  wrapper.className = "language-switcher";
  wrapper.innerHTML = `<button class="language-toggle" type="button" aria-expanded="false" aria-label="${copy.language}"><b aria-hidden="true">◎</b> ${language.toUpperCase()} <span>⌄</span></button><div class="language-options" role="menu"><button data-language="en" role="menuitem">English</button><button data-language="th" role="menuitem">ไทย</button><button data-language="zh" role="menuitem">中文</button></div>`;
  const toggle = wrapper.querySelector(".language-toggle");
  toggle.addEventListener("click", () => {
    const opened = wrapper.classList.toggle("is-open");
    toggle.setAttribute("aria-expanded", String(opened));
  });
  wrapper.querySelectorAll("[data-language]").forEach((button) =>
    button.addEventListener("click", () => {
      localStorage.setItem("gsb-language", button.dataset.language);
      window.location.reload();
    }),
  );
  return wrapper;
}

const solarProjects = [
  [
    "3 MW",
    "Samut Sakhon",
    "สมุทรสาคร",
    "Industrial rooftop energy system",
    "ระบบผลิตไฟฟ้าสำหรับโรงงานอุตสาหกรรม",
  ],
  [
    "2 MW",
    "Ayutthaya",
    "พระนครศรีอยุธยา",
    "Factory rooftop solar",
    "ระบบโซลาร์เซลล์รูฟท็อปสำหรับโรงงาน",
  ],
  [
    "3 MW",
    "Samut Sakhon",
    "สมุทรสาคร",
    "Nonwoven production facility",
    "โครงการโซลาร์เซลล์สำหรับโรงงานผลิตผ้าไม่ถักทอ",
  ],
  [
    "1.3 MW",
    "Hua Hin",
    "หัวหิน",
    "Coastal commercial site",
    "ระบบโซลาร์เซลล์สำหรับพื้นที่พาณิชยกรรม",
  ],
  [
    "1.14 MW",
    "Rayong",
    "ระยอง",
    "Manufacturing rooftop system",
    "ระบบผลิตไฟฟ้าสำหรับโรงงานในระยอง",
  ],
  [
    "900 kW",
    "Chachoengsao",
    "ฉะเชิงเทรา",
    "High-performance solar equipment",
    "งานติดตั้งอุปกรณ์โซลาร์เซลล์ประสิทธิภาพสูง",
  ],
  [
    "550 kW",
    "Rama II",
    "พระราม 2",
    "Urban industrial energy system",
    "โซลาร์เซลล์รูฟท็อปสำหรับพื้นที่อุตสาหกรรมในเมือง",
  ],
  [
    "530 kW",
    "Rayong",
    "ระยอง",
    "Market canopy solar",
    "ระบบพลังงานแสงอาทิตย์สำหรับพื้นที่ตลาด",
  ],
  [
    "1.7 MW",
    "Rayong",
    "ระยอง",
    "Packaging facility solar",
    "โครงการโซลาร์เซลล์สำหรับโรงงานบรรจุภัณฑ์",
  ],
  [
    "1.7 MW",
    "Rayong",
    "ระยอง",
    "Lighting manufacturing facility",
    "ระบบโซลาร์เซลล์สำหรับโรงงานผลิตอุปกรณ์แสงสว่าง",
  ],
  [
    "1 MW",
    "Rayong",
    "ระยอง",
    "Chemical industry solar",
    "โครงการพลังงานสะอาดสำหรับอุตสาหกรรมเคมี",
  ],
  [
    "200 kW",
    "Phetchaburi",
    "เพชรบุรี",
    "Commercial rooftop solution",
    "ระบบโซลาร์เซลล์รูฟท็อปสำหรับธุรกิจพาณิชย์",
  ],
  [
    "120 kW",
    "Prachuap Khiri Khan",
    "ประจวบคีรีขันธ์",
    "Wood processing solar",
    "โครงการโซลาร์เซลล์สำหรับโรงงานแปรรูปไม้",
  ],
  [
    "150 kW",
    "Prachuap Khiri Khan",
    "ประจวบคีรีขันธ์",
    "Service facility energy system",
    "ระบบพลังงานสะอาดสำหรับศูนย์บริการ",
  ],
  [
    "150 kW",
    "Rama III",
    "พระราม 3",
    "City-based solar infrastructure",
    "โครงสร้างพื้นฐานพลังงานสะอาดในเขตเมือง",
  ],
];

const solarCases = [...document.querySelectorAll(".solar-case")];
function getModalLabels() {
  const language = document.documentElement.dataset.language || "en";
  const labels = {
    en: {
      capacity: "Investment value",
      location: "Location",
      model: "Service model",
      type: "System type",
      delivery: "Project delivery",
      scope: "Delivery scope",
      status: "Project status",
      discuss: "Discuss a similar project",
      rooftop: "Rooftop solar",
      endToEnd: "End-to-end",
      turnkey: "Turnkey delivery",
      commissioned: "Commissioned",
      ppaService: "Investment, installation & operations",
      epcService: "Design, procurement, construction & handover",
    },
    th: {
      capacity: "มูลค่าโครงการ",
      location: "สถานที่ตั้ง",
      model: "รูปแบบบริการ",
      type: "ประเภทระบบ",
      delivery: "ขอบเขตโครงการ",
      scope: "ขอบเขตงาน",
      status: "สถานะโครงการ",
      discuss: "ปรึกษาโครงการลักษณะเดียวกัน",
      rooftop: "โซลาร์เซลล์รูฟท็อป",
      endToEnd: "ครบวงจร",
      turnkey: "ส่งมอบแบบครบวงจร",
      commissioned: "ส่งมอบและพร้อมใช้งาน",
      ppaService: "ลงทุน ติดตั้ง และดูแลระบบ",
      epcService: "ออกแบบ จัดหา ก่อสร้าง และส่งมอบ",
    },
    zh: {
      capacity: "投资金额",
      location: "项目地点",
      model: "服务模式",
      type: "系统类型",
      delivery: "项目交付",
      scope: "交付范围",
      status: "项目状态",
      discuss: "咨询类似项目",
      rooftop: "屋顶太阳能",
      endToEnd: "端到端服务",
      turnkey: "交钥匙交付",
      commissioned: "已调试交付",
      ppaService: "投资、安装与运营",
      epcService: "设计、采购、施工和交付",
    },
  };
  return { language, ...(labels[language] || labels.en) };
}
if (solarCases.length) {
  const modal = document.createElement("dialog");
  modal.className = "project-modal";
  modal.innerHTML =
    '<div class="project-modal-panel"><button class="project-modal-close" type="button" aria-label="Close project details">×</button><div class="project-modal-image"><img alt=""></div><div class="project-modal-content"><p class="project-modal-kicker">SOLAR PPA / SELECTED INSTALLATION</p><h2></h2><p class="project-modal-thai" lang="th"></p><div class="project-modal-capacity"><span>System capacity</span><strong></strong></div><div class="project-modal-details"></div><a href="contact.html" class="project-modal-cta">Discuss a similar project <span>→</span></a></div></div>';
  document.body.append(modal);
  const closeModal = () => modal.close();
  modal
    .querySelector(".project-modal-close")
    .addEventListener("click", closeModal);
  modal.addEventListener("click", (event) => {
    if (event.target === modal) closeModal();
  });
  solarCases.forEach((card, index) => {
    const project = solarProjects[index];
    card.tabIndex = 0;
    card.setAttribute("role", "button");
    card.setAttribute("aria-label", `View details: ${project[3]}`);
    const openModal = () => {
      const copy = getModalLabels();
      const image = card.querySelector("img");
      modal.querySelector(".project-modal-image img").src = image.src;
      modal.querySelector(".project-modal-image img").alt = image.alt;
      modal.querySelector(".project-modal-kicker").textContent =
        copy.language === "en"
          ? `SOLAR PPA / ${String(index + 1).padStart(2, "0")}`
          : copy.language === "th"
            ? "โครงการ Solar PPA"
            : "太阳能购电协议项目";
      modal.querySelector("h2").textContent =
        copy.language === "en"
          ? project[3]
          : card.dataset.localTitle || project[4];
      modal.querySelector(".project-modal-thai").textContent =
        copy.language === "en"
          ? ""
          : copy.language === "zh"
            ? "面向企业的清洁能源解决方案"
            : project[3];
      modal.querySelector(".project-modal-capacity span").textContent =
        copy.capacity;
      modal.querySelector(".project-modal-cta").childNodes[0].nodeValue =
        `${copy.discuss} `;
      modal.querySelector(".project-modal-capacity strong").textContent =
        project[0];
      modal.querySelector(".project-modal-details").innerHTML =
        `<div><span>${copy.location}</span><b>${copy.language === "th" ? project[2] : project[1]}</b></div><div><span>${copy.model}</span><b>Solar PPA</b><small>${copy.ppaService}</small></div><div><span>${copy.type}</span><b>${copy.rooftop}</b><small>${copy.language === "th" ? "ระบบผลิตไฟฟ้าพลังงานแสงอาทิตย์" : copy.language === "zh" ? "太阳能光伏发电系统" : "Solar photovoltaic energy system"}</small></div><div><span>${copy.delivery}</span><b>${copy.endToEnd}</b><small>${copy.language === "th" ? "ออกแบบ ติดตั้ง และบำรุงรักษา" : copy.language === "zh" ? "设计、安装与长期维护" : "Design, installation & long-term maintenance"}</small></div>`;
      modal.showModal();
    };
    card.addEventListener("click", openModal);
    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        openModal();
      }
    });
  });
}

const epcGallery = document.querySelector(".epc-gallery");
if (epcGallery) {
  const epcProjects = [
    [
      "pexels-hoan-ng-c-510735-6961088.jpg",
      "Industrial rooftop solar",
      "2.8 MWp",
      "โครงการโซลาร์เซลล์รูฟท็อปสำหรับโรงงาน",
    ],
    [
      "pexels-budget-bizar-92378004-12224996.jpg",
      "Commercial rooftop solar",
      "2.2 MWp",
      "ระบบโซลาร์เซลล์สำหรับอาคารพาณิชย์",
    ],
    [
      "pexels-vladimirsrajber-17965455.jpg",
      "Manufacturing energy system",
      "2.5 MWp",
      "ระบบพลังงานสำหรับภาคการผลิต",
    ],
    [
      "pexels-molnartamasphotography-33757669.jpg",
      "Large-scale rooftop solar",
      "4.0 MWp",
      "โครงการโซลาร์เซลล์รูฟท็อปขนาดใหญ่",
    ],
    [
      "VCG211356276859.webp",
      "Factory rooftop PV",
      "2.6 MWp",
      "ระบบผลิตไฟฟ้าพลังงานแสงอาทิตย์สำหรับโรงงาน",
    ],
    [
      "VCG211361487513.webp",
      "Commercial energy upgrade",
      "1.8 MWp",
      "ยกระดับการใช้พลังงานสะอาดของธุรกิจ",
    ],
    [
      "VCG211383569639.webp",
      "Rooftop solar deployment",
      "1.4 MWp",
      "โครงการติดตั้งระบบโซลาร์เซลล์รูฟท็อป",
    ],
    [
      "VCG211486470866.webp",
      "Industrial PV system",
      "1.9 MWp",
      "ระบบ PV สำหรับโรงงานอุตสาหกรรม",
    ],
    [
      "VCG211563313917.webp",
      "Site-specific solar design",
      "3.1 MWp",
      "ออกแบบระบบโซลาร์เซลล์ให้เหมาะกับพื้นที่",
    ],
    [
      "VCG211566851116.webp",
      "Business rooftop solution",
      "2.3 MWp",
      "โซลูชันโซลาร์เซลล์รูฟท็อปสำหรับธุรกิจ",
    ],
    [
      "VCG211576523975.webp",
      "Integrated solar delivery",
      "1.6 MWp",
      "ส่งมอบระบบโซลาร์เซลล์แบบครบวงจร",
    ],
    [
      "VCG211486470866.webp",
      "Operational solar asset",
      "2.7 MWp",
      "สินทรัพย์พลังงานสะอาดที่พร้อมใช้งาน",
    ],
  ];
  epcGallery.classList.add("epc-project-gallery");
  epcGallery.innerHTML = epcProjects
    .map(
      ([image, title, capacity, thai], index) =>
        `<article class="epc-card"><img src="image/EPC/${image}" alt="${title}"><div><small>${String(index + 1).padStart(2, "0")} / SOLAR EPC</small><h3>${title}</h3><p><b>${capacity}</b><span lang="th">${thai}</span></p></div></article>`,
    )
    .join("");
  document
    .querySelector(".epc-heading .kicker")
    ?.replaceChildren(document.createTextNode("Selected Solar EPC projects"));
  const epcHeading = document.querySelector(".epc-heading h2");
  if (epcHeading) epcHeading.innerHTML = "Solar EPC,<br><em>delivered.</em>";
  const epcModal = document.createElement("dialog");
  epcModal.className = "epc-modal";
  epcModal.innerHTML =
    '<div class="epc-modal-panel"><button class="epc-modal-close" type="button" aria-label="Close project details">×</button><div class="epc-modal-image"><img alt=""></div><div class="epc-modal-content"><p class="epc-modal-kicker"></p><h2></h2><p class="epc-modal-thai" lang="th"></p><div class="epc-modal-capacity"><span>System capacity</span><strong></strong></div><div class="epc-modal-details"></div><a class="epc-modal-cta" href="contact.html">Discuss a similar project <span>→</span></a></div></div>';
  document.body.append(epcModal);
  epcModal
    .querySelector(".epc-modal-close")
    .addEventListener("click", () => epcModal.close());
  epcModal.addEventListener("click", (event) => {
    if (event.target === epcModal) epcModal.close();
  });
  epcGallery.querySelectorAll(".epc-card").forEach((card, index) => {
    const project = epcProjects[index];
    card.tabIndex = 0;
    card.setAttribute("role", "button");
    card.setAttribute("aria-label", `View details: ${project[1]}`);
    const openEpcModal = () => {
      const copy = getModalLabels();
      const image = card.querySelector("img");
      epcModal.querySelector(".epc-modal-image img").src = image.src;
      epcModal.querySelector(".epc-modal-image img").alt = image.alt;
      epcModal.querySelector(".epc-modal-kicker").textContent =
        copy.language === "en"
          ? `SOLAR EPC / ${String(index + 1).padStart(2, "0")}`
          : copy.language === "th"
            ? "โครงการ Solar EPC"
            : "太阳能 EPC 项目";
      epcModal.querySelector("h2").textContent =
        copy.language === "en"
          ? project[1]
          : card.dataset.localTitle || project[3];
      epcModal.querySelector(".epc-modal-thai").textContent =
        copy.language === "en"
          ? ""
          : copy.language === "zh"
            ? "一体化太阳能 EPC 项目"
            : project[1];
      epcModal.querySelector(".epc-modal-capacity span").textContent =
        copy.capacity;
      epcModal.querySelector(".epc-modal-cta").childNodes[0].nodeValue =
        `${copy.discuss} `;
      epcModal.querySelector(".epc-modal-capacity strong").textContent =
        project[2];
      epcModal.querySelector(".epc-modal-details").innerHTML =
        `<div><span>${copy.model}</span><b>Solar EPC</b><small>${copy.epcService}</small></div><div><span>${copy.type}</span><b>${copy.rooftop}</b><small>${copy.language === "th" ? "ระบบผลิตไฟฟ้าพลังงานแสงอาทิตย์" : copy.language === "zh" ? "太阳能光伏发电系统" : "Solar photovoltaic energy system"}</small></div><div><span>${copy.scope}</span><b>${copy.turnkey}</b><small>${copy.language === "th" ? "บริหารโครงการแบบครบวงจร" : copy.language === "zh" ? "项目全流程管理" : "Full project management"}</small></div><div><span>${copy.status}</span><b>${copy.commissioned}</b><small>${copy.language === "th" ? "พร้อมใช้งานและส่งมอบระบบ" : copy.language === "zh" ? "系统已投入使用" : "System ready for operation"}</small></div>`;
      epcModal.showModal();
    };
    card.addEventListener("click", openEpcModal);
    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        openEpcModal();
      }
    });
  });
}

const solarExploreButton = document.querySelector(".solar-hero-cta");
solarExploreButton?.addEventListener("click", (event) => {
  const target = document.querySelector("#solar-projects");
  if (!target) return;
  event.preventDefault();
  target.scrollIntoView({ behavior: "smooth", block: "start" });
  target.setAttribute("tabindex", "-1");
  window.setTimeout(() => target.focus({ preventScroll: true }), 520);
});

const epcExploreButton = document.querySelector(".epc-cta");
epcExploreButton?.addEventListener("click", (event) => {
  const target = document.querySelector("#epc-capabilities");
  if (!target) return;
  event.preventDefault();
  target.scrollIntoView({ behavior: "smooth", block: "start" });
  target.setAttribute("tabindex", "-1");
  window.setTimeout(() => target.focus({ preventScroll: true }), 520);
});

const evGallery = document.querySelector(".ev-gallery");
if (evGallery) {
  const evSites = [
    ["charge_site_01_the_rest.jpeg", "The Rest Hotel"],
    ["charge_site_02_wellington.jpeg", "Wellington Hotel"],
    ["charge_site_03_aviyana.jpeg", "Aviyana Hua Hin"],
    ["charge_site_04_khun_ton.jpeg", "Khun Ton Restaurant"],
    ["charge_site_05_hadthong.jpg", "Hadthong Hotel"],
    ["charge_site_06_golden_beach.jpg", "Golden Beach Boutique Resort"],
    ["charge_site_08_montalay.jpg", "Montalay Hotel"],
    ["charge_site_09_khun_ton_80kw.jpg", "Khun Ton Restaurant"],
    ["charge_site_10_lipda.jpeg", "Lipda Resort"],
    ["charge_site_12_make_a_day.jpeg", "Make A Day Cafe"],
    ["charge_site_13_munruk.jpeg", "Munruk Hotel"],
    ["charge_site_14_meet_more.jpeg", "Meet & More Cafe"],
    ["charge_site_15_the_yana.jpeg", "The Yana Villas Hua Hin"],
    ["charge_site_16_palayana.jpeg", "The Palayana Hotel"],
    ["charge_site_17_runnava.jpg", "Runnava Pranburi Cafe"],
    ["charge_site_18_demi_iii.jpeg", "DEMI III"],
    ["charge_site_19_dookhao.jpeg", "Dookhao Chomklong Resort"],
  ];
  evGallery.innerHTML = evSites
    .map(
      ([image, name], index) =>
        `<article class="ev-card"><img src="image/EV/${image}" alt="EV charging at ${name}"><div><small>EV CHARGING</small><h3>${name}</h3><p>${document.documentElement.dataset.language === "th" ? "จุดติดตั้งสถานีชาร์จรถยนต์ไฟฟ้า" : document.documentElement.dataset.language === "zh" ? "电动车充电站" : "EV charging location"}</p></div></article>`,
    )
    .join("");
  const evSpecs = [
    "7.3 kW × 4",
    "7.3 kW × 4",
    "7.3 kW × 4",
    "DC 80 kW / 4 guns",
    "DC 40 kW × 2, AC 7.3 kW × 2",
    "AC 7.3 kW × 2",
    "DC 40 kW × 2, AC 7.3 kW × 2",
    "DC 40 kW × 2, AC 7.3 kW × 2",
    "DC 80 kW × 4",
    "AC 7.3 kW × 4",
    "DC 80 kW × 2, AC 7.3 kW × 2",
    "DC 80 kW × 2",
    "AC 7.3 kW × 2",
    "DC 80 kW × 2",
    "AC 7.3 kW × 2",
    "AC 7.3 kW × 2",
    "DC 80 kW × 2",
    "AC 7.3 kW × 2",
    "AC 7.3 kW × 2",
  ];
  const evModal = document.createElement("dialog");
  evModal.className = "epc-modal";
  evModal.innerHTML =
    '<div class="epc-modal-panel"><button class="epc-modal-close" type="button">×</button><div class="epc-modal-image"><img alt=""></div><div class="epc-modal-content"><p class="epc-modal-kicker">EV CHARGING</p><h2></h2><p class="epc-modal-thai"></p><div class="epc-modal-capacity"><span>Charging capacity</span><strong></strong></div><div class="epc-modal-details"></div><a class="epc-modal-cta" href="contact.html">Discuss a similar installation <span>→</span></a></div></div>';
  document.body.append(evModal);
  evModal.querySelector(".epc-modal-close").onclick = () => evModal.close();
  evModal.onclick = (e) => {
    if (e.target === evModal) evModal.close();
  };
  evGallery.querySelectorAll(".ev-card").forEach((card, index) => {
    card.tabIndex = 0;
    card.setAttribute("role", "button");
    const open = () => {
      const lang = document.documentElement.dataset.language || "en";
      const thai = lang === "th",
        zh = lang === "zh";
      evModal.querySelector("img").src = card.querySelector("img").src;
      evModal.querySelector("h2").textContent =
        card.querySelector("h3").textContent;
      evModal.querySelector(".epc-modal-thai").textContent = thai
        ? "จุดติดตั้งสถานีชาร์จรถยนต์ไฟฟ้า"
        : zh
          ? "电动车充电服务点"
          : "EV charging installation";
      evModal.querySelector("strong").textContent = evSpecs[index];
      evModal.querySelector(".epc-modal-capacity span").textContent = thai
        ? "กำลังชาร์จ"
        : zh
          ? "充电功率"
          : "Charging capacity";
      evModal.querySelector(".epc-modal-details").innerHTML =
        `<div><span>${thai ? "ประเภทเครื่องชาร์จ" : zh ? "充电类型" : "Charger type"}</span><b>${evSpecs[index].includes("DC") ? "DC / AC" : "AC"}</b><small>${thai ? "สถานีชาร์จสำหรับรถยนต์ไฟฟ้า" : zh ? "电动车充电设备" : "EV charging equipment"}</small></div><div><span>${thai ? "รูปแบบบริการ" : zh ? "服务模式" : "Service model"}</span><b>${thai ? "EV Charging" : zh ? "电动车充电" : "EV Charging"}</b><small>${thai ? "ติดตั้งและดูแลระบบ" : zh ? "安装与运维" : "Installation & operations"}</small></div><div><span>${thai ? "จำนวนหัวชาร์จ" : zh ? "充电枪数量" : "Charging guns"}</span><b>${evSpecs[index].match(/× (\d)|\/ (\d)/)?.[1] || evSpecs[index].match(/\/ (\d)/)?.[1] || "2"}</b><small>${thai ? "รองรับการใช้งานในสถานที่" : zh ? "适用于场地使用" : "For site users"}</small></div><div><span>${thai ? "สถานะ" : zh ? "项目状态" : "Project status"}</span><b>${thai ? "พร้อมใช้งาน" : zh ? "已投入使用" : "Operational"}</b><small>${thai ? "ส่งมอบและพร้อมให้บริการ" : zh ? "已交付并投入服务" : "Commissioned and in service"}</small></div>`;
      evModal.showModal();
    };
    card.onclick = open;
    card.onkeydown = (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        open();
      }
    };
  });
}

const nrgVideoModal = document.querySelector(".nrg-video-modal");
const nrgVideoCards = document.querySelectorAll(".nrg-video-card");

if (nrgVideoModal && nrgVideoCards.length) {
  const player = nrgVideoModal.querySelector("video");
  const title = nrgVideoModal.querySelector("h2");
  const location = nrgVideoModal.querySelector(".nrg-video-modal-meta span");
  const close = () => {
    player.pause();
    player.removeAttribute("src");
    player.load();
    nrgVideoModal.close();
  };

  nrgVideoModal.querySelector(".nrg-video-modal-close").addEventListener("click", close);
  nrgVideoModal.addEventListener("click", (event) => {
    if (event.target === nrgVideoModal) close();
  });
  nrgVideoCards.forEach((card) => {
    card.addEventListener("click", () => {
      title.textContent = card.dataset.videoTitle || "GSB EV Story";
      location.textContent = card.dataset.videoLocation || "Thailand";
      player.src = card.dataset.videoSrc;
      nrgVideoModal.showModal();
      player.play().catch(() => {});
    });
  });
}

const nrgPromoModal = document.querySelector(".nrg-promo-modal");
const nrgPromoCards = document.querySelectorAll(".nrg-promo-card");

if (nrgPromoModal && nrgPromoCards.length) {
  const modalImage = nrgPromoModal.querySelector("img");
  const close = () => nrgPromoModal.close();
  nrgPromoModal.querySelector(".nrg-promo-modal-close").addEventListener("click", close);
  nrgPromoModal.addEventListener("click", (event) => {
    if (event.target === nrgPromoModal) close();
  });
  nrgPromoCards.forEach((card) => {
    card.addEventListener("click", () => {
      modalImage.src = card.dataset.promoImage;
      modalImage.alt = card.querySelector("img").alt;
      nrgPromoModal.showModal();
    });
  });
}

const unifiedPage = document.body.matches(".solutions-app-page, .solar-app-page, .epc-app-page, .ev-app-page");
if (unifiedPage) {
  document.body.classList.add("gsb-experience");
  const legacyHeader = document.querySelector(".site-header");
  if (legacyHeader) legacyHeader.outerHTML = `<header class="gx-header gx-header-overlay gx-unified-header" id="top"><a class="gx-logo" href="/"><img src="/image/GSBSUNPOWER-Edit.png" alt="GSB SunPower Group"></a><div class="gx-nav-area"><div class="gx-topline"><span>☎ 098-828-5767</span><a aria-label="Facebook" href="https://www.facebook.com/gsbsunpowerofficial" target="_blank" rel="noopener noreferrer">f</a><a aria-label="Instagram" href="https://www.instagram.com/gsb_sunpower" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.7" r="1"/></svg></a><a aria-label="TikTok" href="https://www.tiktok.com/@gsb.sunpower" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.2 3h2.7c.3 2.3 1.6 3.9 3.8 4.2v3.1a8.4 8.4 0 0 1-3.8-1.1v6.3a5.8 5.8 0 1 1-5-5.7c.3 0 .6 0 .9.1V13a2.7 2.7 0 1 0 1.4 2.4V3Z"/></svg></a><details class="gx-language"><summary><span class="gx-globe" aria-hidden="true"></span><b>EN</b><i aria-hidden="true"></i></summary><div><button type="button">English</button><button type="button">ไทย</button><button type="button">中文</button></div></details></div><div class="gx-navline"><nav><a href="about.html">About us <i></i></a><a href="blog.html">Blog <i></i></a><a href="solutions.html">Solutions <i></i></a></nav><a class="gx-contact" href="contact.html">Contact us</a></div></div></header>`;
  document.querySelector(".gx-language summary b")?.replaceChildren(activeLanguage.toUpperCase());
  if (!document.body.matches(".solutions-app-page")) document.querySelector("main > section")?.insertAdjacentHTML("afterbegin", '<a class="gx-back-solutions" href="solutions.html">Back to Solutions</a>');
  document.querySelectorAll(".gx-language button").forEach((button, index) => button.addEventListener("click", () => {
    localStorage.setItem("gsb-language", ["en", "th", "zh"][index]);
    window.location.reload();
  }));
}

const setupExperienceMobileNavigation = () => {
  document.querySelectorAll(".gx-header").forEach((header, index) => {
    const navigation = header.querySelector(".gx-navline nav");
    const navigationLine = header.querySelector(".gx-navline");

    if (!navigation || !navigationLine || navigationLine.querySelector(".gx-mobile-menu-toggle")) return;

    const navigationId = navigation.id || `gx-mobile-navigation-${index + 1}`;
    navigation.id = navigationId;

    const toggle = document.createElement("button");
    toggle.type = "button";
    toggle.className = "gx-mobile-menu-toggle";
    toggle.setAttribute("aria-controls", navigationId);
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", "Open navigation menu");
    toggle.innerHTML = '<span></span><span></span><span></span>';
    navigationLine.insertBefore(toggle, navigation);

    const closeNavigation = () => {
      navigation.classList.remove("is-mobile-open");
      toggle.setAttribute("aria-expanded", "false");
      toggle.setAttribute("aria-label", "Open navigation menu");
    };

    toggle.addEventListener("click", () => {
      const opened = navigation.classList.toggle("is-mobile-open");
      toggle.setAttribute("aria-expanded", String(opened));
      toggle.setAttribute("aria-label", opened ? "Close navigation menu" : "Open navigation menu");
    });

    navigation.querySelectorAll("a").forEach((link) => link.addEventListener("click", closeNavigation));

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") closeNavigation();
    });
  });
};

setupExperienceMobileNavigation();

const setupCookieConsent = () => {
  const consentCookie = "gsb_cookie_consent";
  const hasConsent = document.cookie.split("; ").some((item) => item.startsWith(`${consentCookie}=`));

  if (hasConsent) return;

  const language = ["en", "th", "zh"].includes(localStorage.getItem("gsb-language"))
    ? localStorage.getItem("gsb-language")
    : "en";
  const copy = {
    en: {
      title: "Your privacy matters",
      text: "We use essential cookies to keep this website working and improve your browsing experience.",
      accept: "Accept all",
      necessary: "Necessary only",
      label: "Cookie consent",
    },
    th: {
      title: "เราให้ความสำคัญกับความเป็นส่วนตัวของคุณ",
      text: "เราใช้คุกกี้ที่จำเป็นเพื่อให้เว็บไซต์ทำงานได้อย่างเหมาะสม และช่วยพัฒนาประสบการณ์การใช้งานของคุณ",
      accept: "ยอมรับทั้งหมด",
      necessary: "ยอมรับเฉพาะที่จำเป็น",
      label: "การตั้งค่าคุกกี้",
    },
    zh: {
      title: "我们重视您的隐私",
      text: "我们使用必要的 Cookie 来确保网站正常运行，并改善您的浏览体验。",
      accept: "接受全部 Cookie",
      necessary: "仅接受必要 Cookie",
      label: "Cookie 同意设置",
    },
  }[language];

  const banner = document.createElement("section");
  banner.className = "gsb-cookie-banner";
  banner.setAttribute("role", "dialog");
  banner.setAttribute("aria-label", copy.label);
  banner.setAttribute("aria-live", "polite");
  banner.innerHTML = `
    <div class="gsb-cookie-banner__mark" aria-hidden="true">◌</div>
    <div class="gsb-cookie-banner__content">
      <strong>${copy.title}</strong>
      <p>${copy.text}</p>
    </div>
    <div class="gsb-cookie-banner__actions">
      <button type="button" class="gsb-cookie-banner__necessary">${copy.necessary}</button>
      <button type="button" class="gsb-cookie-banner__accept">${copy.accept}</button>
    </div>`;

  const saveConsent = (choice) => {
    document.cookie = `${consentCookie}=${choice}; max-age=31536000; path=/; SameSite=Lax`;
    banner.classList.add("is-leaving");
    window.setTimeout(() => banner.remove(), 220);
  };

  banner.querySelector(".gsb-cookie-banner__accept").addEventListener("click", () => saveConsent("accepted"));
  banner.querySelector(".gsb-cookie-banner__necessary").addEventListener("click", () => saveConsent("necessary"));
  document.body.append(banner);
  requestAnimationFrame(() => banner.classList.add("is-visible"));
};

setupCookieConsent();

const siteFooter = document.querySelector(".site-footer") || document.querySelector("body.solutions-app-page footer, body.solar-app-page footer, body.epc-app-page footer, body.ev-app-page footer");

if (siteFooter) {
  const icon = (path) => `<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="${path}"/></svg>`;
  siteFooter.classList.add("site-footer", "contact-footer");
  siteFooter.innerHTML = `
    <div class="shell contact-footer-grid">
      <div class="contact-footer-brand">
        <a class="brand footer-brand" href="/" aria-label="GSB SunPower Group home"><img class="brand-logo" src="/image/GSBSUNPOWER-Edit.png" alt="GSB SunPower Group"></a>
        <p>Clean energy, made practical.</p>
        <a class="contact-footer-map" href="https://maps.app.goo.gl/Qxmo7veFMyM6Ec439" target="_blank" rel="noopener noreferrer">${icon("M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z")}<span>Visit our office</span></a>
      </div>
      <div class="contact-footer-column">
        <p class="contact-footer-label">CONTACT</p>
        <a href="tel:+66988285767">${icon("M6.6 2.5 4.3 3.6c-.8.4-1.2 1.3-1 2.1 1.7 7.3 7.4 13 14.7 14.7.8.2 1.7-.2 2.1-1l1.1-2.3-4.2-2.1-1.3 1.6c-2.2-1-4-2.8-5-5L12 10.3 9.9 6.1 8.3 7.4c-1-.9-1.6-2.1-1.7-3.4Z")}<span>098 828 5767</span></a>
        <a href="mailto:marketing.gsbsunpower@gmail.com">${icon("M3 5h18v14H3V5Zm2.5 2 6.5 5 6.5-5H5.5Zm13.5 10V9.2l-7 5.4-7-5.4V17h14Z")}<span>marketing.gsbsunpower@gmail.com</span></a>
      </div>
      <div class="contact-footer-column contact-footer-address">
        <p class="contact-footer-label">OUR OFFICE</p>
        <a href="https://maps.app.goo.gl/Qxmo7veFMyM6Ec439" target="_blank" rel="noopener noreferrer">${icon("M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z")}<span>BHIRAJ TOWER at BITEC, 22nd Floor, Room 2209<br>4345 Sukhumvit Road, Bang Na Tai Subdistrict,<br>Bang Na District, Bangkok 10260, Thailand</span></a>
      </div>
      <div class="contact-footer-column contact-footer-socials">
        <p class="contact-footer-label">FOLLOW GSB</p>
        <div>
          <a href="https://www.facebook.com/gsbsunpowerofficial" target="_blank" rel="noopener noreferrer" aria-label="Facebook">${icon("M14 8h3V4h-3c-2.8 0-5 2.2-5 5v3H6v4h3v6h4v-6h3l1-4h-4V9c0-.6.4-1 1-1Z")}</a>
          <a href="https://www.tiktok.com/@gsb.sunpower" target="_blank" rel="noopener noreferrer" aria-label="TikTok">${icon("M14 3v11.1a3.5 3.5 0 1 1-3-3.46V7.5A7 7 0 1 0 18 14V8.8c1 .7 2.2 1.1 3.5 1.2V6.4A4 4 0 0 1 18 3h-4Z")}</a>
          <a href="https://www.instagram.com/gsb_sunpower" target="_blank" rel="noopener noreferrer" aria-label="Instagram">${icon("M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5A4.5 4.5 0 1 1 7.5 12 4.5 4.5 0 0 1 12 7.5Zm0 2A2.5 2.5 0 1 0 14.5 12 2.5 2.5 0 0 0 12 9.5ZM17.8 6a1.2 1.2 0 1 1-1.2 1.2A1.2 1.2 0 0 1 17.8 6Z")}</a>
          <a href="https://line.me/R/ti/p/@gsbsunpower" target="_blank" rel="noopener noreferrer" aria-label="LINE">${icon("M20 11.7c0-4.1-3.6-7.5-8-7.5s-8 3.4-8 7.5 3.6 7.5 8 7.5c.8 0 1.6-.1 2.3-.4l3.1 1.7-.8-2.7c2.1-1.4 3.4-3.6 3.4-6.1Zm-11.5 2H7v-4h1.5v2.7h1.4v1.3Zm3.2 0h-1.5v-4h1.5v4Zm4.8 0H15l-1.7-2.2v2.2h-1.5v-4h1.5l1.7 2.2V9.7h1.5v4Z")}</a>
        </div>
        <div class="contact-footer-apps" aria-label="Download the EV Charge by Sun Power app">
          <a class="contact-footer-store contact-footer-store--apple" href="https://apps.apple.com/th/app/ev-charge-by-sun-power/id6711341578?l=th" target="_blank" rel="noopener noreferrer" aria-label="Download EV Charge by Sun Power on the App Store">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.1 12.8c0-2.1 1.7-3.1 1.8-3.2-1-1.4-2.5-1.6-3-1.6-1.3-.1-2.5.8-3.1.8-.7 0-1.7-.8-2.8-.8-1.4 0-2.8.9-3.5 2.2-1.5 2.6-.4 6.5 1 8.5.7 1 1.5 2.1 2.6 2.1 1 0 1.4-.7 2.7-.7s1.6.7 2.7.7c1.1 0 1.9-1 2.6-2 .8-1.2 1.1-2.3 1.1-2.4-.1 0-2.1-.8-2.1-3.6Zm-2.1-6.2c.6-.7 1-1.7.9-2.6-.9 0-2 .6-2.7 1.3-.6.6-1.1 1.6-1 2.5 1 .1 2-.5 2.8-1.2Z"/></svg>
            <span><small>Download on the</small><b>App Store</b></span>
          </a>
          <a class="contact-footer-store contact-footer-store--play" href="https://play.google.com/store/apps/details?id=com.sunpower.evcharge&amp;hl=th" target="_blank" rel="noopener noreferrer" aria-label="Get EV Charge by Sun Power on Google Play">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m3.5 3.2 10.1 8.8-10.1 8.8a2.1 2.1 0 0 1-.5-1.4V4.6c0-.5.2-1 .5-1.4Zm11.4 9.9 2.5 2.2-10.2 5.6 7.7-7.8Zm3.6-3.1 1.6.9c1 .6 1 1.6 0 2.2l-1.6.9-2.6-2.2 2.6-2.2ZM7.2 3.1l10.2 5.6-2.5 2.2-7.7-7.8Z"/></svg>
            <span><small>GET IT ON</small><b>Google Play</b></span>
          </a>
        </div>
      </div>
    </div>
    <div class="shell contact-footer-bottom"><span>© 2026 GSB SunPower Group</span><a href="#top">Back to top ↑</a></div>`;
}

// The experience pages add their own content blocks. Keep their copy in the
// same language system as the rest of the site, after shared UI is in place.
const experienceLanguageScript = document.createElement("script");
experienceLanguageScript.src = "/gx-site-language.js?v=20261001-blog-nav";
document.head.append(experienceLanguageScript);

// The Blog is only public after an editor publishes its first article or video.
// Resolve this once per page load so every independently-built header follows the
// same source of truth, including headers rebuilt by the language scripts.
const syncBlogAvailability = async () => {
  try {
    const response = await fetch('/site-status', { headers: { Accept: 'application/json' } });
    const payload = response.ok ? await response.json() : { available: false };
    window.gsbHasPublishedBlogContent = payload.blog_available === true;
  } catch {
    // Hiding is safer than exposing a route that is not ready for visitors.
    window.gsbHasPublishedBlogContent = false;
  }

  document.querySelectorAll('a[href="blog.html"], a[href="/blog.html"], a[href$="/blog.html"]').forEach((link) => {
    if (!window.gsbHasPublishedBlogContent) link.remove();
  });
  window.dispatchEvent(new CustomEvent('gsb:blog-availability'));
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', syncBlogAvailability, { once: true });
} else {
  syncBlogAvailability();
}
