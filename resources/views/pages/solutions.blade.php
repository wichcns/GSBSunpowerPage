<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Integrated clean-energy solutions by GSB SunPower Group.">
    <title>Solutions | GSB SunPower Group</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="{{ asset('operations-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ev-first-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ui-polish.css') }}">
    <style>
        /* Kept with this page so the Solar Hero stays correct even if a browser has an older CSS file cached. */
        .solutions-app-hero { position: relative !important; isolation: isolate; overflow: hidden; background: #06343d !important; }
        .solutions-app-hero-video { display: block; position: absolute !important; z-index: 0; inset: 0; width: 100% !important; height: 100% !important; object-fit: cover; }
        .solutions-app-hero::before { content: ""; position: absolute; z-index: 1; inset: 0; background: linear-gradient(90deg, rgba(1, 32, 41, .89), rgba(3, 52, 57, .6), rgba(0, 29, 35, .18)); }
        .solutions-app-hero .shell { position: relative; z-index: 2; }
    </style>
</head>

<body class="solutions-hub solutions-catalog-page solutions-app-page"><a class="skip-link" href="#main">Skip to main content</a>
    <div class="utility-bar">
        <div class="shell utility-content"><span>Clean energy, made practical.</span><a href="contact.html">Talk to an
                expert <span aria-hidden="true">↗</span></a></div>
    </div>
    <header class="site-header" id="top">
        <div class="shell nav-wrap"><a class="brand" href="index.html" aria-label="GSB SunPower Group home"><img
                    class="brand-logo" src="image/GSBSUNPOWER.png" alt="GSB SunPower Group"></a><button
                class="menu-button" type="button" aria-expanded="false" aria-controls="site-menu"><span
                    class="sr-only">Open menu</span><i></i><i></i></button>
            <nav class="site-menu" id="site-menu" aria-label="Main navigation"><a href="index.html">Home</a><a href="about.html">About Us</a><a class="active" href="solutions.html">Solutions</a><a href="contact.html">Contact Us</a><a class="nrg-get-app" href="index.html#app-experience">Get GSB</a><a class="nav-button" href="contact.html">Language <span aria-hidden="true">⌄</span></a></nav>
        </div>
    </header>
    <main id="main">
        <section class="solutions-app-layout">
            <section class="solutions-app-hero">
                <video class="solutions-app-hero-video" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
                    <source src="{{ asset('video/Solar-v1-20261005.mp4') }}" type="video/mp4">
                </video>
                <div class="shell"><p class="solutions-app-kicker">GSB SUNPOWER / SOLUTIONS</p><h1>Choose the next<br><em>move for your site.</em></h1><p>From charging and solar to full energy delivery, start with the service that creates the most value now.</p><a href="contact.html" class="solutions-app-button">Talk through your site <b>→</b></a></div>
            </section>
            <section class="solutions-app-list"><div class="shell"><div class="solutions-app-intro"><p class="solutions-app-kicker">THREE WAYS FORWARD</p><h2>Built around your<br><em>real-world priorities.</em></h2><p>Each service can stand on its own or work together as your site grows.</p></div><div class="solutions-app-rows">
                @foreach ($solutions as $solution)
                    <a href="{{ $solution->destination_url ?: route('contact') }}" class="solutions-app-row {{ $loop->first ? 'is-ev' : '' }}" data-solution-card data-title-en="{{ $solution->title_en }}" data-title-th="{{ $solution->title_th ?: $solution->title_en }}" data-title-zh="{{ $solution->title_zh ?: $solution->title_en }}" data-summary-en="{{ $solution->summary_en }}" data-summary-th="{{ $solution->summary_th ?: $solution->summary_en }}" data-summary-zh="{{ $solution->summary_zh ?: $solution->summary_en }}"><figure><img src="{{ asset($solution->image_path ?: 'image/GSBSUNPOWER.png') }}" alt="{{ $solution->title_en }}"></figure><div><small>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ strtoupper($solution->title_en) }}</small><h3 data-solution-title>{{ $solution->title_en }}</h3><p data-solution-summary>{{ $solution->summary_en }}</p><b>Explore {{ $solution->title_en }} <span>→</span></b></div></a>
                @endforeach
            </div></div></section>
            <section class="solutions-app-cta"><div class="shell"><p class="solutions-app-kicker">ONE CONVERSATION IS ENOUGH</p><h2>Not sure where<br>to <em>begin?</em></h2><p>We will help you identify the next practical energy move for your site.</p><a href="contact.html" class="solutions-app-button">Contact our team <b>→</b></a></div></section>
        </section>
        <section class="solution-new-layout modern-solutions">
            <div class="shell">
                <div class="solution-title-row"><div><p class="solution-kicker">EV MOBILITY STARTS HERE</p><h1>Build the charge<br><em>your site deserves.</em></h1></div><p>Start with EV charging for your people and visitors. Connect solar and engineering services whenever your site is ready for more.</p></div>
                <div class="solution-dossiers">
                    <a href="ev-charging.html" class="solution-dossier solution-dossier-featured"><figure style="background-image:url('{{ asset('legacy/images/charge_photo/charge_site_04_khun_ton.jpeg') }}')"></figure><span>01</span><div><small>EV CHARGING</small><h2>Make your site<br>ready for<br>electric mobility.</h2></div><p>Charging infrastructure shaped around users and demand.</p><b>Explore EV charging <i>→</i></b></a>
                    <a href="solar-ppa.html" class="solution-dossier"><figure style="background-image:url('{{ asset('image/SolarPPA/ppa_case_08_rayong_market.jpg') }}')"></figure><span>02</span><div><small>SOLAR PPA</small><h2>Use solar power<br>without owning<br>the asset.</h2></div><p>Investment, delivery and operations in one practical agreement.</p><b>Explore Solar PPA <i>→</i></b></a>
                    <a href="epc.html" class="solution-dossier"><figure style="background-image:url('{{ asset('image/SolarPPA/ppa_case_13_prachuap_wood.png') }}')"></figure><span>03</span><div><small>ENGINEERING &amp; EPC</small><h2>Build an energy<br>asset with one<br>responsible team.</h2></div><p>Engineering, procurement, construction and handover.</p><b>Explore EPC <i>→</i></b></a>
                </div>
                <div class="solution-compare"><p>NOT SURE WHICH MODEL FITS?</p><a href="contact.html">Talk through your site with our team <b>→</b></a></div>
            </div>
        </section>
        <section class="solutions-hub-hero">
            <div class="shell">
                <p class="kicker on-dark"><span></span> Integrated energy solutions</p>
                <p class="solutions-hub-thai" lang="th">โซลูชันพลังงานสะอาดที่ครบวงจรสำหรับธุรกิจ</p>
                <h1>One energy partner.<br><em>Every step forward.</em></h1>
                <p>Explore the right clean-energy route for your business—from investment and delivery to the
                    infrastructure your people rely on every day.</p>
            </div>
        </section>
        <section class="solutions-hub-list">
            <div class="shell">
                <div class="solutions-hub-intro">
                    <p class="kicker"><span></span> Choose a solution</p>
                    <!-- <p>Each area has its own dedicated page, so you can explore its approach, capabilities, and relevant
                        work without one long page getting in the way.</p> -->
                </div>
                <div class="solutions-hub-cards"><a class="solution-hub-card solution-hub-solar"
                        href="solar-ppa.html"><span class="solution-hub-number">01</span>
                        <div>
                            <p>SOLAR PPA</p>
                            <h2>Clean energy<br>with no upfront<br><em>burden.</em></h2><span
                                class="solution-hub-description" lang="th">เราลงทุน ออกแบบ ติดตั้ง และดูแลระบบโซลาร์เซลล์
                                เพื่อให้ธุรกิจเปลี่ยนมาใช้พลังงานสะอาดได้อย่างมั่นใจ</span>
                        </div><b aria-hidden="true">↗</b>
                    </a><a class="solution-hub-card solution-hub-epc" href="epc.html"><span
                            class="solution-hub-number">02</span>
                        <div>
                            <p>ENGINEERING &amp; EPC</p>
                            <h2>Turn complex<br>energy plans into<br><em>working assets.</em></h2><span
                                class="solution-hub-description" lang="th">ออกแบบ จัดหา ก่อสร้าง
                                และส่งมอบระบบอย่างเป็นหนึ่งเดียว</span>
                        </div><b aria-hidden="true">↗</b>
                    </a><a class="solution-hub-card solution-hub-ev" href="ev-charging.html"><span
                            class="solution-hub-number">03</span>
                        <div>
                            <p>EV CHARGING</p>
                            <h2>Charging built<br>for where your<br><em>business goes.</em></h2><span
                                class="solution-hub-description" lang="th">โครงสร้างพื้นฐาน EV
                                ที่ออกแบบให้เหมาะกับสถานที่และการเติบโต</span>
                        </div><b aria-hidden="true">↗</b>
                    </a></div>
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
    <script src="script.js"></script>
</body>

</html>
