@php
    $cashToCollect = $subject->payments->where('gateway', 'cash')->firstWhere('status', 'initiated');
    $cashPaid = $subject->payments->where('gateway', 'cash')->firstWhere('status', 'paid');
    $anyPaid = $subject->payments->contains('status', 'paid');
@endphp
@if($cashToCollect && !$anyPaid && in_array($subject->status, ['confirmed', 'packing', 'delivered'], true))
    <p>Tiền mặt: chờ thu {{ number_format($cashToCollect->amount) }} đ</p>
    <form method="post" action="{{ $collectRoute }}" style="margin: 8px 0;">
        @csrf
        <label><input type="checkbox" name="confirmed" value="1" style="width: auto;" required> Đã thực nhận đủ tiền mặt</label>
        <button class="btn btn-small" type="submit">Xác nhận đã thu tiền</button>
    </form>
@elseif($cashPaid && in_array($subject->status, ['cancelled', 'expired'], true))
    <form method="post" action="{{ $refundRoute }}" style="margin: 8px 0;">
        @csrf
        <label><input type="checkbox" name="confirmed" value="1" style="width: auto;" required> Đã trả lại {{ number_format($cashPaid->amount) }} đ tiền mặt cho khách</label>
        <button class="btn btn-small" type="submit">Xác nhận đã hoàn tiền</button>
    </form>
@elseif($cashPaid)
    <p>Đã thu tiền mặt</p>
@endif
