@php
    $localizedCovers = [];
    foreach (['th', 'en', 'zh'] as $locale) {
        $localizedCovers[$locale] = $post->{'cover_image_'.$locale} ?: $post->cover_image;
    }
@endphp
@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/summernote/summernote-bs4.min.css') }}">
    <style>
        .note-editor.note-frame { border: 1px solid #cddfdd; border-radius: 11px; overflow: hidden; }
        .note-editor.note-frame .note-toolbar { background: #f5fbfa; border-bottom-color: #dceae7; }
        .note-editor.note-frame .note-editing-area .note-editable { min-height: 270px; color: #173f45; line-height: 1.75; }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('vendor/adminlte/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/plugins/summernote/lang/summernote-th-TH.min.js') }}"></script>
@endpush

<script>
    (() => {
        const addLocalizedImageFields = () => {
            const form = document.querySelector('form[action*="/blog/posts"]');
            if (!form || form.dataset.localizedBlogImagesReady) return;
            form.dataset.localizedBlogImagesReady = 'true';

            const imagePaths = @json($localizedCovers);
            const mediaTemplate = @json(route('media.public', ['path' => '__BLOG_MEDIA_PATH__']));
            const locales = { th: 'ไทย', en: 'English', zh: '中文' };
            const legacyInput = form.querySelector('[name="cover_image"]');

            // The original one-image field is retained only for old records as a
            // fallback. New images are added inside the matching language tab.
            legacyInput?.closest('.form-group')?.classList.add('d-none');

            Object.entries(locales).forEach(([locale, label]) => {
                const panel = document.querySelector('#' + locale);
                const titleField = panel?.querySelector('[name="title_' + locale + '"]')?.closest('.form-group');
                if (!panel || !titleField) return;

                const path = imagePaths[locale];
                const source = path
                    ? mediaTemplate.replace('__BLOG_MEDIA_PATH__', path.split('/').map(encodeURIComponent).join('/'))
                    : '';
                const preview = source
                    ? '<div class="mb-2"><img class="img-thumbnail" style="max-height:150px" src="' + source + '" alt="ภาพปก ' + label + '"></div>'
                    : '';

                titleField.insertAdjacentHTML('beforebegin',
                    '<div class="form-group">' +
                        '<label>ภาพปกสำหรับภาษา ' + label + '</label>' +
                        preview +
                        '<input type="file" class="form-control-file" name="cover_image_' + locale + '" accept="image/*">' +
                        '<small class="form-text text-muted">ขนาดแสดงผล 1672 × 941 px · ไฟล์ไม่เกิน 5 MB · หากยังไม่มีภาพภาษานี้ จะใช้ภาพเดิมเป็นภาพสำรอง</small>' +
                    '</div>'
                );

                const excerptLabel = panel.querySelector('[name="excerpt_' + locale + '"]')?.closest('.form-group')?.querySelector('label');
                const contentInput = panel.querySelector('[name="content_' + locale + '"]');
                const contentLabel = contentInput?.closest('.form-group')?.querySelector('label');
                const titleInput = panel.querySelector('[name="title_' + locale + '"]');
                const titleLabel = titleInput?.closest('.form-group')?.querySelector('label');
                if (titleInput && titleInput.tagName === 'INPUT') {
                    const titleArea = document.createElement('textarea');
                    titleArea.className = titleInput.className;
                    titleArea.name = titleInput.name;
                    titleArea.rows = 3;
                    titleArea.value = titleInput.value;
                    titleArea.placeholder = 'กด Enter เพื่อกำหนดตำแหน่งขึ้นบรรทัดบนหน้า Blog';
                    titleInput.replaceWith(titleArea);
                }
                if (titleLabel) titleLabel.textContent = 'หัวข้อ / จัดบรรทัด ' + label;
                if (excerptLabel) excerptLabel.textContent = 'คำเกริ่น ' + label;
                if (contentLabel) contentLabel.textContent = 'รายละเอียดบทความ / จัดรูปแบบ ' + label;

                // Summernote keeps the underlying textarea in sync when the form is
                // submitted, so each language keeps its own independently formatted
                // article body. Intentionally omit image insertion: cover images have
                // their own upload fields and this prevents large base64 images being
                // saved into the database.
                if (contentInput && window.jQuery && window.jQuery.fn.summernote && !contentInput.dataset.richTextReady) {
                    contentInput.dataset.richTextReady = 'true';
                    window.jQuery(contentInput).summernote({
                        height: 300,
                        lang: 'th-TH',
                        placeholder: 'พิมพ์เนื้อหาบทความ และจัดย่อหน้า / ตัวหนา / รายการ / ลิงก์ ได้ตามต้องการ',
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link']],
                            ['view', ['codeview', 'help']]
                        ]
                    });
                }
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', addLocalizedImageFields);
        } else {
            addLocalizedImageFields();
        }
    })();
</script>
