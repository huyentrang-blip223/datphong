@extends('layouts.app')
@section('title', 'Đặt trải nghiệm')
@section('content')
<h1>{{ auth()->user()->role === 'host' ? 'Khách đăng ký trải nghiệm' : 'Trải nghiệm đã đặt' }}</h1>
<p>Hủy trước giờ bắt đầu; đặt chỗ đã trả tiền phải hủy trước 24 giờ. Hoàn tiền chỉ mô phỏng.</p>
<div class="table-wrap"><table><thead><tr><th>Trải nghiệm</th><th>Khách</th><th>Giờ bắt đầu</th><th>Số người</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($bookings as $booking)
<tr><td>{{ $booking->experience->title }}</td><td>{{ $booking->guest->full_name }}</td><td>{{ $booking->experience->start_at }}</td><td>{{ $booking->people_count }}</td><td>{{ $booking->status }}</td><td>
@if(auth()->user()->role === 'guest')
<a href="{{ route('guest.experience-payments.show', $booking) }}">Thanh toán / giao dịch</a>
@if(in_array($booking->status, ['pending','confirmed']))
<form method="post" action="{{ route('guest.experience-bookings.cancel', $booking) }}">@csrf<button class="btn btn-small">Hủy đặt chỗ</button></form>
@endif
@elseif($booking->status === 'confirmed')
<form method="post" action="{{ route('host.experience-bookings.complete', $booking) }}">@csrf<button class="btn btn-small">Hoàn tất tham gia</button></form>
@endif
</td></tr>
@empty <tr><td colspan="6">Chưa có đặt chỗ trải nghiệm.</td></tr> @endforelse
</tbody></table></div>{{ $bookings->links() }}
@endsection
