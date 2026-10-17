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
    <p>Kiểm tra thông tin đặt phòng và ghi nhận thanh toán mô phỏng cho demo học thuật.</p>
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

    <aside class="card" style="padding: 24px;">
        <h2 style="margin-top: 0;">Cổng thanh toán demo</h2>
        <p style="color: var(--muted); margin-bottom: 18px;">Mô phỏng gateway để chứng minh luồng thanh toán, ghi nhận payment, audit log, notification và điểm thưởng.</p>

        @if($canPay)
            <form method="post" action="{{ route('guest.payments.vnpay', $booking) }}" style="display: grid; gap: 10px; margin-bottom: 10px;">
                @csrf
                <button class="btn" type="submit">
                    Thanh toán VNPay sandbox
                </button>
            </form>
            <form method="post" action="{{ route('guest.payments.mock', $booking) }}" style="display: grid; gap: 10px;">
                @csrf
                <input type="hidden" name="result" value="success">
                <button class="btn btn-accent" type="submit">
                    Thanh toán thành công
                </button>
            </form>
            <form method="post" action="{{ route('guest.payments.mock', $booking) }}" style="display: grid; gap: 10px; margin-top: 10px;">
                @csrf
                <input type="hidden" name="result" value="fail">
                <button class="btn btn-secondary" type="submit">
                    Mô phỏng thất bại
                </button>
            </form>
        @elseif($paidPayment)
            <div class="alert" role="status" style="margin: 0;">
                Booking này đã thanh toán thành công lúc {{ optional($paidPayment->paid_at)->format('d/m/Y H:i') }}.
            </div>
        @else
            <div class="alert alert-danger" role="status" style="margin: 0;">
                Trạng thái booking hiện tại không cho phép thanh toán.
            </div>
        @endif
    </aside>
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
                        <td>{{ $payment->gateway }}</td>
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
