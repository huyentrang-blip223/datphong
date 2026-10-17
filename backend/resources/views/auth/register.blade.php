@extends('layouts.app')
@section('title', 'Đăng ký tài khoản | ĐT-07 Homestay Cộng Đồng')
@section('content')

<div class="card auth-card" style="padding: 36px 32px; border-radius: var(--radius-lg);">
    <div style="text-align: center; margin-bottom: 20px;">
        <div class="brand-mark" style="margin: 0 auto 12px; width: 50px; height: 50px; font-size: 1.3rem;">ĐT</div>
        <span class="eyebrow">Tạo tài khoản mới</span>
        <h1 style="font-size: 1.85rem; margin-top: 4px;">Đăng ký</h1>
        <p class="muted" style="font-size: 0.9rem;">
            Đăng ký công khai chỉ tạo tài khoản khách du lịch. Quyền host, seller và admin cần được cấp qua quản trị.
        </p>
    </div>

    <form method="post" action="{{ route('register.submit') }}">
        @csrf
        <div>
            <label>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Họ và tên
            </label>
            <input name="full_name" value="{{ old('full_name') }}" placeholder="Nguyễn Văn A" required maxlength="120" autocomplete="name">
        </div>

        <div>
            <label>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Email
            </label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required maxlength="180" autocomplete="email">
        </div>

        <div>
            <label>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Số điện thoại
            </label>
            <input name="phone" value="{{ old('phone') }}" placeholder="0901234567" maxlength="30" autocomplete="tel">
        </div>

        <div class="alert" style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; padding: 10px 14px; margin: 10px 0 14px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>Vai trò sau khi đăng ký: Khách du lịch</span>
        </div>

        <div>
            <label>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Mật khẩu
            </label>
            <input type="password" name="password" placeholder="Tối thiểu 8 ký tự" required minlength="8" maxlength="128" autocomplete="new-password">
        </div>

        <div>
            <label>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><polyline points="20 6 9 17 4 12"/></svg>
                Nhập lại mật khẩu
            </label>
            <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu" required minlength="8" maxlength="128" autocomplete="new-password">
        </div>

        <button class="btn btn-accent" type="submit" style="width: 100%; height: 44px; font-size: 1rem; margin-top: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            Tạo tài khoản
        </button>
    </form>

    <div style="text-align: center; margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--border-light);">
        <p class="muted auth-switch" style="margin: 0; font-size: 0.92rem;">
            Đã có tài khoản? <a href="{{ route('login') }}" style="font-weight: 700;">Đăng nhập</a>.
        </p>
    </div>
</div>

@endsection
