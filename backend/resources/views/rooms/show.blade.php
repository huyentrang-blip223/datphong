@extends('layouts.app')
@section('title', $room->name . ' — ' . $room->homestay->name . ' | ĐT-07')
@section('content')

{{-- 1. BREADCRUMBS & TIÊU ĐỀ --}}
<div class="page-heading" style="margin-bottom: 20px;">
    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: var(--muted); margin-bottom: 8px; flex-wrap: wrap;">
        <a href="{{ route('home') }}" style="color: var(--muted);">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('search.index') }}" style="color: var(--muted);">Tìm homestay</a>
        <span>/</span>
        <span style="color: var(--primary); font-weight: 600;">{{ $room->homestay->name }}</span>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 2.1rem; margin-bottom: 6px;">{{ $room->name }}</h1>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 0.95rem; color: var(--muted);">
                <span style="font-weight: 700; color: var(--text); display: inline-flex; align-items: center; gap: 4px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    {{ $room->homestay->name }}
                </span>
                <span>•</span>
                <span style="display: inline-flex; align-items: center; gap: 4px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ $room->homestay->address_line }}, {{ $room->homestay->district ? $room->homestay->district . ', ' : '' }}{{ $room->homestay->province }}
                </span>
                <span>•</span>
                <span class="rating-pill">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6.4-4.8-6.4 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
                    {{ number_format($room->homestay->avg_rating, 1) }}
                </span>
                <span class="badge badge-success">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Homestay đã xác thực
                </span>
            </div>
        </div>
        <a href="{{ route('search.index') }}" class="btn btn-secondary btn-small">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Quay lại tìm kiếm
        </a>
    </div>
</div>

{{-- 2. THƯ VIỆN ẢNH PHÒNG & HOMESTAY (PHOTO GALLERY) --}}
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 32px; border-radius: var(--radius-lg); overflow: hidden;">
    <div style="height: 380px; position: relative; background: var(--surface-alt);">
        <img src="{{ $room->image_url }}" alt="Ảnh {{ $room->name }}"
             onerror="this.onerror=null;this.src='{{ asset('images/rooms/default.svg') }}';"
             style="width: 100%; height: 100%; object-fit: cover; border-radius: 0;">
        <span class="badge badge-accent" style="position: absolute; bottom: 16px; left: 16px; font-size: 0.82rem; padding: 6px 14px;">
            Ảnh thực tế phòng: {{ $room->name }}
        </span>
    </div>
    <div style="display: grid; grid-template-rows: 1fr 1fr; gap: 14px; height: 380px;">
        <div style="position: relative; background: var(--surface-alt); overflow: hidden;">
            <img src="{{ $room->homestay->cover_image_url }}" alt="Ảnh {{ $room->homestay->name }}"
                 onerror="this.onerror=null;this.src='{{ asset('images/homestays/default.svg') }}';"
                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 0;">
            <span class="badge" style="position: absolute; bottom: 12px; left: 12px; background: rgba(0,0,0,0.65); color: #fff;">
                Khuôn viên Homestay
            </span>
        </div>
        <div style="position: relative; background: var(--surface-alt); overflow: hidden;">
            <img src="{{ asset('images/banners/dalat-homestay.jpg') }}" alt="Không gian tiện nghi"
                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 0;">
            <span class="badge" style="position: absolute; bottom: 12px; left: 12px; background: rgba(0,0,0,0.65); color: #fff;">
                Không gian tiện nghi
            </span>
        </div>
    </div>
</div>

{{-- 3. BỐ CỤC 2 CỘT: CHI TIẾT PHÒNG & WIDGET ĐẶT PHÒNG --}}
<div class="form-grid" style="grid-template-columns: 1.7fr 1fr; gap: 28px; align-items: start;">
    
    {{-- CỘT TRÁI: THÔNG SỐ, MÔ TẢ, TIỆN NGHI, SẢN PHẨM & TRẢI NGHIỆM LIÊN QUAN --}}
    <div>
        {{-- Thông số cốt lõi của phòng --}}
        <div class="card" style="padding: 24px; margin-top: 0; display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; text-align: center;">
            <div style="border-right: 1px solid var(--border-light);">
                <div style="font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Loại phòng</div>
                <div style="font-size: 1.05rem; font-weight: 750; color: var(--text); margin-top: 4px;">
                    {{ match($room->room_type) {
                        'private_room' => 'Phòng riêng',
                        'family_room' => 'Phòng gia đình',
                        'dorm' => 'Dorm tập thể',
                        'bungalow' => 'Bungalow',
                        'whole_house' => 'Nguyên căn',
                        default => $room->room_type,
                    } }}
                </div>
            </div>
            <div style="border-right: 1px solid var(--border-light);">
                <div style="font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Sức chứa</div>
                <div style="font-size: 1.05rem; font-weight: 750; color: var(--primary-strong); margin-top: 4px;">
                    Tối đa {{ $room->max_guests }} khách
                </div>
            </div>
            <div style="border-right: 1px solid var(--border-light);">
                <div style="font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Giường ngủ</div>
                <div style="font-size: 1.05rem; font-weight: 750; color: var(--text); margin-top: 4px;">
                    {{ $room->bed_count }} giường
                </div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Số phòng</div>
                <div style="font-size: 1.05rem; font-weight: 750; color: var(--accent); margin-top: 4px;">
                    {{ $room->quantity }} phòng sẵn có
                </div>
            </div>
        </div>

        {{-- Mô tả không gian homestay & phòng --}}
        <div class="card" style="padding: 28px;">
            <h3 style="font-size: 1.25rem; margin-bottom: 12px;">Giới thiệu không gian phòng & Homestay</h3>
            <p style="color: var(--text); line-height: 1.7; font-size: 0.98rem; margin-bottom: 20px;">
                {{ $room->homestay->description ?? 'Homestay cộng đồng mộc mạc, gần gũi với thiên nhiên bản địa. Không gian yên tĩnh, thoáng đãng, mang đến cho du khách những phút giây thư thái trọn vẹn.' }}
            </p>

            <div style="background: var(--surface-alt); padding: 18px 20px; border-radius: var(--radius-md); display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <span style="font-size: 0.82rem; color: var(--muted); font-weight: 700; text-transform: uppercase;">Giờ nhận phòng:</span>
                    <p style="font-size: 1rem; font-weight: 700; color: var(--text); margin: 2px 0 0;">Từ {{ substr($room->homestay->checkin_time, 0, 5) }}</p>
                </div>
                <div>
                    <span style="font-size: 0.82rem; color: var(--muted); font-weight: 700; text-transform: uppercase;">Giờ trả phòng:</span>
                    <p style="font-size: 1rem; font-weight: 700; color: var(--text); margin: 2px 0 0;">Trước {{ substr($room->homestay->checkout_time, 0, 5) }}</p>
                </div>
                @if($room->homestay->owner)
                    <div style="grid-column: span 2; border-top: 1px solid var(--border-light); padding-top: 10px;">
                        <span style="font-size: 0.82rem; color: var(--muted); font-weight: 700; text-transform: uppercase;">Chủ nhà bản địa:</span>
                        <p style="font-size: 0.95rem; font-weight: 600; color: var(--primary); margin: 2px 0 0;">
                            {{ $room->homestay->owner->full_name }} ({{ $room->homestay->owner->phone ?? 'Hỗ trợ 24/7' }})
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tiện nghi phòng tiêu chuẩn --}}
        <div class="card" style="padding: 28px;">
            <h3 style="font-size: 1.25rem; margin-bottom: 16px;">Tiện nghi & Dịch vụ đi kèm</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                    Wifi tốc độ cao
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Nước nóng năng lượng
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    Bữa sáng bản địa
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Ban công view thoáng
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    Trà & Cà phê miễn phí
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    Chỗ đỗ xe rộng rãi
                </div>
            </div>
        </div>

        {{-- Các phòng khác cùng Homestay --}}
        @if($room->homestay->rooms->where('id', '!=', $room->id)->count())
            <div class="card" style="padding: 28px;">
                <h3 style="font-size: 1.25rem; margin-bottom: 14px;">Các loại phòng khác tại {{ $room->homestay->name }}</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
                    @foreach($room->homestay->rooms->where('id', '!=', $room->id) as $otherRoom)
                        <div style="background: var(--surface-alt); padding: 14px; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="font-size: 0.96rem; color: var(--text);">{{ $otherRoom->name }}</strong>
                                <div style="font-size: 0.82rem; color: var(--muted); margin-top: 2px;">
                                    Tối đa {{ $otherRoom->max_guests }} khách • {{ number_format($otherRoom->base_price) }} đ/đêm
                                </div>
                            </div>
                            <a href="{{ route('rooms.show', $otherRoom) }}" class="btn btn-small btn-secondary">
                                Xem
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Đặc sản OCOP liên kết với Homestay --}}
        @if($room->homestay->localProducts->count())
            <div class="card" style="padding: 28px;">
                <h3 style="font-size: 1.25rem; margin-bottom: 14px;">Đặc sản OCOP bản địa có tại Homestay</h3>
                <div class="products-grid" style="grid-template-columns: repeat(2, 1fr); gap: 14px;">
                    @foreach($room->homestay->localProducts->take(2) as $prod)
                        <div class="product-card" style="margin: 0;">
                            <div class="product-card-top" style="height: 120px;">
                                <img src="{{ $prod->image_url }}" alt="Ảnh {{ $prod->name }}"
                                     onerror="this.onerror=null;this.src='{{ asset('images/products/default.svg') }}';"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="product-card-body" style="padding: 14px;">
                                <strong style="font-size: 1rem;"><a href="{{ route('products.show', $prod) }}">{{ $prod->name }}</a></strong>
                                <div style="font-size: 0.84rem; color: var(--accent); font-weight: 750; margin-top: 4px;">
                                    {{ number_format($prod->price) }} đ / {{ $prod->unit }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- CỘT PHẢI: WIDGET ĐẶT PHÒNG THỜI GIAN THỰC (STICKY BOOKING BOX) --}}
    <div style="position: sticky; top: 90px;">
        <div class="card" style="padding: 28px; border-radius: var(--radius-lg); border-color: var(--primary); box-shadow: var(--shadow-lg); margin-top: 0;">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border-light);">
                <div>
                    <span style="font-size: 0.8rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Giá phòng:</span>
                    <div class="price-tag" style="font-size: 1.6rem; color: var(--primary-strong); font-weight: 800;">
                        {{ number_format($quote['average_unit_price'] ?? $room->base_price) }} đ
                        <small style="font-size: 0.88rem; font-weight: 500; color: var(--muted);">/ đêm</small>
                    </div>
                </div>
                <span class="badge badge-success">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Phòng có sẵn
                </span>
            </div>

            @auth
                @if(auth()->user()->role === 'guest')
                    <form method="post" action="{{ route('bookings.store') }}">
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $room->id }}">

                        <div style="margin-bottom: 14px;">
                            <label style="font-size: 0.85rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Ngày nhận phòng (Check-in)
                            </label>
                            <input type="date" name="checkin" value="{{ $checkin }}" required style="margin-top: 4px;">
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label style="font-size: 0.85rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Ngày trả phòng (Check-out)
                            </label>
                            <input type="date" name="checkout" value="{{ $checkout }}" required style="margin-top: 4px;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 0.85rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                Số khách lưu trú
                            </label>
                            <input type="number" min="1" max="{{ $room->max_guests }}" name="guests" value="{{ min($guests, $room->max_guests) }}" required style="margin-top: 4px;">
                            <span style="font-size: 0.78rem; color: var(--muted);">Tối đa {{ $room->max_guests }} khách cho loại phòng này</span>
                        </div>

                        <button class="btn btn-accent" type="submit" style="width: 100%; height: 46px; font-size: 1.05rem; font-weight: 750;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            Đặt phòng ngay
                        </button>
                    </form>
                @else
                    <div style="background: var(--surface-alt); padding: 16px; border-radius: var(--radius-md); text-align: center;">
                        <p style="margin: 0; font-size: 0.92rem; color: var(--muted);">
                            Bạn đang đăng nhập với quyền <strong>{{ strtoupper(auth()->user()->role) }}</strong>. Chỉ tài khoản Khách du lịch mới có thể đặt phòng trực tiếp.
                        </p>
                    </div>
                @endif
            @else
                <div style="text-align: center; padding: 12px 0;">
                    <p style="color: var(--muted); font-size: 0.92rem; margin-bottom: 16px;">
                        Vui lòng đăng nhập tài khoản du khách để đặt giữ phòng ngay lập tức.
                    </p>
                    <a href="{{ route('login') }}" class="btn btn-accent" style="width: 100%;">
                        Đăng nhập để đặt phòng
                    </a>
                    <p style="font-size: 0.85rem; margin-top: 10px; color: var(--muted);">
                        Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký miễn phí</a>
                    </p>
                </div>
            @endauth

            <div style="margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--border-light); font-size: 0.82rem; color: var(--muted); display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Khóa phòng tức thì chống trùng lịch</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Không thu phụ phí thanh toán ẩn</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Tích lũy điểm thưởng thành viên sau khi ở</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
