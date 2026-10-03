@extends('layouts.app')
@section('title', $product->name)
@section('content')
<h1>{{ $product->name }}</h1>
<div class="card">
    <p><strong>Nơi sản xuất:</strong> {{ $product->origin_place }}</p>
    <p><strong>Giá:</strong> {{ number_format($product->price) }} / {{ $product->unit }}</p>
    <p><strong>Tồn kho:</strong> {{ $product->stock_qty }}</p>
    <p>{{ $product->description }}</p>
    @if($product->homestays->count())
        <p><strong>Homestay liên quan:</strong> {{ $product->homestays->pluck('name')->join(', ') }}</p>
    @endif
</div>

@auth
@if(auth()->user()->role === 'guest' && $product->stock_qty > 0)
<div class="card">
    <h2>Đặt mua test</h2>
    <form method="post" action="{{ route('guest.orders.store') }}">
        @csrf
        <input type="hidden" name="items[0][product_id]" value="{{ $product->id }}">
        <label>Số lượng
            <input type="number" min="1" max="{{ $product->stock_qty }}" name="items[0][quantity]" value="1" required>
        </label>
        <button class="btn" type="submit">Tạo đơn hàng</button>
    </form>
</div>
@endif
@endauth

<h2>Sản phẩm gợi ý</h2>
<p>Nguồn gợi ý: {{ $recommendations['source'] === 'python' ? 'Python service' : 'Laravel fallback' }}</p>
@if(!empty($recommendations['message']))
    <div class="alert">{{ $recommendations['message'] }}</div>
@endif
@forelse($recommendations['items'] as $item)
    <div class="card">
        <h3><a href="{{ route('products.show', $item['product_id']) }}">{{ $item['name'] ?? ('Sản phẩm #' . $item['product_id']) }}</a></h3>
        @isset($item['origin_place'])<p>{{ $item['origin_place'] }}</p>@endisset
        @isset($item['price'])<p>{{ number_format($item['price']) }}</p>@endisset
        @isset($item['score'])<p>Điểm tương đồng: {{ $item['score'] === null ? 'N/A' : number_format($item['score'], 3) }}</p>@endisset
    </div>
@empty
    <div class="card">Chưa có sản phẩm gợi ý phù hợp.</div>
@endforelse
@endsection
