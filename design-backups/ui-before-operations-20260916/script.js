const menuButton = document.querySelector(".menu-button");
const menu = document.querySelector(".site-menu");

// These pages remain available by URL, but are not part of the public navigation.
menu?.querySelectorAll('a[href="approach.html"], a[href="projects.html"]').forEach((link) => link.remove());
menu?.querySelectorAll('a[href="insights.html"]').forEach((link) => { link.href = "contact.html"; link.textContent = "Contact Us"; });

document.querySelectorAll(".brand").forEach((brand) => {
  if (brand.querySelector(".brand-logo")) return;

  const logo = document.createElement("img");
  logo.className = "brand-logo";
  logo.src = "image/GSBSUNPOWER.png";
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
  localiseContactForm(language);
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
  const detailCopy = { th:["ติดต่อเรา","สำนักงาน","กรุงเทพฯ ประเทศไทย","เรายินดีนัดหมายเพื่อพูดคุยรายละเอียดโครงการของคุณ","อีเมล","โทรศัพท์","บอกเราเกี่ยวกับพื้นที่ของคุณ แล้วเราจะช่วยวางขั้นตอนถัดไปที่เหมาะสม"], zh:["联系我们","办公室","泰国曼谷","欢迎预约沟通您的项目需求。","电子邮箱","电话","告诉我们您的场地，我们将帮助规划下一步。"], en:["GET IN TOUCH","OFFICE","Bangkok, Thailand","We’re happy to discuss your project.","EMAIL","PHONE","Tell us about your site, and we’ll help map the next practical step."] }[language];
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
    "โซลาร์รูฟท็อปโรงงานอุตสาหกรรม สมุทรสาคร",
    "โซลาร์รูฟท็อปโรงงาน อยุธยา",
    "โซลาร์สำหรับโรงงานผ้าไม่ถักทอ สมุทรสาคร",
    "โซลาร์สำหรับพื้นที่พาณิชย์ หัวหิน",
    "โซลาร์รูฟท็อปโรงงาน ระยอง",
    "ระบบโซลาร์ประสิทธิภาพสูง ฉะเชิงเทรา",
    "โซลาร์สำหรับพื้นที่อุตสาหกรรม พระราม 2",
    "โซลาร์หลังคาตลาด ระยอง",
    "โซลาร์สำหรับโรงงานบรรจุภัณฑ์ ระยอง",
    "โซลาร์สำหรับโรงงานอุปกรณ์แสงสว่าง ระยอง",
    "โซลาร์สำหรับอุตสาหกรรมเคมี ระยอง",
    "โซลาร์รูฟท็อปธุรกิจ เพชรบุรี",
    "โซลาร์สำหรับโรงงานแปรรูปไม้ ประจวบคีรีขันธ์",
    "โซลาร์สำหรับศูนย์บริการ ประจวบคีรีขันธ์",
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
            thai ? "โซลาร์ พีพีเอ ↗" : "太阳能购电协议 ↗",
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
  wrapper.innerHTML = `<button class="language-toggle" type="button" aria-expanded="false" aria-label="${copy.language}">${language.toUpperCase()} <span>⌄</span></button><div class="language-options" role="menu"><button data-language="en" role="menuitem">English</button><button data-language="th" role="menuitem">ไทย</button><button data-language="zh" role="menuitem">中文</button></div>`;
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
    "ระบบโซลาร์รูฟท็อปสำหรับโรงงาน",
  ],
  [
    "3 MW",
    "Samut Sakhon",
    "สมุทรสาคร",
    "Nonwoven production facility",
    "โครงการโซลาร์สำหรับโรงงานผลิตผ้าไม่ถักทอ",
  ],
  [
    "1.3 MW",
    "Hua Hin",
    "หัวหิน",
    "Coastal commercial site",
    "ระบบโซลาร์สำหรับพื้นที่พาณิชยกรรม",
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
    "งานติดตั้งอุปกรณ์โซลาร์ประสิทธิภาพสูง",
  ],
  [
    "550 kW",
    "Rama II",
    "พระราม 2",
    "Urban industrial energy system",
    "โซลาร์รูฟท็อปสำหรับพื้นที่อุตสาหกรรมในเมือง",
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
    "โครงการโซลาร์สำหรับโรงงานบรรจุภัณฑ์",
  ],
  [
    "1.7 MW",
    "Rayong",
    "ระยอง",
    "Lighting manufacturing facility",
    "ระบบโซลาร์สำหรับโรงงานผลิตอุปกรณ์แสงสว่าง",
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
    "ระบบโซลาร์รูฟท็อปสำหรับธุรกิจพาณิชย์",
  ],
  [
    "120 kW",
    "Prachuap Khiri Khan",
    "ประจวบคีรีขันธ์",
    "Wood processing solar",
    "โครงการโซลาร์สำหรับโรงงานแปรรูปไม้",
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
      capacity: "System capacity",
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
      capacity: "กำลังติดตั้ง",
      location: "สถานที่ตั้ง",
      model: "รูปแบบบริการ",
      type: "ประเภทระบบ",
      delivery: "ขอบเขตโครงการ",
      scope: "ขอบเขตงาน",
      status: "สถานะโครงการ",
      discuss: "ปรึกษาโครงการลักษณะเดียวกัน",
      rooftop: "โซลาร์รูฟท็อป",
      endToEnd: "ครบวงจร",
      turnkey: "ส่งมอบแบบครบวงจร",
      commissioned: "ส่งมอบและพร้อมใช้งาน",
      ppaService: "ลงทุน ติดตั้ง และดูแลระบบ",
      epcService: "ออกแบบ จัดหา ก่อสร้าง และส่งมอบ",
    },
    zh: {
      capacity: "系统容量",
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
      "โครงการโซลาร์รูฟท็อปสำหรับโรงงาน",
    ],
    [
      "pexels-budget-bizar-92378004-12224996.jpg",
      "Commercial rooftop solar",
      "2.2 MWp",
      "ระบบโซลาร์สำหรับอาคารพาณิชย์",
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
      "โครงการโซลาร์รูฟท็อปขนาดใหญ่",
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
      "โครงการติดตั้งระบบโซลาร์รูฟท็อป",
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
      "ออกแบบระบบโซลาร์ให้เหมาะกับพื้นที่",
    ],
    [
      "VCG211566851116.webp",
      "Business rooftop solution",
      "2.3 MWp",
      "โซลูชันโซลาร์รูฟท็อปสำหรับธุรกิจ",
    ],
    [
      "VCG211576523975.webp",
      "Integrated solar delivery",
      "1.6 MWp",
      "ส่งมอบระบบโซลาร์แบบครบวงจร",
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
