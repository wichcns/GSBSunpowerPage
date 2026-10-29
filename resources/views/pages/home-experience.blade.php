<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GSB SunPower Group | Solar & EV solutions</title>
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
    <link rel="stylesheet" href="{{ asset('operations-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ev-first-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ui-polish.css') }}?v=20261007-client-logos">
    <link rel="stylesheet" href="{{ asset('gx-modal.css') }}">
    <style>
        .gx-hero h1,
        .gx-intro h2,
        .gx-commitment h2,
        .gx-next-consult h2,
        .gx-connect h2,
        .gx-reviews h2 {
            text-wrap: balance
        }

        .gx-intro-statement h2 {
            max-width: 15ch
        }

        .gsb-experience .gx-header-overlay .gx-logo {
            width: 245px;
            height: 92px
        }

        .gsb-experience .gx-header-overlay .gx-logo img {
            width: 245px
        }

        .gsb-experience .gx-nav-area {
            width: min(1100px, calc(100% - 270px))
        }

        .gsb-experience .gx-topline {
            transform: translateY(18px)
        }

        @media(max-width:760px) {
            .gsb-experience .gx-header-overlay .gx-logo {
                width: 165px;
                height: 58px
            }

            .gsb-experience .gx-header-overlay .gx-logo img {
                width: 165px
            }

            .gsb-experience .gx-topline {
                transform: none
            }
        }

        @media(max-width:760px) {
            .gx-intro-statement h2 {
                max-width: 100%
            }
        }
    </style>
</head>

<body class="gsb-experience">
    <header class="gx-header gx-header-overlay" id="top">
        <a class="gx-logo" href="{{ route('home') }}"><img src="{{ asset('image/GSBSUNPOWER-Edit.png') }}"
                alt="GSB SunPower Group"></a>
        <div class="gx-nav-area">
            <div class="gx-topline"><span>☎ 098-828-5767</span><a aria-label="Facebook"
                    href="https://www.facebook.com/gsbsunpowerofficial" target="_blank"
                    rel="noopener noreferrer">f</a><a aria-label="Instagram"
                    href="https://www.instagram.com/gsb_sunpower" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.7" r="1"/></svg></a><a
                    aria-label="TikTok" href="https://www.tiktok.com/@gsb.sunpower" target="_blank"
                    rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.2 3h2.7c.3 2.3 1.6 3.9 3.8 4.2v3.1a8.4 8.4 0 0 1-3.8-1.1v6.3a5.8 5.8 0 1 1-5-5.7c.3 0 .6 0 .9.1V13a2.7 2.7 0 1 0 1.4 2.4V3Z"/></svg></a>
                <details class="gx-language">
                    <summary><span class="gx-globe" aria-hidden="true"></span><b data-gx-language-label>EN</b><i
                            aria-hidden="true"></i></summary>
                    <div><button type="button" data-gx-language="EN">English</button><button type="button"
                            data-gx-language="TH">ไทย</button><button type="button" data-gx-language="ZH">中文</button>
                    </div>
                </details>
            </div>
            <div class="gx-navline">
                <nav><a href="{{ route('about') }}">About us <i></i></a><a href="{{ route('blog') }}">Blog <i></i></a><a href="{{ route('solutions') }}">Solutions <i></i></a></nav><a class="gx-contact" href="{{ route('contact') }}">Contact us</a>
            </div>
        </div>
    </header>
    <main>
        <section class="gx-hero gx-hero-photo">
            <div class="gx-hero-photo-layer">
                <video data-home-hero-video autoplay muted loop playsinline preload="auto"
                    poster="{{ asset('hero-solar-thailand.png') }}" aria-hidden="true">
                    <source src="{{ asset('video/home-ev-solar-loop-20261008.mp4') }}" type="video/mp4">
                </video>
            </div>
            <div class="gx-hero-inner">
                <p class="gx-label">{{ $homeHero['en']['label'] }}</p>
                <h1>{{ $homeHero['en']['heading'] }}<br><em>{{ $homeHero['en']['emphasis'] }}</em></h1>
                <p class="gx-lead">{{ $homeHero['en']['lead'] }}</p>
                <p class="gx-prompt">{{ $homeHero['en']['prompt'] }}</p>
                <div class="gx-hero-choice" role="tablist" aria-label="Choose a GSB service">
                    <button class="is-active" type="button" data-gx-service="solar" role="tab"
                        aria-selected="true">SOLAR INSTALLATION</button><button type="button" data-gx-service="ev"
                        role="tab" aria-selected="false">EV CHARGING STATION</button><button type="button"
                        data-gx-service="general" role="tab" aria-selected="false">GENERAL INQUIRY</button>
                </div>
                <div class="gx-hero-action">
                    <details class="gx-service-select">
                        <summary><span data-gx-link>Select what you are interested in</span><i aria-hidden="true"></i>
                        </summary>
                        <div data-gx-options><button type="button" data-gx-option>Factory installation</button><button
                                type="button" data-gx-option>Commercial building installation</button><button
                                type="button" data-gx-option>Investment and operating model consultation</button></div>
                    </details><button class="gx-consult" type="button" data-gx-consult>{{ $homeHero['en']['consult'] }}</button>
                </div>
            </div>
            <div class="gx-hero-proof">
                <article><b>Built for businesses</b>
                    <p>End-to-end energy solutions shaped around the way you work.</p>
                </article>
                <article><b>Performance you can see</b>
                    <p>Clear plans, proven technology and measurable value.</p>
                </article>
                <article><b>Ready for what’s next</b>
                    <p>Solar and EV infrastructure that can scale with you.</p>
                </article>
            </div>
        </section>

        @if ($leftClientLogos->isNotEmpty())
        <section class="gx-client-credits gx-client-credits-compact" aria-label="GSB SunPower client logos">
            <div class="gx-logo-rail">
                <div class="gx-logo-track">
                    @foreach ($leftClientLogos as $logo)
                        <img src="{{ asset($logo->image_path) }}" alt="{{ $logo->alt_text ?: ($logo->name ?: 'GSB SunPower client logo') }}">
                    @endforeach
                    @foreach ($leftClientLogos as $logo)
                        <img src="{{ asset($logo->image_path) }}" alt="" aria-hidden="true">
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if ($rightClientLogos->isNotEmpty())
        <section class="gx-solar-client-credits" aria-label="GSB SunPower solar-cell customer logos">
            <div class="gx-logo-rail gx-logo-rail--right">
                <div class="gx-logo-track">
                    @foreach ($rightClientLogos as $logo)
                        <img src="{{ asset($logo->image_path) }}" alt="{{ $logo->alt_text ?: ($logo->name ?: 'GSB SunPower client logo') }}">
                    @endforeach
                    @foreach ($rightClientLogos as $logo)
                        <img src="{{ asset($logo->image_path) }}" alt="" aria-hidden="true">
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <dialog class="gx-consult-dialog" data-gx-dialog aria-labelledby="consult-title">
            <form method="post" action="{{ route('contact-enquiries.store') }}" class="gx-consult-form"><button class="gx-dialog-close" type="button"
                    data-gx-close aria-label="Close form">×</button>
                @csrf
                <input type="hidden" name="source" value="consultation">
                <aside class="gx-form-intro-panel">
                    <p class="gx-label">GSB SUNPOWER GROUP</p><span class="gx-form-number">01 / CONSULTATION</span>
                    <h2 id="consult-title">Let’s make your<br>next move <em data-gx-form-category>Solar
                            installation.</em></h2>
                    <p>Share the essentials. We will return with a practical point of view for your site.</p>
                    <div class="gx-form-promise"><span>↗</span> A focused conversation, not a generic sales call.</div>
                </aside>
                <div class="gx-form-fields">
                    <p class="gx-form-kicker">PROJECT BRIEF</p>
                    <p class="gx-form-intro">Tell us where you are now and what you want to achieve.</p>
                    <p class="gx-form-required"><span>*</span> Required fields</p><input type="hidden"
                        name="request_category" data-gx-form-value value="solar"><label
                        class="gx-field-service">Service requested <input name="service" type="text" data-gx-form-service readonly
                            value="Factory installation"></label>
                    <div class="gx-form-row"><label><span class="gx-field-label">Your name <b>*</b></span><input
                                name="name" required placeholder="Name / Company"
                                autocomplete="name"></label><label><span class="gx-field-label">Phone number
                                <b>*</b></span><span class="gx-phone-field"><span class="gx-th-flag"
                                    aria-hidden="true"></span><span class="gx-phone-code">+66</span><input
                                    name="phone" type="tel" required inputmode="tel" placeholder="98 000 0000"
                                    autocomplete="tel"
                                    aria-label="Phone number, Thailand country code plus 66"></span></label></div>
                    <div class="gx-form-row"><label><span class="gx-field-label">Email <b>*</b></span><input
                                name="email" type="email" required placeholder="you@company.com"
                                autocomplete="email"></label><label>Company <input name="company"
                                placeholder="Business / organisation" autocomplete="organization"></label></div>
                    <div class="gx-form-row"><label>Location <input name="location"
                                placeholder="Province / site location"></label><label>Website <input name="website"
                                type="url" placeholder="www.yourcompany.com"></label></div><label
                        class="gx-field-message">Tell us about your project
                        <textarea name="message" rows="3" placeholder="What would you like to build, improve or discuss?"></textarea>
                    </label>@include('components.contact-form-protection', ['action' => 'consultation'])<button class="gx-form-submit" type="submit">Send consultation request
                        <span>↗</span></button>
                </div>
            </form>
        </dialog>

        <section class="gx-intro gx-intro-trust" id="about">
            <div class="gx-wrap gx-intro-grid">
                <div class="gx-intro-statement">
                    <p class="gx-label">GSB SUNPOWER PARTNERSHIPS</p>
                    <h2><span>Trusted by</span><span>organisations across</span><span><em>industries.</em></span></h2>
                    <p>We make energy infrastructure easier to choose, deliver and use — for businesses ready to move
                        forward.</p>
                </div>
                <div class="gx-intro-details">
                    <p>GSB SunPower brings together solar, EV charging and engineering in one practical team. We begin
                        with the way your site and business really operate.</p>
                    <p>From first assessment through delivery and long-term support, every recommendation is built to
                        perform in the real world.</p>
                    <h3>Trusted by businesses across Thailand</h3>
                    <div class="gx-intro-logo-grid" aria-label="Selected GSB SunPower customers"><img
                            src="{{ asset('image/client-logos/client-01.jpg') }}" alt="GSB SunPower customer"><img
                            src="{{ asset('image/client-logos/client-02.png') }}" alt="GSB SunPower customer"><img
                            src="{{ asset('image/client-logos/client-03.jpg') }}" alt="GSB SunPower customer"><img
                            src="{{ asset('image/client-logos/client-04.png') }}" alt="GSB SunPower customer"><img
                            src="{{ asset('image/client-logos/client-05.png') }}" alt="GSB SunPower customer"><img
                            src="{{ asset('image/client-logos/client-06.jpg') }}" alt="GSB SunPower customer"><img
                            src="{{ asset('image/client-logos/client-07.png') }}" alt="GSB SunPower customer"><img
                            src="{{ asset('image/client-logos/client-08.png') }}" alt="GSB SunPower customer"></div>
                    <a class="gx-intro-link" href="{{ route('about') }}">Get to know GSB <span>↗</span></a>
                </div>
            </div>
        </section>

        <section class="gx-client-credits gx-client-credits-old" aria-labelledby="client-title">
            <div class="gx-wrap">
                <div class="gx-client-heading">
                    <div>
                        <p class="gx-label">TRUSTED BY REAL BUSINESSES</p>
                        <h2 id="client-title">Proud to power<br><em>our customers’ next move.</em></h2>
                    </div>
                    <p>From hospitality and retail to factories and commercial destinations, GSB SunPower is trusted by
                        organisations across Thailand.</p>
                </div>
            </div>
            <div class="gx-logo-rail" aria-label="GSB SunPower client logos">
                <div class="gx-logo-track">
                    <img src="{{ asset('image/client-logos/client-01.jpg') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-02.png') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-03.jpg') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-04.png') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-05.png') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-06.jpg') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-07.png') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-08.png') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-09.jpg') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-10.jpg') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-11.png') }}" alt="GSB SunPower client logo"><img
                        src="{{ asset('image/client-logos/client-12.png') }}" alt="GSB SunPower client logo">
                    <img src="{{ asset('image/client-logos/client-01.jpg') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-02.png') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-03.jpg') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-04.png') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-05.png') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-06.jpg') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-07.png') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-08.png') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-09.jpg') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-10.jpg') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-11.png') }}" alt=""
                        aria-hidden="true"><img src="{{ asset('image/client-logos/client-12.png') }}" alt=""
                        aria-hidden="true">
                </div>
            </div>
        </section>

        <section class="gx-services gx-benefits gx-wrap" id="services">
            <div class="gx-benefits-intro">
                <p class="gx-label" data-gx-i18n="benefits.eyebrow">WHY GSB SUNPOWER</p>
                <h2><span data-gx-i18n="benefits.titleOne">Built around</span> <span><em
                            data-gx-i18n="benefits.titleTwo">what matters.</em></span></h2>
                <p data-gx-i18n="benefits.summary">Clean energy projects should be simple to understand, dependable to
                    run and ready to grow with your business.</p>
            </div>
            <div class="gx-benefit-grid">
                <article class="gx-benefit-card"><span class="gx-benefit-icon" aria-hidden="true"><svg
                            viewBox="0 0 48 48">
                            <path d="M24 4 40 10v11c0 10-6.6 18.6-16 23-9.4-4.4-16-13-16-23V10L24 4Z" />
                            <path d="m16 24 5 5 11-12" />
                        </svg></span>
                    <h3 data-gx-i18n="benefits.boiTitle">BOI-supported investment</h3>
                    <p data-gx-i18n="benefits.boiText">Eligible projects can access investment incentives and tax
                        benefits.</p>
                </article>
                <article class="gx-benefit-card"><span class="gx-benefit-icon" aria-hidden="true"><svg
                            viewBox="0 0 48 48">
                            <circle cx="17" cy="15" r="6" />
                            <circle cx="32" cy="15" r="6" />
                            <path d="M5 39c1.5-8 6.2-12 12-12s10.5 4 12 12M23 38c1-5.8 4.5-9 9-9 5.2 0 9.2 3.6 11 10" />
                        </svg></span>
                    <h3 data-gx-i18n="benefits.teamTitle">Experienced specialist team</h3>
                    <p data-gx-i18n="benefits.teamText">Practical guidance from the first conversation to every next
                        step.</p>
                </article>
                <article class="gx-benefit-card"><span class="gx-benefit-icon" aria-hidden="true"><svg
                            viewBox="0 0 48 48">
                            <circle cx="24" cy="24" r="8" />
                            <path d="M24 4v7M24 37v7M4 24h7M37 24h7M10 10l5 5M33 33l5 5M38 10l-5 5M15 33l-5 5" />
                        </svg></span>
                    <h3 data-gx-i18n="benefits.deliveryTitle">End-to-end delivery</h3>
                    <p data-gx-i18n="benefits.deliveryText">Site survey, design, installation, project management and
                        maintenance.</p>
                </article>
                <article class="gx-benefit-card"><span class="gx-benefit-icon" aria-hidden="true"><svg
                            viewBox="0 0 48 48">
                            <path d="M7 40V28h8v12M20 40V20h8v20M33 40V12h8v28M7 8l12 6 9-8 13 2" />
                            <path d="m36 4 5 4-4 5" />
                        </svg></span>
                    <h3 data-gx-i18n="benefits.smartTitle">Smart energy management</h3>
                    <p data-gx-i18n="benefits.smartText">Real-time system visibility through an application designed
                        for your site.</p>
                </article><a class="gx-benefits-link" href="{{ route('about') }}"
                    data-gx-i18n-html="benefits.cta">Explore GSB SunPower <span>↗</span></a>
            </div>
        </section>

        <section class="gx-connect gx-wrap" aria-labelledby="connect-title">
            <div class="gx-connect-shell">
                <div class="gx-connect-copy">
                    <p class="gx-label">CONNECT WITH GSB</p>
                    <h2 id="connect-title">Find the right<br><em>conversation.</em></h2>
                    <p>Follow our work, ask a question or start your next energy project through the channel that suits
                        you.</p>
                </div>
                <div class="gx-channel-grid"><a class="gx-channel gx-channel-page"
                        href="https://www.facebook.com/solargsbsunpower" target="_blank"
                        rel="noopener noreferrer"><img src="{{ asset('image/social-sunpower.jpg') }}"
                            alt="Sun Power Facebook page"><span><b>Sun Power</b><small>Facebook page
                                ↗</small></span></a><a class="gx-channel gx-channel-page"
                        href="https://www.facebook.com/gsbsunpowerofficial" target="_blank"
                        rel="noopener noreferrer"><img src="{{ asset('image/social-gsb-ev.jpg') }}"
                            alt="GSB EV Facebook page"><span><b>GSB EV</b><small>Facebook page ↗</small></span></a><a
                        class="gx-channel" href="https://www.tiktok.com/@gsb.sunpower" target="_blank"
                        rel="noopener noreferrer" aria-label="GSB SunPower on TikTok"><span class="gx-social-icon"
                            aria-hidden="true"><svg class="gx-icon-tiktok" viewBox="0 0 48 48">
                                <path class="gx-tiktok-cyan"
                                    d="M25 7v23a8 8 0 1 1-6-7.75V17a13 13 0 1 0 11 12.86V18.2c3.1 2.65 6.85 4.1 11 4.1v-5.5C34.2 16.8 30 12.6 30 7Z" />
                                <path class="gx-tiktok-pink"
                                    d="M28 7v23a8 8 0 1 1-6-7.75V17a13 13 0 1 0 11 12.86V18.2c3.1 2.65 6.85 4.1 11 4.1v-5.5C37.2 16.8 33 12.6 33 7Z" />
                                <path
                                    d="M29 6v23a8 8 0 1 1-6-7.75V16a13 13 0 1 0 11 12.86V17.2c3.1 2.65 6.85 4.1 11 4.1v-5.5C38.2 15.8 34 11.6 34 6Z" />
                            </svg></span><b>TikTok</b><small>@gsb.sunpower</small></a><a class="gx-channel"
                        href="https://www.instagram.com/gsb_sunpower" target="_blank" rel="noopener noreferrer"
                        aria-label="GSB SunPower on Instagram"><span class="gx-social-icon" aria-hidden="true"><svg
                                class="gx-icon-instagram" viewBox="0 0 48 48">
                                <defs>
                                    <linearGradient id="gx-instagram-gradient" x1="5" y1="45"
                                        x2="43" y2="3" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#feda75" />
                                        <stop offset=".38" stop-color="#fa7e1e" />
                                        <stop offset=".65" stop-color="#d62976" />
                                        <stop offset="1" stop-color="#4f5bd5" />
                                    </linearGradient>
                                </defs>
                                <rect x="3" y="3" width="42" height="42" rx="12"
                                    fill="url(#gx-instagram-gradient)" />
                                <rect x="12" y="12" width="24" height="24" rx="8" fill="none"
                                    stroke="#fff" stroke-width="3" />
                                <circle cx="24" cy="24" r="6" fill="none" stroke="#fff"
                                    stroke-width="3" />
                                <circle cx="33.5" cy="14.5" r="2" fill="#fff" />
                            </svg></span><b>Instagram</b><small>@gsb_sunpower</small></a><a class="gx-channel"
                        href="https://line.me/R/ti/p/@gsbsunpower" target="_blank" rel="noopener noreferrer"
                        aria-label="GSB SunPower on LINE"><span class="gx-social-icon" aria-hidden="true"><svg
                                class="gx-icon-line" viewBox="0 0 48 48">
                                <rect x="3" y="3" width="42" height="42" rx="10" fill="#06c755" />
                                <path
                                    d="M9.5 20c0-6 6.5-10.5 14.5-10.5S38.5 14 38.5 20 32 30.5 24 30.5c-1.2 0-2.4-.1-3.5-.35l-5.7 3.3 1.3-5.35C12 26.25 9.5 23.3 9.5 20Z"
                                    fill="#fff" /><text x="24" y="22.5" fill="#06c755" font-size="7.7"
                                    font-family="Arial, sans-serif" font-weight="900"
                                    text-anchor="middle">LINE</text>
                            </svg></span><b>LINE</b><small>@gsbsunpower</small></a></div>
            </div>
        </section>

        <section class="gx-next-consult">
            <div class="gx-wrap">
                <p class="gx-label">START WITH GSB</p>
                <h2>Ready to make your<br><em>next energy move?</em></h2>
                <p>Tell us what you are planning. Our team will help you find the most practical next step.</p><a
                    class="gx-next-consult-button" href="{{ route('contact') }}">Talk to our team <span>↗</span></a>
            </div>
        </section>
        <section class="gx-commitment"><span class="gx-commitment-dot gx-dot-one" aria-hidden="true"></span><span
                class="gx-commitment-dot gx-dot-two" aria-hidden="true"></span><span
                class="gx-commitment-dot gx-dot-three" aria-hidden="true"></span>
            <div class="gx-wrap gx-commitment-inner">
                <p class="gx-label">THE GSB TEAM</p>
                <h2><span>Clear advice, <img src="{{ asset('image/team-03.png') }}" alt="GSB SunPower team member">
                        practical delivery, <img src="{{ asset('image/team-01.png') }}"
                            alt="GSB SunPower team member"></span><span>and <img
                            src="{{ asset('image/team-02.png') }}" alt="GSB SunPower team member"> support that stays
                        <em>close.</em> <img src="{{ asset('image/team-04.png') }}"
                            alt="GSB SunPower team member"></span></h2>
                <p>We bring specialist knowledge together so every decision is clear, practical and ready for your
                    real-world operation.</p><a class="gx-commitment-link" href="{{ route('about') }}">Meet the GSB
                    team <span>↗</span></a>
            </div>
        </section>
        <section class="gx-reviews" aria-labelledby="reviews-title">
            <div class="gx-wrap">
                <p class="gx-label">PARTNER FEEDBACK</p>
                <h2 id="reviews-title">What it feels like to<br><em>work with GSB.</em></h2>
                <div class="gx-review-stage" data-gx-reviews>
                    <div class="gx-review-page is-current" data-gx-review-page>
                        <article>
                            <p lang="th">“ตอนแรกยังไม่แน่ใจว่าธุรกิจของเราเหมาะกับการติดตั้งโซลาร์เซลล์ไหม ทีม GSB Sun
                                Power ช่วยอธิบายและประเมินจากการใช้ไฟจริง ทำให้เห็นภาพและตัดสินใจได้ง่ายขึ้นครับ”</p>
                            <span class="gx-stars" aria-label="5 stars">★★★★★</span>
                            <footer><b>Kitsada Chokevichitkul</b><img class="gx-review-avatar"
                                    src="{{ asset('image/review-01.jpg') }}" alt="Kitsada Chokevichitkul"></footer>
                        </article>
                        <article>
                            <p lang="th">“ชอบที่ทีมเข้ามาดูทั้งพื้นที่หลังคาและรูปแบบการใช้ไฟของโรงงานก่อนเสนอระบบ
                                พร้อมอธิบายเหตุผลในการเลือกขนาดติดตั้ง ทำให้เรามีข้อมูลประกอบการวางแผนลงทุนค่ะ”</p><span
                                class="gx-stars" aria-label="5 stars">★★★★★</span>
                            <footer><b>Siwanath Simueang</b><img class="gx-review-avatar"
                                    src="{{ asset('image/review-02.jpg') }}" alt="Siwanath Simueang"></footer>
                        </article>
                        <article>
                            <p lang="th">“โครงการมีรายละเอียดที่ต้องประสานงานหลายส่วน แต่ทีม GSB Sun Power
                                แจ้งขั้นตอนและติดตามความคืบหน้าให้ตลอด เวลามีข้อสงสัยก็มีคนช่วยประสานงาน
                                ทำให้ทำงานร่วมกันได้สะดวกครับ”</p><span class="gx-stars"
                                aria-label="5 stars">★★★★★</span>
                            <footer><b>MI Kornpong</b><img class="gx-review-avatar"
                                    src="{{ asset('image/review-03.jpg') }}" alt="MI Kornpong"></footer>
                        </article>
                    </div>
                    <div class="gx-review-page" data-gx-review-page>
                        <article>
                            <p lang="th">“เรากังวลว่างานติดตั้งจะกระทบการทำงานประจำวัน
                                ทีมจึงเข้ามาคุยและวางแผนร่วมกันก่อนเริ่มงาน มีการแจ้งกำหนดการให้เตรียมตัวล่วงหน้า
                                ประทับใจความใส่ใจตรงนี้ค่ะ”</p><span class="gx-stars"
                                aria-label="5 stars">★★★★★</span>
                            <footer><b>BOW Kanokwan</b><img class="gx-review-avatar"
                                    src="{{ asset('image/review-04.jpg') }}" alt="BOW Kanokwan"></footer>
                        </article>
                        <article>
                            <p lang="th">“หลังติดตั้ง
                                ทีมช่วยสอนวิธีดูข้อมูลการผลิตไฟและอธิบายการใช้งานระบบให้เข้าใจ
                                พอมีคำถามเพิ่มเติมก็สามารถติดต่อสอบถามได้ ทำให้เรามั่นใจในการใช้งานมากขึ้นครับ”</p><span
                                class="gx-stars" aria-label="5 stars">★★★★★</span>
                            <footer><b>Jirayuth</b><img class="gx-review-avatar"
                                    src="{{ asset('image/review-05.png') }}" alt="Jirayuth"></footer>
                        </article>
                        <article>
                            <p lang="th">“เราต้องการนำพลังงานสะอาดมาใช้ในธุรกิจ แต่ยังไม่รู้ว่าจะเริ่มตรงไหน
                                การได้คุยกับ GSB Sun Power ช่วยให้เห็นทั้งแนวทางติดตั้งและสิ่งที่ต้องเตรียม
                                ทำให้แผนของเราชัดเจนขึ้นค่ะ”</p><span class="gx-stars"
                                aria-label="5 stars">★★★★★</span>
                            <footer><b>Pinsuda Tangmo</b><img class="gx-review-avatar"
                                    src="{{ asset('image/review-06.png') }}" alt="Pinsuda Tangmo"></footer>
                        </article>
                    </div>
                </div>
                <div class="gx-review-dots" aria-label="Review pages"><button class="is-active" type="button"
                        data-gx-review-dot aria-label="Show reviews 1 to 3"></button><button type="button"
                        data-gx-review-dot aria-label="Show reviews 4 to 6"></button></div>
            </div>
        </section>
        <section class="gx-faq-th" aria-labelledby="faq-title">
            <div class="gx-wrap">
                <p class="gx-label">HELP &amp; INFORMATION</p>
                <h2 id="faq-title" lang="th">คำถามที่พบบ่อย</h2>
                <div class="gx-faq-list" lang="th">
                    <details open>
                        <summary><span>01</span>GSB Sun Power ให้บริการอะไรบ้าง?<b>+</b></summary>
                        <p>เราให้บริการด้านพลังงานสะอาด ทั้งโครงการโซลาร์เซลล์และสถานีชาร์จรถยนต์ไฟฟ้า
                            ครอบคลุมการให้คำปรึกษา ประเมินพื้นที่ ออกแบบ ติดตั้ง และดูแลระบบ
                            โดยพิจารณารูปแบบโครงการให้เหมาะกับแต่ละธุรกิจ</p>
                    </details>
                    <details>
                        <summary><span>02</span>จะรู้ได้อย่างว่าพื้นที่ของเราเหมาะกับการติดตั้งโซลาร์เซลล์?<b>+</b>
                        </summary>
                        <p>เบื้องต้นจะพิจารณาจากปริมาณการใช้ไฟฟ้า ช่วงเวลาที่ใช้ไฟ พื้นที่ติดตั้ง
                            และความพร้อมของโครงสร้าง คุณสามารถส่งบิลค่าไฟและภาพสถานที่ให้ทีมงานประเมินเบื้องต้น
                            ก่อนสำรวจรายละเอียดหน้างาน</p>
                    </details>
                    <details>
                        <summary><span>03</span>ติดตั้งโซลาร์เซลล์แล้วช่วยลดค่าไฟได้เท่าไร?<b>+</b></summary>
                        <p>ขึ้นอยู่กับขนาดระบบ ปริมาณแสงแดด และการใช้ไฟในช่วงที่ระบบผลิตไฟได้
                            โดยธุรกิจที่ใช้ไฟช่วงกลางวันสามารถนำไฟจากโซลาร์เซลล์มาใช้ได้โดยตรง
                            ทีมงานจะประเมินการประหยัดจากข้อมูลของแต่ละสถานที่
                            เพื่อให้ได้ประมาณการที่เหมาะกับการใช้งานจริง</p>
                    </details>
                    <details>
                        <summary><span>04</span>การติดตั้งโซลาร์เซลล์ใช้เวลานานแค่ไหน
                            และกระทบการดำเนินธุรกิจหรือไม่?<b>+</b></summary>
                        <p>ระยะเวลาขึ้นอยู่กับขนาดโครงการ ความพร้อมของสถานที่ และขั้นตอนการขออนุญาต
                            ทีมงานจะวางแผนและประสานกำหนดการล่วงหน้า รวมถึงแจ้งช่วงที่ต้องหยุดจ่ายไฟเพื่อเชื่อมต่อระบบ
                            เพื่อให้ธุรกิจเตรียมตัวและลดผลกระทบต่อการทำงาน</p>
                    </details>
                    <details>
                        <summary><span>05</span>หลังติดตั้ง มีบริการดูแลและรับประกันอย่างไร?<b>+</b></summary>
                        <p>ทีมงานจะแนะนำวิธีใช้งานและติดตามการทำงานของระบบ พร้อมให้ข้อมูลการดูแลหลังติดตั้ง
                            ส่วนการรับประกันอุปกรณ์ งานติดตั้ง
                            และขอบเขตการบำรุงรักษาจะเป็นไปตามข้อเสนอและสัญญาของแต่ละโครงการ
                            โดยสามารถสอบถามรายละเอียดก่อนตัดสินใจได้</p>
                    </details>
                    <details>
                        <summary><span>06</span>สนใจติดตั้งโซลาร์เซลล์หรือสถานีชาร์จ EV ต้องเริ่มต้นอย่างไร?<b>+</b>
                        </summary>
                        <p>ติดต่อผ่านแบบฟอร์มบนเว็บไซต์ หรือ LINE OA: @gsbsunpower พร้อมแจ้งประเภทธุรกิจ พิกัดสถานที่
                            และข้อมูลติดต่อ หากสนใจโซลาร์เซลล์ แนะนำให้เตรียมบิลค่าไฟล่าสุด ส่วนสถานีชาร์จ EV
                            ให้เตรียมภาพพื้นที่และจำนวนช่องจอด เพื่อช่วยให้ทีมงานประเมินเบื้องต้นได้สะดวกขึ้น</p>
                    </details>
                </div>
            </div>
        </section>

        <section class="gx-home-enquiry" aria-labelledby="home-enquiry-title">
            <div class="gx-wrap gx-home-enquiry-grid">
                <div class="gx-home-enquiry-copy">
                    <p class="gx-label" data-gx-home-enquiry="label">START A CONVERSATION</p>
                    <h2 id="home-enquiry-title" data-gx-home-enquiry-html="title">Tell us about<br><em>your energy plan</em></h2>
                    <p data-gx-home-enquiry="lead">Share a few details and our team will help identify the most practical next step for your site</p>
                </div>
                <form class="gx-home-enquiry-form" action="{{ route('contact-enquiries.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="source" value="home">
                    <label class="gx-home-enquiry-full"><span data-gx-home-enquiry="name">Full name / Company name*</span><input name="name" type="text" required autocomplete="name" placeholder="Full name / Company name*"></label>
                    <label><span data-gx-home-enquiry="email">Email*</span><input name="email" type="email" required autocomplete="email" placeholder="Email*"></label>
                    <label><span data-gx-home-enquiry="phone">Phone*</span><input name="phone" type="tel" required autocomplete="tel" placeholder="Phone*"></label>
                    <label><span data-gx-home-enquiry="service">Service of interest</span><select name="service"><option value="solar" data-gx-home-enquiry="solar">Solar PPA</option><option value="epc" data-gx-home-enquiry="epc">Engineering &amp; EPC</option><option value="ev" data-gx-home-enquiry="ev">EV charging</option></select></label>
                    <label><span data-gx-home-enquiry="budget">Estimated budget</span><select name="budget"><option value="" data-gx-home-enquiry="budgetPrompt">Select a range</option><option data-gx-home-enquiry="budgetSmall">Under ฿1 million</option><option data-gx-home-enquiry="budgetMedium">฿1–5 million</option><option data-gx-home-enquiry="budgetLarge">Over ฿5 million</option></select></label>
                    <label class="gx-home-enquiry-full"><span data-gx-home-enquiry="message">How can we help?*</span><textarea name="message" rows="4" required placeholder="How can we help?*"></textarea></label>
                    @include('components.contact-form-protection', ['action' => 'home'])
                    <button type="submit" data-gx-home-enquiry="submit">Send enquiry <span>↗</span></button>
                </form>
            </div>
        </section>

        <section class="gx-work" id="work">
            <div class="gx-wrap">
                <div class="gx-work-heading">
                    <div>
                        <p class="gx-label">PROJECTS IN PROGRESS</p>
                        <h2>Made for real<br><em>places and people.</em></h2>
                    </div>
                    <div class="gx-slider-controls"><button type="button" data-gx-prev
                            aria-label="Previous project">←</button><span data-gx-count>01 / 03</span><button
                            type="button" data-gx-next aria-label="Next project">→</button></div>
                </div>
                <div class="gx-project-slider" data-gx-slider>
                    <article class="is-current"><img src="{{ asset('image/SolarPPA/ppa_case_01_samut_sakhon.png') }}"
                            alt="Solar PPA project">
                        <div><small>SOLAR PPA</small>
                            <h3>Energy that supports business growth.</h3><a href="{{ route('solar-ppa') }}">View
                                project ↗</a>
                        </div>
                    </article>
                    <article><img src="{{ asset('image/EV/charge_site_03_aviyana.jpeg') }}"
                            alt="EV charging project">
                        <div><small>EV CHARGING</small>
                            <h3>A better arrival for every driver.</h3><a href="{{ route('ev-charging') }}">View
                                project ↗</a>
                        </div>
                    </article>
                    <article><img src="{{ asset('image/EPC/pexels-budget-bizar-92378004-12224996.jpg') }}"
                            alt="EPC project">
                        <div><small>ENGINEERING &amp; EPC</small>
                            <h3>Infrastructure that is ready to work.</h3><a href="{{ route('epc') }}">View project
                                ↗</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="gx-proof gx-wrap">
            <div><span>01</span>
                <h3>Understand</h3>
                <p>Site-first planning, with real commercial context.</p>
            </div>
            <div><span>02</span>
                <h3>Deliver</h3>
                <p>One accountable team from design to launch.</p>
            </div>
            <div><span>03</span>
                <h3>Stay close</h3>
                <p>Support and performance care for the long term.</p>
            </div>
        </section>
        <section class="gx-testimonial">
            <div class="gx-wrap">
                <p class="gx-label">WHAT PARTNERS VALUE</p>
                <div class="gx-quote" data-gx-quote>
                    <p>“The best energy solution is the one that lets your business move forward with confidence.”</p>
                    <span>GSB SUNPOWER GROUP</span>
                </div>
            </div>
        </section>
        <section class="gx-faq gx-wrap">
            <div>
                <p class="gx-label">COMMON QUESTIONS</p>
                <h2>Let’s make the<br><em>next step clear.</em></h2>
            </div>
            <div>
                <details open>
                    <summary>Can our site use Solar PPA?<span>+</span></summary>
                    <p>We start with your electricity profile, roof and business goals, then recommend the most
                        practical option.</p>
                </details>
                <details>
                    <summary>Can GSB design EV charging for our location?<span>+</span></summary>
                    <p>Yes. We design charging for workplaces, hotels, destinations, fleets and commercial property.</p>
                </details>
                <details>
                    <summary>What should we prepare first?<span>+</span></summary>
                    <p>A short overview of your site is enough. Our team will guide the next conversation.</p>
                </details>
            </div>
        </section>
        <section class="gx-cta">
            <div class="gx-wrap">
                <p class="gx-label">START WITH GSB</p>
                <h2>Ready to turn<br>ambition into <em>energy?</em></h2><a href="{{ route('contact') }}">Talk to our
                    team <span>↗</span></a>
            </div>
        </section>
    </main>
    <footer class="site-footer" id="site-footer"></footer>
    <script>window.gsbHomeHero = @json($homeHero); window.gsbTestimonials = @json($testimonials);</script>
    <script src="{{ asset('script.js') }}"></script>
    @include('components.enquiry-success-modal')
    <script src="{{ asset('gx-home.js') }}"></script>
</body>

</html>
