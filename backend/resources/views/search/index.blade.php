@extends('layouts.app')
@section('title', 'Tìm kiếm phòng Homestay')
@section('content')

<div class="page-banner" style="background-image: linear-gradient(135deg, rgba(14, 42, 36, 0.88) 0%, rgba(12, 34, 30, 0.72) 100%), url('{{ asset('images/banners/ninhbinh-homestay.jpg') }}');">
    <span class="hero-chip hero-chip-ghost" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Lưu trú cộng đồng
    </span>
    <h1>Tìm homestay</h1>
    <p>Hệ thống tra cứu phòng trống thời gian thực với cơ chế chống trùng lịch nghiêm ngặt tại các vùng bản địa.</p>
</div>

<div class="card" style="padding: 24px; margin-bottom: 32px;">
    <form method="get" action="{{ route('search.results') }}">
        <div class="form-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 14px;">
            <div>
                <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Tỉnh/huyện
                </label>
                <input name="location" placeholder="Đà Lạt, Sa Pa, Ninh Bình..." value="{{ old('location', $filters['location'] ?? '') }}">
            </div>
            <div>
                <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Check-in
                </label>
                <input type="date" name="checkin" value="{{ old('checkin', $filters['checkin'] ?? '') }}" required>
            </div>
            <div>
                <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Check-out
                </label>
                <input type="date" name="checkout" value="{{ old('checkout', $filters['checkout'] ?? '') }}" required>
            </div>
            <div>
                <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Số khách
                </label>
                <input type="number" min="1" max="20" name="guests" value="{{ old('guests', $filters['guests'] ?? 2) }}" required>
            </div>
            <div>
                <label>Giá tối thiểu (VNĐ)</label>
                <input type="number" min="0" placeholder="0" name="min_price" value="{{ old('min_price', $filters['min_price'] ?? '') }}">
            </div>
            <div>
                <label>Giá tối đa (VNĐ)</label>
                <input type="number" min="0" placeholder="Không giới hạn" name="max_price" value="{{ old('max_price', $filters['max_price'] ?? '') }}">
            </div>
            <label>Sắp xếp<select name="sort">
                @foreach(['price_asc'=>'Giá tăng dần','price_desc'=>'Giá giảm dần','newest'=>'Mới nhất'] as $value=>$label)
                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'price_asc') === $value)>{{ $label }}</option>
                @endforeach
            </select></label>
            <label>Loại phòng<select name="room_type"><option value="">Tất cả</option>
                @foreach(['private_room'=>'Phòng riêng','family_room'=>'Phòng gia đình','dorm'=>'Giường tập thể','bungalow'=>'Bungalow','whole_house'=>'Nguyên căn'] as $value=>$label)
                <option value="{{ $value }}" @selected(($filters['room_type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select></label>
        </div>
        <div style="display: flex; justify-content: flex-end;">
            <button class="btn" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Tìm phòng
            </button>
        </div>
    </form>
</div>

@if($rooms->count())
    <div class="section-heading">
        <span class="eyebrow">Kết quả tìm kiếm</span>
        <h2>Kết quả phù hợp</h2>
        <p>Tìm thấy {{ $rooms->total() }} phòng còn trống phù hợp tiêu chí của bạn.</p>
    </div>

    <div class="cards-grid" style="margin-top: 16px;">
        @foreach($rooms as $room)
            <div class="homestay-card card" style="margin: 0; padding: 0;">
                <div style="height: 190px; overflow: hidden; position: relative; background: var(--surface-alt);">
                    <a href="{{ route('rooms.show', $room) }}" style="display: block; width: 100%; height: 100%;">
                        <img src="{{ $room->image_url }}" alt="Ảnh {{ $room->name }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/rooms/default.svg') }}';"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </a>
                    <span class="badge badge-success" style="position: absolute; top: 12px; left: 12px;">Đã xác thực</span>
                    <span class="rating-pill" style="position: absolute; top: 12px; right: 12px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6.4-4.8-6.4 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
                        {{ number_format($room->homestay->avg_rating, 1) }}
                    </span>
                </div>
                <div class="homestay-card-body">
                    <h3><a href="{{ route('rooms.show', $room) }}" style="color: var(--text);">{{ $room->homestay->name }} - {{ $room->name }}</a></h3>
                    <div class="homestay-location">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $room->homestay->province }} @if($room->homestay->district) / {{ $room->homestay->district }} @endif
                    </div>
                    
                    <div class="homestay-rooms-summary">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            {{ $room->max_guests }} khách
                        </span>
                        <span>
                            từ <strong>{{ number_format($room->quote['average_unit_price'] ?? $room->base_price) }} đ</strong> / đêm
                        </span>
                    </div>

                    <div class="homestay-card-footer" style="padding-top: 14px; gap: 8px;">
                        <a href="{{ route('rooms.show', $room) }}" class="btn btn-secondary btn-small" style="flex: 1;">
                            Chi tiết
                        </a>
                        @auth
                            @if(auth()->user()->role === 'guest')
                                <form method="post" action="{{ route('bookings.store') }}" style="flex: 1;">
                                    @csrf
                                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                                    <input type="hidden" name="checkin" value="{{ $filters['checkin'] }}">
                                    <input type="hidden" name="checkout" value="{{ $filters['checkout'] }}">
                                    <input type="hidden" name="guests" value="{{ $filters['guests'] }}">
                                    <button class="btn btn-small" type="submit" style="width: 100%;">
                                        Đặt phòng
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{ $rooms->links() }}
@elseif(!empty($filters))
    <div class="card" style="text-align: center; padding: 48px 24px; color: var(--muted);">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; color: var(--muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
        <p style="font-size: 1.1rem; font-weight: 600; margin: 0;">Không có phòng phù hợp.</p>
        <p style="font-size: 0.9rem; margin-top: 4px;">Vui lòng thử chọn khoảng ngày khác hoặc điều chỉnh số lượng khách.</p>
    </div>
@endif

@endsection
