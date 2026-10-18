@php
    $cashPending = $paymentSubject->payments->where('gateway', 'cash')->firstWhere('status', 'initiated');
    $waitingCash = $cashPending && !$paidPayment && in_array($paymentSubject->status, ['confirmed', 'packing', 'delivered'], true);
    $vnpayAvailable = isset($vnpayRoute) && config('services.vnpay.enabled') && !$cashPending;
@endphp
<aside class="card" style="padding: 24px;">
    <h2 style="margin-top: 0;">Phương thức thanh toán</h2>
    @if($canPay || $waitingCash)
        <p>{{ $cashDescription }}</p>
        @if($cashPending)
            <p role="status"><strong>Đã chọn tiền mặt — chờ thanh toán trực tiếp.</strong> Đơn vị cung cấp sẽ xác nhận sau khi nhận tiền.</p>
            <button class="btn btn-accent" type="button" disabled>Đã chọn thanh toán tiền mặt</button>
        @else
            <form method="post" action="{{ $cashRoute }}" style="margin-bottom: 12px;">
                @csrf
                <button class="btn btn-accent" type="submit">Thanh toán tiền mặt</button>
            </form>
        @endif
        @if($vnpayAvailable)
            <form method="post" action="{{ $vnpayRoute }}" style="margin-top: 12px;">
                @csrf
                <button class="btn" type="submit">Thanh toán VNPay</button>
            </form>
        @else
            <div style="margin-top: 12px;">
                <button class="btn btn-secondary" type="button" disabled aria-describedby="vnpay-unavailable">Thanh toán VNPay</button>
                <p id="vnpay-unavailable">{{ $cashPending && isset($vnpayRoute) && config('services.vnpay.enabled') ? 'Đơn đã chọn thanh toán tiền mặt.' : 'VNPay hiện chưa khả dụng.' }}</p>
            </div>
        @endif
    @elseif($paidPayment)
        <p role="status">Đã thanh toán {{ match($paidPayment->gateway) { 'cash' => 'bằng tiền mặt', 'vnpay' => 'qua VNPay', default => '' } }} lúc {{ optional($paidPayment->paid_at)->format('d/m/Y H:i') }}.</p>
    @else
        <p role="status">Trạng thái hiện tại không cho phép thanh toán.</p>
    @endif
</aside>
