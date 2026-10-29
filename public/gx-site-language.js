const localiseExperience = () => {
  const language = ["en", "th", "zh"].includes(localStorage.getItem("gsb-language"))
    ? localStorage.getItem("gsb-language")
    : "en";
  const html = document.documentElement;
  html.lang = language;
  html.dataset.language = language;

  const text = (selector, value) => {
    document.querySelectorAll(selector).forEach((element) => { element.textContent = value; });
  };
  const markup = (selector, value) => {
    document.querySelectorAll(selector).forEach((element) => { element.innerHTML = value; });
  };
  const placeholder = (selector, value) => {
    document.querySelectorAll(selector).forEach((element) => { element.placeholder = value; });
  };
  const setMany = (selector, values, html = false) => document.querySelectorAll(selector).forEach((element, index) => {
    if (values[index] !== undefined) {
      if (html) element.innerHTML = values[index]; else element.textContent = values[index];
    }
  });

  const shared = {
    en: { about: "About us", blog: "Blog", solutions: "Solutions", contact: "Contact us", back: "Back to Solutions", contactLabel: "CONTACT", follow: "FOLLOW GSB", office: "OUR OFFICE", visit: "Visit our office", top: "Back to top ↑", tagline: "Clean energy, made practical.", address: "BHIRAJ TOWER at BITEC, 22nd Floor, Room 2209<br>4345 Sukhumvit Road, Bang Na Tai Subdistrict,<br>Bang Na District, Bangkok 10260, Thailand" },
    th: { about: "เกี่ยวกับเรา", blog: "บทความ", solutions: "โซลูชัน", contact: "ติดต่อเรา", back: "กลับไปหน้าโซลูชัน", contactLabel: "ติดต่อเรา", follow: "ติดตาม GSB", office: "สำนักงานของเรา", visit: "ดูแผนที่สำนักงาน", top: "กลับไปด้านบน ↑", tagline: "พลังงานสะอาดที่ออกแบบมาเพื่อการใช้งานจริง", address: "อาคาร BHIRAJ TOWER at BITEC ชั้น 22 ห้อง 2209<br>4345 ถนนสุขุมวิท แขวงบางนาใต้<br>เขตบางนา กรุงเทพมหานคร 10260 ประเทศไทย" },
    zh: { about: "关于我们", blog: "博客", solutions: "解决方案", contact: "联系我们", back: "返回解决方案", contactLabel: "联系我们", follow: "关注 GSB", office: "我们的办公室", visit: "访问我们的办公室", top: "返回顶部 ↑", tagline: "让清洁能源真正落地。", address: "BITEC 会展中心 BHIRAJ TOWER，22 层 2209 室<br>泰国曼谷挽那泰分区素坤逸路 4345 号<br>挽那区，邮编 10260" },
  }[language];

  const hasPublishedBlogContent = () => window.gsbHasPublishedBlogContent !== false;
  const renderNavigation = () => document.querySelectorAll(".gx-header .gx-navline nav").forEach((nav) => {
    const isBlogPage = window.location.pathname.endsWith('/blog.html');
    nav.innerHTML = `<a href="about.html">${shared.about}</a>${hasPublishedBlogContent() ? `<a class="${isBlogPage ? 'is-current' : ''}" href="blog.html">${shared.blog}</a>` : ''}<a href="solutions.html">${shared.solutions}</a>`;
  });
  renderNavigation();
  window.addEventListener('gsb:blog-availability', renderNavigation);
  text(".gx-header .gx-contact", shared.contact);
  text(".gx-back-solutions", shared.back);
  text(".contact-footer-brand > p", shared.tagline);
  text(".contact-footer-map span", shared.visit);
  text(".contact-footer-column:not(.contact-footer-address):not(.contact-footer-socials) .contact-footer-label", shared.contactLabel);
  text(".contact-footer-address .contact-footer-label", shared.office);
  markup(".contact-footer-address a span", shared.address);
  text(".contact-footer-socials .contact-footer-label", shared.follow);
  text(".contact-footer-bottom a", shared.top);

  const home = {
    en: {
      label: "GSB SUNPOWER GROUP", heading: "Solar and EV<br><em>One team One clear path</em>", lead: "Clean-energy infrastructure, planned around your business — from solar to EV charging", prompt: "Choose a service to start", solar: "Solar solutions", ev: "EV charging", general: "General enquiry", select: "Select what you are interested in", options: ["Factory installation", "Commercial building installation", "Investment and operating model consultation"], consult: "Get a consultation", faqLabel: "HELP & INFORMATION", faqTitle: "Frequently asked questions",
      faq: [["What services does GSB Sun Power provide?", "We provide clean-energy solutions for solar projects and EV charging stations, from consultation and site assessment through design, installation and system care. Each project is planned to suit the business."], ["How do we know whether our site is suitable for solar?", "We first consider electricity use, when power is used, available installation space and structural readiness. Send us a recent bill and site photos for an initial review before a detailed survey."], ["How much can solar reduce our electricity bill?", "Savings depend on system size, sunlight and how much electricity is used while the system is producing. We assess each site’s data to provide a practical estimate."], ["How long does installation take, and will it affect operations?", "Timing depends on project size, site readiness and approvals. We plan work in advance, including any connection shutdowns, so your team can prepare and disruption stays low."], ["What support and warranty are available after installation?", "We explain how to use and monitor the system and remain available for support. Equipment, installation and maintenance warranties follow each project proposal and agreement."], ["How do we get started with solar or EV charging?", "Contact us through the website form or LINE OA: @gsbsunpower with your business type, location and contact details. A recent power bill helps with solar assessment; site photos and parking capacity help with EV charging."]],
    },
    th: {
      label: "GSB SUNPOWER GROUP", heading: "พลังงานแสงอาทิตย์และ EV<br><em>ทีมเดียว ดูแลครบ</em>", lead: "โครงสร้างพื้นฐานด้านพลังงานสะอาดที่ออกแบบให้เหมาะกับธุรกิจของคุณ ตั้งแต่ระบบโซลาร์เซลล์ไปจนถึงสถานีชาร์จ EV", prompt: "เลือกบริการเพื่อเริ่มต้น", solar: "บริการโซลาร์เซลล์", ev: "สถานีชาร์จ EV", general: "สอบถามทั่วไป", select: "เลือกสิ่งที่คุณสนใจ", options: ["ติดตั้งระบบสำหรับโรงงาน", "ติดตั้งระบบสำหรับอาคารพาณิชย์", "ปรึกษาด้านการลงทุนและการดำเนินงาน"], consult: "ขอรับคำปรึกษา", faqLabel: "คำถามและข้อมูลที่ควรรู้", faqTitle: "คำถามที่พบบ่อย",
      faq: [["GSB Sun Power ให้บริการอะไรบ้าง?", "เราให้บริการด้านพลังงานสะอาด ทั้งโครงการโซลาร์เซลล์และสถานีชาร์จรถยนต์ไฟฟ้า ครอบคลุมการให้คำปรึกษา ประเมินพื้นที่ ออกแบบ ติดตั้ง และดูแลระบบ โดยพิจารณารูปแบบโครงการให้เหมาะกับแต่ละธุรกิจ"], ["จะรู้ได้อย่างไรว่าพื้นที่ของเราเหมาะกับการติดตั้งโซลาร์เซลล์?", "เบื้องต้นจะพิจารณาจากปริมาณการใช้ไฟฟ้า ช่วงเวลาที่ใช้ไฟ พื้นที่ติดตั้ง และความพร้อมของโครงสร้าง คุณสามารถส่งบิลค่าไฟและภาพสถานที่ให้ทีมงานประเมินเบื้องต้น ก่อนสำรวจรายละเอียดหน้างาน"], ["ติดตั้งโซลาร์เซลล์แล้วช่วยลดค่าไฟได้เท่าไร?", "ขึ้นอยู่กับขนาดระบบ ปริมาณแสงแดด และการใช้ไฟในช่วงที่ระบบผลิตไฟได้ ทีมงานจะประเมินการประหยัดจากข้อมูลของแต่ละสถานที่ เพื่อให้ได้ประมาณการที่เหมาะกับการใช้งานจริง"], ["การติดตั้งโซลาร์เซลล์ใช้เวลานานแค่ไหน และกระทบการดำเนินธุรกิจหรือไม่?", "ระยะเวลาขึ้นอยู่กับขนาดโครงการ ความพร้อมของสถานที่ และขั้นตอนการขออนุญาต ทีมงานจะวางแผนและประสานกำหนดการล่วงหน้าเพื่อลดผลกระทบต่อการทำงาน"], ["หลังติดตั้ง มีบริการดูแลและรับประกันอย่างไร?", "ทีมงานจะแนะนำวิธีใช้งานและติดตามการทำงานของระบบ พร้อมให้ข้อมูลการดูแลหลังติดตั้ง ส่วนการรับประกันเป็นไปตามข้อเสนอและสัญญาของแต่ละโครงการ"], ["สนใจติดตั้งโซลาร์เซลล์หรือสถานีชาร์จ EV ต้องเริ่มต้นอย่างไร?", "ติดต่อผ่านแบบฟอร์มบนเว็บไซต์ หรือ LINE OA: @gsbsunpower พร้อมแจ้งประเภทธุรกิจ พิกัดสถานที่ และข้อมูลติดต่อ เพื่อให้ทีมงานประเมินเบื้องต้นได้สะดวกขึ้น"]],
    },
    zh: {
      label: "GSB SUNPOWER GROUP", heading: "太阳能与电动汽车充电<br><em>一支团队，一条清晰路径</em>", lead: "围绕您的业务规划清洁能源基础设施，从太阳能到电动汽车充电", prompt: "选择一项服务开始", solar: "太阳能解决方案", ev: "电动汽车充电", general: "一般咨询", select: "请选择您感兴趣的服务", options: ["工厂安装", "商业建筑安装", "投资与运营模式咨询"], consult: "获取咨询", faqLabel: "帮助与信息", faqTitle: "常见问题",
      faq: [["GSB Sun Power 提供哪些服务？", "我们提供太阳能项目和电动汽车充电站等清洁能源解决方案，涵盖咨询、现场评估、设计、安装及系统维护，并根据每项业务的需要规划项目。"], ["如何判断场地是否适合安装太阳能？", "我们会先了解用电量、用电时段、可安装空间及结构条件。您可以提供近期电费单和现场照片，我们会在详细勘察前进行初步评估。"], ["太阳能可以节省多少电费？", "节省金额取决于系统规模、日照及发电时段的用电量。团队会按每个场地的数据提供贴近实际的估算。"], ["安装需要多久，会影响日常业务吗？", "工期取决于项目规模、场地条件和审批流程。团队会提前规划并协调时间，包括必要的并网停电时间，以减少对业务的影响。"], ["安装后有哪些维护和保修服务？", "团队会说明系统使用与监测方法，并提供安装后的支持。设备、安装和维护保修范围以每个项目的方案及合同为准。"], ["想安装太阳能或 EV 充电站，该如何开始？", "可通过网站表单或 LINE OA：@gsbsunpower 联系我们，并提供业务类型、地点和联系方式。太阳能评估建议准备最新电费单；EV 充电建议准备场地照片和车位数量。"]],
    },
  }[language];

  const managedHomeHero = window.gsbHomeHero?.[language];
  if (managedHomeHero) {
    const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#039;", '"': "&quot;" }[character]));
    home.label = managedHomeHero.label || home.label;
    home.heading = `${escapeHtml(managedHomeHero.heading || '')}<br><em>${escapeHtml(managedHomeHero.emphasis || '')}</em>`;
    home.lead = managedHomeHero.lead || home.lead;
    home.prompt = managedHomeHero.prompt || home.prompt;
    home.consult = managedHomeHero.consult || home.consult;
  }

  if (document.querySelector(".gx-hero")) {
    text(".gx-hero .gx-label", home.label); markup(".gx-hero h1", home.heading); text(".gx-hero .gx-lead", home.lead); text(".gx-hero .gx-prompt", home.prompt); text("[data-gx-service=solar]", home.solar); text("[data-gx-service=ev]", home.ev); text("[data-gx-service=general]", home.general); text("[data-gx-link]", home.select); document.querySelectorAll("[data-gx-option]").forEach((button, index) => { button.textContent = home.options[index]; }); text("[data-gx-consult]", home.consult);
    text(".gx-faq-th .gx-label", home.faqLabel); text("#faq-title", home.faqTitle); const faq = document.querySelector(".gx-faq-list"); if (faq) { faq.lang = language; faq.querySelectorAll("details").forEach((item, index) => { const row = home.faq[index]; if (!row) return; item.querySelector("summary").childNodes[1].nodeValue = row[0]; item.querySelector("p").textContent = row[1]; }); }
    const enquiry = {
      en: { label:"START A CONVERSATION", title:"Tell us about<br><em>your energy plan</em>", lead:"Share a few details and our team will help identify the most practical next step for your site", name:"Full name / Company name*", email:"Email*", phone:"Phone*", service:"Service of interest", solar:"Solar PPA", epc:"Engineering & EPC", ev:"EV charging", budget:"Estimated budget", budgetPrompt:"Select a range", budgetSmall:"Under ฿1 million", budgetMedium:"฿1–5 million", budgetLarge:"Over ฿5 million", message:"How can we help?*", submit:"Send enquiry ↗" },
      th: { label:"เริ่มวางแผนกับ GSB", title:"เล่าให้เราฟัง<br><em>ถึงแผนพลังงาน<br>ของคุณ</em>", lead:"แชร์รายละเอียดเบื้องต้น แล้วทีมงานจะช่วยแนะนำแนวทางที่เหมาะกับพื้นที่ของคุณ", name:"ชื่อ / ชื่อบริษัท*", email:"อีเมล*", phone:"หมายเลขโทรศัพท์*", service:"บริการที่สนใจ", solar:"Solar PPA", epc:"วิศวกรรมและ EPC", ev:"สถานีชาร์จรถยนต์ไฟฟ้า", budget:"งบประมาณโดยประมาณ", budgetPrompt:"เลือกช่วงงบประมาณ", budgetSmall:"ต่ำกว่า 1 ล้านบาท", budgetMedium:"1–5 ล้านบาท", budgetLarge:"มากกว่า 5 ล้านบาท", message:"ต้องการให้เราช่วยเรื่องใด?*", submit:"ส่งข้อมูล ↗" },
      zh: { label:"开始与 GSB 沟通", title:"告诉我们您的<br><em>能源计划</em>", lead:"分享一些基本信息，我们的团队将帮助您找到最适合场地的下一步", name:"姓名 / 公司名称*", email:"电子邮箱*", phone:"电话*", service:"感兴趣的服务", solar:"太阳能购电协议", epc:"工程与 EPC", ev:"电动汽车充电", budget:"预计预算", budgetPrompt:"选择预算范围", budgetSmall:"低于 100 万泰铢", budgetMedium:"100–500 万泰铢", budgetLarge:"高于 500 万泰铢", message:"我们能如何协助您？*", submit:"发送咨询 ↗" },
    }[language];
    document.querySelectorAll("[data-gx-home-enquiry]").forEach((element) => { const value = enquiry[element.dataset.gxHomeEnquiry]; if (value) element.textContent = value; });
    document.querySelectorAll("[data-gx-home-enquiry-html]").forEach((element) => { const value = enquiry[element.dataset.gxHomeEnquiryHtml]; if (value) element.innerHTML = value; });
    placeholder(".gx-home-enquiry-form input[name=name]", enquiry.name); placeholder(".gx-home-enquiry-form input[name=email]", enquiry.email); placeholder(".gx-home-enquiry-form input[name=phone]", enquiry.phone); placeholder(".gx-home-enquiry-form textarea", enquiry.message);
    const extra = {
      en: { trustLabel:"GSB SUNPOWER PARTNERSHIPS", trustTitle:"Trusted by<br><em>organisations across industries</em>", trustLead:"We make energy infrastructure easier to choose, deliver and use — for businesses ready to move forward", trustOne:"GSB SunPower brings together solar, EV charging and engineering in one practical team We begin with the way your site and business really operate", trustTwo:"From first assessment through delivery and long-term support, every recommendation is built to perform in the real world", trustBusinesses:"Trusted by businesses across Thailand", trustCta:"Get to know GSB <span>↗</span>", connectLabel:"CONNECT WITH GSB", connectTitle:"Find the right<br><em>conversation</em>", connectLead:"Follow our work, ask a question or start your next energy project through the channel that suits you", nextLabel:"START WITH GSB", nextTitle:"Ready to make your<br><em>next energy move?</em>", nextLead:"Tell us what you are planning Our team will help you find the most practical next step", talk:"Talk to our team <span>↗</span>", teamLabel:"THE GSB TEAM", teamTitle:"Clear advice, <img src=\"image/team-03.png\" alt=\"GSB SunPower team member\"> practical delivery, <img src=\"image/team-01.png\" alt=\"GSB SunPower team member\"><br>and <img src=\"image/team-02.png\" alt=\"GSB SunPower team member\"> support that stays <em>close</em> <img src=\"image/team-04.png\" alt=\"GSB SunPower team member\">", teamLead:"We bring specialist knowledge together so every decision is clear, practical and ready for your real-world operation", teamCta:"Meet the GSB team <span>↗</span>", reviewLabel:"PARTNER FEEDBACK", reviewTitle:"What it feels like to<br><em>work with GSB</em>" },
      th: { trustLabel:"พันธมิตรของ GSB SUNPOWER", trustTitle:"หลากหลายองค์กร<br><em>ไว้วางใจ GSB</em>", trustLead:"เราทำให้การวางระบบพลังงานเข้าใจง่าย วางแผนชัดเจน และส่งมอบได้จริง สำหรับธุรกิจที่พร้อมก้าวต่อไป", trustOne:"GSB SunPower รวมระบบโซลาร์เซลล์ สถานีชาร์จรถยนต์ไฟฟ้า และงานวิศวกรรมไว้ในทีมเดียว โดยเริ่มจากรูปแบบการใช้งานจริงของพื้นที่และธุรกิจคุณ", trustTwo:"ตั้งแต่การประเมิน การส่งมอบ ไปจนถึงการดูแลระยะยาว ทุกคำแนะนำออกแบบมาเพื่อให้ใช้งานได้จริง", trustBusinesses:"ได้รับความไว้วางใจจากธุรกิจทั่วประเทศไทย", trustCta:"รู้จัก GSB มากขึ้น <span>↗</span>", connectLabel:"เชื่อมต่อกับ GSB", connectTitle:"เริ่มบทสนทนา<br><em>กับทีม GSB</em>", connectLead:"ติดตามผลงาน สอบถามข้อมูล หรือเริ่มวางแผนโครงการพลังงานครั้งต่อไปผ่านช่องทางที่คุณสะดวก", nextLabel:"เริ่มต้นกับ GSB", nextTitle:"พร้อมวางแผน<br><em>ก้าวต่อไปด้านพลังงาน?</em>", nextLead:"บอกเราถึงสิ่งที่คุณกำลังวางแผน แล้วทีมงานจะช่วยแนะนำขั้นตอนต่อไปที่เหมาะกับคุณ", talk:"พูดคุยกับทีมของเรา <span>↗</span>", teamLabel:"ทีม GSB", teamTitle:"คำแนะนำที่ชัดเจน <img src=\"image/team-03.png\" alt=\"สมาชิกทีม GSB SunPower\"> ส่งมอบงานอย่างมืออาชีพ <img src=\"image/team-01.png\" alt=\"สมาชิกทีม GSB SunPower\"><br>และดูแลใกล้ชิด <img src=\"image/team-02.png\" alt=\"สมาชิกทีม GSB SunPower\"> ในทุกขั้นตอน <em>ที่สำคัญ</em> <img src=\"image/team-04.png\" alt=\"สมาชิกทีม GSB SunPower\">", teamLead:"เรารวมผู้เชี่ยวชาญไว้ด้วยกัน เพื่อให้ทุกการตัดสินใจชัดเจน ใช้งานได้จริง และพร้อมต่อการดำเนินงานของคุณ", teamCta:"พบกับทีม GSB <span>↗</span>", reviewLabel:"ความคิดเห็นจากพาร์ทเนอร์", reviewTitle:"ประสบการณ์การทำงาน<br><em>ร่วมกับ GSB</em>" },
      zh: { trustLabel:"GSB SUNPOWER 合作伙伴", trustTitle:"各行业组织<br><em>信赖 GSB</em>", trustLead:"我们让能源基础设施更易选择、交付和使用，帮助企业从容前行", trustOne:"GSB SunPower 将太阳能、电动汽车充电和工程服务整合为一支务实的团队，从场地和业务的实际运作开始", trustTwo:"从首次评估到交付及长期支持，每一项建议都以实际成效为目标", trustBusinesses:"受到泰国各地企业信赖", trustCta:"了解 GSB <span>↗</span>", connectLabel:"联系 GSB", connectTitle:"开启合适的<br><em>对话</em>", connectLead:"关注我们的工作、提出问题，或通过适合您的渠道开始下一个能源项目", nextLabel:"从 GSB 开始", nextTitle:"准备好规划您的<br><em>下一步能源行动了吗？</em>", nextLead:"告诉我们您的计划，团队将协助您找到最务实的下一步", talk:"与我们的团队交流 <span>↗</span>", teamLabel:"GSB 团队", teamTitle:"清晰建议 <img src=\"image/team-03.png\" alt=\"GSB SunPower 团队成员\"> 务实交付 <img src=\"image/team-01.png\" alt=\"GSB SunPower 团队成员\"><br>并始终陪伴 <img src=\"image/team-02.png\" alt=\"GSB SunPower 团队成员\"> 让支持更 <em>贴近</em> <img src=\"image/team-04.png\" alt=\"GSB SunPower 团队成员\">", teamLead:"我们汇集专业知识，让每个决定都清晰、务实，并支持您的实际运营", teamCta:"认识 GSB 团队 <span>↗</span>", reviewLabel:"合作伙伴反馈", reviewTitle:"与 GSB 合作的<br><em>感受</em>" },
    }[language];
    text(".gx-intro-statement .gx-label", extra.trustLabel); markup(".gx-intro-statement h2", extra.trustTitle); text(".gx-intro-statement > p:last-child", extra.trustLead); const trustDetails = document.querySelectorAll(".gx-intro-details > p"); if (trustDetails[0]) trustDetails[0].textContent = extra.trustOne; if (trustDetails[1]) trustDetails[1].textContent = extra.trustTwo; text(".gx-intro-details h3", extra.trustBusinesses); markup(".gx-intro-link", extra.trustCta);
    text(".gx-connect-copy .gx-label", extra.connectLabel); markup(".gx-connect-copy h2", extra.connectTitle); text(".gx-connect-copy > p:last-child", extra.connectLead); text(".gx-next-consult .gx-label", extra.nextLabel); markup(".gx-next-consult h2", extra.nextTitle); text(".gx-next-consult .gx-wrap > p:last-of-type", extra.nextLead); markup(".gx-next-consult-button", extra.talk); text(".gx-commitment .gx-label", extra.teamLabel); markup(".gx-commitment-inner h2", extra.teamTitle); text(".gx-commitment-inner > p:not(.gx-label)", extra.teamLead); markup(".gx-commitment-link", extra.teamCta); text(".gx-reviews .gx-label", extra.reviewLabel); markup("#reviews-title", extra.reviewTitle);
    const proof = {
      en: [["Built for businesses", "End-to-end energy solutions shaped around the way you work."], ["Performance you can see", "Clear plans, proven technology and measurable value."], ["Ready for what’s next", "Solar and EV infrastructure that can scale with you."]],
      th: [["ออกแบบเพื่อธุรกิจ", "โซลูชันพลังงานครบวงจรที่ออกแบบตามรูปแบบการทำงานของคุณ"], ["เห็นผลลัพธ์ได้ชัด", "แผนงานชัดเจน เทคโนโลยีที่พิสูจน์แล้ว และคุณค่าที่วัดผลได้"], ["พร้อมสำหรับก้าวถัดไป", "โครงสร้างพื้นฐานโซลาร์เซลล์และ EV ที่ขยายตามธุรกิจของคุณได้"]],
      zh: [["为企业而打造", "围绕您的工作方式提供端到端能源解决方案。"], ["可见的成效", "清晰规划、成熟技术与可衡量的价值。"], ["为下一步做好准备", "可随业务扩展的太阳能和电动汽车基础设施。"]],
    }[language];
    document.querySelectorAll(".gx-hero-proof article").forEach((card, index) => { if (!proof[index]) return; const title = card.querySelector("b"); const description = card.querySelector("p"); if (title) title.textContent = proof[index][0]; if (description) description.textContent = proof[index][1]; });
    text(".gx-channel-page small", language === "th" ? "หน้า Facebook ↗" : language === "zh" ? "Facebook 页面 ↗" : "Facebook page ↗");
    const commitment = document.querySelector(".gx-commitment");
    if (commitment && !commitment.querySelector(".gx-commitment-spark")) {
      commitment.insertAdjacentHTML("afterbegin", '<span class="gx-commitment-spark gx-spark-one" aria-hidden="true"></span><span class="gx-commitment-spark gx-spark-two" aria-hidden="true"></span><span class="gx-commitment-spark gx-spark-three" aria-hidden="true"></span><span class="gx-commitment-spark gx-spark-four" aria-hidden="true"></span><span class="gx-commitment-spark gx-spark-five" aria-hidden="true"></span><span class="gx-commitment-spark gx-spark-six" aria-hidden="true"></span>');
    }
    const secondCustomerLogo = document.querySelector(".gx-intro-logo-grid img:nth-child(2)");
    if (secondCustomerLogo) {
      secondCustomerLogo.src = "image/client-logos/client-le-srivaree.png";
      secondCustomerLogo.alt = "Le Srivaree";
    }
    const consult = {
      en: { brief:"PROJECT BRIEF", intro:"Tell us where you are now and what you want to achieve.", required:"Required fields", service:"Service requested", name:"Your name", phone:"Phone number", email:"Email", company:"Company", location:"Location", website:"Website", message:"Tell us about your project", send:"Send consultation request <span>↗</span>" },
      th: { brief:"ข้อมูลโครงการ", intro:"บอกเราว่าคุณอยู่จุดไหน และต้องการผลลัพธ์แบบใด", required:"ช่องที่ต้องกรอก", service:"บริการที่ต้องการ", name:"ชื่อของคุณ", phone:"หมายเลขโทรศัพท์", email:"อีเมล", company:"บริษัท", location:"สถานที่", website:"เว็บไซต์", message:"บอกเราเกี่ยวกับโครงการของคุณ", send:"ส่งคำขอรับคำปรึกษา <span>↗</span>" },
      zh: { brief:"项目简介", intro:"告诉我们您目前的情况以及希望达成的目标。", required:"必填项", service:"所需服务", name:"您的姓名", phone:"电话号码", email:"电子邮箱", company:"公司", location:"地点", website:"网站", message:"告诉我们您的项目", send:"发送咨询申请 <span>↗</span>" },
    }[language];
    text(".gx-form-kicker", consult.brief); text(".gx-form-intro", consult.intro); text(".gx-form-required", consult.required); const formLabels = document.querySelectorAll(".gx-consult-form .gx-field-label, .gx-consult-form label:not(.gx-field-service):not(.gx-field-message)");
    [consult.name, consult.phone, consult.email, consult.company, consult.location, consult.website].forEach((copy, index) => { const label = formLabels[index]; if (!label) return; const star = label.querySelector("b"); label.replaceChildren(document.createTextNode(copy + (star ? " " : ""))); if (star) label.append(star); });
    const serviceLabel = document.querySelector(".gx-field-service"); if (serviceLabel?.firstChild) serviceLabel.firstChild.nodeValue = `${consult.service} `; const messageLabel = document.querySelector(".gx-field-message"); if (messageLabel?.firstChild) messageLabel.firstChild.nodeValue = `${consult.message} `; markup(".gx-form-submit", consult.send);
  }

  const about = {
    en: { label: "GSB SUNPOWER GROUP", title: "One team for<br><em>clean-energy progress.</em>", lead: "We bring solar, EV charging and practical delivery together for businesses across Thailand", profile: "Company <em>profile</em>", orgLabel: "PEOPLE & TEAMS", orgTitle: "Meet the team<br><em>behind the work</em>", stats: ["Solar + EV", "One accountable team", "Thailand-wide delivery"], statDescriptions: ["Integrated energy solutions", "From planning to long-term support", "Built for real sites and operations"], growth: { label:"LOOKING AHEAD", title:"Growth plans<br><em>in progress</em>", evLabel:"EV CHARGING", evUnit:"Charging piles", evTarget:"Within <b>the next 3 years</b>", solarLabel:"SOLAR ENERGY", solarUnit:"PV power capacity", solarTarget:"Focused on industrial renewable-energy investment", investment:"International investment", boi:"Thailand BOI recognition", open:"View full document ↗", close:"Close dialog", altInvestment:"International investment certificate", altBoi:"Thailand BOI recognition document" } },
    th: { label: "GSB SUNPOWER GROUP", title: "ทีมเดียวเพื่อ<br><em>ความก้าวหน้าด้านพลังงานสะอาด</em>", lead: "เรารวมระบบโซลาร์เซลล์ สถานีชาร์จ EV และการส่งมอบโครงการไว้ในทีมเดียว เพื่อธุรกิจทั่วประเทศไทย", profile: "ข้อมูล <em>บริษัท</em>", orgLabel: "ทีมงานและโครงสร้างองค์กร", orgTitle: "ทีมที่อยู่เบื้องหลัง<br><em>ทุกความสำเร็จ</em>", stats: ["โซลาร์เซลล์ + EV", "ทีมเดียวที่ดูแลครบ", "ให้บริการทั่วประเทศไทย"], statDescriptions: ["โซลูชันพลังงานครบวงจร", "ตั้งแต่วางแผนจนดูแลระยะยาว", "ออกแบบให้เหมาะกับการใช้งานหน้างานจริง"], growth: { label:"มองไปข้างหน้า", title:"เป้าหมายการเติบโต<br><em>ที่เราวางไว้</em>", evLabel:"สถานีชาร์จ EV", evUnit:"จุดชาร์จ", evTarget:"ภายใน <b>3 ปีข้างหน้า</b>", solarLabel:"พลังงานแสงอาทิตย์", solarUnit:"กำลังการผลิตไฟฟ้าจากแสงอาทิตย์", solarTarget:"มุ่งสู่การลงทุนในพลังงานหมุนเวียนสำหรับภาคอุตสาหกรรม", investment:"การลงทุนต่างประเทศ", boi:"การรับรอง BOI ประเทศไทย", open:"เปิดดูเอกสารฉบับเต็ม ↗", close:"ปิดหน้าต่าง", altInvestment:"เอกสารรับรองการลงทุนต่างประเทศ", altBoi:"เอกสารรับรอง BOI ประเทศไทย" } },
    zh: { label: "GSB SUNPOWER GROUP", title: "一支团队，共创<br><em>清洁能源进步。</em>", lead: "我们整合太阳能、电动汽车充电与务实交付，为泰国各地企业提供服务", profile: "公司 <em>简介</em>", orgLabel: "团队与组织", orgTitle: "成就背后的<br><em>专业团队</em>", stats: ["太阳能 + EV", "一支负责到底的团队", "服务覆盖泰国"], statDescriptions: ["一体化能源解决方案", "从规划到长期支持", "为真实场地和运营而设计"], growth: { label:"展望未来", title:"规划中的<br><em>增长目标</em>", evLabel:"电动汽车充电", evUnit:"充电桩", evTarget:"未来 <b>3 年内</b>", solarLabel:"太阳能", solarUnit:"光伏发电装机容量", solarTarget:"聚焦工业可再生能源投资", investment:"境外投资", boi:"泰国 BOI 认证", open:"查看完整文件 ↗", close:"关闭窗口", altInvestment:"境外投资证书", altBoi:"泰国 BOI 认证文件" } },
  }[language];
  if (document.body.classList.contains("about-app-page")) {
    text(".gx-about-kicker", about.label); markup(".gx-about-hero-copy h1", about.title); text(".gx-about-hero-copy > p:last-child", about.lead); markup(".gsb-company-profile__heading h2", about.profile); text("[data-gx-org-label]", about.orgLabel); markup("[data-gx-org-title]", about.orgTitle);
    document.querySelectorAll(".gx-about-stats b").forEach((item, index) => { if (about.stats[index]) item.textContent = about.stats[index]; }); document.querySelectorAll(".gx-about-stats span").forEach((item, index) => { if (about.statDescriptions[index]) item.textContent = about.statDescriptions[index]; });
    const growth = about.growth;
    text("[data-gx-growth-label]", growth.label); markup("[data-gx-growth-title]", growth.title); text("[data-gx-growth-ev-label]", growth.evLabel); text("[data-gx-growth-ev-unit]", growth.evUnit); markup("[data-gx-growth-ev-target]", growth.evTarget); text("[data-gx-growth-solar-label]", growth.solarLabel); text("[data-gx-growth-solar-unit]", growth.solarUnit); text("[data-gx-growth-solar-target]", growth.solarTarget); text("[data-gx-growth-document-open]", growth.open); text("[data-gx-growth-modal-label]", about.label);
    document.querySelectorAll("[data-gx-growth-document]").forEach((item) => { item.textContent = growth[item.dataset.gxGrowthDocument]; });
    document.querySelectorAll("[data-gx-growth-document-alt]").forEach((item) => { item.alt = growth[item.dataset.gxGrowthDocumentAlt === "investment" ? "altInvestment" : "altBoi"]; });
    document.querySelectorAll("[data-gx-growth-modal-close]").forEach((item) => { item.setAttribute("aria-label", growth.close); });
  }

  const contact = {
    en: { label: "START WITH GSB", title: "Let’s make the<br><em>next step clear.</em>", lead: "Tell us what you are planning. Our team will help you find the most practical next step.", social: "Follow us", form: "Tell us about your project", submit: "Send enquiry" },
    th: { label: "เริ่มต้นกับ GSB", title: "วางแผน<br><em>ก้าวต่อไป</em>", lead: "บอกเราถึงสิ่งที่คุณกำลังวางแผน แล้วทีมงานจะช่วยแนะนำก้าวต่อไปที่เหมาะกับคุณ", social: "ติดตามเรา", form: "บอกเราเกี่ยวกับโครงการของคุณ", submit: "ส่งข้อมูล" },
    zh: { label: "从 GSB 开始", title: "让下一步<br><em>更清晰。</em>", lead: "告诉我们您的计划，团队将协助您找到最务实的下一步。", social: "关注我们", form: "告诉我们您的项目", submit: "发送咨询" },
  }[language];
  if (document.body.classList.contains("contact-app-page")) {
    text(".gx-contact-kicker", contact.label); markup(".gx-contact-heading h1", contact.title); text(".gx-contact-heading > p:last-child", contact.lead); text(".gx-contact-follow h2", contact.social); text(".gx-contact-form h2", contact.form); text(".gx-contact-form button[type=submit]", contact.submit);
    const contactDetails = {
      en: ["Bangkok", "Talk with us", "Project details", "What can we help with?", "Name*", "Email*", "Phone*", "Company*", "Project location", "Estimated budget", "How can we help?*", "We’ll connect you with the right GSB specialist."],
      th: ["กรุงเทพฯ", "พูดคุยกับเรา", "รายละเอียดโครงการ", "ให้เราช่วยเรื่องอะไรได้บ้าง?", "ชื่อ*", "อีเมล*", "โทรศัพท์*", "บริษัท*", "สถานที่โครงการ", "งบประมาณโดยประมาณ", "เราช่วยอะไรได้บ้าง?*", "เราจะเชื่อมต่อคุณกับผู้เชี่ยวชาญ GSB ที่เหมาะสม"],
      zh: ["曼谷", "联系我们", "项目详情", "我们能如何协助您？", "姓名*", "电子邮箱*", "电话*", "公司*", "项目地点", "预计预算", "我们如何协助您？*", "我们会为您联系合适的 GSB 专家。"],
    }[language];
    const detailSections = document.querySelectorAll(".gx-contact-details > section h2");
    text(detailSections[0] ? ".gx-contact-details > section:first-child h2" : "", contactDetails[0]);
    text(detailSections[1] ? ".gx-contact-details > section:nth-child(2) h2" : "", contactDetails[1]);
    markup(".gx-contact-details > section:first-child > p", shared.address);
    text(".gx-contact-form > p", contactDetails[3]);
    document.querySelectorAll(".gx-contact-fields > label").forEach((label, index) => {
      const field = label.querySelector("input, select, textarea");
      if (!field) return;
      const labels = contactDetails.slice(4, 11);
      const current = label.childNodes[0];
      if (current?.nodeType === Node.TEXT_NODE && labels[index]) current.nodeValue = labels[index];
    });
    text(".gx-contact-form small", contactDetails[11]);
    const contactServices = {
      en: ["Solar PPA", "Engineering & EPC", "EV charging"],
      th: ["Solar PPA", "วิศวกรรมและ EPC", "สถานีชาร์จรถยนต์ไฟฟ้า"],
      zh: ["Solar PPA", "工程与 EPC", "电动汽车充电"],
    }[language];
    document.querySelectorAll(".gx-contact-services label span").forEach((service, index) => {
      if (contactServices[index]) service.textContent = contactServices[index];
    });
    placeholder(".gx-contact-fields input[name=phone]", language === "th" ? "หมายเลขโทรศัพท์" : language === "zh" ? "电话号码" : "Phone number");
    placeholder(".gx-contact-fields textarea", language === "th" ? "บอกเราเกี่ยวกับโครงการ พื้นที่ หรือช่วงเวลาที่ต้องการ..." : language === "zh" ? "请告诉我们您的项目、场地或时间安排..." : "Share your project, site or timeline...");
    const budgetOptions = language === "th"
      ? ["เลือกช่วงงบประมาณ", "ต่ำกว่า 1 ล้านบาท", "1–5 ล้านบาท", "มากกว่า 5 ล้านบาท"]
      : language === "zh"
        ? ["选择预算范围", "低于 100 万泰铢", "100–500 万泰铢", "高于 500 万泰铢"]
        : ["Select a range", "Under ฿1 million", "฿1–5 million", "Over ฿5 million"];
    document.querySelectorAll(".gx-contact-fields select[name=budget] option").forEach((option, index) => {
      if (budgetOptions[index]) option.textContent = budgetOptions[index];
    });
  }

  // The four public service pages have their own layouts. Keep the smaller
  // labels, helper text and calls to action in the same dictionary as their
  // headings, so changing language cannot leave an English fragment behind.
  const serviceCopy = {
    en: {
      solutionKickers: ["GSB SUNPOWER / SOLUTIONS", "THREE WAYS FORWARD", "ONE CONVERSATION IS ENOUGH"],
      solutionRows: ["Explore EV charging", "Explore Solar PPA", "Explore EPC"],
      evKickers: ["GSB EV CHARGING", "DESIGNED FOR REAL PLACES", "ACTIVE LOCATIONS", "READY WHEN YOU ARE"], evStatus: "READY TO CHARGE", evLocations: "Explore selected GSB EV charging locations across hospitality, retail and destination sites",
      solar: ["Solar PPA | Clean energy designed around your business", "A practical Solar PPA model that turns unused space into predictable clean-energy value—managed from design to long-term performance", "Solar PPA installations", "Selected installations", "Explore a selection of systems delivered for industrial, commercial and community sites across Thailand", "START WITH YOUR SITE", "See what your roof<br>can <mark>power</mark>", "Talk to our Solar PPA team"],
      epc: ["Engineering & EPC | Energy projects delivered with confidence", "From survey and design through procurement, construction, commissioning and handover—one accountable delivery team", "Delivery capabilities", "Our EPC approach connects disciplined engineering, transparent delivery and practical site coordination", "Design with site reality in mind", "Quality equipment, selected with purpose", "Delivery managed safely and precisely", "Ready for dependable operation", "Project delivery", "Survey & design", "Procurement", "Construction", "Commissioning"],
    },
    th: {
      solutionKickers: ["GSB SUNPOWER / โซลูชัน", "3 แนวทางเพื่อก้าวต่อไป", "เริ่มต้นด้วยบทสนทนาเดียว"],
      solutionRows: ["ดูโซลูชันสถานีชาร์จ EV", "ดูโซลูชัน Solar PPA", "ดูบริการ EPC"],
      evKickers: ["GSB EV CHARGING", "ออกแบบสำหรับพื้นที่ใช้งานจริง", "จุดให้บริการ", "พร้อมเสมอเมื่อคุณต้องการ"], evStatus: "พร้อมชาร์จ", evLocations: "ดูตัวอย่างจุดชาร์จ GSB EV ในโรงแรม ร้านค้า และสถานที่ท่องเที่ยว",
      solar: ["โซลาร์เซลล์ พีพีเอ | พลังงานสะอาดที่ออกแบบตามธุรกิจของคุณ", "Solar PPA ที่เปลี่ยนพื้นที่ว่างให้เป็นพลังงานสะอาดที่คาดการณ์ได้ พร้อมดูแลตั้งแต่ออกแบบจนถึงการเดินระบบระยะยาว", "โครงการติดตั้ง Solar PPA", "ผลงานติดตั้ง", "ดูตัวอย่างระบบที่ส่งมอบให้แก่พื้นที่อุตสาหกรรม พาณิชยกรรม และชุมชนทั่วประเทศไทย", "เริ่มจากพื้นที่ของคุณ", "ดูว่าหลังคาของคุณ<br>สร้าง <mark>พลังงานได้อย่างไร</mark>", "พูดคุยกับทีม Solar PPA"],
      epc: ["วิศวกรรมและ EPC | โครงการพลังงานที่ส่งมอบอย่างมั่นใจ", "ตั้งแต่สำรวจ ออกแบบ จัดหา ก่อสร้าง ทดสอบระบบ และส่งมอบ โดยทีมเดียวที่ดูแลรับผิดชอบตลอดโครงการ", "ขีดความสามารถด้านการส่งมอบ", "แนวทาง EPC ของเราผสานงานวิศวกรรมที่เป็นระบบ การส่งมอบที่โปร่งใส และการบริหารหน้างานอย่างรอบคอบ", "ออกแบบจากเงื่อนไขจริงของหน้างาน", "คัดเลือกอุปกรณ์ที่เหมาะสมและได้มาตรฐาน", "บริหารการส่งมอบอย่างปลอดภัยและแม่นยำ", "พร้อมสำหรับการเดินระบบที่เชื่อถือได้", "การส่งมอบโครงการ", "สำรวจและออกแบบ", "จัดหาอุปกรณ์", "ก่อสร้าง", "ทดสอบและส่งมอบ"],
    },
    zh: {
      solutionKickers: ["GSB SUNPOWER / 解决方案", "三种前进方式", "一次沟通即可开始"],
      solutionRows: ["探索电动汽车充电", "探索太阳能购电协议", "探索 EPC 服务"],
      evKickers: ["GSB EV 充电", "为真实场地而设计", "运营中的地点", "随时为您准备"], evStatus: "准备充电", evLocations: "探索酒店、零售和目的地场所的精选 GSB EV 充电地点",
      solar: ["太阳能购电协议 | 为企业量身打造的清洁能源", "通过 Solar PPA 将闲置空间转化为可预测的清洁能源价值，从设计到长期运行全程管理", "Solar PPA 安装项目", "精选安装案例", "查看为泰国各地工业、商业和社区场地交付的系统案例", "从您的场地开始", "看看您的屋顶<br>能够提供怎样的 <mark>电力</mark>", "联系 Solar PPA 团队"],
      epc: ["工程与 EPC | 自信交付能源项目", "从现场勘测、设计、采购、施工、调试到移交，全程由同一责任团队管理", "交付能力", "我们的 EPC 方法融合严谨工程、透明交付和务实的现场协调", "以现场实际为基础进行设计", "为目标而精选优质设备", "安全精准地管理交付", "为可靠运行做好准备", "项目交付", "勘测与设计", "采购", "施工", "调试与移交"],
    },
  }[language];

  if (document.body.classList.contains("solutions-app-page")) {
    setMany(".solutions-app-kicker", serviceCopy.solutionKickers);
    document.querySelectorAll(".solutions-app-row b").forEach((item, index) => {
      if (serviceCopy.solutionRows[index]) item.childNodes[0].nodeValue = `${serviceCopy.solutionRows[index]} `;
    });
  }
  if (document.body.classList.contains("ev-app-page")) {
    setMany(".ev-app-layout .ev-app-kicker", serviceCopy.evKickers);
    const status = document.querySelector(".ev-app-status");
    if (status?.childNodes[1]) status.childNodes[1].nodeValue = ` ${serviceCopy.evStatus}`;
    text(".ev-app-location-heading > p", serviceCopy.evLocations);
  }
  if (document.body.classList.contains("solar-app-page")) {
    const solar = serviceCopy.solar;
    text(".solar-thai-title", solar[0]); text(".solar-hero-copy", solar[1]); text(".solar-hero-stat span", solar[2]);
    text(".solar-project-heading .kicker", solar[3]); text(".solar-project-heading > p", solar[4]); text(".solar-contact .kicker", solar[5]); markup(".solar-contact h2", solar[6]); text(".solar-contact a", solar[7]);
  }
  if (document.body.classList.contains("epc-app-page")) {
    const epc = serviceCopy.epc;
    text(".epc-thai-title", epc[0]); text(".epc-hero-content > p:not(.kicker):not(.epc-thai-title)", epc[1]); text(".epc-heading .kicker", epc[2]); text(".epc-heading > p", epc[3]);
    setMany(".epc-gallery h3", epc.slice(4, 8)); text(".epc-process .kicker", epc[8]); setMany(".epc-steps h3", epc.slice(9));
  }

  // The public service pages use a compact application layout.  Keep every
  // visible label in that layout in the selected language rather than leaving
  // the original English markup underneath the shared language switcher.
  const pages = {
    en: {
      about: ["Energy partnerships<br><em>built to move forward</em>", "One team<br>for every <em>energy move</em>", "Practical today<br><em>ready for what is next</em>", "Tell us where you want to <em>go next</em>"],
      solutions: ["Choose the next <em>move for your site</em>", "Built around your <em>real-world priorities</em>", "Not sure where to <em>begin</em>", ["Make every arrival easier", "Use clean power<br>keep your capital", "Turn plans into<br>working assets"]],
      ev: ["Charge with <em>confidence</em>", "Every charge starts with a better <em>experience</em>", "Power where <em>people go</em>", "Let’s make your site <em>ready to charge</em>"],
      solar: ["Solar power<br><em>without the upfront burden</em>", "Solar PPA <em>in action</em>"],
      epc: ["Engineering certainty<br><em>delivered end to end</em>", "Built to perform<br><em>from day one</em>", "Complex energy work<br>made <mark>clear</mark>"],
      actions: ["Talk to our team", "Explore our solutions", "Contact GSB SunPower", "Talk through your site", "Contact our team", "Plan your charging site", "See charging locations", "Start a conversation", "Explore our installations", "Explore our capability", "Talk to our EPC team"],
    },
    th: {
      about: ["พันธมิตรด้านพลังงาน<br><em>เพื่อก้าวไปข้างหน้า</em>", "ทีมเดียว<br>ดูแลทุก <em>ก้าวด้านพลังงาน</em>", "ใช้งานได้จริงวันนี้<br><em>พร้อมสำหรับวันข้างหน้า</em>", "บอกเราว่าคุณอยาก <em>ไปต่ออย่างไร</em>"],
      solutions: ["เลือกก้าวถัดไป<br><em>สำหรับพื้นที่ของคุณ</em>", "ออกแบบตาม<br><em>สิ่งที่สำคัญกับคุณ</em>", "ยังไม่แน่ใจว่าจะ <em>เริ่มอย่างไร</em>", ["ทุกการมาถึง<br>สะดวกขึ้น", "ใช้พลังงานสะอาด<br>รักษาเงินทุน", "เปลี่ยนแผน<br>เป็นระบบที่ใช้งานได้"]],
      ev: ["ชาร์จได้อย่าง<br><em>มั่นใจ</em>", "ทุกการชาร์จเริ่มจาก<br><em>ประสบการณ์ที่ดีกว่า</em>", "พลังงานสำหรับ<br><em>ทุกจุดหมาย</em>", "ทำให้พื้นที่ของคุณ<br><em>พร้อมสำหรับการชาร์จ</em>"],
      solar: ["พลังงานแสงอาทิตย์<br><em>โดยไม่ต้องลงทุนก้อนแรก</em>", "Solar PPA<br><em>ที่ใช้งานจริง</em>"],
      epc: ["วิศวกรรมที่มั่นใจ<br><em>ส่งมอบครบทุกขั้นตอน</em>", "พร้อมใช้งานตั้งแต่<br><em>วันแรก</em>", "งานพลังงานที่ซับซ้อน<br>ทำให้ <mark>ชัดเจน</mark>"],
      actions: ["พูดคุยกับทีมของเรา", "ดูโซลูชันของเรา", "ติดต่อ GSB SunPower", "ปรึกษาพื้นที่ของคุณ", "ติดต่อทีมของเรา", "วางแผนจุดชาร์จ", "ดูจุดให้บริการ", "เริ่มบทสนทนา", "ดูผลงานติดตั้ง", "ดูขีดความสามารถ", "พูดคุยกับทีม EPC"],
    },
    zh: {
      about: ["能源伙伴关系<br><em>携手向前</em>", "一支团队<br>负责每一步<em>能源行动</em>", "立足当下<br><em>为下一步准备</em>", "告诉我们您想 <em>如何前进</em>"],
      solutions: ["为您的场地选择<br><em>下一步行动</em>", "围绕您的<br><em>实际优先事项</em>", "不确定该 <em>从哪里开始</em>", ["让每次到达<br>更轻松", "使用清洁电力<br>保留资金", "把计划变成<br>可用资产"]],
      ev: ["安心 <em>充电</em>", "每一次充电<br>都从更好的<em>体验开始</em>", "为人们前往的<br><em>每个地点供电</em>", "让您的场地<br><em>准备好充电</em>"],
      solar: ["太阳能<br><em>无需前期负担</em>", "Solar PPA<br><em>实际案例</em>"],
      epc: ["可靠工程<br><em>端到端交付</em>", "从第一天起<br><em>稳定运行</em>", "复杂能源工作<br>变得<mark>清晰</mark>"],
      actions: ["与我们的团队交流", "探索我们的解决方案", "联系 GSB SunPower", "讨论您的场地", "联系团队", "规划您的充电场地", "查看充电地点", "开始沟通", "查看安装案例", "探索我们的能力", "联系 EPC 团队"],
    },
  }[language];

  if (document.body.classList.contains("about-app-page")) {
    setMany(".about-app-layout h1, .about-app-layout h2", pages.about, true);
    setMany(".about-app-layout .about-app-button", pages.actions.slice(0, 3));
    const copy = language === "th"
      ? ["เรารวมการลงทุนด้านพลังงานสะอาด งานวิศวกรรม และการดำเนินงานไว้ด้วยกัน เพื่อให้ธุรกิจก้าวต่อไปได้อย่างมั่นใจ", "GSB SunPower คือพันธมิตรด้านพลังงานของไทย ที่เปลี่ยนเรื่องโซลาร์เซลล์ งานวิศวกรรม และ EV ให้เป็นระบบที่ใช้งานได้จริงในระยะยาว", "เราเริ่มจากพื้นที่ ผู้ใช้งาน และเป้าหมายทางธุรกิจ แล้วดูแลต่อเนื่องจนระบบพร้อมเดินได้จริง"]
      : language === "zh"
        ? ["我们整合清洁能源投资、工程与运营，让企业充满信心地迈出下一步", "GSB SunPower 扎根泰国，将太阳能、工程与电动汽车充电转化为可长期运行的实用系统", "我们从场地、使用者和商业目标出发，并持续负责交付与运营"]
        : ["We bring clean-energy investment, engineering and operations together so businesses can take the next step with confidence", "GSB SunPower is a Thailand-rooted energy partner that turns solar, engineering and EV charging decisions into practical long-term systems", "We start with the site, the people who use it and the business case behind it, then stay accountable through delivery and operation"];
    setMany(".about-app-hero-copy > p:not(.about-app-kicker), .about-app-intro-grid > div > p:not(.about-app-kicker), .about-app-trust-grid > div > p:not(.about-app-kicker)", copy);
  }
  if (document.body.classList.contains("solutions-app-page")) {
    setMany(".solutions-app-layout h1, .solutions-app-layout h2", pages.solutions.slice(0, 3), true);
    setMany(".solutions-app-row h3", pages.solutions[3], true);
    setMany(".solutions-app-button", [pages.actions[3], pages.actions[4]]);
    const copy = language === "th"
      ? ["เริ่มจากบริการที่สร้างคุณค่าให้พื้นที่ของคุณได้มากที่สุดในตอนนี้", "แต่ละบริการทำงานแยกกันได้ หรือเชื่อมต่อกันเมื่อพื้นที่ของคุณเติบโต", "โครงสร้างพื้นฐานการชาร์จที่ออกแบบตามผู้ใช้และการดำเนินงาน", "ใช้พลังงานแสงอาทิตย์โดยไม่ต้องลงทุนซื้อสินทรัพย์", "ทีมเดียวดูแลงานวิศวกรรม ก่อสร้าง และส่งมอบ"]
      : language === "zh"
        ? ["从最能为您场地创造价值的服务开始", "每项服务可独立运作，也可随场地发展协同配合", "围绕驾驶者、访客与日常运营设计的充电基础设施", "无需前期资产投资即可使用太阳能", "一支团队负责工程、施工与交付"]
        : ["Start with the service that creates the most value for your site now", "Each service can stand on its own or work together as your site grows", "Charging infrastructure designed around your drivers, visitors and daily operation", "Access solar energy without an upfront asset investment", "One accountable team from engineering through construction and handover"];
    setMany(".solutions-app-hero > div > p:not(.solutions-app-kicker), .solutions-app-intro > p:not(.solutions-app-kicker), .solutions-app-row p", copy);
  }
  if (document.body.classList.contains("ev-app-page")) {
    setMany(".ev-app-layout h1, .ev-app-layout h2", pages.ev, true);
    setMany(".ev-app-actions a, .ev-app-cta a", [pages.actions[5], pages.actions[6], pages.actions[7]]);
    const evKicker = document.querySelector(".ev-app-hero .ev-app-kicker");
    if (evKicker) evKicker.textContent = language === "th" ? "สถานีชาร์จ GSB EV" : language === "zh" ? "GSB EV 充电" : "GSB EV CHARGING";
    const copy = language === "th"
      ? ["ตั้งแต่การสำรวจพื้นที่จนถึงทุกครั้งที่ชาร์จ GSB EV ช่วยให้ระบบชาร์จเป็นส่วนหนึ่งของสถานที่ที่ใช้งานได้จริง", "ไม่ว่าจะเป็นโรงแรม สำนักงาน ร้านค้า หรือจุดหมายปลายทาง เราวางแผนระบบชาร์จให้สอดคล้องกับผู้ใช้งาน", "วางแผนตามความต้องการ", "เลือกอุปกรณ์ กำลังไฟ และตำแหน่งที่เหมาะสม", "เริ่มใช้งานได้อย่างชัดเจน", "ทีมเดียวดูแลตั้งแต่ออกแบบจนเปิดใช้งาน", "พร้อมเดินหน้าต่อ", "ดูแลผู้ขับขี่และการดำเนินงานที่ต้องพึ่งพาระบบ"]
      : language === "zh"
        ? ["从首次现场勘察到每次充电，GSB EV 让充电成为场地实用的一部分", "无论酒店、办公室、零售空间或目的地，我们都根据使用者规划充电组合", "按需求规划", "为场地选择合适的设备、功率与位置", "清晰上线", "一支团队负责设计、交付与启用", "持续前行", "为依赖系统的驾驶者与运营提供务实支持"]
        : ["From the first site survey to every completed session, GSB EV makes charging a useful part of your place", "Whether your site is a hotel, office, retail location or destination, we plan the charging mix around the people who use it", "Plan around demand", "Choose the right equipment, power and placement for your site", "Launch with clarity", "One accountable team from design and delivery to activation", "Keep moving", "Practical support for the drivers and operations that rely on it"];
    setMany(".ev-app-hero-grid > div:first-child > p:not(.ev-app-kicker), .ev-app-intro > p:not(.ev-app-kicker), .ev-app-benefit-grid h3, .ev-app-benefit-grid p", copy);
  }
  if (document.body.classList.contains("solar-app-page")) {
    markup(".solar-hero h1", pages.solar[0]); markup(".solar-project-heading h2", pages.solar[1]);
    text(".solar-hero-cta", pages.actions[8]);
  }
  if (document.body.classList.contains("epc-app-page")) {
    markup(".epc-hero h1", pages.epc[0]); markup(".epc-heading h2", pages.epc[1]); markup(".epc-intro h2", pages.epc[2]);
    text(".epc-cta", pages.actions[9]); text(".epc-process > .shell > a", pages.actions[10]);
  }

  // Project galleries render their cards independently. Notify them after the
  // page dictionary is complete so cards and modal labels use this language.
  window.dispatchEvent(new CustomEvent("gsb-language-change", { detail: { language } }));

  // Marketing copy is intentionally clean and punctuation-free across the
  // three languages. Review quotes are the sole content explicitly excluded.
  const removeTerminalStops = (root) => {
    if (root.closest?.(".gx-reviews")) return;
    root.childNodes.forEach((node) => {
      if (node.nodeType === Node.TEXT_NODE) node.nodeValue = node.nodeValue.replace(/[.。]+(?=\s*$)/g, "");
      else if (node.nodeType === Node.ELEMENT_NODE && !["SCRIPT", "STYLE", "SVG"].includes(node.tagName)) removeTerminalStops(node);
    });
  };
  removeTerminalStops(document.body);

  // Reviews are intentionally excluded: their Thai copy and reviewer names remain unchanged in every language.
};

localiseExperience();
// gx-home.js enhances the Home page after this shared file has loaded. Apply
// once more after all page scripts complete so its default English copy cannot
// overwrite the selected language.
window.addEventListener("load", localiseExperience, { once: true });
