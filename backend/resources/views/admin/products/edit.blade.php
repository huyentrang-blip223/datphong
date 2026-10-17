@extends('layouts.app')
@section('title', 'Admin - Sửa sản phẩm')
@section('content')

<div class="page-heading">
    <a href="{{ route('admin.products.index') }}" class="section-link" style="margin-bottom: 8px;">Quay lại danh sách sản phẩm</a>
    <span class="eyebrow">Khu vực quản trị</span>
    <h1>Sửa sản phẩm</h1>
    <p>Cập nhật thông tin, người bán và ảnh đại diện cho sản phẩm {{ $product->name }}.</p>
</div>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @method('put')
        @php($productBackRoute = route('admin.products.index'))
        @include('seller.products._form')
    </form>
</div>

@endsection
