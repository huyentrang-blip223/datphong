<?php
namespace App\Services;

use App\Models\ExperienceBooking;
use App\Models\ExperienceBookingPayment;
use App\Models\ProductOrder;
use App\Models\ProductOrderPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CommercePaymentService
{
    public function __construct(
        private LoyaltyService $loyaltyService,
        private NotificationService $notificationService,
        private AuditLogService $auditLogService
    ) {
    }

    public function payProductOrder(ProductOrder $order, User $guest, string $result): ProductOrderPayment
    {
        if ((int) $order->buyer_id !== (int) $guest->id || $guest->role !== 'guest') {
            throw new InvalidArgumentException('Không có quyền thanh toán đơn hàng này.');
        }

        return DB::transaction(function () use ($order, $guest, $result) {
            $locked = ProductOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Đơn hàng đã được thanh toán.');
            }

            $status = $result === 'success' ? 'paid' : 'failed';
            $payment = ProductOrderPayment::create([
                'order_id' => $locked->id,
                'gateway' => 'mock_test',
                'txn_ref' => 'POMOCK-' . strtoupper(Str::random(14)),
                'amount' => $locked->total_amount,
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
            ]);

            if ($status === 'paid' && $locked->status === 'pending') {
                $locked->update(['status' => 'confirmed']);
                $this->loyaltyService->award($guest, (int) floor((float) $locked->total_amount / 10000), 'product_order_paid', null, $locked->id);
                $this->notificationService->send($guest, 'product_order_paid', 'Thanh toán đơn hàng thành công', 'Đơn ' . $locked->code . ' đã được thanh toán.', 'product_order', $locked->id);
            }

            if ($status === 'failed') {
                $this->notificationService->send($guest, 'product_order_payment_failed', 'Thanh toán đơn hàng thất bại', 'Đơn ' . $locked->code . ' chưa được thanh toán.', 'product_order', $locked->id);
            }

            $this->auditLogService->record($guest, 'product_order_payment_' . $status, 'product_order_payments', $payment->id, null, [
                'order_id' => $locked->id,
                'status' => $status,
                'gateway' => 'mock_test',
            ]);

            return $payment;
        });
    }

    public function payExperienceBooking(ExperienceBooking $booking, User $guest, string $result): ExperienceBookingPayment
    {
        if ((int) $booking->guest_id !== (int) $guest->id || $guest->role !== 'guest') {
            throw new InvalidArgumentException('Không có quyền thanh toán đặt chỗ trải nghiệm này.');
        }

        return DB::transaction(function () use ($booking, $guest, $result) {
            $locked = ExperienceBooking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($locked->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Đặt chỗ trải nghiệm đã được thanh toán.');
            }

            $status = $result === 'success' ? 'paid' : 'failed';
            $payment = ExperienceBookingPayment::create([
                'experience_booking_id' => $locked->id,
                'gateway' => 'mock_test',
                'txn_ref' => 'EXPMOCK-' . strtoupper(Str::random(14)),
                'amount' => $locked->total_amount,
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
            ]);

            if ($status === 'paid' && $locked->status === 'pending') {
                $locked->update(['status' => 'confirmed']);
                $this->notificationService->send($guest, 'experience_booking_paid', 'Thanh toán trải nghiệm thành công', 'Đặt chỗ trải nghiệm #' . $locked->id . ' đã được thanh toán.', 'experience_booking', $locked->id);
            }

            if ($status === 'failed') {
                $this->notificationService->send($guest, 'experience_payment_failed', 'Thanh toán trải nghiệm thất bại', 'Đặt chỗ trải nghiệm #' . $locked->id . ' chưa được thanh toán.', 'experience_booking', $locked->id);
            }

            $this->auditLogService->record($guest, 'experience_booking_payment_' . $status, 'experience_booking_payments', $payment->id, null, [
                'experience_booking_id' => $locked->id,
                'status' => $status,
                'gateway' => 'mock_test',
            ]);

            return $payment;
        });
    }
}
