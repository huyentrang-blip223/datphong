@extends('layouts.app')
@section('title','Booking của tôi')
@section('content')
<h1>Booking của tôi</h1>
<div class="table-wrap">
<table>
    <thead><tr><th>Mã</th><th>Phòng</th><th>Ngày</th><th>Trạng thái</th><th>Tổng</th><th>Thao tác</th></tr></thead>
    <tbody>
    @forelse($bookings as $booking)
        <tr>
            <td>{{ $booking->code }}</td>
            <td>{{ $booking->room->name ?? '' }}</td>
            <td>{{ $booking->checkin_date->toDateString() }} - {{ $booking->checkout_date->toDateString() }}</td>
            <td>{{ $booking->status }}</td>
            <td>{{ number_format($booking->total_amount) }}</td>
            <td>
                @if(in_array($booking->status, ['holding','pending_payment','confirmed'], true))
                    <form method="post" action="{{ route('guest.bookings.cancel', $booking) }}">@csrf <button class="btn btn-secondary" type="submit">Hủy</button></form>
                    <form method="post" action="{{ route('guest.payments.mock', $booking) }}">@csrf <input type="hidden" name="result" value="success"><button class="btn" type="submit">Thanh toán test</button></form>
                @endif
                @if($booking->status === 'completed' && !$booking->reviews->where('direction','guest_to_host')->count())
                    <form method="post" action="{{ route('guest.reviews.store', $booking) }}">@csrf <label>Rating <input type="number" min="1" max="5" name="rating" value="5"></label><label>Nội dung <textarea name="content"></textarea></label><button class="btn" type="submit">Đánh giá</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="6">Chưa có booking.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $bookings->links() }}
@endsection
