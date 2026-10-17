@extends('layouts.app')
@section('title', 'Dashboard Người Bán OCOP | ĐT-07')
@section('content')

<section class="page-heading">
    <span class="eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Khu vực người bán
    </span>
    <h1>Dashboard sản phẩm địa phương</h1>
    <p>Theo dõi gian hàng, tồn kho và đơn hàng liên quan đến sản phẩm của bạn.</p>
</section>

<div class="stats-grid">
    <div class="metric-card">
        <span>{{ $productCount }}</span>
        <p>Tổng sản phẩm</p>
    </div>
    <div class="metric-card">
        <span style="color: var(--success);">{{ $publishedCount }}</span>
        <p>Đang hiển thị</p>
    </div>
    <div class="metric-card">
        <span>{{ $orderCount }}</span>
        <p>Đơn hàng liên quan</p>
    </div>
    <div class="metric-card" style="{{ $lowStockCount > 0 ? 'border-color: var(--danger);' : '' }}">
        <span style="{{ $lowStockCount > 0 ? 'color: var(--danger);' : '' }}">{{ $lowStockCount }}</span>
        <p>Sắp hết hàng</p>
    </div>
</div>

<div class="card" style="padding: 24px; margin-top: 24px; border-radius: var(--radius-md);">
    <h3 style="font-size: 1.15rem; margin-bottom: 14px;">Quản lý gian hàng</h3>
    <div class="action-strip">
        <a class="btn" href="{{ route('seller.products.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            Quản lý sản phẩm
        </a>
        <a class="btn btn-secondary" href="{{ route('seller.products.create') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Thêm sản phẩm
        </a>
        <a class="btn btn-secondary" href="{{ route('seller.orders.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Xử lý đơn hàng
        </a>
    </div>
</div>

@endsection
