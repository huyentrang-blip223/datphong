<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ĐT-07 Homestay Cộng Đồng')</title>
    <style>
        body{font-family:Arial,sans-serif;margin:0;background:#f6f7f8;color:#222}.wrap{max-width:1100px;margin:auto;padding:16px}
        header,footer{background:#fff;border-bottom:1px solid #ddd}footer{border-top:1px solid #ddd;border-bottom:0;margin-top:36px}
        nav a{margin-right:14px}.card{background:#fff;border:1px solid #ddd;border-radius:10px;padding:16px;margin:12px 0}
        table{width:100%;border-collapse:collapse;background:#fff}th,td{border:1px solid #ddd;padding:8px;text-align:left}.btn{display:inline-block;padding:8px 12px;border:1px solid #333;border-radius:6px;text-decoration:none;background:#fff;color:#111}
        input,select,textarea{width:100%;box-sizing:border-box;padding:8px;margin-top:4px;margin-bottom:10px}.alert{padding:10px;background:#fff;border:1px solid #999;margin:10px 0}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
        @media(max-width:700px){.grid{grid-template-columns:1fr}.wrap{padding:12px}}
    </style>
</head>
<body>
<header><div class="wrap"><strong>ĐT-07 Homestay Cộng Đồng</strong><nav style="margin-top:8px">
<a href="{{ route('home') }}">Trang chủ</a>
@auth
@if(auth()->user()->role === 'admin')<a href="{{ route('admin.dashboard') }}">Quản trị</a>@endif
@if(auth()->user()->role === 'host')<a href="{{ route('host.dashboard') }}">Chủ homestay</a>@endif
<form action="{{ route('logout') }}" method="post" style="display:inline">@csrf <button>Đăng xuất</button></form>
@else <a href="{{ route('login') }}">Đăng nhập</a> @endauth
</nav></div></header>
<main class="wrap">
@if(session('status'))<div class="alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
</body></html>
