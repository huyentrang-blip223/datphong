@csrf
<div class="form-grid">
    <label>Tên sản phẩm <input name="name" value="{{ old('name', $product->name) }}" required></label>
    <label>Nơi sản xuất <input name="origin_place" value="{{ old('origin_place', $product->origin_place) }}" required></label>
    <label>Đơn vị <input name="unit" value="{{ old('unit', $product->unit) }}" required></label>
    <label>Giá <input type="number" min="0" name="price" value="{{ old('price', $product->price) }}" required></label>
    <label>Tồn kho <input type="number" min="0" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty ?? 0) }}" required></label>
    <label>Trạng thái
        <select name="status">
            @foreach(['draft','published','hidden'] as $status)
                <option value="{{ $status }}" @selected(old('status', $product->status ?? 'draft') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </label>
</div>
<label>Mô tả <textarea name="description" rows="5">{{ old('description', $product->description) }}</textarea></label>
<button class="btn" type="submit">Lưu sản phẩm</button>
