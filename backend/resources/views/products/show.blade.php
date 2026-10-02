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
