@extends('layouts.app')
@section('title', 'Thanh Toán Đơn Hàng ' . $order->code)
@section('content')

@php
    $paidPayment = $order->payments->firstWhere('status', 'paid');
    $canPay = !$paidPayment && in_array($order->status, ['pending', 'confirmed'], true);
@endphp

<div class="page-heading">
    <a href="{{ route('guest.orders.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại đơn hàng
    </a>
    <span class="eyebrow">Thanh toán sản phẩm địa phương</span>
    <h1>Thanh toán đơn {{ $order->code }}</h1>
    <p>Tạm tính: {{ number_format($order->subtotal) }} đ · Dùng {{ $order->points_redeemed }} điểm, giảm {{ number_format($order->discount_amount) }} đ · Còn thanh toán: {{ number_format($order->total_amount) }} đ</p>
    <p>Kiểm tra sản phẩm OCOP/nông sản đã đặt và ghi nhận thanh toán mô phỏng.</p>
</div>

<div class="form-grid" style="grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr); gap: 20px; align-items: start;">
    <section class="card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 18px;">
            <div>
                <span class="badge badge-accent">{{ $order->status }}</span>
                <span class="badge {{ $paidPayment ? 'badge-success' : 'badge-outline' }}">{{ $paidPayment ? 'Đã thanh toán' : 'Chưa thanh toán' }}</span>
            </div>
            <strong style="font-size: 1.35rem; color: var(--primary);">{{ number_format($order->total_amount) }} đ</strong>
        </div>

        <div class="table-wrap" style="border: 0;">
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? ('#' . $item->product_id) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price) }} đ</td>
                            <td>{{ number_format($item->line_total) }} đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <aside class="card" style="padding: 24px;">
        <h2 style="margin-top: 0;">Cổng thanh toán demo</h2>
        <p style="color: var(--muted); margin-bottom: 18px;">Ghi nhận thanh toán đơn hàng sản phẩm, tự chuyển đơn từ pending sang confirmed khi thành công.</p>

        @if($canPay)
            <form method="post" action="{{ route('guest.order-payments.mock', $order) }}" style="display: grid; gap: 10px;">
                @csrf
                <input type="hidden" name="result" value="success">
                <button class="btn btn-accent" type="submit">Thanh toán thành công</button>
            </form>
            <form method="post" action="{{ route('guest.order-payments.mock', $order) }}" style="display: grid; gap: 10px; margin-top: 10px;">
                @csrf
                <input type="hidden" name="result" value="fail">
                <button class="btn btn-secondary" type="submit">Mô phỏng thất bại</button>
            </form>
        @elseif($paidPayment)
            <div class="alert" role="status" style="margin: 0;">Đơn hàng đã thanh toán lúc {{ optional($paidPayment->paid_at)->format('d/m/Y H:i') }}.</div>
        @else
            <div class="alert alert-danger" role="status" style="margin: 0;">Trạng thái đơn hàng hiện tại không cho phép thanh toán.</div>
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
                @forelse($order->payments as $payment)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700;">{{ $payment->txn_ref }}</td>
                        <td>{{ $payment->gateway }}</td>
                        <td>{{ number_format($payment->amount) }} đ</td>
                        <td><span class="badge {{ $payment->status === 'paid' ? 'badge-success' : 'badge-warning' }}">{{ $payment->status }}</span></td>
                        <td>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : 'Chưa thanh toán' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; padding: 28px; color: var(--muted);">Chưa có giao dịch thanh toán.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endsection
