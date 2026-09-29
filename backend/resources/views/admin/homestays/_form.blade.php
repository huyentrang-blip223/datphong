<label>Chủ homestay<select name="owner_id" required>@foreach($hosts as $u)<option value="{{ $u->id }}" @selected(old('owner_id',$homestay->owner_id ?? null)==$u->id)>{{ $u->full_name }}</option>@endforeach</select></label>
<label>Tên<input name="name" required maxlength="180" value="{{ old('name',$homestay->name ?? '') }}"></label>
<label>Slug<input name="slug" required maxlength="200" value="{{ old('slug',$homestay->slug ?? '') }}"></label>
<label>Tỉnh/TP<input name="province" required maxlength="100" value="{{ old('province',$homestay->province ?? '') }}"></label>
<label>Quận/Huyện<input name="district" maxlength="100" value="{{ old('district',$homestay->district ?? '') }}"></label>
<label>Địa chỉ<input name="address_line" required maxlength="255" value="{{ old('address_line',$homestay->address_line ?? '') }}"></label>
<label>Mô tả<textarea name="description" maxlength="3000">{{ old('description',$homestay->description ?? '') }}</textarea></label>
<label>Check-in<input type="time" name="checkin_time" required value="{{ old('checkin_time',isset($homestay)?substr($homestay->checkin_time,0,5):'14:00') }}"></label>
<label>Check-out<input type="time" name="checkout_time" required value="{{ old('checkout_time',isset($homestay)?substr($homestay->checkout_time,0,5):'12:00') }}"></label>
<label>Trạng thái<select name="status">@foreach(['pending','approved','rejected','suspended'] as $s)<option value="{{ $s }}" @selected(old('status',$homestay->status ?? 'pending')===$s)>{{ $s }}</option>@endforeach</select></label>
