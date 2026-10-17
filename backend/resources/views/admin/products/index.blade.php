@extends('layouts.app')
@section('title', 'Admin - Quản lý sản phẩm')
@section('content')

<div class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="eyebrow">Khu vực quản trị</span>
        <h1>Quản lý sản phẩm</h1>
        <p>Admin có thể thêm, sửa, xóa sản phẩm địa phương và cập nhật ảnh hiển thị.</p>
    </div>
    <a class="btn" href="{{ route('admin.products.create') }}">Thêm sản phẩm</a>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Sản phẩm</th>
                    <th>Người bán</th>
                    <th>Giá</th>
                    <th>Tồn</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td><img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 86px; height: 56px; object-fit: cover; border-radius: var(--radius-sm);"></td>
                    <td>
                        <strong style="color: var(--text);">{{ $product->name }}</strong>
                        <div style="font-size: 0.8rem; color: var(--muted);">Xuất xứ: {{ $product->origin_place }}</div>
                    </td>
                    <td>{{ $product->seller->full_name ?? $product->seller->email ?? 'N/A' }}</td>
                    <td style="font-weight: 700; color: var(--accent);">{{ number_format($product->price) }} đ / {{ $product->unit }}</td>
                    <td>{{ $product->stock_qty }}</td>
                    <td><span class="badge {{ $product->status === 'published' ? 'badge-success' : 'badge-outline' }}">{{ $product->status }}</span></td>
                    <td>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-small btn-secondary">Sửa</a>
                            <form method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Xóa sản phẩm này?');">
                                @csrf
                                @method('delete')
                                <button class="btn btn-small btn-secondary" type="submit">Xóa</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có sản phẩm.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">{{ $products->links() }}</div>

@endsection
