@extends('layouts.app')
@section('title', 'Booking Liên Quan Homestay Của Tôi')
@section('content')

<div class="page-heading">
    <a href="{{ route('host.dashboard') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại dashboard
    </a>
    <span class="eyebrow">Quản lý đặt phòng</span>
    <h1>Booking liên quan homestay của tôi</h1>
    <p>Theo dõi danh sách khách đặt phòng, tình trạng lưu trú và tổng doanh thu.</p>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Khách</th>
                    <th>Homestay</th>
                    <th>Phòng</th>
                    <th>Ngày</th>
                    <th>Trạng thái</th>
                    <th>Tổng</th>
                </tr>
            </thead>
            <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td style="font-family: monospace; font-weight: 700; color: var(--primary);">
                        {{ $booking->code }}
                    </td>
                    <td style="font-weight: 600;">
                        {{ $booking->guest->full_name ?? 'N/A' }}
                    </td>
                    <td>{{ $booking->room->homestay->name ?? '' }}</td>
                    <td style="font-weight: 600;">{{ $booking->room->name ?? '' }}</td>
                    <td style="font-size: 0.88rem;">
                        {{ $booking->checkin_date->toDateString() }} - {{ $booking->checkout_date->toDateString() }}
                    </td>
                    <td>
                        @php
                            $stClass = match($booking->status) {
                                'confirmed', 'completed' => 'badge-success',
                                'cancelled' => 'badge-warning',
                                'holding', 'pending_payment' => 'badge-accent',
                                default => 'badge-outline',
                            };
                        @endphp
                        <span class="badge {{ $stClass }}">{{ $booking->status }}</span>
                        @if($booking->status === 'confirmed')
                        <form method="post" action="{{ route('host.bookings.complete', $booking) }}">@csrf<button class="btn btn-small">Hoàn tất lưu trú</button></form>
                        @endif
                    </td>
                    <td style="font-weight: 750; color: var(--text);">
                        {{ number_format($booking->total_amount) }} đ
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có booking liên quan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $bookings->links() }}
</div>

@endsection
