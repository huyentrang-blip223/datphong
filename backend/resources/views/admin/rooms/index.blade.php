@extends('layouts.app')
@section('title', 'Admin - Quản lý phòng')
@section('content')

<div class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="eyebrow">Khu vực quản trị</span>
        <h1>Quản lý phòng</h1>
        <p>Admin có thể thêm, sửa, xóa và cập nhật ảnh phòng trên toàn hệ thống.</p>
    </div>
    <a class="btn" href="{{ route('admin.rooms.create') }}">Thêm phòng</a>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Phòng</th>
                    <th>Homestay</th>
                    <th>Sức chứa</th>
                    <th>Giá cơ bản</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rooms as $room)
                <tr>
                    <td><img src="{{ $room->image_url }}" alt="{{ $room->name }}" style="width: 86px; height: 56px; object-fit: cover; border-radius: var(--radius-sm);"></td>
                    <td>
                        <strong style="color: var(--text);">{{ $room->name }}</strong>
                        <div style="font-size: 0.8rem; color: var(--muted);">{{ $room->room_type }}</div>
                    </td>
                    <td>{{ $room->homestay->name }}</td>
                    <td>{{ $room->max_guests }} khách</td>
                    <td style="font-weight: 700; color: var(--primary);">{{ number_format($room->base_price) }} đ</td>
                    <td><span class="badge {{ $room->active ? 'badge-success' : 'badge-outline' }}">{{ $room->active ? 'Đang mở' : 'Tạm ẩn' }}</span></td>
                    <td>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-small btn-secondary">Sửa</a>
                            <form method="post" action="{{ route('admin.rooms.destroy', $room) }}" onsubmit="return confirm('Xóa phòng này?');">
                                @csrf
                                @method('delete')
                                <button class="btn btn-small btn-secondary" type="submit">Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có phòng.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">{{ $rooms->links() }}</div>

@endsection
