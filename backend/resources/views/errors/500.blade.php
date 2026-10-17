@extends('layouts.app')
@section('title','500 — Lỗi Máy Chủ')
@section('content')
<div class="card" style="max-width: 600px; margin: 60px auto; text-align: center; padding: 48px 32px; border-radius: var(--radius-lg);">
    <div style="width: 72px; height: 72px; border-radius: var(--radius-full); background: var(--danger-soft); color: var(--danger); display: grid; place-items: center; margin: 0 auto 20px;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <span class="eyebrow" style="color: var(--danger);">Mã lỗi 500</span>
    <h1 style="font-size: 1.8rem; margin: 8px 0 12px;">Hệ thống gặp sự cố tạm thời</h1>
    <p style="color: var(--muted); font-size: 0.96rem; margin-bottom: 24px;">Đã xảy ra lỗi ngoài ý muốn trên máy chủ. Đội ngũ kỹ thuật đã được ghi nhận sự cố.</p>
    <a class="btn" href="{{ route('home') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Về trang chủ
    </a>
</div>
@endsection
