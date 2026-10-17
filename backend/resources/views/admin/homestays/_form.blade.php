<div class="form-grid" style="grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
    <div>
        <label>Chủ homestay
            <select name="owner_id" required>
                @foreach($hosts as $u)
                    <option value="{{ $u->id }}" @selected(old('owner_id', $homestay->owner_id ?? null) == $u->id)>{{ $u->full_name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </label>
    </div>
    <div>
        <label>Tên homestay
            <input name="name" required maxlength="180" value="{{ old('name', $homestay->name ?? '') }}" placeholder="Tên homestay...">
        </label>
    </div>
    <div>
        <label>Slug đường dẫn
            <input name="slug" required maxlength="200" value="{{ old('slug', $homestay->slug ?? '') }}" placeholder="ten-homestay-ban-dia">
        </label>
    </div>
    <div>
        <label>Trạng thái duyệt
            <select name="status">
                @foreach(['pending','approved','rejected','suspended'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $homestay->status ?? 'pending') === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <div>
        <label>Tỉnh/TP
            <input name="province" required maxlength="100" value="{{ old('province', $homestay->province ?? '') }}" placeholder="Lâm Đồng, Lào Cai...">
        </label>
    </div>
    <div>
        <label>Quận/Huyện
            <input name="district" maxlength="100" value="{{ old('district', $homestay->district ?? '') }}" placeholder="Đà Lạt, Sa Pa...">
        </label>
    </div>
    <div style="grid-column: span 2;">
        <label>Địa chỉ cụ thể
            <input name="address_line" required maxlength="255" value="{{ old('address_line', $homestay->address_line ?? '') }}" placeholder="Số nhà, thôn bản, đường...">
        </label>
    </div>
    <div>
        <label>Giờ Check-in
            <input type="time" name="checkin_time" required value="{{ old('checkin_time', isset($homestay) ? substr($homestay->checkin_time, 0, 5) : '14:00') }}">
        </label>
    </div>
    <div>
        <label>Giờ Check-out
            <input type="time" name="checkout_time" required value="{{ old('checkout_time', isset($homestay) ? substr($homestay->checkout_time, 0, 5) : '12:00') }}">
        </label>
    </div>
</div>

<div style="margin-bottom: 16px;">
    <label>Mô tả homestay
        <textarea name="description" maxlength="3000" placeholder="Giới thiệu về cảnh quan, văn hóa, không gian homestay...">{{ old('description', $homestay->description ?? '') }}</textarea>
    </label>
</div>

@if(!empty($homestay?->cover_image_path))
    <div class="image-preview" style="margin-bottom: 16px; padding: 12px; background: var(--surface-alt); border-radius: var(--radius-sm); display: inline-block;">
        <p style="font-size: 0.8rem; color: var(--muted); margin-bottom: 6px;">Ảnh bìa hiện tại:</p>
        <img src="{{ asset('storage/'.$homestay->cover_image_path) }}" alt="Ảnh homestay {{ $homestay->name }}" style="max-height: 140px; border-radius: var(--radius-sm);">
    </div>
@endif

<div style="margin-bottom: 20px;">
    <label>Ảnh bìa homestay
        <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp">
        <span class="muted" style="font-size: 0.82rem; display: block; margin-top: 4px;">Chọn ảnh từ máy tính, định dạng JPG/PNG/WebP, dung lượng tối đa 2MB.</span>
    </label>
</div>
