@extends('admin.layouts.app')

@section('title', 'จัดการหน้าบ้าน')
@section('heading', 'จัดการเว็บไซต์')

@push('styles')
    <style>
        .content-header { display: none; }
        .content-wrapper > .content { padding-top: 1.5rem; }
        .content-hub { max-width: none; width: 100%; }
        .content-hub-hero { display: flex; align-items: flex-end; justify-content: space-between; gap: 1.25rem; margin-bottom: 1.35rem; }
        .content-hub-kicker { margin: 0 0 .4rem; color: #07877d; font-size: .68rem; font-weight: 850; letter-spacing: .14em; }
        .content-hub-hero h2 { margin: 0; color: #173f45; font-size: 1.55rem; font-weight: 800; letter-spacing: -.035em; }
        .content-hub-hero p { margin: .4rem 0 0; color: #71888c; font-size: .83rem; }
        .content-hub-view-site { display: inline-flex; align-items: center; gap: .5rem; padding: .65rem .9rem; border: 1px solid #cfe5e0; border-radius: 9px; background: #fff; color: #087d76; font-size: .76rem; font-weight: 800; white-space: nowrap; }
        .content-hub-view-site:hover { border-color: #087d76; background: #087d76; color: #fff; text-decoration: none; }
        .content-hub-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: .8rem; margin-bottom: 1.1rem; }
        .content-hub-metric { position: relative; overflow: hidden; min-height: 96px; padding: 1rem 1.05rem; border: 1px solid #e1ecea; border-radius: 13px; background: #fff; box-shadow: 0 5px 18px rgba(21, 64, 68, .045); }
        .content-hub-metric i { position: absolute; right: 1rem; top: 1rem; font-size: 1.15rem; opacity: .18; }
        .content-hub-metric small { display: block; color: #70898d; font-size: .68rem; font-weight: 800; }
        .content-hub-metric strong { display: block; margin-top: .32rem; color: #143e45; font-size: 1.65rem; letter-spacing: -.055em; }
        .content-hub-metric.teal i, .content-hub-metric.teal strong { color: #07877d; }.content-hub-metric.amber i, .content-hub-metric.amber strong { color: #aa7415; }.content-hub-metric.blue i, .content-hub-metric.blue strong { color: #277bb1; }.content-hub-metric.purple i, .content-hub-metric.purple strong { color: #7961a9; }
        .content-hub-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .9rem; }
        .content-hub-card { display: flex; flex-direction: column; min-height: 255px; padding: 1.25rem; border: 1px solid #dfebe9; border-radius: 15px; background: #fff; box-shadow: 0 6px 20px rgba(21, 64, 68, .05); }
        .content-hub-card-top { display: flex; align-items: flex-start; gap: .8rem; }.content-hub-icon { display: grid; flex: 0 0 41px; width: 41px; height: 41px; place-items: center; border-radius: 12px; font-size: 1rem; }.content-hub-icon.teal { background: #e4f8f4; color: #07877d; }.content-hub-icon.amber { background: #fff5df; color: #aa7415; }.content-hub-icon.blue { background: #eaf4fc; color: #277bb1; }.content-hub-icon.purple { background: #f2edfb; color: #7961a9; }
        .content-hub-card small { color: #70898d; font-size: .64rem; font-weight: 850; letter-spacing: .12em; }.content-hub-card h3 { margin: .16rem 0 0; color: #173f45; font-size: 1rem; font-weight: 800; }.content-hub-card > p { margin: .95rem 0 1rem; color: #71898d; font-size: .77rem; line-height: 1.55; }
        .content-hub-links { margin-top: auto; border-top: 1px solid #edf3f2; }.content-hub-link { display: flex; align-items: center; gap: .6rem; padding: .75rem 0; color: #21595d; font-size: .77rem; font-weight: 750; }.content-hub-link + .content-hub-link { border-top: 1px solid #edf3f2; }.content-hub-link:hover { color: #07877d; text-decoration: none; }.content-hub-link b { display: grid; min-width: 24px; height: 24px; place-items: center; border-radius: 7px; background: #f0f7f6; color: #087d76; font-size: .67rem; }.content-hub-link i { margin-left: auto; color: #0c9d8e; font-size: .7rem; }
        .content-hub-note { display: flex; align-items: flex-start; gap: .65rem; margin-top: 1rem; padding: .85rem 1rem; border: 1px solid #d5ebe6; border-radius: 11px; background: #effaf7; color: #477075; font-size: .76rem; line-height: 1.5; }.content-hub-note i { margin-top: .1rem; color: #0a998a; }
        @media (max-width: 991px) { .content-hub-summary { grid-template-columns: repeat(2, 1fr); }.content-hub-grid { grid-template-columns: 1fr; }.content-hub-card { min-height: auto; } }
        @media (max-width: 575px) { .content-hub-hero { align-items: flex-start; flex-direction: column; }.content-hub-summary { grid-template-columns: 1fr 1fr; gap: .6rem; }.content-hub-metric { min-height: 86px; } }
        /* Content workspace redesign */
        .content-hub-v2 { padding-bottom: 2rem; }
        .content-hub-v2 .content-hub-hero { position: relative; overflow: hidden; align-items: center; min-height: 170px; margin-bottom: 1.25rem; padding: 2rem; border-radius: 22px; background: linear-gradient(125deg, #053b43, #087b78 62%, #10aa94); box-shadow: 0 18px 40px rgba(5, 65, 69, .18); }
        .content-hub-v2 .content-hub-hero::after { position: absolute; right: -42px; bottom: -88px; width: 260px; height: 260px; border: 44px solid rgba(173,255,239,.13); border-radius: 50%; content: ''; }
        .content-hub-v2 .content-hub-kicker { color: #8af3df; }.content-hub-v2 .content-hub-hero h2 { color: #fff; font-size: clamp(1.55rem, 2.7vw, 2.25rem); }.content-hub-v2 .content-hub-hero p { color: #d6f5ee; font-size: .88rem; }
        .content-hub-v2 .content-hub-view-site { position: relative; z-index: 1; border: 0; background: #fff; box-shadow: 0 9px 20px rgba(0, 29, 34, .16); }.content-hub-v2 .content-hub-view-site:hover { background: #d9fff6; color: #076c66; }
        .content-hub-v2 .content-hub-summary { gap: 1rem; }.content-hub-v2 .content-hub-metric { min-height: 112px; padding: 1.15rem 1.2rem; border: 0; border-radius: 17px; box-shadow: 0 10px 25px rgba(16, 62, 67, .07); }.content-hub-v2 .content-hub-metric::before { position: absolute; top: 0; left: 0; width: 4px; height: 100%; border-radius: 9px 0 0 9px; background: currentColor; content: ''; }.content-hub-v2 .content-hub-metric strong { font-size: 1.8rem; }
        .content-hub-v2 .content-hub-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }.content-hub-v2 .content-hub-card { position: relative; min-height: 240px; padding: 1.35rem; border: 0; border-radius: 18px; box-shadow: 0 10px 26px rgba(16, 62, 67, .06); transition: transform .2s ease, box-shadow .2s ease; }.content-hub-v2 .content-hub-card:hover { transform: translateY(-4px); box-shadow: 0 18px 34px rgba(16, 62, 67, .12); }.content-hub-v2 .content-hub-card::after { position: absolute; top: 1.15rem; right: 1.15rem; color: #dcefed; font-family: 'Font Awesome 5 Free'; font-size: 1.25rem; font-weight: 900; content: '\\f0c9'; }.content-hub-v2 .content-hub-card h3 { font-size: 1.08rem; }.content-hub-v2 .content-hub-card > p { min-height: 38px; }.content-hub-v2 .content-hub-link { padding: .82rem 0; }
        .content-hub-v2 .content-hub-note { border: 0; border-radius: 15px; background: #e3f8f3; }.content-hub-v2 .content-hub-note span { color: #2d6567; }
        @media (max-width: 991px) { .content-hub-v2 .content-hub-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 575px) { .content-hub-v2 .content-hub-hero { min-height: 0; padding: 1.4rem; }.content-hub-v2 .content-hub-grid { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    <div class="content-hub content-hub-v2">
        <div class="content-hub-hero">
            <div><p class="content-hub-kicker">WEBSITE / CONTENT</p><h2>จัดการข้อมูลหน้าบ้าน</h2><p>อัปเดตโครงการ บริการ และข้อมูลผู้สนใจจากที่เดียว</p></div>
            <a class="content-hub-view-site" href="{{ route('home') }}" target="_blank"><i class="fas fa-external-link-alt"></i>ดูหน้าบ้าน</a>
        </div>
        <div class="content-hub-summary">
            @foreach ($summary as $item)
                <article class="content-hub-metric {{ $item['tone'] }}"><i class="{{ $item['icon'] }}"></i><small>{{ $item['label'] }}</small><strong>{{ $item['value'] }}</strong></article>
            @endforeach
        </div>
        <div class="content-hub-grid">
            @foreach ($contentGroups as $group)
                <article class="content-hub-card"><div class="content-hub-card-top"><span class="content-hub-icon {{ $group['tone'] }}"><i class="{{ $group['icon'] }}"></i></span><div><small>{{ $group['eyebrow'] }}</small><h3>{{ $group['title'] }}</h3></div></div><p>{{ $group['description'] }}</p><div class="content-hub-links">@foreach ($group['items'] as $item)<a class="content-hub-link" href="{{ $item['route'] }}"><b>{{ $item['count'] }}</b>{{ $item['label'] }}<i class="fas fa-arrow-right"></i></a>@endforeach</div></article>
            @endforeach
        </div>
        <div class="content-hub-note"><i class="fas fa-info-circle"></i><span>ส่วน Banner, About us, Header และ Footer จะต่อยอดจากศูนย์นี้ในลำดับถัดไป โดยใช้รูปแบบเดียวกัน: แก้ไขเนื้อหา จัดลำดับ และกำหนดสถานะเผยแพร่</span></div>
    </div>
@endsection
