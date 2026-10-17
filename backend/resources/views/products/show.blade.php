@extends('layouts.app')
@section('title', $product->name . ' — Đặc Sản Địa Phương')
@section('content')

<div class="page-heading">
    <a href="{{ route('products.index') }}" class="section-link" style="margin-bottom: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Quay lại chợ sản phẩm
    </a>
    <h1>{{ $product->name }}</h1>
</div>

<div class="form-grid" style="grid-template-columns: 1.4fr 1fr; gap: 24px; align-items: start; margin-bottom: 36px;">
    {{-- Cột thông tin sản phẩm --}}
    <div class="card" style="padding: 28px; margin: 0;">
        <div style="height: 300px; margin: -28px -28px 22px; background: var(--surface-alt); overflow: hidden; border-radius: var(--radius-md) var(--radius-md) 0 0;">
            <img src="{{ $product->image_url }}" alt="Ảnh {{ $product->name }}"
                 onerror="this.onerror=null;this.src='{{ asset('images/products/default.svg') }}';"
                 style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-light); padding-bottom: 14px;">
            <span class="badge badge-accent">Đặc sản OCOP bản địa</span>
            <span class="price-tag" style="font-size: 1.25rem;">
                <strong>{{ number_format($product->price) }} đ</strong> <small>/ {{ $product->unit }}</small>
            </span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
            <div>
                <p style="margin: 0; font-size: 0.82rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Nơi sản xuất:</p>
                <p style="font-size: 1rem; font-weight: 600; margin: 2px 0 0;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <strong>Nơi sản xuất:</strong> {{ $product->origin_place }}
                </p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.82rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Giá niêm yết:</p>
                <p style="font-size: 1rem; font-weight: 600; margin: 2px 0 0;">
                    <strong>Giá:</strong> {{ number_format($product->price) }} / {{ $product->unit }}
                </p>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.82rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Số lượng tồn kho:</p>
                <p style="font-size: 1rem; font-weight: 600; margin: 2px 0 0;">
                    <strong>Tồn kho:</strong> {{ $product->stock_qty }}
                </p>
            </div>
        </div>

        <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border-light);">
            <h4 style="font-size: 0.95rem; margin-bottom: 6px;">Mô tả sản phẩm</h4>
            <p style="color: var(--muted); font-size: 0.94rem; line-height: 1.6;">{{ $product->description }}</p>
        </div>

        @if($product->homestays->count())
            <div style="margin-top: 16px; background: var(--surface-alt); padding: 12px 16px; border-radius: var(--radius-sm);">
                <p style="margin: 0; font-size: 0.88rem;">
                    <strong>Homestay liên quan:</strong> {{ $product->homestays->pluck('name')->join(', ') }}
                </p>
            </div>
        @endif
    </div>

    {{-- Cột đặt mua (dành cho guest) --}}
    <div>
        @auth
            @if(auth()->user()->role === 'guest' && $product->stock_qty > 0)
                <div class="card" style="padding: 24px; margin: 0; border-color: var(--primary);">
                    <h2 style="font-size: 1.25rem; margin-bottom: 12px;">Đặt mua test</h2>
                    <form method="post" action="{{ route('guest.orders.store') }}">
                        @csrf
                        <input type="hidden" name="items[0][product_id]" value="{{ $product->id }}">
                        <label>
                            Số lượng
                            <input type="number" min="1" max="{{ $product->stock_qty }}" name="items[0][quantity]" value="1" required style="margin-top: 6px;">
                        </label>
                        <button class="btn btn-accent" type="submit" style="width: 100%; margin-top: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            Tạo đơn hàng
                        </button>
                    </form>
                </div>
            @endif
        @else
            <div class="card" style="padding: 20px; text-align: center; margin: 0;">
                <p style="margin-bottom: 12px; color: var(--muted);">Đăng nhập tài khoản du khách để đặt mua sản phẩm trực tuyến.</p>
                <a href="{{ route('login') }}" class="btn btn-small">Đăng nhập</a>
            </div>
        @endauth
    </div>
</div>

{{-- Phần Gợi ý AI / Python --}}
<div style="margin-top: 48px;">
    <div class="section-header">
        <div class="section-header-left">
            <span class="eyebrow">Trí tuệ nhân tạo gợi ý</span>
            <h2>Sản phẩm gợi ý</h2>
            <p>Nguồn gợi ý: {{ $recommendations['source'] === 'python' ? 'Python service' : 'Laravel fallback' }}</p>
        </div>
    </div>

    @if(!empty($recommendations['message']))
        <div class="alert" style="margin-bottom: 18px;">{{ $recommendations['message'] }}</div>
    @endif

    <div class="products-grid">
        @forelse($recommendations['items'] as $item)
            <div class="product-card card" style="margin: 0;">
                <div class="product-card-top" style="height: 80px;">
                    <img src="{{ isset($item['image_url']) ? $item['image_url'] : asset('images/products/default.svg') }}"
                         alt="{{ $item['name'] ?? 'Sản phẩm gợi ý' }}"
                         style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div class="product-card-body" style="padding: 16px;">
                    <h3 style="font-size: 1.05rem;">
                        <a href="{{ route('products.show', $item['product_id']) }}">
                            {{ $item['name'] ?? ('Sản phẩm #' . $item['product_id']) }}
                        </a>
                    </h3>
                    @isset($item['origin_place'])
                        <p style="font-size: 0.82rem; color: var(--muted); margin-bottom: 6px;">{{ $item['origin_place'] }}</p>
                    @endisset
                    @isset($item['price'])
                        <p style="font-weight: 750; color: var(--accent); margin-bottom: 6px;">{{ number_format($item['price']) }}</p>
                    @endisset
                    @isset($item['score'])
                        <div style="font-size: 0.78rem; background: var(--surface-alt); padding: 4px 8px; border-radius: var(--radius-sm); color: var(--muted);">
                            <p style="margin: 0;">Điểm tương đồng: {{ $item['score'] === null ? 'N/A' : number_format($item['score'], 3) }}</p>
                        </div>
                    @endisset
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 32px;">Chưa có sản phẩm gợi ý phù hợp.</div>
        @endforelse
    </div>
</div>

@endsection
