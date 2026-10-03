@extends('layouts.app')
@section('title','Dashboard chủ homestay | ĐT-07')
@section('content')
<h1>Dashboard chủ homestay</h1>
<div class="grid"><div class="card"><strong>{{ $homestayCount }}</strong><br>Cơ sở của tôi</div><div class="card"><strong>{{ $pendingCount }}</strong><br>Đang chờ duyệt</div></div>
<p><a class="btn" href="{{ route('host.rooms.index') }}">Quản lý phòng</a> <a class="btn btn-secondary" href="{{ route('host.availability.index') }}">Lịch phòng</a> <a class="btn btn-secondary" href="{{ route('host.seasonal-prices.index') }}">Giá mùa</a></p>
@endsection
