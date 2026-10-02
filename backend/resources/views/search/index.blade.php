@extends('layouts.app')
@section('title', 'Tìm homestay')
@section('content')
<h1>Tìm homestay</h1>
<div class="card">
    <form method="get" action="{{ route('search.results') }}">
        <label>Tỉnh/huyện
            <input name="location" value="{{ old('location', $filters['location'] ?? '') }}">
        </label>
        <label>Check-in
            <input type="date" name="checkin" value="{{ old('checkin', $filters['checkin'] ?? '') }}" required>
        </label>
        <label>Check-out
            <input type="date" name="checkout" value="{{ old('checkout', $filters['checkout'] ?? '') }}" required>
        </label>
        <label>Số khách
            <input type="number" min="1" max="20" name="guests" value="{{ old('guests', $filters['guests'] ?? 2) }}" required>
        </label>
        <label>Giá tối thiểu
            <input type="number" min="0" name="min_price" value="{{ old('min_price', $filters['min_price'] ?? '') }}">
        </label>
        <label>Giá tối đa
            <input type="number" min="0" name="max_price" value="{{ old('max_price', $filters['max_price'] ?? '') }}">
        </label>
        <button class="btn" type="submit">Tìm phòng</button>
    </form>
</div>

@if($rooms->count())
    <h2>Kết quả phù hợp</h2>
    @foreach($rooms as $room)
        <div class="card">
            <h3>{{ $room->homestay->name }} - {{ $room->name }}</h3>
            <p>{{ $room->homestay->province }} @if($room->homestay->district) / {{ $room->homestay->district }} @endif</p>
            <p>{{ $room->max_guests }} khách, từ {{ number_format($room->quote['average_unit_price'] ?? $room->base_price) }} / đêm</p>
            @auth
                @if(auth()->user()->role === 'guest')
                    <form method="post" action="{{ route('bookings.store') }}">
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $room->id }}">
                        <input type="hidden" name="checkin" value="{{ $filters['checkin'] }}">
                        <input type="hidden" name="checkout" value="{{ $filters['checkout'] }}">
                        <input type="hidden" name="guests" value="{{ $filters['guests'] }}">
                        <button class="btn" type="submit">Đặt phòng</button>
                    </form>
                @endif
            @endauth
        </div>
    @endforeach
@elseif(!empty($filters))
    <div class="card">Không có phòng phù hợp.</div>
@endif
@endsection
