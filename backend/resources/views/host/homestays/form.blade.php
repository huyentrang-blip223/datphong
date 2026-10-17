@extends('layouts.app')
@section('title', 'Hồ sơ homestay')
@section('content')
<h1>{{ $homestay->exists ? 'Sửa homestay của tôi' : 'Đăng ký homestay' }}</h1>
<p>Mỗi lần gửi lại thông tin, cơ sở sẽ chờ quản trị viên duyệt trước khi mở bán. Giấy tờ chỉ chủ cơ sở và quản trị viên được tải xuống.</p>
<form class="card" method="post" enctype="multipart/form-data" action="{{ $homestay->exists ? route('host.homestays.update', $homestay) : route('host.homestays.store') }}">
@csrf @if($homestay->exists) @method('PUT') @endif
<div class="form-grid">
@foreach(['name' => 'Tên homestay', 'slug' => 'Mã đường dẫn (không dấu)', 'province' => 'Tỉnh/thành', 'district' => 'Quận/huyện', 'address_line' => 'Địa chỉ'] as $field => $label)
<label>{{ $label }}<input name="{{ $field }}" value="{{ old($field, $homestay->$field) }}" maxlength="{{ $field === 'address_line' ? 255 : ($field === 'slug' ? 200 : ($field === 'name' ? 180 : 100)) }}" @if($field !== 'district') required @endif></label>
@endforeach
<label>Giờ nhận phòng<input type="time" name="checkin_time" value="{{ old('checkin_time', substr($homestay->checkin_time ?? '14:00', 0, 5)) }}" required></label>
<label>Giờ trả phòng<input type="time" name="checkout_time" value="{{ old('checkout_time', substr($homestay->checkout_time ?? '12:00', 0, 5)) }}" required></label>
</div>
<label>Mô tả<textarea name="description" maxlength="3000">{{ old('description', $homestay->description) }}</textarea></label>
<label>Loại giấy tờ<select name="document_type">@foreach(['business_license'=>'Giấy phép kinh doanh','id_document'=>'Giấy tờ định danh','ownership_proof'=>'Chứng minh quyền sở hữu','other'=>'Khác'] as $value=>$label)<option value="{{ $value }}" @selected(old('document_type') === $value)>{{ $label }}</option>@endforeach</select></label>
<label>Giấy tờ PDF/JPG/PNG (tối đa 4 MB)<input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" @if(!$homestay->exists) required @endif></label>
<p>Khi sửa, có thể giữ giấy tờ cũ nếu không tải tệp mới.</p>
<button class="btn">Lưu và gửi duyệt</button>
</form>
@foreach($verifications ?? [] as $verification)
<div class="card">{{ $verification->submitted_at }} — {{ $verification->status }} — {{ $verification->note }} <a href="{{ route('verification.document', $verification) }}">Tải giấy tờ</a></div>
@endforeach
@endsection
