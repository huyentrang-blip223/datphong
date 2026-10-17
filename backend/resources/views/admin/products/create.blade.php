@extends('layouts.app')
@section('title', 'Admin - Thêm sản phẩm')
@section('content')

<div class="page-heading">
    <a href="{{ route('admin.products.index') }}" class="section-link" style="margin-bottom: 8px;">Quay lại danh sách sản phẩm</a>
    <span class="eyebrow">Khu vực quản trị</span>
    <h1>Thêm sản phẩm</h1>
    <p>Chọn seller sở hữu, nhập thông tin sản phẩm và tải ảnh đại diện.</p>
</div>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <form method="post" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @php($productBackRoute = route('admin.products.index'))
        @include('seller.products._form')
    </form>
</div>

@endsection
