@extends('layouts.app')
@section('title', 'Sửa Homestay #' . $homestay->id . ' | ĐT-07')
@section('content')

<div class="page-heading">
    <a href="{{ route('admin.homestays.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại danh sách homestay
    </a>
    <h1>Sửa homestay #{{ $homestay->id }}</h1>
    <p>Chỉnh sửa thông tin, hồ sơ chủ cơ sở và cập nhật trạng thái duyệt của homestay.</p>
</div>

<form method="post" action="{{ route('admin.homestays.update', $homestay) }}" class="card" enctype="multipart/form-data" style="padding: 28px; border-radius: var(--radius-md);">
    @csrf
    @method('PUT')
    @include('admin.homestays._form')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <a href="{{ route('admin.homestays.index') }}" class="btn btn-secondary">Hủy</a>
        <button class="btn" type="submit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Cập nhật
        </button>
    </div>
</form>

@endsection
