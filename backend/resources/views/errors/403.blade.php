@extends('layouts.app')
@section('title','403 — Không Đủ Quyền')
@section('content')
<div class="card" style="max-width: 600px; margin: 60px auto; text-align: center; padding: 48px 32px; border-radius: var(--radius-lg);">
    <div style="width: 72px; height: 72px; border-radius: var(--radius-full); background: var(--danger-soft); color: var(--danger); display: grid; place-items: center; margin: 0 auto 20px;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <span class="eyebrow" style="color: var(--danger);">Mã lỗi 403</span>
    <h1 style="font-size: 1.8rem; margin: 8px 0 12px;">Không đủ quyền truy cập</h1>
    <p style="color: var(--muted); font-size: 0.96rem; margin-bottom: 24px;">Bạn không có quyền thực hiện thao tác này hoặc truy cập trang này.</p>
    <a class="btn" href="{{ route('home') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Về trang chủ
    </a>
</div>
@endsection
