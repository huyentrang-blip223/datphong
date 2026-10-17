@extends('layouts.app')
@section('title', 'Quản Lý Trải Nghiệm Của Host')
@section('content')

<div class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
            Chủ homestay
        </span>
        <h1>Trải nghiệm văn hóa</h1>
        <p>Quản lý các hoạt động gắn với homestay của bạn. Trải nghiệm có đặt chỗ nên chuyển trạng thái thay vì xóa.</p>
    </div>
    <a class="btn" href="{{ route('host.experiences.create') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm trải nghiệm
    </a>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Tên</th>
                    <th>Homestay</th>
                    <th>Thời gian</th>
                    <th>Sức chứa</th>
                    <th>Giá</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($experiences as $experience)
                <tr>
                    <td style="font-weight: 750; color: var(--text);">
                        {{ $experience->title }}
                    </td>
                    <td>{{ $experience->homestay->name }}</td>
                    <td style="font-size: 0.88rem;">{{ $experience->start_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge badge-outline">
                            {{ $experience->booked_count }}/{{ $experience->capacity }}
                        </span>
                    </td>
                    <td style="font-weight: 700; color: var(--primary);">{{ number_format($experience->price) }} đ</td>
                    <td>
                        <span class="badge {{ $experience->status === 'open' ? 'badge-success' : 'badge-warning' }}">
                            {{ $experience->status }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('host.experiences.edit', $experience) }}" class="btn btn-small btn-secondary">
                            Sửa
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có trải nghiệm.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $experiences->links() }}
</div>

@endsection
