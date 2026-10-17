@extends('layouts.app')
@section('title', 'Sản Phẩm Của Tôi — Gian Hàng Địa Phương')
@section('content')

<div class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Gian hàng địa phương
        </span>
        <h1>Sản phẩm của tôi</h1>
        <p>Chỉ hiển thị sản phẩm thuộc tài khoản seller hiện tại.</p>
    </div>
    <div class="action-strip">
        <a class="btn" href="{{ route('seller.products.create') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Thêm sản phẩm
        </a>
        <a class="btn btn-secondary" href="{{ route('seller.orders.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Đơn hàng liên quan
        </a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Tên</th>
                    <th>Giá</th>
                    <th>Tồn</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <strong style="color: var(--text);">{{ $product->name }}</strong>
                        <div style="font-size: 0.8rem; color: var(--muted);">Xuất xứ: {{ $product->origin_place }}</div>
                    </td>
                    <td style="font-weight: 700; color: var(--accent);">
                        {{ number_format($product->price) }} đ <small style="font-weight: normal; color: var(--muted);">/ {{ $product->unit }}</small>
                    </td>
                    <td>
                        <span style="font-weight: 600; color: {{ $product->stock_qty <= 5 ? 'var(--danger)' : 'var(--text)' }};">
                            {{ $product->stock_qty }}
                        </span>
                    </td>
                    <td>
                        @php
                            $pStatus = match($product->status) {
                                'published' => 'badge-success',
                                'draft' => 'badge-warning',
                                'hidden' => 'badge-outline',
                                default => 'badge-outline',
                            };
                        @endphp
                        <span class="badge {{ $pStatus }}">{{ $product->status }}</span>
                    </td>
                    <td>
                        <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-small btn-secondary">
                            Sửa
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có sản phẩm.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $products->links() }}
</div>

@endsection
