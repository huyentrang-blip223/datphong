<?php

namespace App\Services;

use App\Models\Booking;

class BookingReportService
{
    public function writeCsv($stream, array $filters): void
    {
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Mã booking', 'Homestay', 'Phòng', 'Nhận phòng', 'Trả phòng', 'Trạng thái', 'Tổng tiền', 'Đã thu', 'Đã hoàn (mô phỏng)']);
        Booking::with('room.homestay')->withSum(['payments as collected' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withSum(['payments as refunded_amount' => fn ($q) => $q->where('status', 'refunded')], 'amount')
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->where('checkin_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->where('checkin_date', '<=', $date))
            ->chunkById(200, function ($bookings) use ($stream) {
                foreach ($bookings as $booking) {
                    fputcsv($stream, array_map([$this, 'safeCell'], [$booking->code, $booking->room->homestay->name,
                        $booking->room->name, $booking->checkin_date->toDateString(), $booking->checkout_date->toDateString(),
                        $booking->status, $booking->total_amount, $booking->collected ?? 0, $booking->refunded_amount ?? 0]));
                }
            });
    }

    public function safeCell($value): string
    {
        $value = (string) $value;

        return preg_match('/^[\s]*[=+@-]/u', $value) ? "'".$value : $value;
    }
}
