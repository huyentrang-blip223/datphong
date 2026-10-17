@extends('layouts.app')
@section('title', 'Điểm Thưởng Thành Viên — Loyalty Points')
@section('content')

<div class="page-heading">
    <span class="eyebrow">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Chương trình khách hàng thân thiết
    </span>
    <h1>Điểm thành viên</h1>
    <p>Tích lũy điểm thưởng từ các chuyến lưu trú và đơn hàng đặc sản để nhận ưu đãi hấp dẫn.</p>
</div>

<div class="card" style="display: flex; align-items: center; gap: 20px; padding: 24px; border-radius: var(--radius-md); background: linear-gradient(135deg, color-mix(in srgb, var(--primary-soft) 90%, var(--surface)), var(--surface)); border-color: var(--primary);">
    <div style="width: 56px; height: 56px; border-radius: var(--radius-full); background: var(--primary); color: #ffffff; display: grid; place-items: center;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6.4-4.8-6.4 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
    </div>
    <div>
        <strong style="font-size: 2.2rem; font-weight: 800; color: var(--primary-strong); line-height: 1;">{{ $balance }}</strong><br>
        <span style="font-size: 0.92rem; color: var(--muted); font-weight: 600;">Điểm hiện tại</span>
    </div>
</div>

<div class="section-heading">
    <h2>Lịch sử giao dịch điểm</h2>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-md);">
    <div class="table-wrap" style="border: 0;">
        <table>
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Điểm</th>
                    <th>Số dư</th>
                    <th>Lý do</th>
                </tr>
            </thead>
            <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td style="font-size: 0.88rem; color: var(--muted);">{{ $transaction->created_at }}</td>
                    <td style="font-weight: 750; color: {{ $transaction->points_delta >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $transaction->points_delta > 0 ? '+' : '' }}{{ $transaction->points_delta }}
                    </td>
                    <td style="font-weight: 600;">{{ $transaction->balance_after }}</td>
                    <td>{{ $transaction->reason }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 36px; color: var(--muted);">
                        Chưa có giao dịch điểm.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $transactions->links() }}
</div>

@endsection
