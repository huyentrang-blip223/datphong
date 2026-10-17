@extends('layouts.app')
@section('title', 'Quản Trị Thanh Toán | ĐT-07')
@section('content')

<div class="page-heading">
    <span class="eyebrow">Quản trị tài chính</span>
    <h1>Giao dịch thanh toán</h1>
    <p>Tổng hợp thanh toán phòng, đơn sản phẩm và đặt chỗ trải nghiệm.</p>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Loại</th>
                    <th>Mã nghiệp vụ</th>
                    <th>Khách hàng</th>
                    <th>Gateway</th>
                    <th>Txn ref</th>
                    <th>Số tiền</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><span class="badge badge-outline">{{ $payment['type'] }}</span></td>
                        <td style="font-family: monospace; font-weight: 700;">{{ $payment['code'] }}</td>
                        <td>{{ $payment['customer'] }}</td>
                        <td>{{ $payment['gateway'] }}</td>
                        <td style="font-family: monospace;">{{ $payment['txn_ref'] }}</td>
                        <td style="font-weight: 750;">{{ number_format($payment['amount']) }} đ</td>
                        <td>
                            <span class="badge {{ $payment['status'] === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                {{ $payment['status'] }}
                            </span>
                            @if($payment['type'] === 'booking' && $payment['gateway'] === 'vnpay')
                            <span>{{ $payment['refund_state'] ?? '' }}</span>
                            <form method="post" action="{{ route('admin.payments.reconcile',$payment['payment_id']) }}">@csrf<button class="btn btn-small">Đối soát VNPay sandbox</button></form>
                            @endif
                            @if($payment['type'] === 'booking' && $payment['status'] === 'paid' && in_array($payment['booking_status'] ?? '', ['cancelled','expired'],true))
                            <form method="post" action="{{ route('admin.bookings.refund', $payment['booking_id']) }}">@csrf<button class="btn btn-small">Hoàn tiền test/sandbox</button></form>
                            @endif
                        </td>
                        <td>{{ $payment['paid_at'] ? $payment['paid_at']->format('d/m/Y H:i') : optional($payment['created_at'])->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có giao dịch thanh toán.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
