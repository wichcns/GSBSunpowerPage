<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>EV Charging | GSB SunPower Group</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="service-page">
    <div class="utility-bar">
        <div class="shell utility-content"><span>Clean energy, made practical.</span><a href="contact.html">Talk to an
                expert ↗</a></div>
    </div>
    <header class="site-header">
        <div class="shell nav-wrap"><a class="brand" href="index.html"><img class="brand-logo"
                    src="image/GSBSUNPOWER.png" alt="GSB SunPower Group"></a><button class="menu-button" type="button"
                aria-expanded="false" aria-controls="site-menu"><span class="sr-only">Open
                    menu</span><i></i><i></i></button>
            <nav class="site-menu" id="site-menu"><a href="about.html">About Us</a><a class="active"
                    href="solutions.html">Solutions</a><a href="approach.html">Our Approach</a><a
                    href="projects.html">Projects</a><a href="insights.html">Insights</a><a class="nav-button"
                    href="contact.html">Start a Project ↗</a></nav>
        </div>
    </header>
    <main>
        <section class="service-hero service-ev">
            <div class="shell">
                <a class="epc-back" href="solutions.html"><span>←</span> Back to Solutions</a>
                <p class="kicker on-dark"><span></span> SOLUTIONS / 03</p>
                <h1>Charging that keeps<br><em>business moving.</em></h1>
                <p>EV infrastructure shaped around your site, your users, and the way demand will grow.</p><a
                    href="#ev-sites">Explore charging locations <span>↓</span></a>
            </div>
        </section>
        <section class="service-summary">
            <div class="shell">
                <p class="kicker"><span></span> EV CHARGING</p>
                <h2>Ready for every<br>route <mark>ahead.</mark></h2>
                <p lang="th">ออกแบบโครงสร้างพื้นฐาน EV ที่ตอบโจทย์พื้นที่ ผู้ใช้งาน และการขยายตัวในอนาคต
                    เพื่อให้ธุรกิจพร้อมสำหรับการเดินทางยุคใหม่</p>
            </div>
        </section>
        <section class="epc-capabilities" id="ev-sites">
            <div class="shell">
                <div class="epc-heading">
                    <div>
                        <p class="kicker on-dark"><span></span> Charging locations</p>
                        <h2>Power where<br><em>people go.</em></h2>
                    </div>
                    <p>Selected EV charging locations across hospitality, retail, and destination sites.<span
                            lang="th">ตัวอย่างจุดติดตั้งสถานีชาร์จ EV สำหรับโรงแรม ร้านค้า
                            และสถานที่ท่องเที่ยว</span></p>
                </div>
                <div class="ev-gallery"></div>
            </div>
        </section>
    </main>
    <footer>
        <div class="shell footer-top"><a class="brand footer-brand" href="index.html"><img class="brand-logo"
                    src="image/GSBSUNPOWER.png" alt="GSB SunPower Group"></a>
            <p>Clean energy, made practical.</p>
        </div>
        <div class="shell footer-bottom"><span>© 2026 GSB SunPower Group</span><span>Bangkok, Thailand</span></div>
    </footer>
    <script>
        window.gsbProjects = @json($projects);
    </script>
    <script src="{{ asset('script.js') }}"></script>
    <script src="{{ asset('dynamic-projects.js') }}"></script>
</body>

</html>
