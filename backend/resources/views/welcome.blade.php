@extends('layouts.app')
@section('title', 'ĐT-07 — Sàn Homestay Cộng Đồng & Đặc Sản Bản Địa')

@push('styles')
<style>
/* ==========================================================================
   ĐT-07 HOMEPAGE STYLES — CHUYỂN ĐỘNG TỰ ĐỘNG & SCROLL REVEAL AN TOÀN
   ========================================================================== */

/* 1. KEYFRAME ANIMATIONS */
@keyframes kenburnsZoom {
    0%   { transform: scale(1); }
    100% { transform: scale(1.08); }
}

@keyframes bannerProgress {
    0%   { width: 0%; }
    100% { width: 100%; }
}

@keyframes radarPulse {
    0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
    70%  { transform: scale(1);    box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
}

@keyframes floatGentle {
    0%, 100% { transform: translateY(0px); }
    50%      { transform: translateY(-8px); }
}

@keyframes marqueeInfinite {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

@keyframes orbDrift {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33%      { transform: translate(30px, -20px) scale(1.1); }
    66%      { transform: translate(-20px, 20px) scale(0.95); }
}

@keyframes iconWiggle {
    0%, 100% { transform: rotate(0deg); }
    25%      { transform: rotate(-8deg); }
    75%      { transform: rotate(8deg); }
}

/* 2. SCROLL REVEAL (Mặc định luôn HIỆN để chống lỗi tàng hình, chỉ animate khi JS sẵn sàng) */
body.has-reveal .scroll-reveal {
    opacity: 0;
    transform: translateY(26px);
    transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: opacity, transform;
}

body.has-reveal .scroll-reveal.is-visible {
    opacity: 1 !important;
    transform: translateY(0) !important;
}

/* Stagger cho card grids */
body.has-reveal .stagger-grid > * {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.55s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.55s cubic-bezier(0.16, 1, 0.3, 1);
}

body.has-reveal .stagger-grid.is-visible > *:nth-child(1) { opacity: 1; transform: translateY(0); transition-delay: 0.04s; }
body.has-reveal .stagger-grid.is-visible > *:nth-child(2) { opacity: 1; transform: translateY(0); transition-delay: 0.10s; }
body.has-reveal .stagger-grid.is-visible > *:nth-child(3) { opacity: 1; transform: translateY(0); transition-delay: 0.16s; }
body.has-reveal .stagger-grid.is-visible > *:nth-child(4) { opacity: 1; transform: translateY(0); transition-delay: 0.22s; }
body.has-reveal .stagger-grid.is-visible > *:nth-child(5) { opacity: 1; transform: translateY(0); transition-delay: 0.28s; }
body.has-reveal .stagger-grid.is-visible > *:nth-child(6) { opacity: 1; transform: translateY(0); transition-delay: 0.34s; }

/* 3. HERO BANNER SLIDESHOW CHUẨN GIAO DIỆN */
.hero-wrapper {
    position: relative;
    padding: 16px 0 24px;
}

.hero-slider-container {
    position: relative;
    border-radius: 26px;
    overflow: hidden;
    box-shadow: 0 20px 50px -10px rgba(10, 32, 28, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: #081c18;
    color: #ffffff;
    min-height: 520px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 48px 44px 36px;
}

/* Các tầng slide background */
.hero-slides {
    position: absolute;
    inset: 0;
    z-index: 1;
    overflow: hidden;
    pointer-events: none;
}

.hero-slide-item {
    position: absolute;
    inset: 0;
    opacity: 0;
    visibility: hidden;
    transition: opacity 1.2s ease-in-out, visibility 1.2s;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transform: scale(1);
}

.hero-slide-item.active {
    opacity: 1;
    visibility: visible;
    animation: kenburnsZoom 7s ease-out forwards;
}

/* Lớp phủ gradient để chữ luôn đọc cực rõ nét */
.hero-slider-container::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        rgba(6, 22, 19, 0.90) 0%,
        rgba(10, 36, 31, 0.78) 50%,
        rgba(8, 26, 23, 0.62) 100%
    );
    z-index: 2;
    pointer-events: none;
}

/* Ambient glow orb trôi tự động */
.ambient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    pointer-events: none;
    z-index: 2;
    opacity: 0.45;
    animation: orbDrift 12s ease-in-out infinite alternate;
}
.ambient-orb-1 {
    width: 320px;
    height: 320px;
    top: -60px;
    left: -40px;
    background: radial-gradient(circle, #1e827b 0%, rgba(30, 130, 123, 0) 70%);
}
.ambient-orb-2 {
    width: 280px;
    height: 280px;
    bottom: -40px;
    right: 10%;
    background: radial-gradient(circle, #c56a2c 0%, rgba(197, 106, 44, 0) 70%);
}

/* Nội dung Hero */
.hero-content {
    position: relative;
    z-index: 3;
    max-width: 860px;
}

.hero-badge-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.hero-chip-radar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 700;
    background: rgba(18, 96, 90, 0.75);
    border: 1px solid rgba(126, 212, 167, 0.4);
    color: #ffffff;
    backdrop-filter: blur(8px);
}

.radar-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4ade80;
    display: inline-block;
    animation: radarPulse 2s infinite;
}

.hero-location-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.82rem;
    font-weight: 600;
    background: rgba(0, 0, 0, 0.45);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffd299;
    backdrop-filter: blur(8px);
    transition: opacity 0.4s ease, transform 0.4s ease;
}

.hero-content h1 {
    font-size: 2.75rem;
    font-weight: 850;
    margin-bottom: 16px;
    color: #ffffff;
    line-height: 1.18;
    letter-spacing: -0.025em;
    text-shadow: 0 3px 14px rgba(0, 0, 0, 0.5);
}

.hero-desc {
    font-size: 1.12rem;
    color: rgba(255, 255, 255, 0.92);
    margin-bottom: 24px;
    line-height: 1.65;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
    max-width: 760px;
}

.hero-trust-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.92);
}

.trust-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7ed4a7;
    flex-shrink: 0;
}

/* ĐIỀU KHIỂN SLIDER (Dots & Prev/Next & Progress bar) */
.slider-nav-bar {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-top: 10px;
}

.slider-dots {
    display: flex;
    align-items: center;
    gap: 8px;
}

.slider-dot {
    width: 32px;
    height: 6px;
    border-radius: 3px;
    background: rgba(255, 255, 255, 0.35);
    border: none;
    cursor: pointer;
    transition: all 0.35s ease;
    padding: 0;
}

.slider-dot.active {
    width: 48px;
    background: #7ed4a7;
    box-shadow: 0 0 10px rgba(126, 212, 167, 0.6);
}

.slider-arrows {
    display: flex;
    align-items: center;
    gap: 8px;
}

.slider-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(6px);
    transition: all 0.25s ease;
}

.slider-btn:hover {
    background: var(--primary, #12605a);
    border-color: #7ed4a7;
    transform: scale(1.08);
}

/* Thanh progress chạy tự động */
.hero-progress-line {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    z-index: 4;
}
.hero-progress-active {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #12605a, #7ed4a7, #f0b36d);
    box-shadow: 0 0 8px rgba(126, 212, 167, 0.8);
}

/* 4. MARQUEE TICKER CHẠY NGANG LIÊN TỤC VÔ TẬN */
.dest-ticker-wrapper {
    overflow: hidden;
    position: relative;
    padding: 16px 0;
    margin: 12px 0 28px;
    border-radius: 16px;
    background: var(--surface-alt, #edf3f0);
    border: 1px solid var(--border-light, #e6ece9);
}

.dest-ticker-wrapper::before,
.dest-ticker-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 50px;
    z-index: 2;
    pointer-events: none;
}
.dest-ticker-wrapper::before {
    left: 0;
    background: linear-gradient(to right, var(--surface-alt, #edf3f0), transparent);
}
.dest-ticker-wrapper::after {
    right: 0;
    background: linear-gradient(to left, var(--surface-alt, #edf3f0), transparent);
}

.dest-ticker-track {
    display: flex;
    width: max-content;
    gap: 14px;
    animation: marqueeInfinite 28s linear infinite;
}
.dest-ticker-track:hover {
    animation-play-state: paused;
}

.ticker-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 9999px;
    font-size: 0.88rem;
    font-weight: 600;
    background: var(--surface, #ffffff);
    color: var(--text, #17212b);
    border: 1px solid var(--border, #d4deda);
    box-shadow: var(--shadow-sm);
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.25s ease;
}
.ticker-item:hover {
    background: var(--primary, #12605a);
    color: #ffffff;
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}
.ticker-item svg {
    color: var(--accent, #c56a2c);
    transition: transform 0.25s ease;
}
.ticker-item:hover svg {
    color: #ffd299;
    transform: scale(1.15);
}

/* 5. NÂNG CẤP THẺ CARD VỚI HIỆU ỨNG CHUYỂN ĐỘNG MƯỢT MÀ */
.homestay-card,
.product-card,
.dest-card,
.exp-card {
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.homestay-card:hover,
.product-card:hover,
.dest-card:hover,
.exp-card:hover {
    transform: translateY(-7px) scale(1.01) !important;
    box-shadow: 0 18px 38px rgba(18, 96, 90, 0.16) !important;
}

.homestay-card:hover img,
.product-card:hover img {
    transform: scale(1.06) !important;
}

/* Nút bấm xem chi tiết khi hover */
.homestay-card:hover .btn-small svg,
.product-card:hover .btn-small svg,
.exp-card:hover .btn-small svg {
    transform: translateX(4px);
}
.btn-small svg {
    transition: transform 0.25s ease;
}

/* 6. STATS BANNER ANIMATION */
.stat-box {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.stat-box:hover {
    transform: translateY(-4px);
}
.stat-box:hover .stat-icon svg {
    animation: iconWiggle 0.6s ease;
}

/* 7. FLOATING QUICK-SEARCH BUTTON */
.floating-search-fab {
    position: fixed;
    bottom: 26px;
    right: 26px;
    z-index: 999;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--primary-gradient, linear-gradient(135deg, #12605a 0%, #1e827b 100%));
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 20px rgba(18, 96, 90, 0.4);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
}
.floating-search-fab:hover {
    transform: scale(1.12) rotate(8deg);
    box-shadow: 0 10px 28px rgba(18, 96, 90, 0.55);
    color: #ffffff;
}

@media (max-width: 768px) {
    .hero-slider-container { padding: 32px 20px 24px; min-height: 460px; }
    .hero-content h1 { font-size: 2rem; }
    .hero-trust-row { gap: 10px; }
}
</style>
@endpush

@section('content')

{{-- =========================================================================
     1. HERO SECTION & FAST SEARCH VỚI BANNER LUÂN PHIÊN ĐA ẢNH
     ========================================================================= --}}
<div class="hero-wrapper scroll-reveal">
    <div class="hero-slider-container" id="heroBannerSlider">
        
        {{-- Lớp các slide hình ảnh luân phiên --}}
        <div class="hero-slides">
            <div class="hero-slide-item active" style="background-image: url('{{ asset('images/banners/hero-banner.jpg') }}');" data-location="🌾 Sa Pa (Lào Cai) • Ruộng bậc thang di sản"></div>
            <div class="hero-slide-item" style="background-image: url('{{ asset('images/banners/hoian-homestay.jpg') }}');" data-location="🏮 Phố Cổ Hội An (Quảng Nam) • Di sản văn hóa"></div>
            <div class="hero-slide-item" style="background-image: url('{{ asset('images/banners/ninhbinh-homestay.jpg') }}');" data-location="⛰️ Tràng An (Ninh Bình) • Non nước hữu tình"></div>
            <div class="hero-slide-item" style="background-image: url('{{ asset('images/banners/dalat-homestay.jpg') }}');" data-location="🌲 Đà Lạt (Lâm Đồng) • Rừng thông &amp; sương mù"></div>
            <div class="hero-slide-item" style="background-image: url('{{ asset('images/banners/ocop-products.jpg') }}');" data-location="🍯 Đặc Sản OCOP • Nông sản sạch từ bản làng"></div>
        </div>

        {{-- Ambient orbs chuyển động trôi nhẹ --}}
        <div class="ambient-orb ambient-orb-1" aria-hidden="true"></div>
        <div class="ambient-orb ambient-orb-2" aria-hidden="true"></div>

        {{-- Thanh progress tự động đếm giờ cho slide --}}
        <div class="hero-progress-line" aria-hidden="true">
            <div class="hero-progress-active" id="heroProgressBar"></div>
        </div>

        {{-- Nội dung tiêu đề Hero --}}
        <div class="hero-content">
            <div class="hero-badge-row">
                <span class="hero-chip-radar">
                    <span class="radar-dot"></span>
                    Sàn Du Lịch Cộng Đồng ĐT-07
                </span>
                <span class="hero-chip hero-chip-ghost">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    100% Xác thực thực địa
                </span>
                <span class="hero-location-tag" id="heroLocationTag">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    <span id="heroLocationText">🌾 Sa Pa (Lào Cai) • Ruộng bậc thang di sản</span>
                </span>
            </div>

            <h1>Homestay Bản Địa, Đặc Sản OCOP &amp; Trải Nghiệm Văn Hóa</h1>
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

        {{-- Điều khiển Slider: Dots & Nút lật trang --}}
        <div class="slider-nav-bar">
            <div class="slider-dots" id="sliderDotsContainer">
                <button class="slider-dot active" data-index="0" aria-label="Slide 1"></button>
                <button class="slider-dot" data-index="1" aria-label="Slide 2"></button>
                <button class="slider-dot" data-index="2" aria-label="Slide 3"></button>
                <button class="slider-dot" data-index="3" aria-label="Slide 4"></button>
                <button class="slider-dot" data-index="4" aria-label="Slide 5"></button>
            </div>
            <div class="slider-arrows">
                <button class="slider-btn" id="sliderPrevBtn" aria-label="Slide trước">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="slider-btn" id="sliderNextBtn" aria-label="Slide tiếp theo">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>

        {{-- FORM TÌM PHÒNG NHANH HERO --}}
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

{{-- =========================================================================
     2. DẢI TICKER MARQUEE ĐIỂM ĐẾN TỰ ĐỘNG CHẠY NGANG LIÊN TỤC
     ========================================================================= --}}
<div class="dest-ticker-wrapper scroll-reveal" aria-hidden="true">
    <div class="dest-ticker-track">
        @php
            $tickerItems = [
                ['name' => '🌾 Sa Pa (Lào Cai)', 'loc' => 'Lao Cai'],
                ['name' => '🏮 Phố Cổ Hội An', 'loc' => 'Quang Nam'],
                ['name' => '⛰️ Tràng An (Ninh Bình)', 'loc' => 'Ninh Binh'],
                ['name' => '🌲 Rừng Thông Đà Lạt', 'loc' => 'Lam Dong'],
                ['name' => '🌾 Ruộng Bậc Thang Mù Cang Chải', 'loc' => 'Yen Bai'],
                ['name' => '🌸 Cao Nguyên Đá Hà Giang', 'loc' => 'Ha Giang'],
                ['name' => '⛵ Chợ Nổi Cái Răng', 'loc' => 'Can Tho'],
                ['name' => '🍵 Đồi Chè Cầu Đất', 'loc' => 'Lam Dong'],
                ['name' => '🏺 Làng Gốm Bát Tràng', 'loc' => 'Ha Noi'],
            ];
        @endphp
        {{-- Vòng lặp thứ nhất --}}
        @foreach($tickerItems as $item)
            <a href="{{ route('search.results', ['location' => $item['loc'], 'checkin' => date('Y-m-d', strtotime('+1 day')), 'checkout' => date('Y-m-d', strtotime('+2 days')), 'guests' => 2]) }}" class="ticker-item">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $item['name'] }}
            </a>
        @endforeach
        {{-- Nhân bản để tạo dòng chạy liên tục không ngắt quãng --}}
        @foreach($tickerItems as $item)
            <a href="{{ route('search.results', ['location' => $item['loc'], 'checkin' => date('Y-m-d', strtotime('+1 day')), 'checkout' => date('Y-m-d', strtotime('+2 days')), 'guests' => 2]) }}" class="ticker-item">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $item['name'] }}
            </a>
        @endforeach
    </div>
</div>

{{-- =========================================================================
     3. STATS BANNER VỚI HIỆU ỨNG ĐẾM SỐ TỰ ĐỘNG
     ========================================================================= --}}
<div class="stats-banner scroll-reveal stagger-grid" id="statsBannerSection">
    <div class="stat-box">
        <div class="stat-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </div>
        <div class="stat-info">
            <strong class="count-up-number" data-target="{{ $stats['homestays_count'] ?? 12 }}">{{ $stats['homestays_count'] ?? '12+' }}</strong>
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
            <strong class="count-up-number" data-target="{{ $stats['rooms_count'] ?? 36 }}">{{ $stats['rooms_count'] ?? '36+' }}</strong>
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
            <strong class="count-up-number" data-target="{{ $stats['products_count'] ?? 24 }}">{{ $stats['products_count'] ?? '24+' }}</strong>
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
            <strong class="count-up-number" data-target="{{ $stats['experiences_count'] ?? 24 }}">{{ $stats['experiences_count'] ?? '24+' }}</strong>
            <span>Trải nghiệm văn hóa</span>
        </div>
    </div>
</div>

{{-- =========================================================================
     4. KHU VỰC LÀM VIỆC THEO VAI TRÒ (KHI ĐÃ ĐĂNG NHẬP)
     ========================================================================= --}}
@auth
    @php
        $role = auth()->user()->role;
    @endphp
    <section class="role-panel scroll-reveal">
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

{{-- =========================================================================
     5. ĐIỂM ĐẾN DU LỊCH CỘNG ĐỒNG NỔI BẬT
     ========================================================================= --}}
<section class="home-section scroll-reveal">
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

    <div class="dest-grid stagger-grid">
        @foreach($popularDestinations ?? [] as $destination)
            <a href="{{ route('search.results', ['location' => $destination['keyword'], 'checkin' => date('Y-m-d', strtotime('+1 day')), 'checkout' => date('Y-m-d', strtotime('+2 days')), 'guests' => 2]) }}"
               class="dest-card" style="background-image: linear-gradient(180deg, rgba(12, 28, 24, 0.25) 0%, rgba(10, 25, 21, 0.9) 100%), url('{{ $destination['image'] ?? asset('images/banners/hero-banner.jpg') }}');">
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

{{-- =========================================================================
     6. HOMESTAY CỘNG ĐỒNG TIÊU BIỂU
     ========================================================================= --}}
<section class="home-section scroll-reveal">
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

    <div class="cards-grid stagger-grid">
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
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;">
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

{{-- =========================================================================
     7. ĐẶC SẢN OCOP & NÔNG SẢN ĐỊA PHƯƠNG
     ========================================================================= --}}
<section class="home-section scroll-reveal">
    <div class="section-header">
        <div class="section-header-left">
            <span class="eyebrow">Sản vật bản địa</span>
            <h2>Đặc sản OCOP &amp; Nông sản địa phương</h2>
            <p>Gắn kết trực tiếp từ các hợp tác xã, nghệ nhân và gắn liền với từng homestay bản địa</p>
        </div>
        <a href="{{ route('products.index') }}" class="section-link">
            Vào chợ địa phương
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>

    <div class="products-grid stagger-grid">
        @forelse($featuredProducts ?? [] as $product)
            <article class="product-card">
                <div class="product-card-top" style="height: 180px; position: relative; overflow: hidden; background: var(--surface-alt);">
                    <a href="{{ route('products.show', $product) }}" style="display: block; width: 100%; height: 100%;">
                        <img src="{{ $product->image_url }}" alt="Ảnh {{ $product->name }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/products/default.svg') }}';"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;">
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
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <p>Đang cập nhật sản phẩm...</p>
        @endforelse
    </div>
</section>

{{-- =========================================================================
     8. TRẢI NGHIỆM VĂN HÓA BẢN ĐỊA
     ========================================================================= --}}
<section class="home-section scroll-reveal">
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

    <div class="exp-grid stagger-grid">
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

{{-- =========================================================================
     9. VÌ SAO NÊN CHỌN ĐT-07
     ========================================================================= --}}
<section class="features-container scroll-reveal">
    <div class="section-header-left">
        <span class="eyebrow">Giá trị cốt lõi</span>
        <h2>Vì sao nên chọn Sàn Homestay Cộng Đồng ĐT-07?</h2>
        <p>Hệ sinh thái du lịch bền vững kết nối trực tiếp du khách với cộng đồng địa phương</p>
    </div>

    <div class="features-grid-custom stagger-grid">
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

{{-- =========================================================================
     10. CALL TO ACTION BANNER
     ========================================================================= --}}
<div class="cta-banner scroll-reveal">
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

{{-- Nút nổi tìm phòng nhanh góc phải màn hình --}}
<a href="#heroBannerSlider" class="floating-search-fab" title="Lên đầu trang tìm phòng">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
</a>

@endsection

@push('scripts')
<script>
/* ==========================================================================
   HOMEPAGE SCRIPTS: BANNER SLIDESHOW + SCROLL REVEAL + AUTO COUNTERS
   ========================================================================== */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // -------------------------------------------------------------------------
    // 1. BANNER SLIDESHOW LUÂN PHIÊN ĐA ẢNH
    // -------------------------------------------------------------------------
    (function initHeroSlider() {
        var slides = document.querySelectorAll('.hero-slide-item');
        var dots = document.querySelectorAll('.slider-dot');
        var prevBtn = document.getElementById('sliderPrevBtn');
        var nextBtn = document.getElementById('sliderNextBtn');
        var locationText = document.getElementById('heroLocationText');
        var progressBar = document.getElementById('heroProgressBar');

        if (!slides.length) return;

        var currentIndex = 0;
        var totalSlides = slides.length;
        var SLIDE_DURATION = 4800; // 4.8 giây đổi slide
        var slideTimer = null;
        var progressAnimation = null;

        function showSlide(index) {
            slides[currentIndex].classList.remove('active');
            if (dots[currentIndex]) dots[currentIndex].classList.remove('active');

            currentIndex = (index + totalSlides) % totalSlides;

            slides[currentIndex].classList.add('active');
            if (dots[currentIndex]) dots[currentIndex].classList.add('active');

            // Cập nhật nhãn địa danh
            if (locationText) {
                var loc = slides[currentIndex].getAttribute('data-location') || '';
                locationText.textContent = loc;
            }

            // Chạy lại thanh tiến trình
            restartProgressBar();
        }

        function restartProgressBar() {
            if (!progressBar) return;
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
            void progressBar.offsetWidth; // Force reflow
            progressBar.style.transition = 'width ' + SLIDE_DURATION + 'ms linear';
            progressBar.style.width = '100%';
        }

        function nextSlide() {
            showSlide(currentIndex + 1);
        }

        function prevSlide() {
            showSlide(currentIndex - 1);
        }

        function startAutoPlay() {
            clearInterval(slideTimer);
            restartProgressBar();
            slideTimer = setInterval(nextSlide, SLIDE_DURATION);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                nextSlide();
                startAutoPlay();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                prevSlide();
                startAutoPlay();
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var idx = parseInt(this.getAttribute('data-index'), 10);
                showSlide(idx);
                startAutoPlay();
            });
        });

        // Tạm dừng khi rê chuột vào khu vực tìm kiếm hero
        var heroBox = document.getElementById('heroBannerSlider');
        if (heroBox) {
            heroBox.addEventListener('mouseenter', function () {
                clearInterval(slideTimer);
            });
            heroBox.addEventListener('mouseleave', function () {
                startAutoPlay();
            });
        }

        startAutoPlay();
    })();

    // -------------------------------------------------------------------------
    // 2. SCROLL REVEAL AN TOÀN (KHÔNG BAO GIỜ BỊ ẨN MẤT NỘI DUNG)
    // -------------------------------------------------------------------------
    (function initScrollReveal() {
        // Kích hoạt class cho body để bật animation
        document.body.classList.add('has-reveal');

        var revealElements = document.querySelectorAll('.scroll-reveal, .stagger-grid');
        if (!revealElements.length) return;

        if (!('IntersectionObserver' in window)) {
            // Trình duyệt cũ: hiện tất cả ngay
            revealElements.forEach(function (el) {
                el.classList.add('is-visible');
            });
            return;
        }

        var revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -20px 0px'
        });

        revealElements.forEach(function (el) {
            // Nếu phần tử đã nằm trong tầm nhìn lúc đầu: hiện luôn
            var rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight + 50) {
                el.classList.add('is-visible');
            } else {
                revealObserver.observe(el);
            }
        });

        // BẢO VỆ CHỐNG LỖI: Sau 800ms tự động mở tất cả nếu chưa hiện
        setTimeout(function () {
            revealElements.forEach(function (el) {
                el.classList.add('is-visible');
            });
        }, 800);
    })();

    // -------------------------------------------------------------------------
    // 3. HIỆU ỨNG ĐẾM SỐ TĂNG DẦN (AUTO NUMBER COUNTER)
    // -------------------------------------------------------------------------
    (function initNumberCounters() {
        var numbers = document.querySelectorAll('.count-up-number');
        if (!numbers.length) return;

        var hasCounted = false;
        function runCounters() {
            if (hasCounted) return;
            hasCounted = true;

            numbers.forEach(function (num) {
                var target = parseInt(num.getAttribute('data-target'), 10) || 0;
                var current = 0;
                var duration = 1200;
                var stepTime = Math.max(Math.floor(duration / (target || 1)), 25);

                var timer = setInterval(function () {
                    current += Math.ceil(target / 25);
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                        num.textContent = target + '+';
                    } else {
                        num.textContent = current + '+';
                    }
                }, stepTime);
            });
        }

        var statsSection = document.getElementById('statsBannerSection');
        if (statsSection && 'IntersectionObserver' in window) {
            var statsObserver = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting) {
                    runCounters();
                    statsObserver.unobserve(statsSection);
                }
            }, { threshold: 0.3 });
            statsObserver.observe(statsSection);
        } else {
            runCounters();
        }
    })();
});
</script>
@endpush
