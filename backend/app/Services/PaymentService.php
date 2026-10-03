<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentService
{
    public function payTest(Booking $booking, User $guest, string $result): Payment
    {
        if ((int) $booking->guest_id !== (int) $guest->id) {
            throw new InvalidArgumentException('Không có quyền thanh toán booking này.');
        }

        return DB::transaction(function () use ($booking, $guest, $result) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($locked->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Booking đã được thanh toán.');
            }

            $status = $result === 'success' ? 'paid' : 'failed';
            $payment = Payment::create([
                'booking_id' => $locked->id,
                'gateway' => 'mock_test',
                'txn_ref' => 'MOCK-' . strtoupper(Str::random(16)),
                'amount' => $locked->total_amount,
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
            ]);

            if ($status === 'paid' && in_array($locked->status, ['holding', 'pending_payment', 'confirmed'], true)) {
                $from = $locked->status;
                $locked->status = 'confirmed';
                $locked->save();
                BookingStatusLog::create([
                    'booking_id' => $locked->id,
                    'from_status' => $from,
                    'to_status' => 'confirmed',
                    'actor_id' => $guest->id,
                    'reason' => 'mock_payment_success',
                ]);
            }

            return $payment;
        });
    }
}
