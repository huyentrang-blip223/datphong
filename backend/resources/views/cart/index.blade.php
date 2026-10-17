@extends('layouts.app')
@section('title','Giỏ hàng')
@section('content')
<h1>Giỏ hàng</h1><p>Giá được tính lại tại máy chủ. Sản phẩm của từng người bán sẽ thành đơn riêng; chỉ trừ kho khi tạo đơn thành công.</p>
@if($lines)
<div class="table-wrap"><table><thead><tr><th>Sản phẩm</th><th>Người bán</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead><tbody>
@foreach($lines as $line)
<tr><td>{{ $line['product']->name }} @if($line['product']->status!=='published')<strong>Đã ngừng bán</strong>@endif</td><td>{{ $line['product']->seller->full_name }}</td><td>{{ number_format($line['product']->price) }} đ</td><td>
<form method="post" action="{{ route('guest.cart.update') }}">@csrf @method('PUT')<input type="hidden" name="product_id" value="{{ $line['product']->id }}"><label>Số lượng (0 để xóa)<input type="number" name="quantity" min="0" max="1000" value="{{ $line['quantity'] }}" required></label><button class="btn btn-small">Cập nhật</button></form>
</td><td>{{ number_format($line['line_total']) }} đ</td></tr>
@endforeach
</tbody></table></div>
<section class="card"><h2>Tạm tính: {{ number_format(collect($lines)->sum('line_total')) }} đ</h2>
<p>Số dư: {{ $balance }} điểm. 1 điểm = {{ number_format(config('loyalty.redemption_value')) }} đ; tối đa {{ config('loyalty.maximum_discount_percent') }}% mỗi đơn. Điểm dùng được hoàn lại khi hủy đơn.</p>
<form method="post" action="{{ route('guest.cart.checkout') }}">@csrf<input type="hidden" name="checkout_key" value="{{ $checkoutKey }}">
<label>Số điểm muốn dùng<input type="number" name="points" min="0" max="{{ $balance }}" value="0" required></label><button class="btn">Tạo đơn từ giỏ hàng</button></form></section>
@else <p>Giỏ hàng đang trống.</p><a class="btn" href="{{ route('products.index') }}">Tiếp tục mua sắm</a> @endif
@endsection
