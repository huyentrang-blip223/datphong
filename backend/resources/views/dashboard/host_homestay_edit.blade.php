@extends('layouts.app')
@section('title', 'Sửa Homestay Của Tôi | ĐT-07')
@section('content')
<div class="page-heading">
    <a href="{{ route('host.dashboard') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại dashboard
    </a>
    <h1>Sửa homestay của tôi</h1>
</div>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <strong style="font-size: 1.25rem; color: var(--primary);">{{ $homestay->name }}</strong>
    <p style="color: var(--muted); margin-top: 8px; line-height: 1.6;">
        Trang này kiểm tra và thực thi phân quyền sở hữu (Ownership Policy). Bạn có thể truy cập các mục Quản lý phòng, Lịch phòng và Giá mùa từ menu điều hướng hoặc Dashboard.
    </p>
</div>
@endsection
