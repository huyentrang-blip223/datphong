@extends('layouts.app')
@section('title', 'Sửa Trải Nghiệm — ' . $experience->title . ' | ĐT-07')
@section('content')

<section class="page-heading">
    <a href="{{ route('host.experiences.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại danh sách trải nghiệm
    </a>
    <p class="eyebrow">Chủ homestay</p>
    <h1>Sửa trải nghiệm</h1>
</section>

<div class="card" style="padding: 28px; border-radius: var(--radius-md); margin-bottom: 24px;">
    <form method="post" action="{{ route('host.experiences.update', $experience) }}">
        @method('put')
        @include('host.experiences._form')
    </form>
</div>

<div class="card" style="padding: 20px; border-color: color-mix(in srgb, var(--danger) 30%, var(--border)); display: flex; justify-content: space-between; align-items: center; border-radius: var(--radius-md);">
    <div>
        <strong style="color: var(--danger);">Xóa trải nghiệm này</strong>
        <p style="margin: 2px 0 0; font-size: 0.88rem; color: var(--muted);">Chỉ có thể xóa khi chưa có du khách nào đặt chỗ.</p>
    </div>
    <form method="post" action="{{ route('host.experiences.destroy', $experience) }}">
        @csrf
        @method('delete')
        <button class="btn btn-secondary" type="submit" style="color: var(--danger) !important; border-color: var(--danger) !important;">
            Xóa nếu chưa có đặt chỗ
        </button>
    </form>
</div>

@endsection
