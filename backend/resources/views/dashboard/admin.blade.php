@extends('layouts.app')
@section('title','Dashboard quản trị | ĐT-07')
@section('content')
<h1>Dashboard quản trị</h1>
<div class="grid"><div class="card"><strong>{{ $homestayCount }}</strong><br>Homestay</div><div class="card"><strong>{{ $pendingCount }}</strong><br>Chờ duyệt</div><div class="card"><strong>{{ $userCount }}</strong><br>Tài khoản</div></div>
<p><a class="btn" href="{{ route('admin.homestays.index') }}">Quản trị homestay</a></p>
@endsection
