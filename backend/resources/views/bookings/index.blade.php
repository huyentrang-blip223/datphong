@extends('layouts.app')
@section('title', 'Lịch Sử Booking Của Tôi')
@section('content')

<div class="page-heading">
    <span class="eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        Tài khoản du khách
    </span>
    <h1>Booking của tôi</h1>
    <p>Theo dõi tình trạng đặt phòng, tiến hành thanh toán hoặc gửi đánh giá trải nghiệm lưu trú.</p>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Phòng</th>
                    <th>Ngày</th>
                    <th>Trạng thái</th>
                    <th>Tổng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td style="font-family: monospace; font-weight: 700; color: var(--primary);">
                        {{ $booking->code }}
                    </td>
                    <td>
                        <strong>{{ $booking->room->name ?? '' }}</strong>
                        @if($booking->room?->homestay)
                            <div style="font-size: 0.8rem; color: var(--muted);">{{ $booking->room->homestay->name }}</div>
                        @endif
                    </td>
                    <td style="font-size: 0.9rem;">
                        {{ $booking->checkin_date->toDateString() }} - {{ $booking->checkout_date->toDateString() }}
                    </td>
                    <td>
                        @php
                            $badgeClass = match($booking->status) {
                                'confirmed' => 'badge-success',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-warning',
                                'holding', 'pending_payment' => 'badge-accent',
                                default => 'badge-outline',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $booking->status }}</span>
                        @if($booking->hold_expires_at)<p>Giữ đến {{ $booking->hold_expires_at->format('H:i:s d/m/Y') }}</p>@endif
                    </td>
                    <td style="font-weight: 750; color: var(--text);">
                        {{ number_format($booking->total_amount) }} đ
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @if(in_array($booking->status,['cancelled','expired'],true) && $booking->payments->contains('status', 'paid'))
                            <form method="post" action="{{ route('guest.bookings.refund', $booking) }}">@csrf<button class="btn btn-small">Hoàn tiền 100% (test/sandbox)</button></form>
                            @endif
                            @if(in_array($booking->status, ['holding','pending_payment','confirmed'], true))
                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                    <form method="post" action="{{ route('guest.bookings.cancel', $booking) }}" style="display: inline;">
                                        @csrf
                                        <button class="btn btn-small btn-secondary" type="submit" style="color: var(--danger) !important; border-color: var(--danger) !important;">
                                            Hủy
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('guest.payments.mock', $booking) }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="result" value="success">
                                        <button class="btn btn-small btn-accent" type="submit">
                                            Thanh toán test
                                        </button>
                                    </form>
                                    <a class="btn btn-small" href="{{ route('guest.payments.show', $booking) }}">
                                        Chi tiết thanh toán
                                    </a>
                                </div>
                            @endif

                            @if($booking->status === 'completed' && !$booking->reviews->where('direction','guest_to_host')->count())
                                <div style="background: var(--surface-alt); padding: 12px; border-radius: var(--radius-sm); margin-top: 6px;">
                                    <form method="post" action="{{ route('guest.reviews.store', $booking) }}">
                                        @csrf
                                        <label style="font-size: 0.82rem;">Rating
                                            <input type="number" min="1" max="5" name="rating" value="5" style="padding: 4px 8px; margin: 4px 0 8px;">
                                        </label>
                                        <label style="font-size: 0.82rem;">Nội dung
                                            <textarea name="content" style="min-height: 60px; padding: 6px 8px; margin: 4px 0 8px;" placeholder="Chia sẻ cảm nhận về homestay..."></textarea>
                                        </label>
                                        <button class="btn btn-small" type="submit">Đánh giá</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 36px; color: var(--muted);">
                        Chưa có booking.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $bookings->links() }}
</div>

@endsection
