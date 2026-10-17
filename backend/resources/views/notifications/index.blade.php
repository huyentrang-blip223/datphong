@extends('layouts.app')
@section('title', 'Thông Báo Hệ Thống')
@section('content')

<div class="page-heading">
    <span class="eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Hộp thư cá nhân
    </span>
    <h1>Thông báo</h1>
    <p>Cập nhật trạng thái booking, đơn hàng nông sản và biến động điểm thưởng.</p>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Tiêu đề</th>
                    <th>Nội dung</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($notifications as $notification)
                <tr style="{{ !$notification->read_at ? 'background-color: color-mix(in srgb, var(--primary-soft) 30%, var(--surface));' : '' }}">
                    <td style="font-size: 0.85rem; color: var(--muted); white-space: nowrap;">
                        {{ $notification->created_at }}
                    </td>
                    <td style="font-weight: 700; color: var(--text);">
                        {{ $notification->title }}
                    </td>
                    <td style="font-size: 0.92rem; color: var(--muted);">
                        {{ $notification->message }}
                    </td>
                    <td>
                        <span class="badge {{ $notification->read_at ? 'badge-outline' : 'badge-accent' }}">
                            {{ $notification->read_at ? 'Đã đọc' : 'Chưa đọc' }}
                        </span>
                    </td>
                    <td>
                        @if(!$notification->read_at)
                            <form method="post" action="{{ route('account.notifications.read', $notification) }}">
                                @csrf
                                <button class="btn btn-small btn-secondary" type="submit">Đã đọc</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 36px; color: var(--muted);">
                        Chưa có thông báo.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $notifications->links() }}
</div>

@endsection
