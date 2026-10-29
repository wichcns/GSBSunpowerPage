@extends('admin.layouts.app')

@section('title', 'แก้ไข Banner หน้าแรก')
@section('heading', 'แก้ไข Banner หน้าแรก')

@push('styles')
    <style>
        .home-hero-editor { max-width: 1320px; }.home-hero-editor-top { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-bottom:1rem; }.home-hero-editor-top p { margin:.35rem 0 0; color:#70898d; font-size:.8rem; }.home-hero-editor-back { color:#07877d; font-size:.76rem; font-weight:800; }.home-hero-editor-back:hover { color:#056d66; text-decoration:none; }.home-hero-editor-card { border:0; }.home-hero-editor-card .card-body { padding:1.25rem; }.home-hero-tabs { gap:.4rem; margin-bottom:1rem; border-bottom:1px solid #e4efed; }.home-hero-tabs .nav-link { border:0; border-bottom:2px solid transparent; border-radius:0; color:#6d8589; font-size:.78rem; font-weight:800; }.home-hero-tabs .nav-link.active { border-color:#079c91; color:#087d76; background:transparent; }.home-hero-fields { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.8rem 1rem; }.home-hero-fields .form-group { margin:0; }.home-hero-fields .full { grid-column:1 / -1; }.home-hero-fields textarea { min-height:92px; resize:vertical; }.home-hero-preview { position:sticky; top:1.2rem; min-height:380px; padding:2rem; border-radius:14px; background:linear-gradient(115deg,rgba(3,51,58,.9),rgba(8,112,106,.48)),url('{{ asset('hero-solar-thailand.png') }}') center/cover; color:#fff; display:flex; flex-direction:column; justify-content:center; }.home-hero-preview small { color:#8be6db; font-size:.68rem; font-weight:800; letter-spacing:.1em; }.home-hero-preview h2 { max-width:11ch; margin:.7rem 0; color:#fff; font-size:2rem; font-weight:800; line-height:1.12; }.home-hero-preview h2 em { color:#84e6d9; }.home-hero-preview p { max-width:31rem; color:#e4fbf8; font-size:.83rem; line-height:1.55; }.home-hero-preview span { display:inline-flex; align-self:flex-start; margin-top:.5rem; padding:.65rem .9rem; border-radius:999px; background:#08aa9e; color:#fff; font-size:.76rem; font-weight:800; }.home-hero-actions { display:flex; justify-content:space-between; gap:.75rem; margin-top:1.25rem; }.home-hero-actions .btn { min-width:110px; }@media(max-width:991px){.home-hero-preview{position:static;min-height:300px;margin-top:1rem}}@media(max-width:575px){.home-hero-editor-top{align-items:flex-start;flex-direction:column}.home-hero-fields{grid-template-columns:1fr}.home-hero-fields .full{grid-column:auto}.home-hero-actions{flex-direction:column-reverse}.home-hero-actions .btn{width:100%}}
        .home-hero-editor > form > .row > .col-lg-7, .home-hero-editor > form > .row > .col-lg-5 { flex: 0 0 100%; max-width: 100%; }
        .home-hero-preview { position: relative; isolation: isolate; min-height: 490px; margin-top: 1rem; padding: 8.5rem 2rem 2rem; overflow: hidden; background: #063b42; }
        .home-hero-preview::before { position: absolute; inset: 0; z-index: -1; background: linear-gradient(90deg, rgba(2, 35, 40, .88), rgba(2, 38, 43, .28)); content: ''; }
        .home-hero-preview > video { position: absolute; inset: 0; z-index: -2; width: 100%; height: 100%; object-fit: cover; }
        .home-hero-preview-chrome { position: absolute; top: 0; right: 1.55rem; left: 1.55rem; display: flex; align-items: center; justify-content: space-between; padding: 1.05rem 0; border-bottom: 1px solid rgba(255,255,255,.45); color: #fff; }.home-hero-preview-chrome b { font-size: 1.08rem; letter-spacing: .02em; }.home-hero-preview-chrome b span { color: #55ddd5; }.home-hero-preview-chrome div { display: flex; align-items: center; gap: 1rem; font-size: .72rem; font-weight: 800; }.home-hero-preview-chrome div span:last-child { padding: .6rem .85rem; border-radius: 99px; background: #09b9ac; color: #053b42; }
        .home-hero-preview small, .home-hero-preview h2, .home-hero-preview p, .home-hero-preview > span { position: relative; }.home-hero-preview h2 { max-width: 760px; font-size: clamp(2rem, 4vw, 3.6rem); }.home-hero-preview p { max-width: 500px; }.home-hero-preview > span { background: #09b9ac; color: #053b42; }
        @media(max-width:575px) { .home-hero-preview { min-height: 420px; padding: 6.5rem 1rem 1rem; }.home-hero-preview-chrome { right: 1rem; left: 1rem; }.home-hero-preview-chrome div { gap: .45rem; font-size: .58rem; }.home-hero-preview-chrome b { font-size: .85rem; } }
        .home-hero-editor > form > .row > .col-lg-5 { display: none; }
        .home-hero-actual-preview { margin-top: 1rem; overflow: hidden; border: 1px solid #dce9e7; border-radius: 15px; background: #fff; box-shadow: 0 10px 28px rgba(17, 70, 77, .07); }
        .home-hero-actual-preview__heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1rem; border-bottom: 1px solid #e4efed; color: #31595e; font-size: .76rem; font-weight: 800; }.home-hero-actual-preview__heading span { color: #738d90; font-weight: 650; }
        .home-hero-actual-preview iframe { display: block; width: 100%; height: 760px; border: 0; background: #063b42; }
        @media(max-width:575px) { .home-hero-actual-preview iframe { height: 600px; }.home-hero-actual-preview__heading span { display: none; } }
    </style>
@endpush

@section('content')
    <div class="home-hero-editor">
        <div class="home-hero-editor-top"><div><a class="home-hero-editor-back" href="{{ route('admin.dashboard') }}"><i class="fas fa-arrow-left mr-1"></i>กลับไปจัดการเว็บไซต์</a><p>แก้ไขข้อความที่แสดงใน Banner ส่วนบนสุดของหน้าแรก</p></div><a class="btn btn-outline-secondary" href="{{ route('home') }}" target="_blank"><i class="fas fa-external-link-alt mr-1"></i>ดูหน้าบ้าน</a></div>
        <form method="post" action="{{ route('admin.home-content.hero.update') }}">@csrf @method('PUT')
            <div class="row"><div class="col-lg-7"><div class="card home-hero-editor-card"><div class="card-body"><ul class="nav home-hero-tabs" role="tablist">@foreach (['en' => 'English', 'th' => 'ไทย', 'zh' => '中文'] as $locale => $label)<li class="nav-item"><a class="nav-link {{ $locale === 'en' ? 'active' : '' }}" data-toggle="tab" href="#hero-{{ $locale }}" role="tab">{{ $label }}</a></li>@endforeach</ul><div class="tab-content">@foreach (['en' => 'English', 'th' => 'ไทย', 'zh' => '中文'] as $locale => $label)<div class="tab-pane fade {{ $locale === 'en' ? 'show active' : '' }}" id="hero-{{ $locale }}" role="tabpanel"><div class="home-hero-fields"><div class="form-group full"><label>ข้อความนำ</label><input class="form-control" name="content[{{ $locale }}][label]" value="{{ old('content.'.$locale.'.label', $homeHero[$locale]['label']) }}"></div><div class="form-group"><label>หัวข้อหลัก</label><input class="form-control" name="content[{{ $locale }}][heading]" value="{{ old('content.'.$locale.'.heading', $homeHero[$locale]['heading']) }}"></div><div class="form-group"><label>ข้อความเน้น</label><input class="form-control" name="content[{{ $locale }}][emphasis]" value="{{ old('content.'.$locale.'.emphasis', $homeHero[$locale]['emphasis']) }}"></div><div class="form-group full"><label>คำอธิบาย</label><textarea class="form-control" name="content[{{ $locale }}][lead]">{{ old('content.'.$locale.'.lead', $homeHero[$locale]['lead']) }}</textarea></div><div class="form-group"><label>ข้อความก่อนเลือกบริการ</label><input class="form-control" name="content[{{ $locale }}][prompt]" value="{{ old('content.'.$locale.'.prompt', $homeHero[$locale]['prompt']) }}"></div><div class="form-group"><label>ข้อความปุ่มปรึกษา</label><input class="form-control" name="content[{{ $locale }}][consult]" value="{{ old('content.'.$locale.'.consult', $homeHero[$locale]['consult']) }}"></div></div></div>@endforeach</div></div></div></div><div class="col-lg-5"><aside class="home-hero-preview"><video autoplay muted loop playsinline preload="metadata" poster="{{ asset('hero-solar-thailand.png') }}"><source src="{{ asset('video/Ev&Solar-v5-20261005.mp4') }}" type="video/mp4"></video><div class="home-hero-preview-chrome"><b>GSB <span>SUNPOWER</span></b><div><span>About us</span><span>Solutions</span><span>Contact us</span></div></div><small class="home-hero-preview-label">{{ $homeHero['en']['label'] }}</small><h2><span class="home-hero-preview-heading">{{ $homeHero['en']['heading'] }}</span><br><em class="home-hero-preview-emphasis">{{ $homeHero['en']['emphasis'] }}</em></h2><p class="home-hero-preview-lead">{{ $homeHero['en']['lead'] }}</p><span class="home-hero-preview-consult">{{ $homeHero['en']['consult'] }}</span></aside></div></div>
            <div class="home-hero-actual-preview"><div class="home-hero-actual-preview__heading"><div><i class="fas fa-desktop mr-1"></i>ตัวอย่างจากหน้า Home จริง</div><span>สัดส่วน, วิดีโอ และ CSS ใช้ชุดเดียวกับหน้าบ้าน</span></div><iframe id="home-hero-live-preview" src="{{ route('home') }}" title="ตัวอย่าง Banner หน้าแรก"></iframe></div>
            <div class="home-hero-actions"><a class="btn btn-light" href="{{ route('admin.dashboard') }}">ยกเลิก</a><button class="btn btn-teal" type="submit"><i class="fas fa-save mr-1"></i>บันทึกข้อความ Banner</button></div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const preview = (locale) => {
                const panel = document.getElementById(`hero-${locale}`);
                if (!panel) return;
                const value = (field) => panel.querySelector(`[name="content[${locale}][${field}]"]`)?.value || '';
                document.querySelector('.home-hero-preview-label').textContent = value('label');
                document.querySelector('.home-hero-preview-heading').textContent = value('heading');
                document.querySelector('.home-hero-preview-emphasis').textContent = value('emphasis');
                document.querySelector('.home-hero-preview-lead').textContent = value('lead');
                document.querySelector('.home-hero-preview-consult').textContent = value('consult');
            };
            document.querySelectorAll('.home-hero-tabs a').forEach((tab) => tab.addEventListener('shown.bs.tab', () => preview(tab.getAttribute('href').replace('#hero-', ''))));
            document.querySelectorAll('.home-hero-fields input, .home-hero-fields textarea').forEach((field) => field.addEventListener('input', () => preview(field.closest('.tab-pane').id.replace('hero-', ''))));
            preview('en');

            const frame = document.getElementById('home-hero-live-preview');
            let activeLocale = 'en';
            const syncLivePreview = (locale) => {
                const documentInFrame = frame.contentDocument;
                const panel = document.getElementById(`hero-${locale}`);
                if (!documentInFrame || !panel) return;
                const value = (field) => panel.querySelector(`[name="content[${locale}][${field}]"]`)?.value || '';
                const label = documentInFrame.querySelector('.gx-hero .gx-label');
                const heading = documentInFrame.querySelector('.gx-hero h1');
                const lead = documentInFrame.querySelector('.gx-hero .gx-lead');
                const prompt = documentInFrame.querySelector('.gx-hero .gx-prompt');
                const consult = documentInFrame.querySelector('[data-gx-consult]');
                if (!label || !heading || !lead || !prompt || !consult) return;
                label.textContent = value('label');
                heading.replaceChildren(documentInFrame.createTextNode(value('heading')), documentInFrame.createElement('br'), Object.assign(documentInFrame.createElement('em'), { textContent: value('emphasis') }));
                lead.textContent = value('lead');
                prompt.textContent = value('prompt');
                consult.textContent = value('consult');
            };
            frame.addEventListener('load', () => {
                const documentInFrame = frame.contentDocument;
                documentInFrame?.querySelectorAll('main > :not(.gx-hero), footer').forEach((element) => { element.style.display = 'none'; });
                if (documentInFrame?.body) documentInFrame.body.style.overflow = 'hidden';
                syncLivePreview(activeLocale);
            });
            document.querySelectorAll('.home-hero-tabs a').forEach((tab) => tab.addEventListener('shown.bs.tab', () => { activeLocale = tab.getAttribute('href').replace('#hero-', ''); syncLivePreview(activeLocale); }));
            document.querySelectorAll('.home-hero-fields input, .home-hero-fields textarea').forEach((field) => field.addEventListener('input', () => syncLivePreview(field.closest('.tab-pane').id.replace('hero-', ''))));

            const previewVideo = document.querySelector('.home-hero-preview video');
            if (previewVideo) {
                const previewPlaylist = [
                    "{{ asset('video/Ev&Solar-v5-20261005.mp4') }}",
                    "{{ asset('video/Solar-v1-20261005.mp4') }}",
                ];
                let previewIndex = 0;
                previewVideo.removeAttribute('loop');
                previewVideo.addEventListener('ended', () => {
                    previewIndex = (previewIndex + 1) % previewPlaylist.length;
                    previewVideo.src = previewPlaylist[previewIndex];
                    previewVideo.load();
                    previewVideo.play().catch(() => {});
                });
            }
        })();
    </script>
@endpush
