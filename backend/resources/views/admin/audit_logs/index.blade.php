@extends('layouts.app')
@section('title', 'Audit Logs | ĐT-07')
@section('content')

<div class="page-heading">
    <span class="eyebrow">Truy vết hệ thống</span>
    <h1>Audit logs</h1>
    <p>Theo dõi hành động nghiệp vụ quan trọng: booking, payment, đơn hàng, review.</p>
</div>

<form method="get" class="card" style="padding: 20px; margin-bottom: 24px;">
    <div style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        <div style="min-width: 220px;">
            <label>Entity</label>
            <input name="entity" value="{{ request('entity') }}" placeholder="payments, bookings..." style="margin-bottom: 0;">
        </div>
        <div style="min-width: 220px;">
            <label>Action</label>
            <input name="action" value="{{ request('action') }}" placeholder="paid, created..." style="margin-bottom: 0;">
        </div>
        <button class="btn btn-secondary" style="height: 44px;">Lọc</button>
    </div>
</form>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Entity ID</th>
                    <th>IP</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="font-weight: 700; color: var(--muted);">#{{ $log->id }}</td>
                        <td>{{ $log->actor?->email ?? 'system' }}</td>
                        <td><span class="badge badge-accent">{{ $log->action }}</span></td>
                        <td>{{ $log->entity }}</td>
                        <td>{{ $log->entity_id ?? 'N/A' }}</td>
                        <td>{{ $log->ip_address ?? 'N/A' }}</td>
                        <td>{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có audit log.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $logs->links() }}
</div>

@endsection
