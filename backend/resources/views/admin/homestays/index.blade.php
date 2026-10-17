@extends('layouts.app')
@section('title', 'Quản Trị Homestay | ĐT-07')
@section('content')

<div class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Quản trị hệ thống
        </span>
        <h1>Quản trị homestay</h1>
        <p>Duyệt, chỉnh sửa và quản lý danh sách toàn bộ cơ sở homestay trên nền tảng.</p>
    </div>
    <a class="btn" href="{{ route('admin.homestays.create') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        + Thêm homestay
    </a>
</div>

<form method="get" class="card" style="padding: 20px; margin-bottom: 24px;">
    <div style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px;">
            <label>Từ khóa</label>
            <input name="q" value="{{ request('q') }}" placeholder="Tên homestay, địa chỉ, tỉnh thành..." style="margin-bottom: 0;">
        </div>
        <div style="min-width: 180px;">
            <label>Trạng thái</label>
            <select name="status" style="margin-bottom: 0;">
                <option value="">Tất cả trạng thái</option>
                @foreach(['pending','approved','rejected','suspended'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-secondary" style="height: 44px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Lọc
        </button>
    </div>
</form>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên</th>
                    <th>Chủ</th>
                    <th>Tỉnh</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($items as $h)
                <tr>
                    <td style="font-weight: 700; color: var(--muted);">#{{ $h->id }}</td>
                    <td>
                        @if($h->cover_image_path)
                            <img class="table-thumb" src="{{ asset('storage/'.$h->cover_image_path) }}" alt="Ảnh {{ $h->name }}" style="width: 54px; height: 38px; object-fit: cover; border-radius: var(--radius-sm);">
                        @else
                            <span class="muted" style="font-size: 0.8rem;">Chưa có</span>
                        @endif
                    </td>
                    <td style="font-weight: 700; color: var(--text);">
                        {{ $h->name }}
                    </td>
                    <td>{{ $h->owner?->full_name ?? 'N/A' }}</td>
                    <td>{{ $h->province }}</td>
                    <td>
                        @php
                            $stClass = match($h->status) {
                                'approved' => 'badge-success',
                                'pending' => 'badge-warning',
                                'rejected', 'suspended' => 'badge-outline',
                                default => 'badge-outline',
                            };
                        @endphp
                        <span class="badge {{ $stClass }}">{{ $h->status }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.homestays.edit',$h) }}" class="btn btn-small btn-secondary">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Sửa
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; padding: 36px; color: var(--muted);">Chưa có homestay nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $items->links() }}
</div>

@endsection
