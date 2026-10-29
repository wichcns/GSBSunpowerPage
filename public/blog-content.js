(() => {
    const posts = Array.isArray(window.gsbBlogPosts) ? window.gsbBlogPosts : [];
    const videos = Array.isArray(window.gsbBlogVideos) ? window.gsbBlogVideos : [];
    const mediaTemplate = window.gsbBlogMediaTemplate || '';
    const locale = (localStorage.getItem('gsb-language') || 'en').toLowerCase();
    const detailUrl = (id) => new URL(`/blog/${encodeURIComponent(id)}`, window.location.origin).toString();
    const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[character]));
    const localField = (item, field) => item[`${field}_${locale}`]
        || item[`${field}_en`]
        || item[`${field}_th`]
        || item[`${field}_zh`]
        || '';
    const mediaUrl = (path) => path
        ? mediaTemplate.replace('__BLOG_MEDIA_PATH__', String(path).split('/').map(encodeURIComponent).join('/'))
        : '';
    const localizedCover = (item) => item[`cover_image_${locale}`] || item.cover_image || '';
    const formattedDate = (value) => {
        const parsed = new Date(value);
        if (Number.isNaN(parsed.getTime())) return '';

        const language = locale === 'th' ? 'th-TH' : locale === 'zh' ? 'zh-CN' : 'en-GB';
        return new Intl.DateTimeFormat(language, {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(parsed).toUpperCase();
    };
    const meta = (item) => [
        String(item.category || '').replace(/-/g, ' ').toUpperCase(),
        formattedDate(item.published_at),
    ].filter(Boolean).join(' · ');
    const imageMarkup = (path, alt) => {
        const source = mediaUrl(path);
        return source ? `<img src="${escapeHtml(source)}" alt="${escapeHtml(alt)}">` : '';
    };

    const articleShell = document.querySelector('.blog-section .blog-shell');
    const emptyMessage = document.querySelector('#blog-empty');

    if (articleShell && posts.length) {
        articleShell.querySelectorAll('.blog-feature, #blog-grid').forEach((element) => element.remove());

        let articleRoot = document.querySelector('#blog-articles');
        if (!articleRoot) {
            articleRoot = document.createElement('div');
            articleRoot.id = 'blog-articles';
            articleShell.insertBefore(articleRoot, emptyMessage || null);
        }

        const [featured, ...remainingPosts] = posts;
        const featuredTitle = localField(featured, 'title');
        const cards = remainingPosts.map((post) => {
            const title = localField(post, 'title');
            return `<article class="blog-card" data-blog-category="${escapeHtml(post.category)}">
                <div class="blog-card-media">${imageMarkup(localizedCover(post), title)}</div>
                <div class="blog-card-body">
                    <p class="blog-meta">${escapeHtml(meta(post))}</p>
                    <h3>${escapeHtml(title)}</h3>
                    <p>${escapeHtml(localField(post, 'excerpt'))}</p>
                    <a class="blog-read-more" href="${escapeHtml(detailUrl(post.id))}">Read article</a>
                </div>
            </article>`;
        }).join('');

        articleRoot.innerHTML = `<article class="blog-feature" data-blog-category="${escapeHtml(featured.category)}">
                <div class="blog-feature-media">${imageMarkup(localizedCover(featured), featuredTitle)}</div>
                <div class="blog-feature-copy">
                    <p class="blog-meta">${escapeHtml(meta(featured))}</p>
                    <h2>${escapeHtml(featuredTitle)}</h2>
                    <p>${escapeHtml(localField(featured, 'excerpt'))}</p>
                    <a class="blog-read-more" href="${escapeHtml(detailUrl(featured.id))}">Read article</a>
                </div>
            </article>
            <div class="blog-grid" id="blog-grid">${cards}</div>`;
    }

    const videoGrid = document.querySelector('.blog-video-grid');
    if (videoGrid) {
        videoGrid.innerHTML = videos.map((video) => {
            const title = localField(video, 'title');
            return `<article class="blog-video-card">
                <div class="blog-video-frame">
                    ${imageMarkup(video.thumbnail_image, title)}
                    <a class="blog-play" href="${escapeHtml(video.video_url)}" target="_blank" rel="noopener" aria-label="Play ${escapeHtml(title)}">▶</a>
                </div>
                <h3>${escapeHtml(title)}</h3>
            </article>`;
        }).join('');
    }

    // Replace controls to remove the listeners that were attached to the old
    // placeholder-card implementation before this script runs.
    document.querySelectorAll('[data-blog-filter]').forEach((button) => {
        button.replaceWith(button.cloneNode(true));
    });

    const buttons = document.querySelectorAll('[data-blog-filter]');
    const applyFilter = (filter) => {
        let visible = 0;
        document.querySelectorAll('#blog-articles [data-blog-category]').forEach((article) => {
            const matches = filter === 'all' || article.dataset.blogCategory.includes(filter);
            article.hidden = !matches;
            if (matches) visible += 1;
        });
        if (emptyMessage) emptyMessage.style.display = visible ? 'none' : 'block';
    };

    buttons.forEach((button) => button.addEventListener('click', () => {
        buttons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        applyFilter(button.dataset.blogFilter);
    }));
})();
