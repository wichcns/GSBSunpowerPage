<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Solar PPA project portfolio by GSB SunPower Group.">
    <title>Solar PPA | GSB SunPower Group</title>
    <link rel="stylesheet" href="{{ asset('styles.css') }}?v=20261005-investment-ui-3">
    <link rel="stylesheet" href="{{ asset('operations-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ev-first-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ui-polish.css') }}">
    <link rel="stylesheet" href="{{ asset('gx-modal.css') }}">
</head>

<body class="solutions-page solar-app-page"><a class="skip-link" href="#main">Skip to main content</a>
    <div class="utility-bar">
        <div class="shell utility-content"><span>Clean energy, made practical.</span><a href="contact.html">Talk to an
                expert <span aria-hidden="true">↗</span></a></div>
    </div>
    <header class="site-header" id="top">
        <div class="shell nav-wrap"><a class="brand" href="index.html" aria-label="GSB SunPower Group home"><img
                    class="brand-logo" src="image/GSBSUNPOWER.png" alt="GSB SunPower Group"></a><button
                class="menu-button" type="button" aria-expanded="false" aria-controls="site-menu"><span
                    class="sr-only">Open
                    menu</span><i></i><i></i></button>
            <nav class="site-menu" id="site-menu" aria-label="Main navigation"><a href="index.html">Home</a><a href="about.html">About Us</a><a class="active" href="solutions.html">Solutions</a><a href="contact.html">Contact Us</a><a class="nrg-get-app" href="index.html#app-experience">Get GSB</a><a class="nav-button" href="contact.html">Language <span aria-hidden="true">⌄</span></a></nav>
        </div>
    </header>
    <main id="main">
        <section class="solar-hero">
            <div class="solar-hero-image" aria-hidden="true"></div>
            <div class="shell solar-hero-content">
                <a class="solar-back" href="solutions.html"><span aria-hidden="true">←</span> Back to Solutions</a>
                <p class="kicker on-dark"><span></span> SOLUTIONS / 01</p>
                <p class="solar-thai-title" lang="th">Solar PPA | พลังงานสะอาดที่ออกแบบให้เหมาะกับธุรกิจของคุณ</p>
                <h1>Solar power,<br><em>without the upfront burden.</em></h1>
                <p class="solar-hero-copy">A practical Solar PPA model that turns unused space into predictable
                    clean-energy
                    value—managed from design to long-term performance.</p><a class="solar-hero-cta"
                    href="#solar-projects">Explore our installations <span aria-hidden="true">↓</span></a>
            </div>
            <div class="solar-hero-stat"><b>{{ $projects->count() }}</b><span>Solar PPA<br>installations</span></div>
        </section>
        <section class="solar-intro">
            <div class="shell solar-intro-layout">
                <div>
                    <p class="kicker"><span></span> Solar PPA</p>
                    <h2>Clean energy<br>on your <mark>terms.</mark></h2>
                </div>
                <div>
                    <p class="solar-lead">We invest in, build, and operate the solar system. Your business receives
                        reliable
                        electricity with a clearer path to lower energy costs.</p>
                    <p lang="th">เราเป็นผู้ลงทุน ออกแบบ ติดตั้ง และดูแลระบบผลิตไฟฟ้าพลังงานแสงอาทิตย์
                        เพื่อให้ธุรกิจของคุณใช้พลังงานสะอาดได้อย่างมั่นใจ โดยไม่ต้องแบกรับภาระการลงทุนเริ่มต้น</p>
                </div>
            </div>
        </section>
        <section class="solar-projects" id="solar-projects">
            <div class="shell">
                <div class="solar-project-heading">
                    <div>
                        <p class="kicker on-dark"><span></span> Selected installations</p>
                        <h2>Solar PPA<br><em>in action.</em></h2>
                    </div>
                    <p>Explore a selection of systems delivered for industrial, commercial, and community sites across
                        Thailand.<br><span lang="th">ตัวอย่างผลงานติดตั้งระบบ Solar PPA สำหรับภาคอุตสาหกรรม
                            พาณิชยกรรม
                            และชุมชนทั่วประเทศไทย</span></p>
                </div>
                <div class="solar-gallery">
                    <article class="solar-case solar-case-featured"><img
                            src="image/SolarPPA/ppa_case_01_samut_sakhon.png"
                            alt="Solar PPA installation in Samut Sakhon">
                        <div><small>01 / SAMUT SAKHON</small>
                            <h3>Industrial rooftop energy system</h3>
                            <p lang="th">ระบบผลิตไฟฟ้าพลังงานแสงอาทิตย์สำหรับโรงงานอุตสาหกรรม</p><span>Solar PPA
                                <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_02_ayutthaya.png"
                            alt="Solar PPA installation in Ayutthaya">
                        <div><small>02 / AYUTTHAYA</small>
                            <h3>Factory rooftop solar</h3>
                            <p lang="th">ระบบโซลาร์เซลล์รูฟท็อปสำหรับโรงงาน</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_03_samut_sakhon_nonwoven.png"
                            alt="Nonwoven factory solar PPA installation">
                        <div><small>03 / SAMUT SAKHON</small>
                            <h3>Nonwoven production facility</h3>
                            <p lang="th">โครงการพลังงานสะอาดสำหรับโรงงานผลิตผ้าไม่ถักทอ</p><span>Solar PPA
                                <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_04_hua_hin.jpg"
                            alt="Solar PPA site in Hua Hin">
                        <div><small>04 / HUA HIN</small>
                            <h3>Coastal commercial site</h3>
                            <p lang="th">ระบบโซลาร์เซลล์สำหรับพื้นที่พาณิชยกรรมหัวหิน</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_05_rayong_factory.jpg"
                            alt="Factory solar installation in Rayong">
                        <div><small>05 / RAYONG</small>
                            <h3>Manufacturing rooftop system</h3>
                            <p lang="th">ระบบผลิตไฟฟ้าสำหรับโรงงานในระยอง</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_06_chachoengsao.png"
                            alt="Solar equipment installation in Chachoengsao">
                        <div><small>06 / CHACHOENGSAO</small>
                            <h3>High-performance solar equipment</h3>
                            <p lang="th">งานติดตั้งอุปกรณ์โซลาร์เซลล์ประสิทธิภาพสูง</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_07_rama_ii.jpg"
                            alt="Solar PPA installation on Rama II">
                        <div><small>07 / RAMA II</small>
                            <h3>Urban industrial energy system</h3>
                            <p lang="th">ระบบโซลาร์เซลล์บนหลังคาสำหรับพื้นที่อุตสาหกรรมในเมือง</p><span>Solar PPA
                                <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_08_rayong_market.jpg"
                            alt="Solar PPA installation at Rayong market">
                        <div><small>08 / RAYONG</small>
                            <h3>Market canopy solar</h3>
                            <p lang="th">ระบบพลังงานแสงอาทิตย์สำหรับพื้นที่ตลาด</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_09_rayong_packaging.png"
                            alt="Solar PPA installation at packaging facility">
                        <div><small>09 / RAYONG</small>
                            <h3>Packaging facility solar</h3>
                            <p lang="th">โครงการโซลาร์เซลล์สำหรับโรงงานบรรจุภัณฑ์</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_10_rayong_lighting.png"
                            alt="Solar PPA electrical installation in Rayong">
                        <div><small>10 / RAYONG</small>
                            <h3>Lighting manufacturing facility</h3>
                            <p lang="th">ระบบโซลาร์เซลล์สำหรับโรงงานผลิตอุปกรณ์แสงสว่าง</p><span>Solar PPA
                                <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_11_rayong_chemical.png"
                            alt="Chemical facility solar PPA installation">
                        <div><small>11 / RAYONG</small>
                            <h3>Chemical industry solar</h3>
                            <p lang="th">โครงการพลังงานสะอาดสำหรับอุตสาหกรรมเคมี</p><span>Solar PPA
                                <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_12_phetchaburi.png"
                            alt="Solar PPA site in Phetchaburi">
                        <div><small>12 / PHETCHABURI</small>
                            <h3>Commercial rooftop solution</h3>
                            <p lang="th">ระบบโซลาร์เซลล์บนหลังคาสำหรับธุรกิจพาณิชย์</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_13_prachuap_wood.png"
                            alt="Wood industry solar PPA project">
                        <div><small>13 / PRACHUAP KHIRI KHAN</small>
                            <h3>Wood processing solar</h3>
                            <p lang="th">โครงการโซลาร์เซลล์สำหรับโรงงานแปรรูปไม้</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_14_prachuap_service.png"
                            alt="Solar PPA service site in Prachuap Khiri Khan">
                        <div><small>14 / PRACHUAP KHIRI KHAN</small>
                            <h3>Service facility energy system</h3>
                            <p lang="th">ระบบพลังงานสะอาดสำหรับศูนย์บริการ</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                    <article class="solar-case"><img src="image/SolarPPA/ppa_case_15_rama_iii.png"
                            alt="Solar PPA installation on Rama III">
                        <div><small>15 / RAMA III</small>
                            <h3>City-based solar infrastructure</h3>
                            <p lang="th">โครงสร้างพื้นฐานพลังงานสะอาดในเขตเมือง</p><span>Solar PPA <b>↗</b></span>
                        </div>
                    </article>
                </div>
            </div>
        </section>
        <section class="solar-contact">
            <div class="shell">
                <p class="kicker"><span></span> Start with your site</p>
                <h2>See what your roof<br>can <mark>power.</mark></h2><a href="contact.html">Talk to our Solar PPA team
                    <span>→</span></a>
            </div>
        </section>
    </main>
    <footer>
        <div class="shell footer-top"><a class="brand footer-brand" href="index.html"><img class="brand-logo"
                    src="image/GSBSUNPOWER.png" alt="GSB SunPower Group"></a>
            <p>Clean energy, made practical.</p><a class="back-top" href="#top">Back to top ↑</a>
        </div>
        <div class="shell footer-bottom"><span>© 2026 GSB SunPower Group</span><span>BHIRAJ TOWER at BITEC, 22nd Floor, Room 2209, 4345 Sukhumvit Road, Bang Na Tai Subdistrict, Bang Na District, Bangkok 10260, Thailand</span></div>
    </footer>
    <script>
        window.gsbProjects = @json($projects);
    </script>
    <script src="{{ asset('script.js') }}?v=20261005-investment"></script>
    <script src="{{ asset('dynamic-projects.js') }}?v=20261007-project-value"></script>
</body>

</html>
