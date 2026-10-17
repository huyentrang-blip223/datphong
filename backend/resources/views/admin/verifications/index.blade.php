@extends('layouts.app')
@section('title', 'Hồ sơ xác minh homestay')
@section('content')
<h1>Hồ sơ xác minh homestay</h1>
@forelse($verifications as $verification)
<section class="card">
<h2>{{ $verification->homestay->name }}</h2>
<p>{{ $verification->homestay->owner->full_name }} — {{ $verification->submitted_at }} — {{ $verification->status }}</p>
<a href="{{ route('verification.document', $verification) }}">Tải giấy tờ kiểm tra</a>
@if($verification->status === 'pending')
<form method="post" action="{{ route('admin.verifications.review', $verification) }}">@csrf
<label>Kết quả<select name="status"><option value="approved">Chấp thuận</option><option value="rejected">Từ chối</option></select></label>
<label>Ghi chú (bắt buộc khi từ chối)<textarea name="note" maxlength="255"></textarea></label>
<button class="btn">Lưu kết quả</button></form>
@else <p>{{ $verification->note }}</p> @endif
</section>
@empty <p>Chưa có hồ sơ.</p> @endforelse
{{ $verifications->links() }}
@endsection
