@extends('layouts.app')
@section('title', $experience->title . ' — Trải Nghiệm Văn Hóa')
@section('content')

<div class="page-heading">
    <a href="{{ route('experiences.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại danh sách trải nghiệm
    </a>
    <h1>{{ $experience->title }}</h1>
</div>

<div class="form-grid" style="grid-template-columns: 1.5fr 1fr; gap: 24px; align-items: start;">
    {{-- Khối thông tin chi tiết --}}
    <div class="card" style="padding: 28px; margin: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border-light);">
            <span class="badge {{ $experience->status === 'open' ? 'badge-success' : 'badge-warning' }}">
                {{ $experience->status === 'open' ? 'Đang mở đăng ký' : 'Đã đủ số lượng' }}
            </span>
            <div class="price-tag" style="font-size: 1.25rem;">
                <strong>{{ number_format($experience->price) }} đ</strong> <small>/ người</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 20px;">
            <div>
                <p style="margin: 0; font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Homestay tổ chức:</p>
                <p style="font-size: 0.98rem; font-weight: 600; margin: 2px 0 0;">
                    <strong>Homestay:</strong> {{ $experience->homestay->name }}
                </p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Thời gian khởi hành:</p>
                <p style="font-size: 0.98rem; font-weight: 600; margin: 2px 0 0;">
                    <strong>Thời gian:</strong> {{ $experience->start_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Thời lượng chương trình:</p>
                <p style="font-size: 0.98rem; font-weight: 600; margin: 2px 0 0;">
                    <strong>Thời lượng:</strong> {{ $experience->duration_minutes }} phút
                </p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Số chỗ còn trống:</p>
                <p style="font-size: 0.98rem; font-weight: 600; margin: 2px 0 0;">
                    <strong>Còn chỗ:</strong> {{ max(0, $experience->capacity - $experience->booked_count) }}
                </p>
            </div>
        </div>

        <div style="padding-top: 16px; border-top: 1px solid var(--border-light);">
            <h4 style="font-size: 1rem; margin-bottom: 8px;">Nội dung trải nghiệm</h4>
            <p style="color: var(--muted); line-height: 1.6; font-size: 0.95rem;">
                {{ $experience->description }}
            </p>
        </div>
    </div>

    {{-- Form đặt chỗ dành cho Guest --}}
    <div>
        @auth
            @if(auth()->user()->role === 'guest' && $experience->status === 'open')
                <div class="card" style="padding: 24px; margin: 0; border-color: var(--primary);">
                    <h3 style="font-size: 1.2rem; margin-bottom: 14px;">Đăng ký tham gia</h3>
                    <form method="post" action="{{ route('experiences.book', $experience) }}">
                        @csrf
                        <label>
                            Số người tham gia
                            <input type="number" min="1" max="20" name="people_count" value="{{ old('people_count', 1) }}" required style="margin-top: 6px;">
                        </label>
                        <p style="font-size: 0.84rem; color: var(--muted); margin-bottom: 16px;">
                            Số chỗ tối đa trong 1 đợt: {{ $experience->capacity }} người.
                        </p>
                        <button class="btn btn-accent" type="submit" style="width: 100%;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            Đặt trải nghiệm
                        </button>
                    </form>
                </div>
            @endif
        @else
            <div class="card" style="padding: 20px; text-align: center; margin: 0;">
                <p style="margin-bottom: 12px; color: var(--muted);">Đăng nhập tài khoản du khách để đăng ký tham gia trải nghiệm.</p>
                <a href="{{ route('login') }}" class="btn btn-small">Đăng nhập ngay</a>
            </div>
        @endauth
    </div>
</div>

@endsection
