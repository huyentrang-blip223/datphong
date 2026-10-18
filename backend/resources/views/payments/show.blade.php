@extends('layouts.app')
@section('title', 'Thanh Toán Booking ' . $booking->code)
@section('content')

@php
    $paidPayment = $booking->payments->firstWhere('status', 'paid');
    $canPay = !$paidPayment && in_array($booking->status, ['holding', 'pending_payment', 'confirmed'], true);
    $statusClass = match($booking->status) {
        'confirmed', 'completed' => 'badge-success',
        'cancelled' => 'badge-warning',
        'holding', 'pending_payment' => 'badge-accent',
        default => 'badge-outline',
    };
@endphp

<div class="page-heading">
    <a href="{{ route('guest.bookings.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại booking của tôi
    </a>
    <span class="eyebrow">Thanh toán lưu trú</span>
    <h1>Thanh toán booking {{ $booking->code }}</h1>
    <p>Chọn thanh toán tiền mặt hoặc VNPay.</p>
    <p>Chọn phương thức trong 15 phút để xác nhận đặt phòng. Với tiền mặt, bạn thanh toán trực tiếp khi nhận phòng. Đặt phòng đã trả tiền cần hủy trước ngày nhận phòng ít nhất 24 giờ; liên hệ chủ homestay để nhận lại tiền mặt.</p>
    @if($booking->hold_expires_at)<p role="status">Hạn thanh toán: <strong>{{ $booking->hold_expires_at->format('H:i:s d/m/Y') }}</strong></p>@endif
</div>

<div class="form-grid" style="grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr); gap: 20px; align-items: start;">
    <section class="card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 18px;">
            <div>
                <span class="badge {{ $statusClass }}">{{ $booking->status }}</span>
                @if($paidPayment)
                    <span class="badge badge-success">Đã thanh toán</span>
                @else
                    <span class="badge badge-accent">Chưa ghi nhận thanh toán</span>
                @endif
            </div>
            <strong style="font-size: 1.35rem; color: var(--primary);">{{ number_format($booking->total_amount) }} đ</strong>
        </div>

        <div class="table-wrap" style="border: 0;">
            <table>
                <tbody>
                    <tr>
                        <th style="width: 180px;">Homestay</th>
                        <td>{{ $booking->room->homestay->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Phòng</th>
                        <td>{{ $booking->room->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Ngày lưu trú</th>
                        <td>{{ $booking->checkin_date->toDateString() }} - {{ $booking->checkout_date->toDateString() }}</td>
                    </tr>
                    <tr>
                        <th>Số khách</th>
                        <td>{{ $booking->guest_count }} khách</td>
                    </tr>
                    <tr>
                        <th>Số đêm</th>
                        <td>{{ $booking->nights }} đêm</td>
                    </tr>
                    <tr>
                        <th>Đơn giá TB</th>
                        <td>{{ number_format($booking->unit_price) }} đ/đêm</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    @include('payments.options', [
        'paymentSubject' => $booking,
        'cashRoute' => route('guest.payments.cash', $booking),
        'cashDescription' => __('payments.cash_booking', [], 'vi'),
        'vnpayRoute' => route('guest.payments.vnpay', $booking),
    ])
</div>

<section class="card" style="padding: 24px; margin-top: 20px;">
    <h2 style="margin-top: 0;">Lịch sử thanh toán</h2>
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Mã giao dịch</th>
                    <th>Cổng</th>
                    <th>Số tiền</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($booking->payments as $payment)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700;">{{ $payment->txn_ref }}</td>
                        <td>{{ match($payment->gateway) { 'cash' => 'Ti?n m?t', 'vnpay' => 'VNPay', default => 'Giao d?ch c?' } }}</td>
                        <td>{{ number_format($payment->amount) }} đ</td>
                        <td>
                            <span class="badge {{ $payment->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                {{ $payment->status }}
                            </span>
                        </td>
                        <td>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : 'Chưa thanh toán' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 28px; color: var(--muted);">
                            Chưa có giao dịch thanh toán.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endsection
