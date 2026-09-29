@extends('layouts.app')
@section('title','Quản trị homestay | ĐT-07')
@section('content')
<h1>Quản trị homestay</h1>
<form method="get" class="card"><label>Từ khóa<input name="q" value="{{ request('q') }}"></label><label>Trạng thái<select name="status"><option value="">Tất cả</option>@foreach(['pending','approved','rejected','suspended'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select></label><button class="btn">Lọc</button></form>
<p><a class="btn" href="{{ route('admin.homestays.create') }}">+ Thêm homestay</a></p>
<table><thead><tr><th>ID</th><th>Tên</th><th>Chủ</th><th>Tỉnh</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@foreach($items as $h)<tr><td>{{ $h->id }}</td><td>{{ $h->name }}</td><td>{{ $h->owner?->full_name }}</td><td>{{ $h->province }}</td><td>{{ $h->status }}</td><td><a href="{{ route('admin.homestays.edit',$h) }}">Sửa</a></td></tr>@endforeach
</tbody></table>
{{ $items->links() }}
@endsection
