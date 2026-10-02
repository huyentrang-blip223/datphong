@extends('layouts.app')
@section('title', 'Sản phẩm địa phương')
@section('content')
<h1>Sản phẩm địa phương</h1>
<div class="card">
    <form method="get" action="{{ route('products.index') }}">
        <label>Tìm kiếm
            <input name="q" value="{{ request('q') }}" placeholder="Tên, mô tả, nơi sản xuất">
        </label>
        <label>Nơi sản xuất
            <input name="origin_place" value="{{ request('origin_place') }}">
        </label>
        <label>Giá tối thiểu
            <input type="number" min="0" name="min_price" value="{{ request('min_price') }}">
        </label>
        <label>Giá tối đa
            <input type="number" min="0" name="max_price" value="{{ request('max_price') }}">
        </label>
        <button class="btn" type="submit">Lọc sản phẩm</button>
    </form>
</div>

@foreach($products as $product)
    <div class="card">
        <h2><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h2>
        <p>{{ $product->origin_place }} - {{ number_format($product->price) }} / {{ $product->unit }}</p>
        <p>{{ $product->description }}</p>
        @if($product->homestays->count())
            <p>Gắn với homestay: {{ $product->homestays->pluck('name')->join(', ') }}</p>
        @endif
    </div>
@endforeach

{{ $products->links() }}
@endsection
