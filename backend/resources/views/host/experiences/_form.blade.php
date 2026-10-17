@csrf
<div class="form-grid" style="grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
    <div>
        <label>Homestay tổ chức
            <select name="homestay_id" required>
                @foreach($homestays as $homestay)
                    <option value="{{ $homestay->id }}" @selected((int) old('homestay_id', $experience->homestay_id) === (int) $homestay->id)>{{ $homestay->name }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <div>
        <label>Trạng thái
            <select name="status" required>
                @foreach(['draft' => 'Nháp', 'open' => 'Đang mở', 'full' => 'Đã đầy', 'cancelled' => 'Đã hủy'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $experience->status ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>
</div>

<div style="margin-bottom: 16px;">
    <label>Tên trải nghiệm
        <input name="title" value="{{ old('title', $experience->title) }}" placeholder="Hái chè cổ thụ, Dệt thổ cẩm cùng nghệ nhân..." required maxlength="160">
    </label>
</div>

<div style="margin-bottom: 16px;">
    <label>Mô tả chi tiết trải nghiệm
        <textarea name="description" maxlength="2000" placeholder="Chi tiết lịch trình, trang phục cần chuẩn bị, câu chuyện văn hóa...">{{ old('description', $experience->description) }}</textarea>
    </label>
</div>

<div class="form-grid" style="grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px;">
    <div style="grid-column: span 2;">
        <label>Thời gian bắt đầu
            <input type="datetime-local" name="start_at" value="{{ old('start_at', $experience->start_at ? $experience->start_at->format('Y-m-d\TH:i') : '') }}" required>
        </label>
    </div>
    <div>
        <label>Thời lượng (phút)
            <input type="number" name="duration_minutes" min="15" max="1440" value="{{ old('duration_minutes', $experience->duration_minutes ?? 120) }}" required>
        </label>
    </div>
    <div>
        <label>Sức chứa tối đa (người)
            <input type="number" name="capacity" min="1" max="500" value="{{ old('capacity', $experience->capacity ?? 10) }}" required>
        </label>
    </div>
    <div style="grid-column: span 2;">
        <label>Giá mỗi người (VNĐ)
            <input type="number" name="price" min="0" step="1000" value="{{ old('price', $experience->price ?? 0) }}" required>
        </label>
    </div>
</div>

<div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-light);">
    <a href="{{ route('host.experiences.index') }}" class="btn btn-secondary">Hủy</a>
    <button class="btn btn-accent" type="submit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Lưu trải nghiệm
    </button>
</div>
