@extends('layouts.app')
@section('title','Seller sản phẩm')
@section('content')
<h1>Sản phẩm của seller</h1>
<p><a class="btn" href="{{ route('seller.products.create') }}">Thêm sản phẩm</a> <a class="btn btn-secondary" href="{{ route('seller.orders.index') }}">Đơn hàng liên quan</a></p>
<div class="table-wrap">
<table>
    <thead><tr><th>Tên</th><th>Giá</th><th>Tồn</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    @forelse($products as $product)
        <tr><td>{{ $product->name }}</td><td>{{ number_format($product->price) }}</td><td>{{ $product->stock_qty }}</td><td>{{ $product->status }}</td><td><a href="{{ route('seller.products.edit', $product) }}">Sửa</a></td></tr>
    @empty
        <tr><td colspan="5">Chưa có sản phẩm.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $products->links() }}
@endsection
