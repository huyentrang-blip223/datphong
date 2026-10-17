@extends('layouts.app')
@section('title','Đặt lại mật khẩu')
@section('content')
<h1>Đặt lại mật khẩu</h1>
<form class="card" method="post" action="{{ route('password.update') }}">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<label>Email<input type="email" name="email" value="{{ old('email',$email) }}" maxlength="180" required autocomplete="email"></label>
<label>Mật khẩu mới<input type="password" name="password" minlength="8" maxlength="128" autocomplete="new-password" required></label>
<label>Nhập lại mật khẩu<input type="password" name="password_confirmation" autocomplete="new-password" required></label>
<button class="btn">Đặt lại mật khẩu</button></form>
@endsection
