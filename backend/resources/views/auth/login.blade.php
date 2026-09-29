@extends('layouts.app')
@section('title','Đăng nhập | ĐT-07')
@section('content')
<div class="card" style="max-width:480px;margin:30px auto">
<h1>Đăng nhập</h1>
<form method="post" action="{{ route('login.submit') }}">@csrf
<label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
<label>Mật khẩu<input type="password" name="password" required minlength="8"></label>
<button class="btn" type="submit">Đăng nhập</button>
</form></div>
@endsection
