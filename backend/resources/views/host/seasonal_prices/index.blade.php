@extends('layouts.app')
@section('title','Giá mùa')
@section('content')
<h1>Giá mùa</h1>
<div class="card">
    <form method="get" action="{{ route('host.seasonal-prices.index') }}">
        <label>Phòng
            <select name="room_id">
                @foreach($rooms as $item)
                    <option value="{{ $item->id }}" @selected($room && $room->id === $item->id)>{{ $item->name }} - {{ $item->homestay->name }}</option>
                @endforeach
            </select>
        </label>
        <button class="btn" type="submit">Chọn phòng</button>
    </form>
</div>
@if($room)
<div class="card">
    <form method="post" action="{{ route('host.seasonal-prices.store', $room) }}" class="form-grid">
        @csrf
        <label>Tên mùa <input name="name" value="{{ old('name') }}" required></label>
        <label>Từ ngày <input type="date" name="start_date" value="{{ old('start_date') }}" required></label>
        <label>Đến ngày <input type="date" name="end_date" value="{{ old('end_date') }}" required></label>
        <label>Giá/đêm <input type="number" min="0" name="price_per_night" value="{{ old('price_per_night') }}" required></label>
        <label>Ưu tiên <input type="number" min="0" max="255" name="priority" value="{{ old('priority', 1) }}" required></label>
        <div><button class="btn" type="submit">Thêm giá mùa</button></div>
    </form>
</div>
<div class="table-wrap">
<table>
    <thead><tr><th>Tên</th><th>Khoảng ngày</th><th>Giá</th><th>Ưu tiên</th><th></th></tr></thead>
    <tbody>
    @forelse($seasonalPrices as $price)
        <tr>
            <td>{{ $price->name }}</td>
            <td>{{ $price->start_date->toDateString() }} - {{ $price->end_date->toDateString() }}</td>
            <td>{{ number_format($price->price_per_night) }}</td>
            <td>{{ $price->priority }}</td>
            <td><form method="post" action="{{ route('host.seasonal-prices.destroy', $price) }}">@csrf @method('delete')<button class="btn btn-secondary" type="submit">Xóa</button></form></td>
        </tr>
    @empty
        <tr><td colspan="5">Chưa có giá mùa.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endif
@endsection
