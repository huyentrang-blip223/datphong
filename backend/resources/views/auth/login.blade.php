@extends('layouts.app')
@section('title', 'Đăng nhập | ĐT-07 Homestay Cộng Đồng')
@section('content')

<div class="card auth-card" style="padding: 36px 32px; border-radius: var(--radius-lg);">
    <div style="text-align: center; margin-bottom: 24px;">
        <div class="brand-mark" style="margin: 0 auto 12px; width: 50px; height: 50px; font-size: 1.3rem;">ĐT</div>
        <span class="eyebrow">Chào mừng trở lại</span>
        <h1 style="font-size: 1.85rem; margin-top: 4px;">Đăng nhập</h1>
        <p class="muted" style="font-size: 0.92rem;">Truy cập vào hệ thống lưu trú cộng đồng ĐT-07</p>
    </div>

    <form method="post" action="{{ route('login.submit') }}">
        @csrf
        <div style="margin-bottom: 14px;">
            <label>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Email
            </label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autocomplete="email">
        </div>

        <div style="margin-bottom: 20px;">
            <label>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Mật khẩu
            </label>
            <input type="password" name="password" placeholder="••••••••" required minlength="8" autocomplete="current-password">
        </div>

        <button class="btn" type="submit" style="width: 100%; height: 44px; font-size: 1rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Đăng nhập
        </button>
    </form>
    <p><a href="{{ route('password.request') }}">Quên mật khẩu?</a></p>

    <div style="text-align: center; margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--border-light);">
        <p class="muted auth-switch" style="margin: 0; font-size: 0.92rem;">
            Chưa có tài khoản? <a href="{{ route('register') }}" style="font-weight: 700;">Đăng ký ngay</a>.
        </p>
    </div>
</div>

@endsection
