@extends('layouts.app')
@section('title', 'Quản Lý Giá Mùa — Seasonal Pricing')
@section('content')

<div class="page-heading">
    <a href="{{ route('host.dashboard') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại dashboard
    </a>
    <h1>Giá mùa</h1>
    <p>Thiết lập các khung giá theo mùa cao điểm, dịp lễ hội hoặc cuối tuần.</p>
</div>

<div class="card" style="padding: 22px; margin-bottom: 24px;">
    <form method="get" action="{{ route('host.seasonal-prices.index') }}">
        <div style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 260px;">
                <label>Chọn phòng homestay
                    <select name="room_id">
                        @foreach($rooms as $item)
                            <option value="{{ $item->id }}" @selected($room && $room->id === $item->id)>{{ $item->name }} — {{ $item->homestay->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <button class="btn btn-secondary" type="submit" style="height: 44px;">
                Chọn phòng
            </button>
        </div>
    </form>
</div>

@if($room)
<div class="card" style="padding: 24px; margin-bottom: 24px; border-color: var(--primary);">
    <h3 style="font-size: 1.18rem; margin-bottom: 16px;">Thêm khung giá mùa mới</h3>
    <form method="post" action="{{ route('host.seasonal-prices.store', $room) }}">
        @csrf
        <div class="form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 16px;">
            <div>
                <label>Tên mùa
                    <input name="name" value="{{ old('name') }}" placeholder="Mùa hoa, Dịp Tết..." required>
                </label>
            </div>
            <div>
                <label>Từ ngày
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required>
                </label>
            </div>
            <div>
                <label>Đến ngày
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required>
                </label>
            </div>
            <div>
                <label>Giá/đêm (VNĐ)
                    <input type="number" min="0" name="price_per_night" value="{{ old('price_per_night') }}" placeholder="Giá áp dụng..." required>
                </label>
            </div>
            <div>
                <label>Ưu tiên (Độ ưu tiên cao hơn sẽ ghi đè)
                    <input type="number" min="0" max="255" name="priority" value="{{ old('priority', 1) }}" required>
                </label>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end;">
            <button class="btn btn-accent" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Thêm giá mùa
            </button>
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Tên</th>
                    <th>Khoảng ngày</th>
                    <th>Giá</th>
                    <th>Ưu tiên</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($seasonalPrices as $price)
                <tr>
                    <td style="font-weight: 700;">{{ $price->name }}</td>
                    <td>{{ $price->start_date->toDateString() }} - {{ $price->end_date->toDateString() }}</td>
                    <td style="font-weight: 750; color: var(--primary);">{{ number_format($price->price_per_night) }} đ</td>
                    <td><span class="badge badge-outline">Mức {{ $price->priority }}</span></td>
                    <td>
                        <form method="post" action="{{ route('host.seasonal-prices.destroy', $price) }}">
                            @csrf
                            @method('delete')
                            <button class="btn btn-small btn-secondary" type="submit" style="color: var(--danger) !important; border-color: var(--danger) !important;">
                                Xóa
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có giá mùa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
