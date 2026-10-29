{{-- Previous Home layout retained below as a rollback reference.
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GSB SunPower Group — clean energy solutions for enduring business.">
    <title>GSB SunPower Group | Clean energy, made practical</title>
    <link rel="preload" as="image" href="hero-solar-thailand.png">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="{{ asset('operations-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ev-first-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ui-polish.css') }}">
</head>

<body class="landing-home operations-catalog nrg-home-page">
    <a class="skip-link" href="#main">Skip to main content</a>

    <div class="utility-bar">
        <div class="shell utility-content">
            <span>Clean energy, made practical.</span>
            <a href="contact.html">Talk to an expert <span aria-hidden="true">↗</span></a>
        </div>
    </div>

    <header class="site-header" id="top">
        <div class="shell nav-wrap">
            <a class="brand" href="index.html" aria-label="GSB SunPower Group home">
                <img class="brand-logo" src="image/GSBSUNPOWER.png" alt="GSB SunPower Group">
            </a>

            <button class="menu-button" type="button" aria-expanded="false" aria-controls="site-menu">
                <span class="sr-only">Open menu</span>
                <i></i><i></i>
            </button>

            <nav class="site-menu" id="site-menu" aria-label="Main navigation">
                <a href="index.html">Home</a>
                <a href="about.html">About</a>
                <a href="solutions.html">Product</a>
                <a href="contact.html">Support</a>
                <a class="nrg-get-app" href="#app-experience">Get GSB</a>
                <a class="nav-button" href="contact.html">Language <span aria-hidden="true">⌄</span></a>
            </nav>
        </div>
    </header>

    <main id="main">
        <section class="nrg-home">
            <section class="nrg-hero">
                <div class="shell nrg-hero-inner">
                    <p class="nrg-overline">GSB EV APPLICATION</p>
                    <h1>GSB <em>EV Charger</em></h1>
                    <p>Your smart charging companion — find a station, scan to charge, manage your wallet and keep every
                        journey moving.</p>
                    <div class="nrg-store-row"><a href="https://play.google.com/store/apps/details?id=com.sunpower.evcharge&amp;pcampaignid=web_share" class="nrg-store nrg-google" target="_blank" rel="noopener noreferrer"><img
                                class="store-icon" src="{{ asset('image/store-google-play.svg') }}"
                                alt=""><span><small>GET IT ON</small><strong>Google Play</strong></span></a><a
                            href="https://apps.apple.com/th/app/ev-charge-by-sun-power/id6711341578?l=th" class="nrg-store nrg-apple" target="_blank" rel="noopener noreferrer"><img class="store-icon"
                                src="{{ asset('image/store-apple.svg') }}" alt=""><span><small>Download on
                                    the</small><strong>App Store</strong></span></a></div>
                </div>
                <div class="nrg-hero-app-device" aria-label="GSB EV application preview">
                    <img src="{{ asset('image/ev-app/3.png') }}" alt="GSB EV account screen">
                </div>
            </section>
            <section class="nrg-real-sites nrg-video-stories" id="video-stories" aria-labelledby="video-stories-heading">
                <div class="shell">
                    <div class="nrg-real-sites-heading">
                        <div>
                            <p class="nrg-label">GSB EV VIDEO STORIES</p>
                            <h2 id="video-stories-heading">Charging stories<br><em>in motion.</em></h2>
                        </div>
                        <p>Take a closer look at real GSB EV locations. Choose a story to watch it in full with sound.</p>
                    </div>
                    <div class="nrg-real-sites-grid">
                        <button type="button" class="nrg-video-card" data-video-src="{{ asset('video/gsb-ev-story.mp4') }}"
                            data-video-title="GSB EV Story" data-video-location="Thailand · 00:26">
                            <img src="{{ asset('image/gsb-ev-content/video-poster.jpg') }}" alt="GSB EV promotional video">
                            <span class="nrg-video-play" aria-hidden="true">▶</span><span class="nrg-video-index">01</span>
                            <span class="nrg-video-copy"><b>GSB EV Story</b><small>Watch our charging network in action</small></span>
                        </button>
                        <button type="button" class="nrg-video-card" data-video-src="{{ asset('video/aviyana-hua-hin.mp4') }}"
                            data-video-title="Aviyana Hua Hin" data-video-location="Hua Hin · 00:36">
                            <img src="{{ asset('image/gsb-ev-content/cover-aviyana.png') }}" alt="Aviyana Hua Hin video">
                            <span class="nrg-video-play" aria-hidden="true">▶</span><span class="nrg-video-index">02</span>
                            <span class="nrg-video-copy"><b>Aviyana Hua Hin</b><small>Watch project story</small></span>
                        </button>
                        <button type="button" class="nrg-video-card" data-video-src="{{ asset('video/le-srivaree.mp4') }}"
                            data-video-title="Le Srivaree Airport Hotel" data-video-location="Suvarnabhumi · 00:48">
                            <img src="{{ asset('image/gsb-ev-content/cover-le-srivaree.png') }}" alt="Le Srivaree hotel video">
                            <span class="nrg-video-play" aria-hidden="true">▶</span><span class="nrg-video-index">03</span>
                            <span class="nrg-video-copy"><b>Le Srivaree</b><small>Watch project story</small></span>
                        </button>
                        <button type="button" class="nrg-video-card" data-video-src="{{ asset('video/runnava-pranburi.mp4') }}"
                            data-video-title="RunNava Pranburi Cafe" data-video-location="Pranburi · 00:37">
                            <img src="{{ asset('image/gsb-ev-content/cover-runnava.png') }}" alt="RunNava Pranburi Cafe video">
                            <span class="nrg-video-play" aria-hidden="true">▶</span><span class="nrg-video-index">04</span>
                            <span class="nrg-video-copy"><b>RunNava Cafe</b><small>Watch project story</small></span>
                        </button>
                        <button type="button" class="nrg-video-card" data-video-src="{{ asset('video/yana-villas.mp4') }}"
                            data-video-title="The Yana Villas Hua Hin" data-video-location="Hua Hin · 00:41">
                            <img src="{{ asset('image/gsb-ev-content/cover-yana.png') }}" alt="The Yana Villas Hua Hin video">
                            <span class="nrg-video-play" aria-hidden="true">▶</span><span class="nrg-video-index">05</span>
                            <span class="nrg-video-copy"><b>The Yana Villas</b><small>Watch project story</small></span>
                        </button>
                    </div>
                </div>
            </section>
            <dialog class="nrg-video-modal" aria-label="GSB EV video story">
                <div class="nrg-video-modal-panel">
                    <button type="button" class="nrg-video-modal-close" aria-label="Close video">×</button>
                    <div class="nrg-video-modal-meta"><p>GSB EV VIDEO STORY</p><h2></h2><span></span></div>
                    <video controls playsinline preload="metadata"></video>
                </div>
            </dialog>
            <section class="nrg-real-sites" id="campaign-gallery" aria-labelledby="real-sites-heading">
                <div class="shell">
                    <div class="nrg-real-sites-heading">
                        <div>
                            <p class="nrg-label">GSB EV CAMPAIGN</p>
                            <h2 id="real-sites-heading">Energy to<br><em>keep moving.</em></h2>
                        </div>
                        <p>Explore the moments, places and people behind the GSB EV charging experience. Select any
                            image to see it in full.</p>
                    </div>
                    <div class="nrg-real-sites-grid">
                        @foreach (['brand-01.png', 'promo-02.jpg', 'promo-03.jpg', 'promo-04.jpg', 'promo-05.jpg', 'promo-06.jpg'] as $index => $image)
                            <button type="button" class="nrg-promo-card" data-promo-image="{{ asset('image/gsb-ev-content/' . $image) }}"
                                aria-label="View GSB EV campaign image {{ $index + 1 }}">
                                <img src="{{ asset('image/gsb-ev-content/' . $image) }}" alt="GSB EV campaign image {{ $index + 1 }}">
                                <span>0{{ $index + 1 }}</span><b aria-hidden="true">↗</b>
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>
            <dialog class="nrg-promo-modal" aria-label="GSB EV campaign image">
                <div class="nrg-promo-modal-panel">
                    <button type="button" class="nrg-promo-modal-close" aria-label="Close image">×</button>
                    <img src="" alt="GSB EV campaign image">
                </div>
            </dialog>
            <section class="nrg-intro" id="app-experience">
                <div class="shell nrg-intro-grid">
                    <div class="nrg-intro-copy">
                        <p class="nrg-label">EFFORTLESS CHARGING</p>
                        <h2>Effortless charging<br>at your <em>fingertips.</em></h2>
                        <p>Manage your charger directly from your smartphone. Schedule charging sessions, monitor
                            real-time performance and receive notifications — all through one beautifully simple
                            interface.</p>
                    </div>
                    <div class="nrg-benefit-grid">
                        <article><i>⌁</i><strong>Start/stop charging remotely.</strong><span>Start or stop charging
                                anytime, anywhere from the GSB EV app.</span></article>
                        <article><i>♧</i><strong>Receive alerts summaries.</strong><span>Get real-time alerts and
                                charging summaries.</span></article>
                        <article><i>◉</i><strong>View progress in real time.</strong><span>Track your charging progress
                                live, right from the app.</span></article>
                        <article><i>ϟ</i><strong>Smart charging control.</strong><span>Monitor, schedule and optimize
                                charging remotely.</span></article>
                    </div>
                    <div class="nrg-phone" aria-label="GSB EV application previews"><img class="nrg-phone-main"
                            src="{{ asset('image/ev-app/2.png') }}" alt="GSB EV app screen"
                            data-default-src="{{ asset('image/ev-app/2.png') }}">
                        <div class="nrg-phone-dots" role="group" aria-label="Choose an app screen">
                            @for ($screen = 1; $screen <= 10; $screen++)
                                <button type="button" class="nrg-phone-choice {{ $screen === 3 ? 'is-active' : '' }}"
                                    data-app-screen="{{ asset('image/ev-app/' . $screen . '.png') }}"
                                    aria-label="Show GSB EV screen {{ $screen }}">{{ $screen }}</button>
                            @endfor
                        </div>
                        <p class="nrg-phone-hint">Choose a screen to preview</p>
                    </div>
                </div>
            </section>
            <section class="nrg-split nrg-track">
                <div class="shell nrg-split-grid">
                    <div class="nrg-copy">
                        <p class="nrg-label">MAKE THE NEXT MOVE</p>
                        <h2>Track energy.<br><em>Charge smarter.</em></h2>
                        <p>From the first site survey to every charging session, our team helps your operation stay
                            ready.</p><a href="ev-charging.html" class="nrg-arrow">How EV charging works <b>→</b></a>
                    </div>
                    <figure><img src="{{ asset('legacy/images/charge_photo/charge_site_17_runnava.jpg') }}"
                            alt="EV charging in use"></figure>
                </div>
            </section>
            <section class="nrg-split nrg-reverse">
                <div class="shell nrg-split-grid">
                    <figure><img src="{{ asset('legacy/images/charge_photo/charge_site_13_munruk.jpeg') }}"
                            alt="EV charger at a business site"></figure>
                    <div class="nrg-copy">
                        <p class="nrg-label">BUILT FOR REAL PLACES</p>
                        <h2>Charge when it is<br><em>best for you.</em></h2>
                        <p>Hotels, offices, retail and destinations all have different rhythms. We design around yours.
                        </p><a href="contact.html" class="nrg-arrow">Discuss your location <b>→</b></a>
                    </div>
                </div>
            </section>
            <section class="nrg-network">
                <div class="shell nrg-network-grid">
                    <div>
                        <p class="nrg-label">ONE NETWORK</p>
                        <h2>One platform.<br>Complete <em>control.</em></h2>
                        <p>Bring your charging plan, equipment and support together with one accountable local partner.
                        </p><a href="solutions.html" class="nrg-light-link">See all solutions <b>→</b></a>
                    </div>
                    <figure><img src="{{ asset('legacy/images/charge_photo/charge_site_01_the_rest.jpeg') }}"
                            alt="GSB EV charging network"></figure>
                </div>
            </section>
            <section class="nrg-security">
                <div class="shell nrg-security-grid">
                    <figure><img src="{{ asset('legacy/images/charge_photo/charge_site_18_demi_iii.jpeg') }}"
                            alt="Reliable EV charging"></figure>
                    <div>
                        <p class="nrg-label">RELIABLE BY DESIGN</p>
                        <h2>Secure. Reliable.<br><em>Always there for you.</em></h2>
                        <p>We build charging infrastructure to be useful long after the ribbon-cutting - with planning,
                            delivery and ongoing support.</p><a href="contact.html" class="nrg-light-link">Start a
                            conversation <b>→</b></a>
                    </div>
                </div>
            </section>
            <section class="nrg-stat">
                <div class="shell nrg-stat-grid">
                    <div>
                        <p class="nrg-label">READY TO CONNECT</p>
                        <h2>Start charging<br>smarter <em>today.</em></h2><strong>19+ <span>EV charging
                                locations</span></strong>
                    </div>
                    <div>
                        <p>Tell us about your location, users and plans. We will help you choose a practical next step.
                        </p><a href="contact.html" class="nrg-cta">Talk to an EV specialist <b>→</b></a>
                    </div>
                </div>
            </section>
        </section>
        <section class="catalog-new-home modern-home">
            <section class="modern-hero">
                <div class="modern-hero-orbit orbit-one"></div>
                <div class="modern-hero-orbit orbit-two"></div>
                <div class="shell modern-hero-grid">
                    <div class="modern-hero-copy">
                        <p class="modern-eyebrow">EV MOBILITY · BUSINESS CHARGING</p>
                        <h1>Charge smarter.<br><em>Move further.</em></h1>
                        <p class="modern-hero-lead">EV charging infrastructure designed around your drivers, your
                            destination and your next stage of growth.</p>
                        <div class="modern-actions"><a href="contact.html" class="modern-primary">Build your charging
                                plan <b>→</b></a><a href="ev-charging.html" class="modern-secondary">Explore EV
                                charging <span>↗</span></a></div>
                    </div>
                    <div class="modern-hero-visual"><img
                            src="{{ asset('legacy/images/charge_photo/charge_site_04_khun_ton.jpeg') }}"
                            alt="EV charging installation in Thailand">
                        <div class="modern-live-card"><span class="modern-pulse"></span>
                            <div><small>OPERATING IN THAILAND</small><strong>Charging that works<br>where people
                                    go.</strong></div>
                        </div>
                    </div>
                </div>
                <div class="shell modern-proof">
                    <div><strong>19+</strong><span>EV charging<br>locations</span></div>
                    <div><strong>AC + DC</strong><span>charging designed<br>for real demand</span></div>
                    <div><strong>24/7</strong><span>planning and support<br>from one team</span></div>
                    <p>From site assessment to charging operations, we make EV infrastructure practical.</p>
                </div>
            </section>
            <section class="modern-services">
                <div class="shell">
                    <div class="modern-section-heading">
                        <p>ONE PLATFORM</p>
                        <h2>Make every charge<br><em>feel effortless.</em></h2><span>Start with EV charging. Add solar
                            and engineering capability when your site needs more.</span>
                    </div>
                    <div class="modern-service-grid">
                        <a href="ev-charging.html" class="modern-service service-ev">
                            <div><small>01 · EV CHARGING</small>
                                <h3>Power every<br>arrival.</h3>
                                <p>Charging infrastructure designed around your users, site and future demand.</p>
                            </div><b>Explore EV charging <i>→</i></b>
                        </a>
                        <a href="solar-ppa.html" class="modern-service service-solar">
                            <div><small>02 · SOLAR PPA</small>
                                <h3>Make energy<br>go further.</h3>
                                <p>Use clean power from day one while we invest, build and operate the asset.</p>
                            </div><b>Explore Solar PPA <i>→</i></b>
                        </a>
                        <a href="epc.html" class="modern-service service-epc">
                            <div><small>03 · ENGINEERING &amp; EPC</small>
                                <h3>Built for<br>what's next.</h3>
                                <p>One accountable engineering partner from site survey to handover.</p>
                            </div><b>Explore EPC <i>→</i></b>
                        </a>
                    </div>
                </div>
            </section>
            <section class="modern-impact">
                <div class="shell modern-impact-grid">
                    <div class="modern-impact-image"><img
                            src="{{ asset('legacy/images/charge_photo/charge_site_17_runnava.jpg') }}"
                            alt="EV charging installation"><span>REAL SITES<br>REAL JOURNEYS</span></div>
                    <div>
                        <p class="modern-eyebrow">EV CHARGING, MADE PRACTICAL</p>
                        <h2>One charging experience.<br>Built around your place.</h2>
                        <p>Whether it is a hotel, office, retail location or destination, we turn charging into a useful
                            part of the customer and driver experience.</p>
                        <ol>
                            <li><b>01</b><span>Assess your location</span></li>
                            <li><b>02</b><span>Plan the charging mix</span></li>
                            <li><b>03</b><span>Launch with confidence</span></li>
                        </ol><a href="contact.html">Talk through your site <b>→</b></a>
                    </div>
                </div>
            </section>
        </section>
        <section class="hero" hidden>
            <div class="hero-image" role="img" aria-label="Solar panels on a commercial site in Thailand"></div>
            <div class="hero-shade"></div>
            <div class="shell hero-content home-hero">
                <p class="kicker on-dark"><span></span> GSB SunPower Group</p>
                <h1>Clean Energy<br>for Business<br><em>Built to Go Further.</em></h1>
                <div class="hero-bottom">
                    <p>Integrated solar and EV infrastructure designed around the way your business operates, invests,
                        and grows.
                    </p>
                    <a class="round-link" href="solutions.html"
                        aria-label="Explore our solutions"><span>Explore<br>Solutions</span><b
                            aria-hidden="true">→</b></a>
                </div>
            </div>
            <div class="hero-stat" aria-label="Integrated clean-energy services">
                <strong>01</strong>
                <span>One partner<br>for clean energy</span>
            </div>
        </section>

        <section class="ticker" aria-label="Our services" hidden>
            <div class="ticker-track">
                <span>Solar PPA</span><b>✦</b><span>Engineering & EPC</span><b>✦</b><span>EV
                    Charging</span><b>✦</b><span>Energy
                    Management</span><b>✦</b>
                <span aria-hidden="true">Solar PPA</span><b aria-hidden="true">✦</b><span
                    aria-hidden="true">Engineering
                    &
                    EPC</span><b aria-hidden="true">✦</b><span aria-hidden="true">EV Charging</span><b
                    aria-hidden="true">✦</b><span aria-hidden="true">Energy Management</span><b
                    aria-hidden="true">✦</b>
            </div>
        </section>

        <section class="statement" hidden>
            <div class="shell statement-grid">
                <div>
                    <p class="kicker"><span></span> Built for long-term value</p>
                    <p class="display-copy">Turn energy use into a business <mark>advantage.</mark></p>
                </div>
                <div class="statement-side">
                    <p>Businesses need more than installed equipment. We turn site data, investment priorities, and
                        operations
                        into energy systems that create enduring value.</p>
                    <a class="text-arrow" href="about.html">Discover GSB SunPower <span>→</span></a>
                </div>
            </div>
        </section>

        <section class="solutions" id="solutions" hidden>
            <div class="shell">
                <div class="section-top">
                    <p class="kicker"><span></span> What we do</p>
                    <h2>One integrated partner<br>for every energy transition.</h2>
                </div>
                <div class="solution-grid">
                    <article class="solution-card primary-card">
                        <div class="solution-card-media"
                            style="background-image:url('legacy/images/case_photo/ppa_case_01_samut_sakhon.png')"
                            aria-hidden="true"></div>
                        <div class="solution-card-copy">
                            <p class="card-index">01 / SOLAR</p>
                            <h3>Solar PPA</h3>
                            <p>A tailored path to solar energy that aligns investment, performance, and long-term
                                business goals.</p>
                            <a href="solutions.html" aria-label="Explore Solar PPA">↗</a>
                        </div>
                    </article>
                    <article class="solution-card dark-card">
                        <div class="solution-card-media"
                            style="background-image:url('legacy/images/case_photo/ppa_case_05_rayong_factory.jpg')"
                            aria-hidden="true"></div>
                        <div class="solution-card-copy">
                            <p class="card-index">02 / ENGINEERING</p>
                            <h3>Engineering<br>& EPC</h3>
                            <p>From site survey and engineering to procurement, construction, and handover.</p>
                            <a href="solutions.html" aria-label="Explore Engineering and EPC">↗</a>
                        </div>
                    </article>
                    <article class="solution-card lime-card">
                        <div class="solution-card-media"
                            style="background-image:url('legacy/images/charge_photo/charge_site_04_khun_ton.jpeg')"
                            aria-hidden="true"></div>
                        <div class="solution-card-copy">
                            <p class="card-index">03 / MOBILITY</p>
                            <h3>EV Charging</h3>
                            <p>EV charging infrastructure designed for real places, real users, and future demand.</p>
                            <a href="solutions.html" aria-label="Explore EV Charging">↗</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="approach" id="approach" hidden>
            <div class="shell approach-layout">
                <div class="approach-photo" role="img" aria-label="Solar energy site"></div>
                <div class="approach-content">
                    <p class="kicker on-dark"><span></span> How we work</p>
                    <h2>Every project<br>starts with<br><em>understanding.</em></h2>
                    <ol>
                        <li><b>01</b><span><strong>See the full picture</strong>Understand the business case, the site,
                                and the
                                data.</span></li>
                        <li><b>02</b><span><strong>Design for reality</strong>Build a solution around day-to-day
                                operations.</span>
                        </li>
                        <li><b>03</b><span><strong>Protect long-term value</strong>Monitor performance and improve
                                continuously.</span></li>
                    </ol>
                </div>
            </div>
        </section>

        <section class="projects" id="projects" hidden>
            <div class="shell">
                <div class="section-top project-heading">
                    <p class="kicker"><span></span> Selected work</p>
                    <h2>Results you can<br>see and measure.</h2>
                    <a class="text-arrow" href="projects.html">View our projects <span>→</span></a>
                </div>
                <article class="featured-project">
                    <div class="project-photo" role="img" aria-label="Clean-energy project"></div>
                    <div class="project-caption">
                        <p>FEATURED PROJECT / 2026</p>
                        <h3>Energy starts<br>with your site.</h3>
                        <span>Replace this area with real project stories, imagery, and results in WordPress.</span>
                    </div>
                </article>
            </div>
        </section>

        <section class="insights" id="insights" hidden>
            <div class="shell insight-layout">
                <div class="insight-copy">
                    <p class="kicker"><span></span> Perspective</p>
                    <h2>Clearer thinking for better energy decisions.</h2>
                </div>
                <a class="insight-link" href="insights.html"><span>Read our insights</span><b>↗</b></a>
            </div>
        </section>

        <section class="contact" id="contact" hidden>
            <div class="shell contact-layout">
                <p class="kicker"><span></span> Start a conversation</p>
                <h2>Ready to turn your energy plan<br>into a business advantage?</h2>
                <a class="contact-button" href="contact.html">Talk to our team <span>↗</span></a>
            </div>
        </section>
    </main>

    <footer>
        <div class="shell footer-top">
            <a class="brand footer-brand" href="#top"><img class="brand-logo" src="image/GSBSUNPOWER.png"
                    alt="GSB SunPower Group"></a>
            <p>Clean energy, made practical.</p>
            <a href="#top" class="back-top">Back to top ↑</a>
        </div>
        <div class="shell footer-bottom"><span>© 2026 GSB SunPower Group</span><span>BHIRAJ TOWER at BITEC, 22nd Floor, Room 2209, 4345 Sukhumvit Road, Bang Na Tai Subdistrict, Bang Na District, Bangkok 10260, Thailand</span></div>
    </footer>

    <script src="script.js"></script>
    <script src="{{ asset('ev-app-preview.js') }}"></script>
</body>

</html>
--}}
{{-- Previous Home redesign iteration retained below as a rollback reference.
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GSB SunPower delivers practical solar and EV charging solutions for modern businesses.">
    <title>GSB SunPower Group | Energy for what’s next</title>
    <link rel="preload" as="image" href="{{ asset('hero-solar-thailand.png') }}">
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
    <link rel="stylesheet" href="{{ asset('operations-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ev-first-ui.css') }}">
</head>
<body class="yes-home">
    <a class="skip-link" href="#main">Skip to main content</a>
    <header class="site-header yes-header" id="top"><div class="shell nav-wrap">
        <a class="brand" href="{{ route('home') }}" aria-label="GSB SunPower Group home"><img class="brand-logo" src="{{ asset('image/GSBSUNPOWER.png') }}" alt="GSB SunPower Group"></a>
        <button class="menu-button" type="button" aria-expanded="false" aria-controls="site-menu"><span class="sr-only">Open menu</span><i></i><i></i></button>
        <nav class="site-menu" id="site-menu" aria-label="Main navigation"><a class="is-current" href="{{ route('home') }}">Home</a><a href="{{ route('about') }}">About us</a><a href="{{ route('blog') }}">Blog</a><a href="{{ route('solutions') }}">Solutions</a><a href="#projects">Projects</a><a href="{{ route('contact') }}">Contact</a><a class="nav-button" href="{{ route('contact') }}">Talk to us <span>↗</span></a></nav>
    </div></header>
    <main id="main">
        <section class="yes-hero" aria-labelledby="hero-title">
            <div class="yes-hero-media" aria-hidden="true"><!-- Replace the source with the finished hero film when ready; the poster is the fallback. --><video autoplay muted loop playsinline poster="{{ asset('hero-solar-thailand.png') }}"><source src="{{ asset('video/home-solar-ev.mp4') }}" type="video/mp4"></video></div>
            <div class="shell yes-hero-copy"><p class="yes-eyebrow">GSB SUNPOWER GROUP</p><h1 id="hero-title">Energy that works<br>for <em>what’s next.</em></h1><p>Solar, EV charging and practical energy systems designed around the way your business moves.</p><a class="yes-round-link" href="#solutions">Explore our solutions <span>↓</span></a></div><div class="yes-hero-note"><span>01</span> Solar power meets electric mobility</div>
        </section>
        <section class="yes-intro shell" aria-labelledby="intro-title"><p class="yes-eyebrow">POWERING PROGRESS, PRACTICALLY</p><div class="yes-intro-grid"><h2 id="intro-title">A clearer route<br>to <em>clean energy.</em></h2><div><p>We turn ambitious energy goals into systems that work in the real world — from rooftop solar and PPA programmes to dependable EV charging infrastructure.</p><a href="{{ route('about') }}" class="yes-text-link">Meet GSB SunPower <span>↗</span></a></div></div></section>
        <section class="yes-trust" aria-label="GSB SunPower strengths"><div class="shell yes-trust-grid"><div><strong>End-to-end</strong><span>From strategy to long-term care</span></div><div><strong>Built locally</strong><span>Designed for Thailand’s conditions</span></div><div><strong>Future-ready</strong><span>Scalable energy for growing teams</span></div></div></section>
        <section class="yes-solutions shell" id="solutions" aria-labelledby="solutions-title"><div class="yes-section-head"><p class="yes-eyebrow">WHAT WE BUILD</p><h2 id="solutions-title">Two systems.<br><em>One smarter energy future.</em></h2></div>
            <article class="yes-solution-card"><div class="yes-solution-image"><img src="{{ asset('image/SolarPPA/ppa_case_02_ayutthaya.png') }}" alt="Solar panels supporting a business site"></div><div class="yes-solution-copy"><span>01 / SOLAR PPA</span><h3>Solar power that pays its way.</h3><p>Reduce energy costs without carrying the burden of upfront ownership. We plan, install and operate the system with you.</p><a href="{{ route('solar-ppa') }}" class="yes-text-link">Discover Solar PPA <span>↗</span></a></div></article>
            <article class="yes-solution-card yes-solution-card-reverse"><div class="yes-solution-image"><img src="{{ asset('image/EV/charge_site_04_khun_ton.jpeg') }}" alt="GSB EV charging location"></div><div class="yes-solution-copy"><span>02 / EV CHARGING</span><h3>Charging that keeps every journey moving.</h3><p>Connected EV charging solutions for destinations, fleets and communities — simple for drivers and clear for operators.</p><a href="{{ route('ev-charging') }}" class="yes-text-link">Explore EV charging <span>↗</span></a></div></article>
        </section>
        <section class="yes-process"><div class="shell"><div class="yes-process-title"><p class="yes-eyebrow">HOW WE WORK</p><h2>Less complexity.<br><em>More momentum.</em></h2></div><div class="yes-process-grid"><article><span>01</span><h3>Listen</h3><p>We start with your site, your goals and your energy use.</p></article><article><span>02</span><h3>Design</h3><p>We create the right technical and commercial model.</p></article><article><span>03</span><h3>Deliver</h3><p>We build, monitor and improve for the long term.</p></article></div></div></section>
        <section class="yes-projects shell" id="projects" aria-labelledby="projects-title"><div class="yes-section-head yes-projects-head"><p class="yes-eyebrow">SELECTED WORK</p><h2 id="projects-title">Made for real places,<br><em>not just presentations.</em></h2><a href="{{ route('about') }}" class="yes-text-link">See our story <span>↗</span></a></div><div class="yes-project-grid"><a href="{{ route('solar-ppa') }}" class="yes-project yes-project-large"><img src="{{ asset('image/SolarPPA/ppa_case_01_samut_sakhon.png') }}" alt="Solar PPA project"><span><small>SOLAR PPA</small><b>Energy made dependable</b></span></a><a href="{{ route('ev-charging') }}" class="yes-project"><img src="{{ asset('image/EV/charge_site_03_aviyana.jpeg') }}" alt="EV charging project"><span><small>EV CHARGING</small><b>Power for everyday arrivals</b></span></a><a href="{{ route('epc') }}" class="yes-project"><img src="{{ asset('image/EPC/pexels-budget-bizar-92378004-12224996.jpg') }}" alt="Solar engineering project"><span><small>ENGINEERING &amp; EPC</small><b>Infrastructure with purpose</b></span></a></div></section>
        <section class="yes-faq shell" aria-labelledby="faq-title"><div><p class="yes-eyebrow">LET’S MAKE IT CLEAR</p><h2 id="faq-title">Questions are part<br>of a <em>good start.</em></h2></div><div class="yes-accordions"><details open><summary>Is Solar PPA right for our business?<span>+</span></summary><p>It can be a strong fit for organisations that want solar savings without the capital expenditure. We will assess your site and energy profile first.</p></details><details><summary>Can GSB install charging at our location?<span>+</span></summary><p>Yes. We design EV charging for commercial destinations, workplaces, fleets and properties across Thailand.</p></details><details><summary>Where do we begin?<span>+</span></summary><p>Start with a conversation. Our team can help you identify the practical first step for your site.</p></details></div></section>
        <section class="yes-contact"><div class="shell yes-contact-inner"><p class="yes-eyebrow">START A CONVERSATION</p><h2>Let’s make your<br>next move <em>electric.</em></h2><a class="yes-contact-button" href="{{ route('contact') }}">Talk to our team <span>↗</span></a></div></section>
    </main>
    <footer class="site-footer" id="site-footer"></footer>
    <script src="{{ asset('script.js') }}"></script>
</body>
</html>
--}}

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Solar PPA and EV charging by GSB SunPower Group.">
    <title>GSB SunPower Group | Solar & EV solutions</title>
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
    <link rel="stylesheet" href="{{ asset('operations-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ev-first-ui.css') }}">
</head>
<body class="yes-home yes-reference-home">
    <a class="skip-link" href="#main">Skip to main content</a>
    <header class="site-header yes-header" id="top"><div class="shell nav-wrap">
        <a class="brand" href="{{ route('home') }}" aria-label="GSB SunPower Group home"><img class="brand-logo" src="{{ asset('image/GSBSUNPOWER.png') }}" alt="GSB SunPower Group"></a>
        <button class="menu-button" type="button" aria-expanded="false" aria-controls="site-menu"><span class="sr-only">Open menu</span><i></i><i></i></button>
        <nav class="site-menu" id="site-menu" aria-label="Main navigation"><a class="is-current" href="{{ route('home') }}">Home</a><a href="{{ route('about') }}">About us</a><a href="#solutions">Solutions</a><a href="#projects">Projects</a><a href="{{ route('contact') }}">Contact</a><a class="nav-button" href="{{ route('contact') }}">Talk to us <span>↗</span></a></nav>
    </div></header>
    <main id="main">
        <section class="yes-ref-hero" aria-labelledby="hero-title"><div class="shell">
            <p class="yes-ref-kicker">GSB SUNPOWER GROUP</p>
            <h1 id="hero-title">Solar and EV energy<br>that moves <em>your business.</em></h1>
            <p class="yes-ref-lead">Practical clean-energy systems, planned around your site, your operations and the future you want to build.</p>
            <p class="yes-ref-question">What can we help you power?</p>
            <div class="yes-ref-choices">
                <a href="{{ route('solar-ppa') }}"><b>Solar PPA</b><span>Lower your energy cost with no upfront investment.</span><i>↗</i></a>
                <a href="{{ route('epc') }}"><b>Solar Engineering & EPC</b><span>Build a system designed for performance and scale.</span><i>↗</i></a>
                <a href="{{ route('ev-charging') }}"><b>EV Charging</b><span>Create a better charging experience for every arrival.</span><i>↗</i></a>
            </div>
        </div></section>

        <section class="yes-ref-intro shell" aria-labelledby="intro-title"><div><p class="yes-ref-kicker">THE GSB DIFFERENCE</p><h2 id="intro-title">Energy infrastructure<br>with a <em>clearer purpose.</em></h2></div><div><p class="yes-ref-statement">We pair proven technology with people who understand real sites. From the first energy assessment to day-to-day operation, GSB makes the transition to clean energy feel straightforward.</p><a class="yes-ref-arrow-link" href="{{ route('about') }}">Learn about GSB <span>↗</span></a></div></section>

        <section class="yes-ref-proof" aria-label="GSB strengths"><div class="shell yes-ref-proof-grid"><article><span>01</span><b>Solar PPA</b><p>Investment-light solar for commercial sites.</p></article><article><span>02</span><b>EV Charging</b><p>Connected charging for the way people move.</p></article><article><span>03</span><b>Engineering</b><p>Systems designed around real operations.</p></article><article><span>04</span><b>Aftercare</b><p>Support that stays with you long after launch.</p></article></div></section>

        <section class="yes-ref-services shell" id="solutions" aria-labelledby="services-title"><div class="yes-ref-services-title"><p class="yes-ref-kicker">GSB DELIVERS</p><h2 id="services-title">One energy partner.<br><em>More ways to progress.</em></h2></div><div class="yes-ref-service-grid">
            <a href="{{ route('solar-ppa') }}" class="yes-ref-service"><img src="{{ asset('image/SolarPPA/ppa_case_02_ayutthaya.png') }}" alt="Solar PPA project"><div><small>01 / SOLAR PPA</small><h3>Power your place with the sun.</h3><p>Long-term solar savings without upfront capital.</p></div><i>↗</i></a>
            <a href="{{ route('epc') }}" class="yes-ref-service"><img src="{{ asset('image/EPC/pexels-budget-bizar-92378004-12224996.jpg') }}" alt="Solar engineering project"><div><small>02 / ENGINEERING & EPC</small><h3>Build energy systems that last.</h3><p>From feasibility to commissioning, handled in one team.</p></div><i>↗</i></a>
            <a href="{{ route('ev-charging') }}" class="yes-ref-service"><img src="{{ asset('image/EV/charge_site_04_khun_ton.jpeg') }}" alt="GSB EV charging location"><div><small>03 / EV CHARGING</small><h3>Make every arrival count.</h3><p>A dependable charging experience for customers and teams.</p></div><i>↗</i></a>
            <a href="{{ route('contact') }}" class="yes-ref-service yes-ref-service-text"><div><small>04 / ENERGY ADVISORY</small><h3>Not sure where to begin?</h3><p>Start with a practical conversation about your site.</p></div><i>↗</i></a>
        </div><a class="yes-ref-arrow-link yes-ref-all-link" href="{{ route('solutions') }}">Explore every solution <span>↗</span></a></section>

        <section class="yes-ref-projects" id="projects" aria-labelledby="projects-title"><div class="shell"><p class="yes-ref-kicker">SELECTED PROJECTS</p><h2 id="projects-title">Built for the places<br>people rely <em>on every day.</em></h2><div class="yes-ref-project-grid"><a href="{{ route('solar-ppa') }}"><img src="{{ asset('image/SolarPPA/ppa_case_01_samut_sakhon.png') }}" alt="Solar PPA site"><span><small>SOLAR PPA</small><b>Business energy, made more certain.</b></span></a><a href="{{ route('ev-charging') }}"><img src="{{ asset('image/EV/charge_site_03_aviyana.jpeg') }}" alt="EV charging site"><span><small>EV CHARGING</small><b>Charging that welcomes the next journey.</b></span></a><a href="{{ route('epc') }}"><img src="{{ asset('image/EPC/pexels-budget-bizar-92378004-12224996.jpg') }}" alt="Solar engineering"><span><small>ENGINEERING</small><b>Purpose-built for performance.</b></span></a></div></div></section>

        <section class="yes-ref-quote shell"><p>“Have an energy project in mind?<br><em>Let’s make the first move together.”</em></p><a href="{{ route('contact') }}">Speak with our team <span>↗</span></a></section>
        <section class="yes-ref-faq shell" aria-labelledby="faq-title"><h2 id="faq-title">Questions, answered<br><em>clearly.</em></h2><div><details open><summary>Is Solar PPA suitable for our site?<span>+</span></summary><p>We assess your electricity use, roof and operational needs before recommending the right approach.</p></details><details><summary>Can we add EV charging to our business?<span>+</span></summary><p>Yes. GSB designs and operates EV charging for workplaces, destinations, fleets and properties.</p></details><details><summary>How do we start?<span>+</span></summary><p>Send us a brief overview of your site. We will help define the most useful next step.</p></details></div></section>
        <section class="yes-ref-contact"><div class="shell"><p class="yes-ref-kicker">CONTACT GSB</p><h2>Ready to make<br>energy <em>work harder?</em></h2><a href="{{ route('contact') }}">Start a conversation <span>↗</span></a></div></section>
    </main>
    <footer class="site-footer" id="site-footer"></footer>
    <script src="{{ asset('script.js') }}"></script>
</body>
</html>
