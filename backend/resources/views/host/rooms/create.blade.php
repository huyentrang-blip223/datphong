@extends('layouts.app')
@section('title', 'Thêm Phòng Mới | ĐT-07')
@section('content')

<div class="page-heading">
    <a href="{{ route('host.rooms.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại danh sách phòng
    </a>
    <h1>Thêm phòng</h1>
    <p>Thiết lập thông tin phòng, sức chứa và giá cơ bản cho homestay của bạn.</p>
</div>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <form method="post" action="{{ route('host.rooms.store') }}" enctype="multipart/form-data">
        @include('host.rooms._form')
    </form>
</div>

@endsection
