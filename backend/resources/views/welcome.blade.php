@extends('layouts.app')
@section('title', 'ĐT-07 — Sàn Homestay Cộng Đồng & Đặc Sản Bản Địa')
@section('content')

{{-- 1. HERO SECTION & FAST SEARCH --}}
<div class="hero-wrapper">
    <div class="hero-banner" style="background-image: linear-gradient(135deg, rgba(8, 28, 24, 0.88) 0%, rgba(12, 42, 36, 0.76) 50%, rgba(10, 32, 28, 0.65) 100%), url('{{ asset('images/banners/hero-banner.jpg') }}');">
        <div class="hero-content">
            <div class="hero-badge-row">
                <span class="hero-chip">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6.4-4.8-6.4 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
                    Sàn Du Lịch Cộng Đồng ĐT-07
                </span>
                <span class="hero-chip hero-chip-ghost">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    100% Xác thực thực địa
                </span>
            </div>

            <h1>Homestay Bản Địa, Đặc Sản OCOP & Trải Nghiệm Văn Hóa</h1>
            <p class="hero-desc">
                Khám phá không gian lưu trú mộc mạc giữa thiên nhiên hùng vĩ, kết nối trực tiếp với người nông dân địa phương, thưởng thức đặc sản OCOP và trải nghiệm văn hóa truyền thống độc bản.
            </p>

            <div class="hero-trust-row">
                <div class="trust-item">
                    <div class="trust-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <span>Kiểm định an toàn</span>
                </div>
                <div class="trust-item">
                    <div class="trust-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </div>
                    <span>Khóa phòng Real-time</span>
                </div>
                <div class="trust-item">
                    <div class="trust-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    </div>
                    <span>OCOP trực tiếp từ nhà nông</span>
                </div>
                <div class="trust-item">
                    <div class="trust-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <span>Tích điểm Loyalty</span>
                </div>
            </div>
        </div>

        {{-- HERO SEARCH FORM --}}
        <div class="hero-search-card">
            <form method="get" action="{{ route('search.results') }}" class="hero-search-form">
                <div class="search-field">
                    <label>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Điểm đến (Tỉnh / Huyện)
                    </label>
                    <input type="text" name="location" placeholder="Đà Lạt, Sa Pa, Ninh Bình, Hội An..." value="{{ request('location') }}">
                </div>
                <div class="search-field">
                    <label>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Ngày nhận phòng
                    </label>
                    <input type="date" name="checkin" value="{{ request('checkin', date('Y-m-d', strtotime('+1 day'))) }}" required>
                </div>
                <div class="search-field">
                    <label>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Ngày trả phòng
                    </label>
                    <input type="date" name="checkout" value="{{ request('checkout', date('Y-m-d', strtotime('+2 days'))) }}" required>
                </div>
                <div class="search-field">
                    <label>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Số khách
                    </label>
                    <input type="number" name="guests" min="1" max="20" value="{{ request('guests', 2) }}" required>
                </div>
                <button type="submit" class="btn hero-search-submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Tìm phòng
                </button>
            </form>

            <div class="quick-tags">
                <span class="quick-tags-title">Gợi ý tìm nhanh:</span>
                @foreach($popularDestinations ?? [] as $dest)
                    <a href="{{ route('search.results', ['location' => $dest['keyword'], 'checkin' => date('Y-m-d', strtotime('+1 day')), 'checkout' => date('Y-m-d', strtotime('+2 days')), 'guests' => 2]) }}" class="tag-pill">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.6; margin-right: 2px;"><circle cx="12" cy="12" r="10"/></svg>
                        {{ $dest['name'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- 2. STATS BANNER --}}
<div class="stats-banner">
    <div class="stat-box">
        <div class="stat-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </div>
        <div class="stat-info">
            <strong>{{ $stats['homestays_count'] ?? '12+' }}</strong>
            <span>Homestay cộng đồng</span>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"/>
            </svg>
        </div>
        <div class="stat-info">
            <strong>{{ $stats['rooms_count'] ?? '36+' }}</strong>
            <span>Phòng có lịch trực tuyến</span>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M12 8v4M12 16h.01"/>
            </svg>
        </div>
        <div class="stat-info">
            <strong>{{ $stats['products_count'] ?? '24+' }}</strong>
            <span>Đặc sản OCOP bản địa</span>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
            </svg>
        </div>
        <div class="stat-info">
            <strong>{{ $stats['experiences_count'] ?? '24+' }}</strong>
            <span>Trải nghiệm văn hóa</span>
        </div>
    </div>
</div>

{{-- 3. ROLE WORKSPACE PANEL (KHI ĐÃ ĐĂNG NHẬP) --}}
@auth
    @php
        $role = auth()->user()->role;
    @endphp
    <section class="role-panel">
        <div class="role-panel-info">
            <span class="role-badge-pill">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Tài khoản: {{ strtoupper($role) }}
            </span>
            <h2>Xin chào, {{ auth()->user()->full_name ?? auth()->user()->email }}!</h2>
            <p>Khu vực làm việc nhanh phù hợp với quyền hạn của bạn. Hệ thống đảm bảo phân quyền chặt chẽ từ Server-side Policy và Middleware.</p>
        </div>
        <div class="action-strip">
            @if($role === 'admin')
                <a class="btn" href="{{ route('admin.dashboard') }}">Dashboard admin</a>
                <a class="btn btn-secondary" href="{{ route('admin.homestays.index') }}">Duyệt homestay</a>
                <a class="btn btn-secondary" href="{{ route('admin.rooms.index') }}">Quản lý phòng</a>
                <a class="btn btn-secondary" href="{{ route('admin.products.index') }}">Quản lý sản phẩm</a>
            @elseif($role === 'host')
                <a class="btn" href="{{ route('host.dashboard') }}">Dashboard host</a>
                <a class="btn btn-secondary" href="{{ route('host.availability.index') }}">Lịch phòng</a>
                <a class="btn btn-secondary" href="{{ route('host.rooms.index') }}">Quản lý phòng</a>
            @elseif($role === 'seller')
                <a class="btn" href="{{ route('seller.dashboard') }}">Dashboard seller</a>
                <a class="btn btn-secondary" href="{{ route('seller.products.index') }}">Sản phẩm của tôi</a>
                <a class="btn btn-secondary" href="{{ route('seller.orders.index') }}">Đơn hàng</a>
            @elseif($role === 'guest')
                <a class="btn" href="{{ route('guest.bookings.index') }}">Booking của tôi</a>
                <a class="btn btn-secondary" href="{{ route('account.loyalty.index') }}">Điểm thưởng</a>
                <a class="btn btn-secondary" href="{{ route('guest.orders.index') }}">Đơn hàng đã đặt</a>
            @endif
        </div>
    </section>
@endauth

{{-- 4. POPULAR DESTINATIONS --}}
<section class="home-section">
    <div class="section-header">
        <div class="section-header-left">
            <span class="eyebrow">Vùng đất di sản</span>
            <h2>Điểm đến du lịch cộng đồng nổi bật</h2>
            <p>Khám phá văn hóa bản địa, danh lam thắng cảnh và làng nghề truyền thống</p>
        </div>
        <a href="{{ route('search.index') }}" class="section-link">
            Xem tất cả điểm đến
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <div class="dest-grid">
        @foreach($popularDestinations ?? [] as $destination)
            <a href="{{ route('search.results', ['location' => $destination['keyword'], 'checkin' => date('Y-m-d', strtotime('+1 day')), 'checkout' => date('Y-m-d', strtotime('+2 days')), 'guests' => 2]) }}"
               class="dest-card" style="background-image: linear-gradient(180deg, rgba(12, 28, 24, 0.25) 0%, rgba(10, 25, 21, 0.9) 100%), url('{{ $destination['image'] ?? '' }}');">
                <div class="dest-card-header">
                    <span class="dest-badge">{{ $destination['badge'] }}</span>
                    <div class="dest-icon-bubble">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                </div>
                <div class="dest-card-content">
                    <h3>{{ $destination['name'] }}</h3>
                    <p>{{ $destination['tagline'] }}</p>
                    <span class="dest-card-action">
                        Khám phá
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </span>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- 5. FEATURED HOMESTAYS --}}
<section class="home-section">
    <div class="section-header">
        <div class="section-header-left">
            <span class="eyebrow">Lưu trú chất lượng</span>
            <h2>Homestay cộng đồng tiêu biểu</h2>
            <p>Cơ sở lưu trú đã được duyệt xác thực, bảo đảm tiện nghi và bản sắc văn hóa</p>
        </div>
        <a href="{{ route('search.index') }}" class="section-link">
            Tìm tất cả homestay
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <div class="cards-grid">
@forelse(($featuredHomestays ?? []) as $homestay)
    @php
        $featuredRoom = $homestay->rooms->first();
        $cardImageUrl = $homestay->cover_image_url;
        $detailUrl = $featuredRoom ? route('rooms.show', $featuredRoom) : route('search.results', ['location' => $homestay->province, 'checkin' => date('Y-m-d', strtotime('+1 day')), 'checkout' => date('Y-m-d', strtotime('+2 days')), 'guests' => 2]);
    @endphp
    <article class="homestay-card">
                <div style="height: 200px; position: relative; overflow: hidden; background: var(--surface-alt);">
                    <a href="{{ $detailUrl }}" style="display: block; width: 100%; height: 100%;">
                        <img src="{{ $cardImageUrl }}" alt="Ảnh homestay {{ $homestay->name }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/homestays/default.svg') }}';"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;"
                             onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    </a>
                    <div style="position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: center; pointer-events: none;">
                        <span class="badge badge-success" style="box-shadow: 0 2px 8px rgba(0,0,0,0.25);">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                            Đã xác thực
                        </span>
                        <span class="rating-pill" style="box-shadow: 0 2px 8px rgba(0,0,0,0.25);">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6.4-4.8-6.4 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
                            {{ number_format($homestay->avg_rating, 1) }}
                        </span>
                    </div>
                    @if($featuredRoom)
                        <span class="badge" style="position: absolute; bottom: 10px; left: 12px; background: rgba(0,0,0,0.7); color: #ffffff; font-size: 0.78rem; backdrop-filter: blur(4px);">
                            {{ $featuredRoom->name }}
                        </span>
                    @endif
                </div>
                <div class="homestay-card-body">
                    <h3><a href="{{ $detailUrl }}" style="color: var(--text);">{{ $homestay->name }}</a></h3>
                    <div class="homestay-location">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $homestay->province }} @if($homestay->district) — {{ $homestay->district }} @endif
                    </div>
                    <p class="homestay-desc">{{ $homestay->description ?? 'Homestay cộng đồng mộc mạc, đậm đà bản sắc địa phương.' }}</p>
                    
                    <div class="homestay-rooms-summary">
                        <span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px; margin-right: 3px;"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                            {{ $homestay->rooms->count() }} loại phòng
                        </span>
                        <span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px; margin-right: 3px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            Tối đa {{ $homestay->rooms->max('max_guests') ?? 4 }} khách/phòng
                        </span>
                    </div>

                    <div class="homestay-card-footer">
                        <div class="price-tag">
                            Từ <strong>{{ number_format($homestay->rooms->min('base_price') ?? 250000) }} đ</strong><small>/đêm</small>
                        </div>
                        <a href="{{ $detailUrl }}" class="btn btn-small">
                            Xem phòng
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <p>Đang cập nhật danh sách homestay...</p>
        @endforelse
    </div>
</section>

{{-- 6. LOCAL PRODUCTS / OCOP --}}
<section class="home-section">
    <div class="section-header">
        <div class="section-header-left">
            <span class="eyebrow">Sản vật bản địa</span>
            <h2>Đặc sản OCOP & Nông sản địa phương</h2>
            <p>Gắn kết trực tiếp từ các hợp tác xã, nghệ nhân và gắn liền với từng homestay bản địa</p>
        </div>
        <a href="{{ route('products.index') }}" class="section-link">
            Vào chợ địa phương
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <div class="products-grid">
        @forelse($featuredProducts ?? [] as $product)
            <article class="product-card">
                <div class="product-card-top">
                    <a href="{{ route('products.show', $product) }}" style="display: block; width: 100%; height: 100%;">
                        <img src="{{ $product->image_url }}" alt="Ảnh {{ $product->name }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/products/default.svg') }}';"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </a>
                </div>
                <div class="product-card-body">
                    <h3>
                        <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                    </h3>
                    <div class="product-origin">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Xuất xứ: {{ $product->origin_place ?? 'Bản địa' }}
                    </div>

                    @if($product->homestays->isNotEmpty())
                        <div class="product-homestay-link">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px; margin-right: 3px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            Tại: {{ $product->homestays->first()->name }}
                        </div>
                    @endif

                    <div class="product-card-footer">
                        <div class="product-price">
                            {{ number_format($product->price) }} đ <small>/{{ $product->unit ?? 'món' }}</small>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-small btn-secondary">
                            Chi tiết
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <p>Đang cập nhật sản phẩm...</p>
        @endforelse
    </div>
</section>

{{-- 7. CULTURAL EXPERIENCES --}}
<section class="home-section">
    <div class="section-header">
        <div class="section-header-left">
            <span class="eyebrow">Hành trình độc đáo</span>
            <h2>Trải nghiệm văn hóa cùng người bản xứ</h2>
            <p>Tham gia dệt thổ cẩm, hái chè cổ thụ, làm nông nghiệp sạch và tìm hiểu phong tục truyền thống</p>
        </div>
        <a href="{{ route('experiences.index') }}" class="section-link">
            Xem tất cả trải nghiệm
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <div class="exp-grid">
        @forelse($featuredExperiences ?? [] as $exp)
            <article class="exp-card">
                <div class="exp-card-header" style="background-image: linear-gradient(180deg, rgba(14, 38, 34, 0.3) 0%, rgba(10, 26, 22, 0.88) 100%), url('{{ $exp->image_url }}');">
                    <span class="badge badge-accent" style="box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 3px;"><circle cx="12" cy="12" r="10"/></svg>
                        {{ $exp->status === 'open' ? 'Đang mở đăng ký' : 'Đã đủ chỗ' }}
                    </span>
                    <div class="exp-header-icon" style="box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                    </div>
                </div>
                <div class="exp-card-body">
                    <h3>
                        <a href="{{ route('experiences.show', $exp) }}">{{ $exp->title }}</a>
                    </h3>
                    <div class="homestay-location">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        {{ $exp->homestay->name ?? 'Homestay cộng đồng' }} ({{ $exp->homestay->province ?? '' }})
                    </div>
                    
                    <div class="exp-meta-row">
                        <span class="exp-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $exp->duration_minutes }} phút
                        </span>
                        <span class="exp-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ $exp->booked_count }}/{{ $exp->capacity }} người
                        </span>
                    </div>

                    <div class="exp-card-footer">
                        <div class="price-tag">
                            <strong>{{ number_format($exp->price) }} đ</strong><small>/người</small>
                        </div>
                        <a href="{{ route('experiences.show', $exp) }}" class="btn btn-small">
                            Đặt chỗ
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <p>Đang cập nhật trải nghiệm...</p>
        @endforelse
    </div>
</section>

{{-- 8. CORE VALUES / WHY CHOOSE US --}}
<section class="features-container">
    <div class="section-header-left">
        <span class="eyebrow">Giá trị cốt lõi</span>
        <h2>Vì sao nên chọn Sàn Homestay Cộng Đồng ĐT-07?</h2>
        <p>Hệ sinh thái du lịch bền vững kết nối trực tiếp du khách với cộng đồng địa phương</p>
    </div>

    <div class="features-grid-custom">
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <polyline points="9 12 11 14 15 10"/>
                </svg>
            </div>
            <h3>100% Homestay Xác Thực</h3>
            <p>Tất cả cơ sở lưu trú được kiểm duyệt thông tin, vị trí và hồ sơ người đại diện uy tín trước khi mở bán.</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
            </div>
            <h3>Chống Trùng Phòng Real-time</h3>
            <p>Xử lý đặt phòng với giao dịch cơ sở dữ liệu khóa dòng nghiêm ngặt, đảm bảo số phòng trống chuẩn xác 100%.</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                    <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                </svg>
            </div>
            <h3>Kinh Tế Bản Địa Bền Vững</h3>
            <p>Doanh thu từ lưu trú, nông sản OCOP và trải nghiệm được chuyển trực tiếp tới các hộ gia đình bản địa.</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon-wrapper">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                    <path d="M4 22h16"/>
                    <path d="M10 14.66V17c0 .55-.45 1-1 1H7c-.55 0-1-.45-1-1v-2.34"/>
                    <path d="M18 17c0 .55-.45 1-1 1h-2c-.55 0-1-.45-1-1v-2.34"/>
                    <path d="M12 2v20"/>
                    <circle cx="12" cy="7" r="5"/>
                </svg>
            </div>
            <h3>Tích Lũy Điểm Thưởng</h3>
            <p>Chương trình điểm thưởng Loyalty cho du khách sau mỗi chuyến đi, đổi quà đặc sản và ưu đãi lần sau.</p>
        </div>
    </div>
</section>

{{-- 9. CALL TO ACTION --}}
<div class="cta-banner">
    <div>
        <h2>Đồng hành cùng Du lịch Cộng đồng</h2>
        <p>Bạn là chủ homestay hoặc nhà sản xuất đặc sản địa phương? Hãy tham gia mạng lưới của chúng tôi để tiếp cận du khách trong và ngoài nước.</p>
    </div>
    <div class="cta-actions">
        <a href="{{ route('register') }}" class="btn cta-btn-light">
            Đăng ký tham gia ngay
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-secondary" style="border-color: rgba(255,255,255,0.6); color: #ffffff !important; background: transparent !important;">
            Khám phá chợ OCOP
        </a>
    </div>
</div>

@endsection
