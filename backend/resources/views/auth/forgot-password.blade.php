@extends('layouts.app')
@section('title','Quên mật khẩu')
@section('content')
<h1>Quên mật khẩu</h1><p>Nhập email để nhận liên kết đặt lại mật khẩu. Liên kết dùng một lần và có hiệu lực 60 phút.</p>
@if(app()->environment('local') && config('mail.default')==='smtp' && !config('mail.mailers.smtp.username'))<p>Môi trường local chưa cấu hình email: liên kết được ghi trong nhật ký Laravel riêng trên máy chủ.</p>@endif
<form class="card" method="post" action="{{ route('password.email') }}">@csrf
<label>Email<input type="email" name="email" autocomplete="email" maxlength="180" value="{{ old('email') }}" required></label>
<button class="btn">Gửi hướng dẫn</button></form>
@endsection
