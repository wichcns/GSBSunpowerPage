@extends('crm.layouts.admin')

@section('content')
    <section class="crm-page-intro">
        <p class="crm-eyebrow">{{ $section }}</p>
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
    </section>
    <section class="crm-welcome-card">
        <span class="crm-welcome-card__spark" aria-hidden="true">ϟ</span>
        <div><h2>กำลังเตรียมโมดูลนี้</h2><p>หน้าจอนี้จะเชื่อมกับข้อมูลจริงหลังจบการออกแบบข้อมูลและการนำเข้าข้อมูลปัจจุบัน</p></div>
    </section>
@endsection
