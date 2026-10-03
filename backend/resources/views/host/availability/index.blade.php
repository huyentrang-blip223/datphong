@extends('layouts.app')
@section('title','Lịch phòng')
@section('content')
<h1>Lịch phòng</h1>
<div class="card">
    <form method="get" action="{{ route('host.availability.index') }}" class="form-grid">
        <label>Phòng
            <select name="room_id">
                @foreach($rooms as $item)
                    <option value="{{ $item->id }}" @selected($room && $room->id === $item->id)>{{ $item->name }} - {{ $item->homestay->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Từ ngày <input type="date" name="from" value="{{ request('from', now()->toDateString()) }}"></label>
        <label>Đến ngày <input type="date" name="to" value="{{ request('to', now()->addDays(14)->toDateString()) }}"></label>
        <div><button class="btn" type="submit">Xem lịch</button></div>
    </form>
</div>

@if($room)
<div class="card">
    <h2>Cập nhật khoảng ngày</h2>
    <form method="post" action="{{ route('host.availability.update', $room) }}" class="form-grid">
        @csrf
        <label>Từ ngày <input type="date" name="start_date" value="{{ old('start_date', request('from', now()->toDateString())) }}" required></label>
        <label>Đến ngày <input type="date" name="end_date" value="{{ old('end_date', request('to', now()->toDateString())) }}" required></label>
        <label>Tổng phòng <input type="number" min="0" name="units_total" value="{{ old('units_total', $room->quantity) }}" required></label>
        <label>Đang giữ <input type="number" min="0" name="units_held" value="{{ old('units_held', 0) }}" required></label>
        <label>Giá override <input type="number" min="0" name="price_override" value="{{ old('price_override') }}"></label>
        <label>Trạng thái
            <select name="status">
                <option value="open">open</option>
                <option value="closed">closed</option>
            </select>
        </label>
        <div><button class="btn" type="submit">Lưu lịch</button></div>
    </form>
</div>

<div class="table-wrap">
<table>
    <thead><tr><th>Ngày</th><th>Tổng</th><th>Giữ</th><th>Đã bán</th><th>Còn</th><th>Giá override</th><th>Trạng thái</th></tr></thead>
    <tbody>
    @forelse($availability as $row)
        <tr>
            <td>{{ $row->stay_date->toDateString() }}</td>
            <td>{{ $row->units_total }}</td>
            <td>{{ $row->units_held }}</td>
            <td>{{ $row->units_sold }}</td>
            <td>{{ $row->units_total - $row->units_held - $row->units_sold }}</td>
            <td>{{ $row->price_override ? number_format($row->price_override) : 'Theo giá mùa/cơ bản' }}</td>
            <td>{{ $row->status }}</td>
        </tr>
    @empty
        <tr><td colspan="7">Chưa có dữ liệu trong khoảng ngày.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@else
<div class="card">Bạn chưa có phòng để quản lý lịch.</div>
@endif
@endsection
