<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ĐT-07 Homestay Cộng Đồng')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;850&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        (function () {
            const saved = localStorage.getItem('dt07-theme');
            const preferred = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'forest' : 'light';
            document.documentElement.dataset.theme = saved || preferred;
        })();
    </script>
</head>
<body>
<header class="site-header">
    <div class="wrap header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="ĐT-07 Homestay Cộng Đồng">
            <span class="brand-mark">ĐT</span>
            <span class="brand-text"><strong>ĐT-07</strong><small>Homestay Cộng Đồng</small></span>
        </a>
        <nav class="site-nav" aria-label="Điều hướng chính">
            @auth
                @php($role = auth()->user()->role)

                @if($role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Dashboard admin</a>
                    <a href="{{ route('admin.homestays.index') }}">Duyệt homestay</a>
                    <a href="{{ route('admin.rooms.index') }}">Phòng</a>
                    <a href="{{ route('admin.products.index') }}">Sản phẩm</a>
                    <a href="{{ route('admin.payments.index') }}">Thanh toán</a>
                    <a href="{{ route('admin.audit-logs.index') }}">Audit</a>
                @elseif($role === 'host')
                    <a href="{{ route('search.index') }}">Tìm homestay</a>
                    <a href="{{ route('products.index') }}">Sản phẩm</a>
                    <a href="{{ route('experiences.index') }}">Trải nghiệm</a>
                    <a href="{{ route('host.dashboard') }}">Dashboard host</a>
                    <a href="{{ route('host.rooms.index') }}">Phòng</a>
                    <a href="{{ route('host.availability.index') }}">Lịch phòng</a>
                    <a href="{{ route('host.seasonal-prices.index') }}">Giá mùa</a>
                    <a href="{{ route('host.experiences.index') }}">Trải nghiệm</a>
                    <a href="{{ route('host.bookings.index') }}">Booking</a>
                    <a href="{{ route('account.notifications.index') }}">Thông báo</a>
                @elseif($role === 'seller')
                    <a href="{{ route('search.index') }}">Tìm homestay</a>
                    <a href="{{ route('products.index') }}">Sản phẩm</a>
                    <a href="{{ route('experiences.index') }}">Trải nghiệm</a>
                    <a href="{{ route('seller.dashboard') }}">Dashboard seller</a>
                    <a href="{{ route('seller.products.index') }}">Sản phẩm của tôi</a>
                    <a href="{{ route('seller.orders.index') }}">Đơn hàng</a>
                    <a href="{{ route('account.notifications.index') }}">Thông báo</a>
                @elseif($role === 'guest')
                    <a href="{{ route('search.index') }}">Tìm homestay</a>
                    <a href="{{ route('products.index') }}">Sản phẩm</a>
                    <a href="{{ route('experiences.index') }}">Trải nghiệm</a>
                    <a href="{{ route('guest.bookings.index') }}">Booking của tôi</a>
                    <a href="{{ route('guest.orders.index') }}">Đơn hàng</a>
                    <a href="{{ route('account.loyalty.index') }}">Điểm</a>
                    <a href="{{ route('account.notifications.index') }}">Thông báo</a>
                @endif

                <form action="{{ route('logout') }}" method="post" class="inline-form">
                    @csrf
                    <button class="link-button" type="submit">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('search.index') }}">Tìm homestay</a>
                <a href="{{ route('products.index') }}">Sản phẩm</a>
                <a href="{{ route('experiences.index') }}">Trải nghiệm</a>
                <a href="{{ route('login') }}">Đăng nhập</a>
                <a href="{{ route('register') }}">Đăng ký</a>
            @endauth

            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Đổi giao diện">Light</button>
        </nav>
    </div>
</header>

<main class="wrap main-wrap">
    @if(session('status'))<div class="alert" role="status">{{ session('status') }}</div>@endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer">
    <div class="wrap">
        <div class="footer-columns">
            <div class="footer-col footer-col-brand">
                <a class="brand" href="{{ route('home') }}" style="margin-bottom: 12px;">
                    <span class="brand-mark">ĐT</span>
                    <span class="brand-text"><strong>ĐT-07</strong><small>Homestay Cộng Đồng</small></span>
                </a>
                <p class="footer-desc">
                    Hệ sinh thái lưu trú cộng đồng mộc mạc và nông sản OCOP bản địa, bảo tồn văn hóa vùng cao và phát triển kinh tế bền vững cho người dân địa phương.
                </p>
                <span class="badge badge-success">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Xác thực thực địa & Chống trùng phòng
                </span>
            </div>

            <div class="footer-col">
                <h4>Hệ sinh thái</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Trang chủ cộng đồng</a></li>
                    <li><a href="{{ route('products.index') }}">Chợ nông sản OCOP</a></li>
                    <li><a href="{{ route('search.index') }}">Danh mục phòng lưu trú</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Tiêu chuẩn & An toàn</h4>
                <ul class="footer-links">
                    <li><a href="#kiem-duyet">Quy chuẩn kiểm duyệt homestay</a></li>
                    <li><a href="#chong-trung">Bảo đảm lịch phòng thời gian thực</a></li>
                    <li><a href="#nguon-goc">Minh bạch nông sản OCOP</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Học thuật & Đồ án</h4>
                <p style="margin-bottom: 6px;"><strong>Môn học:</strong> CSE703073 — Lập trình ứng dụng web trong du lịch 2</p>
                <p style="margin-bottom: 6px;"><strong>Đề tài:</strong> ĐT-07 Sàn lưu trú cộng đồng homestay gắn với sản phẩm địa phương</p>
                <p style="margin: 0;"><strong>Hỗ trợ kỹ thuật:</strong> hotro@dt07-homestay.vn</p>
            </div>
        </div>

        <div class="footer-bottom">
            <div>
                <strong>CSE703073 - Lập trình ứng dụng web trong du lịch 2</strong>
                <p style="margin: 4px 0 0;">ĐT-07: Sàn lưu trú cộng đồng homestay gắn với sản phẩm địa phương.</p>
            </div>
            <div class="footer-status">
                <span class="status-indicator"></span>
                <span>Dữ liệu mô phỏng phục vụ học thuật. Week 9 code freeze package.</span>
            </div>
        </div>
    </div>
</footer>
<script src="{{ asset('js/theme.js') }}" defer></script>
</body>
</html>
