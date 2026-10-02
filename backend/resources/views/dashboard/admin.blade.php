@extends('layouts.app')
@section('title','Dashboard quản trị | ĐT-07')
@section('content')
<h1>Dashboard quản trị</h1>
<div class="grid">
    <div class="card"><strong>{{ $homestayCount }}</strong><br>Homestay</div>
    <div class="card"><strong>{{ $roomCount }}</strong><br>Phòng</div>
    <div class="card"><strong>{{ $bookingCount }}</strong><br>Booking</div>
    <div class="card"><strong>{{ $localProductCount }}</strong><br>Sản phẩm published</div>
    <div class="card"><strong>{{ $pendingCount }}</strong><br>Chờ duyệt</div>
    <div class="card"><strong>{{ $userCount }}</strong><br>Tài khoản</div>
</div>
<p><a class="btn" href="{{ route('admin.homestays.index') }}">Quản trị homestay</a></p>

<h2>Phân tích công suất theo tháng</h2>
<p>Nguồn: {{ $seasonality['source'] === 'python' ? 'Python service' : 'Fallback/empty state' }}</p>
<table>
    <thead><tr><th>Tháng</th><th>Tổng units</th><th>Đã bán</th><th>Công suất</th></tr></thead>
    <tbody>
    @forelse($seasonality['items'] as $row)
        <tr>
            <td>{{ $row['month'] ?? '' }}</td>
            <td>{{ $row['units_total'] ?? 0 }}</td>
            <td>{{ $row['units_sold'] ?? 0 }}</td>
            <td>{{ isset($row['occupancy_rate']) ? number_format($row['occupancy_rate'] * 100, 1) . '%' : 'N/A' }}</td>
        </tr>
    @empty
        <tr><td colspan="4">Chưa có dữ liệu analytics từ Python.</td></tr>
    @endforelse
    </tbody>
</table>

<h2>Top homestay theo booking</h2>
<p>Nguồn: {{ $topHomestays['source'] === 'python' ? 'Python service' : 'Fallback/empty state' }}</p>
<table>
    <thead><tr><th>Homestay</th><th>Tỉnh</th><th>Booking</th><th>Doanh thu</th></tr></thead>
    <tbody>
    @forelse($topHomestays['items'] as $row)
        <tr>
            <td>{{ $row['homestay_name'] ?? '' }}</td>
            <td>{{ $row['province'] ?? '' }}</td>
            <td>{{ $row['booking_count'] ?? 0 }}</td>
            <td>{{ number_format($row['revenue'] ?? 0) }}</td>
        </tr>
    @empty
        <tr><td colspan="4">Chưa có dữ liệu top homestay từ Python.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
