@extends('layouts.app')
@section('title', 'Admin - Thêm phòng')
@section('content')

<div class="page-heading">
    <a href="{{ route('admin.rooms.index') }}" class="section-link" style="margin-bottom: 8px;">Quay lại danh sách phòng</a>
    <span class="eyebrow">Khu vực quản trị</span>
    <h1>Thêm phòng</h1>
    <p>Chọn homestay, nhập thông tin phòng và tải ảnh đại diện nếu có.</p>
</div>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <form method="post" action="{{ route('admin.rooms.store') }}" enctype="multipart/form-data">
        @php($roomBackRoute = route('admin.rooms.index'))
        @include('host.rooms._form')
    </form>
</div>

@endsection
