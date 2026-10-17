@extends('layouts.app')
@section('title','Thông tin tài khoản')
@section('content')
<h1>{{ $managedUser->exists ? 'Sửa tài khoản' : 'Tạo tài khoản' }}</h1>
<p>Không tự khóa/hạ quyền. Tài khoản đã có dữ liệu nghiệp vụ cần bàn giao trước khi đổi vai trò.</p>
<form class="card" method="post" action="{{ $managedUser->exists ? route('admin.users.update',$managedUser) : route('admin.users.store') }}">@csrf @if($managedUser->exists) @method('PUT') @endif
<label>Họ tên<input name="full_name" value="{{ old('full_name',$managedUser->full_name) }}" maxlength="160" required></label>
<label>Email<input type="email" name="email" value="{{ old('email',$managedUser->email) }}" maxlength="180" required></label>
<label>Điện thoại<input name="phone" value="{{ old('phone',$managedUser->phone) }}" maxlength="20"></label>
<label>Vai trò<select name="role">@foreach(['admin','host','seller','guest'] as $role)<option @selected(old('role',$managedUser->role)===$role)>{{ $role }}</option>@endforeach</select></label>
<label>Trạng thái<select name="status">@foreach(['active','locked'] as $status)<option @selected(old('status',$managedUser->status)===$status)>{{ $status }}</option>@endforeach</select></label>
<label>Mật khẩu {{ $managedUser->exists ? '(để trống nếu giữ nguyên)' : '(ít nhất 8 ký tự)' }}<input type="password" name="password" minlength="8" maxlength="128" autocomplete="new-password" @if(!$managedUser->exists) required @endif></label>
<label>Nhập lại mật khẩu<input type="password" name="password_confirmation" autocomplete="new-password"></label><button class="btn">Lưu tài khoản</button></form>
@endsection
