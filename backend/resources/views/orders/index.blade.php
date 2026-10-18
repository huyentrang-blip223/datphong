@extends('layouts.app')
@section('title', 'Đơn Hàng Sản Phẩm & Nông Sản')
@section('content')

<div class="page-heading">
    <span class="eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Quản lý đơn hàng
    </span>
    <h1>Đơn hàng sản phẩm</h1>
    <p>Theo dõi tình trạng đơn đặt hàng nông sản OCOP và đặc sản địa phương.</p>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Trạng thái</th>
                    <th>Tổng</th>
                    <th>Sản phẩm</th>
                    <th>Cập nhật</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td style="font-family: monospace; font-weight: 700; color: var(--primary);">
                        {{ $order->code }}
                    </td>
                    <td>
                        @php
                            $orderBadge = match($order->status) {
                                'delivered' => 'badge-success',
                                'cancelled' => 'badge-warning',
                                'packing', 'confirmed' => 'badge-accent',
                                default => 'badge-outline',
                            };
                        @endphp
                        <span class="badge {{ $orderBadge }}">{{ $order->status }}</span>
                    </td>
                    <td style="font-weight: 750; color: var(--text);">
                        {{ number_format($order->total_amount) }} đ
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            @foreach($order->items as $item)
                                <div style="font-size: 0.88rem;">
                                    <strong>{{ $item->product->name ?? ('#'.$item->product_id) }}</strong>
                                    <span style="color: var(--muted);">x {{ $item->quantity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if(auth()->user()->role !== 'guest')
                            @include('payments.cash_receipt', ['subject' => $order,
                                'collectRoute' => route('seller.orders.cash-received', $order),
                                'refundRoute' => route('seller.orders.cash-refunded', $order)])
                            <form method="post" action="{{ route(auth()->user()->role === 'seller' ? 'seller.orders.status' : 'seller.orders.status', $order) }}" style="display: flex; gap: 6px; align-items: center;">
                                @csrf
                                <select name="status" style="width: auto; padding: 6px 10px; margin: 0;">
                                    @foreach(['confirmed','packing','delivered','cancelled'] as $status)
                                        <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-small" type="submit">Lưu</button>
                            </form>
                        @else
                            <a class="btn btn-small" href="{{ route('guest.order-payments.show', $order) }}">
                                Thanh toán / chi tiết
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 36px; color: var(--muted);">
                        Chưa có đơn hàng.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $orders->links() }}
</div>

@if(auth()->user()->role === 'guest')
<p>Bạn có thể hủy trước khi đóng gói. Nếu đã trả tiền mặt, vui lòng liên hệ người bán để nhận lại tiền.</p>
@foreach($orders as $order)
@if(in_array($order->status, ['pending', 'confirmed']))
<form method="post" action="{{ route('guest.orders.status', $order) }}">@csrf
<input type="hidden" name="status" value="cancelled">
<button class="btn btn-small btn-secondary">Hủy đơn {{ $order->code }}</button>
</form>
@endif
@endforeach
@endif
@endsection
