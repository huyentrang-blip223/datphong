@extends('layouts.app')
@section('title', 'Quản lý phòng')
@section('content')
<h1>Quản lý phòng</h1>
<p><a class="btn" href="{{ route('host.rooms.create') }}">Thêm phòng</a></p>
<table>
    <thead>
    <tr>
        <th>Phòng</th>
        <th>Homestay</th>
        <th>Sức chứa</th>
        <th>Giá cơ bản</th>
        <th>Trạng thái</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @forelse($rooms as $room)
        <tr>
            <td>{{ $room->name }}</td>
            <td>{{ $room->homestay->name }}</td>
            <td>{{ $room->max_guests }} khách</td>
            <td>{{ number_format($room->base_price) }}</td>
            <td>{{ $room->active ? 'Đang mở' : 'Tạm ẩn' }}</td>
            <td><a href="{{ route('host.rooms.edit', $room) }}">Sửa</a></td>
        </tr>
    @empty
        <tr><td colspan="6">Chưa có phòng.</td></tr>
    @endforelse
    </tbody>
</table>
{{ $rooms->links() }}
@endsection
