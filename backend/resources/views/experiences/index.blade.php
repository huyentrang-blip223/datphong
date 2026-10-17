@extends('layouts.app')
@section('title', 'Trải Nghiệm Văn Hóa Bản Địa')
@section('content')

<div class="page-banner" style="background-image: linear-gradient(135deg, rgba(14, 38, 32, 0.88) 0%, rgba(18, 50, 42, 0.75) 100%), url('{{ asset('images/banners/hero-banner.jpg') }}');">
    <span class="hero-chip hero-chip-ghost" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
        Hành trình độc bản
    </span>
    <h1>Trải nghiệm văn hóa</h1>
    <p>Hòa mình vào nếp sống thường nhật, nghề thủ công truyền thống và ẩm thực dân tộc cùng đồng bào bản xứ.</p>
</div>

<div class="card" style="padding: 20px; margin-bottom: 32px;">
    <form method="get" action="{{ route('experiences.index') }}">
        <div style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 260px;">
                <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Tỉnh/huyện
                </label>
                <input name="location" value="{{ request('location') }}" placeholder="Nhập địa điểm muốn trải nghiệm...">
            </div>
            <button class="btn" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Lọc trải nghiệm
            </button>
        </div>
    </form>
</div>

<div class="exp-grid">
    @forelse($experiences as $experience)
        <article class="exp-card card" style="margin: 0; padding: 0;">
            <div class="exp-card-header" style="background-image: linear-gradient(180deg, rgba(14, 38, 34, 0.3) 0%, rgba(10, 26, 22, 0.88) 100%), url('{{ $experience->image_url }}');">
                <span class="badge badge-accent">
                    {{ $experience->status === 'open' ? 'Đang mở đăng ký' : 'Đã đủ chỗ' }}
                </span>
                <div class="exp-header-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                </div>
            </div>
            <div class="exp-card-body">
                <h2>
                    <a href="{{ route('experiences.show', $experience) }}" style="font-size: 1.15rem; font-weight: 750;">
                        {{ $experience->title }}
                    </a>
                </h2>
                <div class="homestay-location" style="margin: 8px 0 12px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <p style="margin: 0;">{{ $experience->homestay->name }} - {{ $experience->homestay->province }}</p>
                </div>

                <div class="exp-meta-row" style="margin-bottom: 16px;">
                    <span class="exp-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $experience->start_at->format('d/m/Y H:i') }}
                    </span>
                    <span class="exp-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ $experience->duration_minutes }} phút
                    </span>
                    <span class="exp-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        Còn {{ max(0, $experience->capacity - $experience->booked_count) }} chỗ
                    </span>
                </div>

                <div class="exp-card-footer">
                    <div class="price-tag">
                        <strong>{{ number_format($experience->price) }} đ</strong> <small>/ người</small>
                    </div>
                    <a href="{{ route('experiences.show', $experience) }}" class="btn btn-small">
                        Chi tiết
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        </article>
    @empty
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <p style="margin: 0; color: var(--muted);">Chưa có trải nghiệm văn hóa nào phù hợp.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 32px;">
    {{ $experiences->links() }}
</div>

@endsection
