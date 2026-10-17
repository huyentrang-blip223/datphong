@extends('layouts.app')
@section('title', 'Dashboard Quản Trị Hệ Thống | ĐT-07')
@section('content')

<section class="page-heading">
    <span class="eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Khu vực quản trị
    </span>
    <h1>Dashboard quản trị</h1>
    <p>Theo dõi dữ liệu vận hành, homestay chờ duyệt và tín hiệu analytics từ Python service.</p>
</section>

<div class="stats-grid">
    <div class="metric-card">
        <span>{{ $homestayCount }}</span>
        <p>Homestay</p>
    </div>
    <div class="metric-card">
        <span>{{ $roomCount }}</span>
        <p>Phòng</p>
    </div>
    <div class="metric-card">
        <span>{{ $bookingCount }}</span>
        <p>Booking</p>
    </div>
    <div class="metric-card">
        <span>{{ $localProductCount }}</span>
        <p>Sản phẩm published</p>
    </div>
    <div class="metric-card" style="border-color: var(--accent);">
        <span style="color: var(--accent);">{{ $pendingCount }}</span>
        <p>Chờ duyệt</p>
    </div>
    <div class="metric-card">
        <span>{{ $userCount }}</span>
        <p>Tài khoản</p>
    </div>
</div>

<div class="action-strip" style="margin: 24px 0 36px;">
    <a class="btn" href="{{ route('admin.homestays.index') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        Quản trị homestay
    </a>
    <a class="btn btn-secondary" href="{{ route('admin.rooms.index') }}">Quản trị phòng</a>
    <a class="btn btn-secondary" href="{{ route('admin.products.index') }}">Quản trị sản phẩm</a>
    <a class="btn btn-secondary" href="{{ route('admin.payments.index') }}">Giao dịch thanh toán</a>
    <a class="btn btn-secondary" href="{{ route('admin.audit-logs.index') }}">Audit logs</a>
</div>

<div class="section-heading">
    <h2>Phân tích công suất theo tháng</h2>
    <p>Nguồn: {{ $seasonality['source'] === 'python' ? 'Python service' : 'Fallback/empty state' }}</p>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md); margin-bottom: 36px;">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Tháng</th>
                    <th>Tổng units</th>
                    <th>Đã bán</th>
                    <th>Công suất</th>
                </tr>
            </thead>
            <tbody>
            @forelse($seasonality['items'] as $row)
                <tr>
                    <td style="font-weight: 700;">{{ $row['month'] ?? '' }}</td>
                    <td>{{ $row['units_total'] ?? 0 }}</td>
                    <td style="color: var(--primary); font-weight: 600;">{{ $row['units_sold'] ?? 0 }}</td>
                    <td>
                        <span class="badge badge-success">
                            {{ isset($row['occupancy_rate']) ? number_format($row['occupancy_rate'] * 100, 1) . '%' : 'N/A' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; padding: 32px; color: var(--muted);">Chưa có dữ liệu analytics từ Python.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="section-heading">
    <h2>Top homestay theo booking</h2>
    <p>Nguồn: {{ $topHomestays['source'] === 'python' ? 'Python service' : 'Fallback/empty state' }}</p>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Homestay</th>
                    <th>Tỉnh</th>
                    <th>Booking</th>
                    <th>Doanh thu</th>
                </tr>
            </thead>
            <tbody>
            @forelse($topHomestays['items'] as $row)
                <tr>
                    <td style="font-weight: 750;">{{ $row['homestay_name'] ?? '' }}</td>
                    <td style="color: var(--muted);">{{ $row['province'] ?? '' }}</td>
                    <td><span class="badge badge-accent">{{ $row['booking_count'] ?? 0 }} booking</span></td>
                    <td style="font-weight: 750; color: var(--primary-strong);">{{ number_format($row['revenue'] ?? 0) }} đ</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; padding: 32px; color: var(--muted);">Chưa có dữ liệu top homestay từ Python.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
