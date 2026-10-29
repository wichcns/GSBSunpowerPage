<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="GSB SunPower blog article">
    <title>Blog | GSB SunPower Group</title>
    <link rel="stylesheet" href="{{ asset('styles.css') }}">
    <link rel="stylesheet" href="{{ asset('operations-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ev-first-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('ui-polish.css') }}">
    <link rel="stylesheet" href="{{ asset('gx-modal.css') }}">
    <style>
        :root { --detail-ink: #063b43; --detail-teal: #00bca2; --detail-mist: #f4f8f7; }
        .blog-detail-page { min-height: 100vh; background: var(--detail-mist); color: var(--detail-ink); }
        .gsb-experience .blog-detail-header { position: relative; background: #063d45; color: #fff; }
        .blog-detail-shell { width: min(920px, calc(100% - 48px)); margin: 0 auto; }
        .blog-detail { padding: 3.6rem 0 6rem; }
        .blog-back { display: inline-flex; align-items: center; gap: .5rem; margin-bottom: 2rem; color: #007e73; font-size: .82rem; font-weight: 850; text-decoration: none; }
        .blog-back:hover { color: #005b54; }
        .blog-detail-card { overflow: hidden; border-radius: 22px; background: #fff; box-shadow: 0 18px 42px rgba(4,54,59,.09); }
        .blog-detail-cover { aspect-ratio: 1672 / 941; margin: 0; overflow: hidden; background: #dfeeea; }
        .blog-detail-cover img { display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; }
        .blog-detail-copy { padding: clamp(1.5rem, 5vw, 4.25rem); }
        .blog-detail-meta { margin: 0 0 1rem; color: #66858a; font-size: .74rem; font-weight: 900; letter-spacing: .08em; }
        .blog-detail h1 { width: 100%; margin: 0; font-size: clamp(2rem, 5vw, 4rem); letter-spacing: -.065em; line-height: 1.02; white-space: pre-line; overflow-wrap: normal; word-break: normal; }
        .blog-detail-excerpt { margin: 1.3rem 0 0; color: #507075; font-size: 1.05rem; line-height: 1.75; white-space: pre-line; }
        .blog-detail-content { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #d9e8e4; color: #274b51; font-size: 1rem; line-height: 1.9; }
        .blog-detail-content p { margin: 0 0 1.1rem; }
        .blog-detail-content h2, .blog-detail-content h3 { margin: 1.75rem 0 .75rem; color: #063b43; line-height: 1.2; }
        .blog-detail-content ul, .blog-detail-content ol { margin: 0 0 1.1rem; padding-left: 1.45rem; }
        .blog-detail-content a { color: #007e73; font-weight: 750; text-decoration: underline; }
        .blog-detail-content blockquote { margin: 1.25rem 0; padding: .2rem 0 .2rem 1rem; border-left: 3px solid #00bca2; color: #507075; }
        .blog-detail-content:empty { display: none; }
        @media (max-width: 700px) { .blog-detail-shell { width: min(100% - 32px, 920px); }.blog-detail { padding: 2rem 0 4rem; }.blog-detail-copy { padding: 1.45rem; } }
    </style>
</head>
<body class="gsb-experience blog-detail-page">
    <a class="skip-link" href="#main">Skip to main content</a>
    <header class="gx-header gx-header-overlay gx-about-header blog-detail-header" id="top">
        <a class="gx-logo" href="{{ route('home') }}"><img src="{{ asset('image/GSBSUNPOWER-Edit.png') }}" alt="GSB SunPower Group"></a>
        <div class="gx-nav-area"><div class="gx-topline"><span>☎ 098-828-5767</span><a aria-label="Facebook" href="https://www.facebook.com/gsbsunpowerofficial" target="_blank" rel="noopener noreferrer">f</a><a aria-label="Instagram" href="https://www.instagram.com/gsb_sunpower" target="_blank" rel="noopener noreferrer">◎</a><a aria-label="TikTok" href="https://www.tiktok.com/@gsb.sunpower" target="_blank" rel="noopener noreferrer">♪</a><details class="gx-language"><summary><span class="gx-globe" aria-hidden="true"></span><b data-gx-language-label>EN</b><i aria-hidden="true"></i></summary><div><button type="button" data-gx-language="EN">English</button><button type="button" data-gx-language="TH">ไทย</button><button type="button" data-gx-language="ZH">中文</button></div></details></div><div class="gx-navline"><nav><a href="{{ route('about') }}">About us <i></i></a><a class="is-current" href="{{ route('blog') }}">Blog <i></i></a><a href="{{ route('solutions') }}">Solutions <i></i></a></nav><a class="gx-contact" href="{{ route('contact') }}">Contact us</a></div></div>
    </header>
    <main id="main">
        <article class="blog-detail">
            <div class="blog-detail-shell">
                <a class="blog-back" href="{{ route('blog') }}">← Back to Blog</a>
                <div class="blog-detail-card">
                    <figure class="blog-detail-cover" id="blog-detail-cover"></figure>
                    <div class="blog-detail-copy">
                        <p class="blog-detail-meta" id="blog-detail-meta"></p>
                        <h1 id="blog-detail-title"></h1>
                        <p class="blog-detail-excerpt" id="blog-detail-excerpt"></p>
                        <div class="blog-detail-content" id="blog-detail-content"></div>
                    </div>
                </div>
            </div>
        </article>
    </main>
    <footer class="site-footer" id="site-footer"></footer>
    <script src="{{ asset('script.js') }}"></script>
    <script src="{{ asset('gx-home.js') }}"></script>
    <script>
        window.gsbBlogDetailPost = @json($post);
        window.gsbBlogMediaTemplate = @json(route('media.public', ['path' => '__BLOG_MEDIA_PATH__']));
        (() => {
            const post = window.gsbBlogDetailPost;
            const locale = (localStorage.getItem('gsb-language') || 'en').toLowerCase();
            const field = (name) => post[`${name}_${locale}`] || post[`${name}_en`] || post[`${name}_th`] || post[`${name}_zh`] || '';
            const parsed = new Date(post.published_at);
            const language = locale === 'th' ? 'th-TH' : locale === 'zh' ? 'zh-CN' : 'en-GB';
            const date = Number.isNaN(parsed.getTime()) ? '' : new Intl.DateTimeFormat(language, { day: '2-digit', month: 'short', year: 'numeric' }).format(parsed).toUpperCase();
            const coverImage = post[`cover_image_${locale}`] || post.cover_image || '';
            const image = coverImage ? window.gsbBlogMediaTemplate.replace('__BLOG_MEDIA_PATH__', coverImage.split('/').map(encodeURIComponent).join('/')) : '';

            document.title = `${field('title')} | GSB SunPower Group`;
            document.querySelector('#blog-detail-meta').textContent = [String(post.category || '').toUpperCase(), date].filter(Boolean).join(' · ');
            document.querySelector('#blog-detail-title').textContent = field('title');
            document.querySelector('#blog-detail-excerpt').textContent = field('excerpt');
            const escapeHtml = (value) => String(value || '').replace(/[&<>'"]/g, (character) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
            }[character]));
            const safeRichText = (html) => {
                const template = document.createElement('template');
                template.innerHTML = html;
                const allowedTags = new Set(['P', 'BR', 'STRONG', 'B', 'EM', 'I', 'U', 'UL', 'OL', 'LI', 'A', 'H2', 'H3', 'BLOCKQUOTE']);
                template.content.querySelectorAll('*').forEach((element) => {
                    if (['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED'].includes(element.tagName)) {
                        element.remove();
                        return;
                    }
                    if (!allowedTags.has(element.tagName)) {
                        element.replaceWith(...element.childNodes);
                        return;
                    }
                    [...element.attributes].forEach((attribute) => {
                        if (element.tagName !== 'A' || !['href', 'target'].includes(attribute.name.toLowerCase())) {
                            element.removeAttribute(attribute.name);
                        }
                    });
                    if (element.tagName === 'A') {
                        const href = element.getAttribute('href') || '';
                        if (!/^(https?:|mailto:|tel:)/i.test(href)) element.removeAttribute('href');
                        element.setAttribute('rel', 'noopener noreferrer');
                    }
                });
                return template.innerHTML;
            };
            const articleContent = field('content');
            const content = document.querySelector('#blog-detail-content');
            content.innerHTML = /<\/?[a-z][\s\S]*>/i.test(articleContent)
                ? safeRichText(articleContent)
                : escapeHtml(articleContent).replace(/\r?\n/g, '<br>');
            if (image) {
                const cover = document.querySelector('#blog-detail-cover');
                const imageElement = document.createElement('img');
                imageElement.src = image;
                imageElement.alt = field('title');
                cover.append(imageElement);
            }
        })();
    </script>
</body>
</html>
