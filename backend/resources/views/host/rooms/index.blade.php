@extends('layouts.app')
@section('title', 'Quản Lý Phòng Homestay')
@section('content')

<div class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            Khu vực chủ homestay
        </span>
        <h1>Quản lý phòng</h1>
        <p>Danh sách các phòng thuộc homestay do bạn sở hữu và vận hành.</p>
    </div>
    <a class="btn" href="{{ route('host.rooms.create') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm phòng
    </a>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
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
                    <td style="font-weight: 750; color: var(--text);">
                        {{ $room->name }}
                        <div style="font-size: 0.8rem; color: var(--muted); font-weight: normal;">
                            {{ $room->room_type }}
                        </div>
                    </td>
                    <td>{{ $room->homestay->name }}</td>
                    <td>
                        <span class="badge badge-outline">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px; margin-right: 2px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            {{ $room->max_guests }} khách
                        </span>
                    </td>
                    <td style="font-weight: 700; color: var(--primary);">
                        {{ number_format($room->base_price) }} đ
                    </td>
                    <td>
                        <span class="badge {{ $room->active ? 'badge-success' : 'badge-outline' }}">
                            {{ $room->active ? 'Đang mở' : 'Tạm ẩn' }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <a href="{{ route('host.rooms.edit', $room) }}" class="btn btn-small btn-secondary">Sửa</a>
                            <a href="{{ route('host.availability.index', ['room_id' => $room->id]) }}" class="btn btn-small btn-secondary">Lịch</a>
                            <a href="{{ route('host.seasonal-prices.index', ['room_id' => $room->id]) }}" class="btn btn-small btn-secondary">Giá mùa</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có phòng.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $rooms->links() }}
</div>

@endsection
