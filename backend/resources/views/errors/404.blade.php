@extends('layouts.app')
@section('title','404 — Không Tìm Thấy Trang')
@section('content')
<div class="card" style="max-width: 600px; margin: 60px auto; text-align: center; padding: 48px 32px; border-radius: var(--radius-lg);">
    <div style="width: 72px; height: 72px; border-radius: var(--radius-full); background: var(--warning-soft); color: var(--warning); display: grid; place-items: center; margin: 0 auto 20px;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
    </div>
    <span class="eyebrow" style="color: var(--warning);">Mã lỗi 404</span>
    <h1 style="font-size: 1.8rem; margin: 8px 0 12px;">Không tìm thấy trang</h1>
    <p style="color: var(--muted); font-size: 0.96rem; margin-bottom: 24px;">Trang bạn yêu cầu không tồn tại hoặc đã được di chuyển.</p>
    <a class="btn" href="{{ route('home') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Về trang chủ
    </a>
</div>
@endsection
