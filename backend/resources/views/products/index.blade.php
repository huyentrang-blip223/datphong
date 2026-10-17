@extends('layouts.app')
@section('title', 'Chợ Sản Phẩm & Đặc Sản Bản Địa')
@section('content')

<div class="page-banner" style="background-image: linear-gradient(135deg, rgba(22, 38, 32, 0.88) 0%, rgba(28, 48, 40, 0.75) 100%), url('{{ asset('images/banners/ocop-products.jpg') }}');">
    <span class="hero-chip" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        Nông sản OCOP & Quà quê
    </span>
    <h1>Sản phẩm địa phương</h1>
    <p>Tinh hoa ẩm thực, thảo mộc quý và quà lưu niệm thủ công tinh xảo gắn liền trực tiếp với các vùng homestay bản địa.</p>
</div>

<div class="card" style="padding: 22px; margin-bottom: 32px;">
    <form method="get" action="{{ route('products.index') }}">
        <div class="form-grid" style="grid-template-columns: 2fr 1.5fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <div>
                <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Tìm kiếm
                </label>
                <input name="q" value="{{ request('q') }}" placeholder="Tên, mô tả, nơi sản xuất...">
            </div>
            <div>
                <label>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Nơi sản xuất
                </label>
                <input name="origin_place" placeholder="Tỉnh / huyện xuất xứ" value="{{ request('origin_place') }}">
            </div>
            <div>
                <label>Giá tối thiểu</label>
                <input type="number" min="0" placeholder="0" name="min_price" value="{{ request('min_price') }}">
            </div>
            <div>
                <label>Giá tối đa</label>
                <input type="number" min="0" placeholder="Tối đa" name="max_price" value="{{ request('max_price') }}">
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end;">
            <button class="btn" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Lọc sản phẩm
            </button>
        </div>
    </form>
</div>

<div class="products-grid">
    @forelse($products as $product)
        <article class="product-card">
            <div class="product-card-top">
                <a href="{{ route('products.show', $product) }}" style="display: block; width: 100%; height: 100%;">
                    <img src="{{ $product->image_url }}" alt="Ảnh {{ $product->name }}"
                         onerror="this.onerror=null;this.src='{{ asset('images/products/default.svg') }}';"
                         style="width: 100%; height: 100%; object-fit: cover;">
                </a>
            </div>
            <div class="product-card-body">
                <h2>
                    <a href="{{ route('products.show', $product) }}" style="font-size: 1.12rem; font-weight: 750;">{{ $product->name }}</a>
                </h2>
                <div class="product-origin">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ $product->origin_place }}
                </div>
                <p style="font-size: 0.88rem; color: var(--muted); margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $product->description }}
                </p>

                @if($product->homestays->count())
                    <div class="product-homestay-link" style="margin-bottom: 14px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px; margin-right: 3px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        Gắn với homestay: {{ $product->homestays->pluck('name')->join(', ') }}
                    </div>
                @endif

                <div class="product-card-footer">
                    <div class="product-price">
                        {{ number_format($product->price) }} đ <small>/ {{ $product->unit }}</small>
                    </div>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-small btn-secondary">
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </article>
    @empty
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <p style="margin: 0; color: var(--muted); font-size: 1rem;">Không tìm thấy sản phẩm nào phù hợp.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 32px;">
    {{ $products->links() }}
</div>

@endsection
