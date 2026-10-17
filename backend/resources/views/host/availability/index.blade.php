@extends('layouts.app')
@section('title', 'Lịch Phòng & Tồn Phòng Trực Tuyến')
@section('content')

<div class="page-heading">
    <a href="{{ route('host.dashboard') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại dashboard
    </a>
    <h1>Lịch phòng</h1>
    <p>Kiểm soát số phòng mở bán, số lượng giữ và giá override theo từng ngày lưu trú.</p>
</div>

<div class="card" style="padding: 22px; margin-bottom: 24px;">
    <form method="get" action="{{ route('host.availability.index') }}">
        <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr auto; gap: 14px; align-items: flex-end;">
            <div>
                <label>Phòng homestay
                    <select name="room_id">
                        @foreach($rooms as $item)
                            <option value="{{ $item->id }}" @selected($room && $room->id === $item->id)>{{ $item->name }} — {{ $item->homestay->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div>
                <label>Từ ngày
                    <input type="date" name="from" value="{{ request('from', now()->toDateString()) }}">
                </label>
            </div>
            <div>
                <label>Đến ngày
                    <input type="date" name="to" value="{{ request('to', now()->addDays(14)->toDateString()) }}">
                </label>
            </div>
            <div>
                <button class="btn" type="submit" style="height: 44px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Xem lịch
                </button>
            </div>
        </div>
    </form>
</div>

@if($room)
<div class="card" style="padding: 24px; margin-bottom: 24px; border-color: var(--primary);">
    <h2 style="font-size: 1.25rem; margin-bottom: 16px;">Cập nhật khoảng ngày</h2>
    <form method="post" action="{{ route('host.availability.update', $room) }}">
        @csrf
        <div class="form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 16px;">
            <div>
                <label>Từ ngày
                    <input type="date" name="start_date" value="{{ old('start_date', request('from', now()->toDateString())) }}" required>
                </label>
            </div>
            <div>
                <label>Đến ngày
                    <input type="date" name="end_date" value="{{ old('end_date', request('to', now()->toDateString())) }}" required>
                </label>
            </div>
            <div>
                <label>Tổng phòng mở bán
                    <input type="number" min="0" name="units_total" value="{{ old('units_total', $room->quantity) }}" required>
                </label>
            </div>
            <div>
                <label>Đang giữ (Held)
                    <input type="number" min="0" name="units_held" value="{{ old('units_held', 0) }}" required>
                </label>
            </div>
            <div>
                <label>Giá override đặc biệt (VNĐ)
                    <input type="number" min="0" placeholder="Bỏ trống nếu theo giá gốc" name="price_override" value="{{ old('price_override') }}">
                </label>
            </div>
            <div>
                <label>Trạng thái
                    <select name="status">
                        <option value="open">open (Mở bán)</option>
                        <option value="closed">closed (Đóng phòng)</option>
                    </select>
                </label>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end;">
            <button class="btn btn-accent" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Lưu lịch
            </button>
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Tổng</th>
                    <th>Giữ</th>
                    <th>Đã bán</th>
                    <th>Còn</th>
                    <th>Giá override</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
            @forelse($availability as $row)
                <tr>
                    <td style="font-weight: 700;">{{ $row->stay_date->toDateString() }}</td>
                    <td>{{ $row->units_total }}</td>
                    <td style="color: var(--warning);">{{ $row->units_held }}</td>
                    <td style="color: var(--primary); font-weight: 600;">{{ $row->units_sold }}</td>
                    <td style="font-weight: 750; color: {{ ($row->units_total - $row->units_held - $row->units_sold) > 0 ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $row->units_total - $row->units_held - $row->units_sold }}
                    </td>
                    <td>
                        @if($row->price_override)
                            <strong>{{ number_format($row->price_override) }} đ</strong>
                        @else
                            <span class="muted" style="font-size: 0.85rem;">Theo giá mùa/cơ bản</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $row->status === 'open' ? 'badge-success' : 'badge-outline' }}">
                            {{ $row->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có dữ liệu trong khoảng ngày.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="card" style="text-align: center; padding: 40px; color: var(--muted);">
    Bạn chưa có phòng để quản lý lịch.
</div>
@endif

@endsection
