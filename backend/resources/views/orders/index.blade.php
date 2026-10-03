@extends('layouts.app')
@section('title','Đơn hàng sản phẩm')
@section('content')
<h1>Đơn hàng sản phẩm</h1>
<div class="table-wrap">
<table>
    <thead><tr><th>Mã</th><th>Trạng thái</th><th>Tổng</th><th>Sản phẩm</th><th>Cập nhật</th></tr></thead>
    <tbody>
    @forelse($orders as $order)
        <tr>
            <td>{{ $order->code }}</td>
            <td>{{ $order->status }}</td>
            <td>{{ number_format($order->total_amount) }}</td>
            <td>
                @foreach($order->items as $item)
                    <div>{{ $item->product->name ?? ('#'.$item->product_id) }} x {{ $item->quantity }}</div>
                @endforeach
            </td>
            <td>
                @if(auth()->user()->role !== 'guest')
                    <form method="post" action="{{ route(auth()->user()->role === 'seller' ? 'seller.orders.status' : 'seller.orders.status', $order) }}">
                        @csrf
                        <select name="status">
                            @foreach(['confirmed','packing','delivered','cancelled'] as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-small" type="submit">Lưu</button>
                    </form>
                @else
                    <span class="muted">Chỉ xem</span>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="5">Chưa có đơn hàng.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $orders->links() }}
@endsection
