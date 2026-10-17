@extends('layouts.app')
@section('title', 'Dashboard Chủ Homestay | ĐT-07')
@section('content')

<section class="page-heading">
    <span class="eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        Khu vực chủ homestay
    </span>
    <h1>Dashboard chủ homestay</h1>
    <p>Quản lý cơ sở lưu trú, phòng, lịch còn phòng, giá mùa và booking thuộc homestay của bạn.</p>
</section>

<div class="stats-grid">
    <div class="metric-card">
        <span>{{ $homestayCount }}</span>
        <p>Cơ sở của tôi</p>
    </div>
    <div class="metric-card">
        <span>{{ $roomCount }}</span>
        <p>Phòng đang quản lý</p>
    </div>
    <div class="metric-card">
        <span>{{ $bookingCount }}</span>
        <p>Booking liên quan</p>
    </div>
    <div class="metric-card">
        <span>{{ $pendingCount }}</span>
        <p>Đang chờ duyệt</p>
    </div>
</div>

<div class="card" style="padding: 24px; margin-top: 24px; border-radius: var(--radius-md);">
    <h3 style="font-size: 1.15rem; margin-bottom: 14px;">Lối tắt thao tác nhanh</h3>
    <div class="action-strip">
        <a class="btn" href="{{ route('host.rooms.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
            Quản lý phòng
        </a>
        <a class="btn btn-secondary" href="{{ route('host.availability.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Lịch phòng
        </a>
        <a class="btn btn-secondary" href="{{ route('host.seasonal-prices.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Giá mùa
        </a>
        <a class="btn btn-secondary" href="{{ route('host.experiences.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
            Trải nghiệm
        </a>
        <a class="btn btn-secondary" href="{{ route('host.bookings.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            Booking liên quan
        </a>
    </div>
</div>

@endsection
