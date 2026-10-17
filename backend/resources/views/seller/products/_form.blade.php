@csrf
<div class="form-grid" style="grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
    @if(isset($sellers))
        <div style="grid-column: span 2;">
            <label>Người bán
                <select name="seller_id" required>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}" @selected(old('seller_id', $product->seller_id) == $seller->id)>
                            {{ $seller->full_name ?: $seller->email }} ({{ $seller->email }})
                        </option>
                    @endforeach
                </select>
            </label>
        </div>
    @endif
    <div>
        <label>Tên sản phẩm
            <input name="name" value="{{ old('name', $product->name) }}" placeholder="Mật ong rừng, Trà Shan tuyết cổ thụ..." required>
        </label>
    </div>
    <div>
        <label>Nơi sản xuất
            <input name="origin_place" value="{{ old('origin_place', $product->origin_place) }}" placeholder="Lâm Đồng, Sa Pa, Hà Giang..." required>
        </label>
    </div>
    <div>
        <label>Đơn vị tính
            <input name="unit" value="{{ old('unit', $product->unit) }}" placeholder="hũ, gói, kg, hộp..." required>
        </label>
    </div>
    <div>
        <label>Giá bán (VNĐ)
            <input type="number" min="0" name="price" value="{{ old('price', $product->price) }}" placeholder="Giá niêm yết..." required>
        </label>
    </div>
    <div>
        <label>Số lượng tồn kho
            <input type="number" min="0" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty ?? 0) }}" required>
        </label>
    </div>
    <div>
        <label>Trạng thái hiển thị
            <select name="status">
                @foreach(['draft'=>'Nháp','published'=>'Đang hiển thị','hidden'=>'Tạm ẩn'] as $status => $label)
                    <option value="{{ $status }}" @selected(old('status', $product->status ?? 'draft') === $status)>{{ $label }} ({{ $status }})</option>
                @endforeach
            </select>
        </label>
    </div>
    <div style="grid-column: span 2;">
        <label>Ảnh sản phẩm
            <input type="file" name="image" accept="image/*">
        </label>
        @if(!empty($product?->image_path))
            <div style="margin-top: 10px; display: flex; align-items: center; gap: 12px;">
                <img src="{{ $product->image_url }}" alt="Ảnh hiện tại của {{ $product->name }}" style="width: 120px; height: 78px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
                <span style="font-size: 0.85rem; color: var(--muted);">Ảnh hiện tại sẽ được thay khi chọn file mới.</span>
            </div>
        @endif
    </div>
</div>

<div style="margin-bottom: 20px;">
    <label>Mô tả chi tiết sản phẩm
        <textarea name="description" rows="5" placeholder="Quy trình sản xuất, công dụng, tiêu chuẩn chứng nhận OCOP...">{{ old('description', $product->description) }}</textarea>
    </label>
</div>

<div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--border-light);">
    <a href="{{ $productBackRoute ?? route('seller.products.index') }}" class="btn btn-secondary">Hủy</a>
    <button class="btn btn-accent" type="submit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Lưu sản phẩm
    </button>
</div>
