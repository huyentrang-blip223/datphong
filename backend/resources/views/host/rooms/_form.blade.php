@csrf
<label>Homestay
    <select name="homestay_id" required>
        @foreach($homestays as $homestay)
            <option value="{{ $homestay->id }}" @selected(old('homestay_id', $room->homestay_id ?? '') == $homestay->id)>
                {{ $homestay->name }}
            </option>
        @endforeach
    </select>
</label>
<label>Tên phòng
    <input name="name" value="{{ old('name', $room->name ?? '') }}" required maxlength="160">
</label>
<label>Loại phòng
    <select name="room_type" required>
        @foreach(['private_room'=>'Phòng riêng','family_room'=>'Phòng gia đình','dorm'=>'Dorm','bungalow'=>'Bungalow','whole_house'=>'Nguyên căn'] as $value => $label)
            <option value="{{ $value }}" @selected(old('room_type', $room->room_type ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>
<label>Sức chứa tối đa
    <input type="number" name="max_guests" min="1" max="20" value="{{ old('max_guests', $room->max_guests ?? 2) }}" required>
</label>
<label>Số giường
    <input type="number" name="bed_count" min="1" max="20" value="{{ old('bed_count', $room->bed_count ?? 1) }}" required>
</label>
<label>Giá cơ bản / đêm
    <input type="number" name="base_price" min="0" step="1000" value="{{ old('base_price', $room->base_price ?? 0) }}" required>
</label>
<label>Số lượng phòng/căn
    <input type="number" name="quantity" min="1" max="1000" value="{{ old('quantity', $room->quantity ?? 1) }}" required>
</label>
<label>Trạng thái
    <select name="active" required>
        <option value="1" @selected((string) old('active', isset($room) ? (int) $room->active : 1) === '1')>Đang mở bán</option>
        <option value="0" @selected((string) old('active', isset($room) ? (int) $room->active : 1) === '0')>Tạm ẩn</option>
    </select>
</label>
<button class="btn" type="submit">Lưu</button>
