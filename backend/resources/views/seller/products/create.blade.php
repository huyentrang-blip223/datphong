@extends('layouts.app')
@section('title', 'Thêm Sản Phẩm Mới — Gian Hàng Địa Phương')
@section('content')

<div class="page-heading">
    <a href="{{ route('seller.products.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại danh sách sản phẩm
    </a>
    <span class="eyebrow">Gian hàng địa phương</span>
    <h1>Thêm sản phẩm</h1>
    <p>Đăng bán sản phẩm OCOP hoặc đặc sản nông nghiệp bản địa lên sàn.</p>
</div>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <form method="post" action="{{ route('seller.products.store') }}" enctype="multipart/form-data">
        @include('seller.products._form')
    </form>
</div>

@endsection
