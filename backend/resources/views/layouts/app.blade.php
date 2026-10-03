<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
<header class="site-header"><div class="wrap header-inner"><strong>ĐT-07 Homestay Cộng Đồng</strong><nav class="site-nav">
<a href="{{ route('home') }}">Trang chủ</a>
<a href="{{ route('search.index') }}">Tìm homestay</a>
<a href="{{ route('products.index') }}">Sản phẩm</a>
<a href="{{ route('experiences.index') }}">Trải nghiệm</a>
@auth
@if(auth()->user()->role === 'admin')<a href="{{ route('admin.dashboard') }}">Quản trị</a>@endif
@if(auth()->user()->role === 'host')<a href="{{ route('host.dashboard') }}">Chủ homestay</a><a href="{{ route('host.rooms.index') }}">Phòng</a><a href="{{ route('host.availability.index') }}">Lịch phòng</a><a href="{{ route('host.seasonal-prices.index') }}">Giá mùa</a>@endif
@if(auth()->user()->role === 'seller')<a href="{{ route('seller.products.index') }}">Seller</a>@endif
@if(auth()->user()->role === 'guest')<a href="{{ route('guest.bookings.index') }}">Booking của tôi</a><a href="{{ route('guest.orders.index') }}">Đơn hàng</a>@endif
<form action="{{ route('logout') }}" method="post" class="inline-form">@csrf <button class="link-button">Đăng xuất</button></form>
@else <a href="{{ route('login') }}">Đăng nhập</a> @endauth
<button class="btn btn-small" type="button" data-theme-toggle aria-label="Đổi theme">Theme</button>
</nav></div></header>
<main class="wrap">
@if(session('status'))<div class="alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
<footer class="site-footer">
    <div class="wrap">
        <strong>CSE703073 - Lập trình ứng dụng web trong du lịch 2</strong>
        <p>ĐT-07 Homestay Cộng Đồng. Sản phẩm học thuật, dữ liệu mô phỏng/project-seed, phiên bản Week 8 Round 1.</p>
    </div>
</footer>
<script src="{{ asset('js/theme.js') }}" defer></script>
</body></html>
