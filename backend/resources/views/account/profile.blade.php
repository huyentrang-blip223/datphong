@extends('layouts.app')
@section('title','Hồ sơ cá nhân')
@section('content')
<h1>Hồ sơ cá nhân</h1>
<form class="card" method="post" action="{{ route('account.profile.update') }}">@csrf @method('PUT')
<label>Họ tên<input name="full_name" value="{{ old('full_name',$user->full_name) }}" maxlength="160" required autocomplete="name"></label>
<label>Email<input type="email" name="email" value="{{ old('email',$user->email) }}" maxlength="180" required autocomplete="email"></label>
<label>Điện thoại<input name="phone" value="{{ old('phone',$user->phone) }}" maxlength="20" autocomplete="tel"></label>
<label>Mật khẩu hiện tại để xác nhận<input type="password" name="current_password" autocomplete="current-password" required></label>
<button class="btn">Lưu hồ sơ</button></form>
<h2>Đổi mật khẩu</h2>
<form class="card" method="post" action="{{ route('account.password.update') }}">@csrf @method('PUT')
<label>Mật khẩu hiện tại<input type="password" name="current_password" autocomplete="current-password" required></label>
<label>Mật khẩu mới (8–128 ký tự)<input type="password" name="password" autocomplete="new-password" minlength="8" maxlength="128" required></label>
<label>Nhập lại mật khẩu mới<input type="password" name="password_confirmation" autocomplete="new-password" required></label>
<button class="btn">Đổi mật khẩu</button></form>
@endsection
