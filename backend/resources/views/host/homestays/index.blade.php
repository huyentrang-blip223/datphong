@extends('layouts.app')
@section('title', 'Homestay của tôi')
@section('content')
<h1>Homestay của tôi</h1>
<a class="btn" href="{{ route('host.homestays.create') }}">Đăng ký homestay</a>
<div class="table-wrap"><table><thead><tr><th>Tên</th><th>Tỉnh</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($homestays as $homestay)
<tr><td>{{ $homestay->name }}</td><td>{{ $homestay->province }}</td><td>{{ $homestay->status }}</td><td><a href="{{ route('host.homestays.edit', $homestay) }}">Sửa / hồ sơ duyệt</a></td></tr>
@empty <tr><td colspan="4">Chưa có homestay.</td></tr> @endforelse
</tbody></table></div>
{{ $homestays->links() }}
@endsection
