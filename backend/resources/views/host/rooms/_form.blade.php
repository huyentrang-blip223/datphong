@csrf
<div class="form-grid" style="grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
    <div>
        <label>Homestay
            <select name="homestay_id" required>
                @foreach($homestays as $homestay)
                    <option value="{{ $homestay->id }}" @selected(old('homestay_id', $room->homestay_id ?? '') == $homestay->id)>
                        {{ $homestay->name }}
                    </option>
                @endforeach
            </select>
        </label>
    </div>
    <div>
        <label>Tên phòng
            <input name="name" value="{{ old('name', $room->name ?? '') }}" placeholder="Phòng Deluxe view đồi, Phòng cộng đồng..." required maxlength="160">
        </label>
    </div>
    <div>
        <label>Loại phòng
            <select name="room_type" required>
                @foreach(['private_room'=>'Phòng riêng','family_room'=>'Phòng gia đình','dorm'=>'Dorm','bungalow'=>'Bungalow','whole_house'=>'Nguyên căn'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('room_type', $room->room_type ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <div>
        <label>Trạng thái
            <select name="active" required>
                <option value="1" @selected((string) old('active', isset($room) ? (int) $room->active : 1) === '1')>Đang mở bán</option>
                <option value="0" @selected((string) old('active', isset($room) ? (int) $room->active : 1) === '0')>Tạm ẩn</option>
            </select>
        </label>
    </div>
    <div>
        <label>Sức chứa tối đa (người)
            <input type="number" name="max_guests" min="1" max="20" value="{{ old('max_guests', $room->max_guests ?? 2) }}" required>
        </label>
    </div>
    <div>
        <label>Số giường
            <input type="number" name="bed_count" min="1" max="20" value="{{ old('bed_count', $room->bed_count ?? 1) }}" required>
        </label>
    </div>
    <div>
        <label>Giá cơ bản / đêm (VNĐ)
            <input type="number" name="base_price" min="0" step="1000" value="{{ old('base_price', $room->base_price ?? 0) }}" required>
        </label>
    </div>
    <div>
        <label>Số lượng phòng/căn tương đương
            <input type="number" name="quantity" min="1" max="1000" value="{{ old('quantity', $room->quantity ?? 1) }}" required>
        </label>
    </div>
    <div style="grid-column: span 2;">
        <label>Ảnh phòng
            <input type="file" name="image" accept="image/*">
        </label>
        @if(!empty($room?->image_path))
            <div style="margin-top: 10px; display: flex; align-items: center; gap: 12px;">
                <img src="{{ $room->image_url }}" alt="Ảnh hiện tại của {{ $room->name }}" style="width: 120px; height: 78px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
                <span style="font-size: 0.85rem; color: var(--muted);">Ảnh hiện tại sẽ được thay khi chọn file mới.</span>
            </div>
        @endif
    </div>
</div>

<div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
    <a href="{{ $roomBackRoute ?? route('host.rooms.index') }}" class="btn btn-secondary">Quay lại</a>
    <button class="btn" type="submit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Lưu
    </button>
</div>
