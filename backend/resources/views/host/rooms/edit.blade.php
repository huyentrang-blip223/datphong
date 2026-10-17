@extends('layouts.app')
@section('title', 'Sửa Phòng — ' . $room->name . ' | ĐT-07')
@section('content')

<div class="page-heading">
    <a href="{{ route('host.rooms.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại danh sách phòng
    </a>
    <h1>Sửa phòng</h1>
    <p>Cập nhật tiện nghi, sức chứa và giá bán cho phòng {{ $room->name }}.</p>
</div>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <form method="post" action="{{ route('host.rooms.update', $room) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('host.rooms._form')
    </form>
</div>

@endsection
