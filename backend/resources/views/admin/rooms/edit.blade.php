@extends('layouts.app')
@section('title', 'Admin - Sửa phòng')
@section('content')

<div class="page-heading">
    <a href="{{ route('admin.rooms.index') }}" class="section-link" style="margin-bottom: 8px;">Quay lại danh sách phòng</a>
    <span class="eyebrow">Khu vực quản trị</span>
    <h1>Sửa phòng</h1>
    <p>Cập nhật thông tin và ảnh đại diện cho phòng {{ $room->name }}.</p>
</div>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <form method="post" action="{{ route('admin.rooms.update', $room) }}" enctype="multipart/form-data">
        @method('put')
        @php($roomBackRoute = route('admin.rooms.index'))
        @include('host.rooms._form')
    </form>
</div>

@endsection
