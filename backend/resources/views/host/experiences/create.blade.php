@extends('layouts.app')
@section('title', 'Thêm Trải Nghiệm Mới | ĐT-07')
@section('content')

<section class="page-heading">
    <a href="{{ route('host.experiences.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại danh sách trải nghiệm
    </a>
    <p class="eyebrow">Chủ homestay</p>
    <h1>Thêm trải nghiệm</h1>
</section>

<div class="card" style="padding: 28px; border-radius: var(--radius-md);">
    <form method="post" action="{{ route('host.experiences.store') }}">
        @include('host.experiences._form')
    </form>
</div>

@endsection
