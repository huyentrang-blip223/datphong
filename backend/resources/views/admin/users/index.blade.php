@extends('layouts.app')
@section('title','Quản trị người dùng')
@section('content')
<h1>Quản trị người dùng</h1><a class="btn" href="{{ route('admin.users.create') }}">Tạo tài khoản</a>
<form method="get" class="form-grid">
<label>Tìm theo tên/email<input name="q" value="{{ request('q') }}" maxlength="180"></label>
<label>Vai trò<select name="role"><option value="">Tất cả</option>@foreach(['admin','guest','host','seller'] as $role)<option @selected(request('role')===$role)>{{ $role }}</option>@endforeach</select></label>
<label>Trạng thái<select name="status"><option value="">Tất cả</option>@foreach(['active','locked'] as $status)<option @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select></label><button class="btn">Lọc</button></form>
<div class="table-wrap"><table><thead><tr><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th></th></tr></thead><tbody>
@forelse($users as $managedUser)<tr><td>{{ $managedUser->full_name }}</td><td>{{ $managedUser->email }}</td><td>{{ $managedUser->role }}</td><td>{{ $managedUser->status }}</td><td><a href="{{ route('admin.users.edit',$managedUser) }}">Sửa / khóa / vai trò</a></td></tr>@empty<tr><td colspan="5">Không có tài khoản phù hợp.</td></tr>@endforelse
</tbody></table></div>{{ $users->links() }}
@endsection
