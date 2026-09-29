@extends('layouts.app')
@section('title','Dashboard chủ homestay | ĐT-07')
@section('content')
<h1>Dashboard chủ homestay</h1>
<div class="grid"><div class="card"><strong>{{ $homestayCount }}</strong><br>Cơ sở của tôi</div><div class="card"><strong>{{ $pendingCount }}</strong><br>Đang chờ duyệt</div></div>
<p>Tuần 5 mới dựng khung role + điều hướng; quản lý phòng/lịch/giá sẽ tiếp tục ở Tuần 6.</p>
@endsection
