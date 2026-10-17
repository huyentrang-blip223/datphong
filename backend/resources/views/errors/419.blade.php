@extends('layouts.app')
@section('title','419 — Hết Hạn Phiên')
@section('content')
<div class="card" style="max-width: 600px; margin: 60px auto; text-align: center; padding: 48px 32px; border-radius: var(--radius-lg);">
    <div style="width: 72px; height: 72px; border-radius: var(--radius-full); background: var(--primary-soft); color: var(--primary); display: grid; place-items: center; margin: 0 auto 20px;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <span class="eyebrow">Mã lỗi 419</span>
    <h1 style="font-size: 1.8rem; margin: 8px 0 12px;">Phiên làm việc đã hết hạn</h1>
    <p style="color: var(--muted); font-size: 0.96rem; margin-bottom: 24px;">Phiên làm việc hoặc mã bảo vệ CSRF đã hết hạn. Vui lòng tải lại hoặc đăng nhập lại.</p>
    <a class="btn" href="{{ route('login') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Đăng nhập lại
    </a>
</div>
@endsection
