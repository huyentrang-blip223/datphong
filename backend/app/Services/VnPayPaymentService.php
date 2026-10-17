<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VnPayPaymentService
{
    public function __construct(
        private LoyaltyService $loyaltyService,
        private NotificationService $notificationService,
        private AuditLogService $auditLogService
    ) {
    }

    public function createPaymentUrl(Booking $booking, User $guest, string $ipAddress): string
    {
        if ((int) $booking->guest_id !== (int) $guest->id || $guest->role !== 'guest') {
            throw new InvalidArgumentException('Không có quyền thanh toán booking này.');
        }

        if ($booking->payments()->where('status', 'paid')->exists()) {
            throw new InvalidArgumentException('Booking đã được thanh toán.');
        }

        $this->assertConfigured();

        $payment = DB::transaction(function () use ($booking) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            app(BookingLifecycleService::class)->assertPayable($locked);

            if ($locked->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Booking đã được thanh toán.');
            }

            return Payment::create([
                'booking_id' => $locked->id,
                'gateway' => 'vnpay',
                'txn_ref' => $this->newTxnRef($locked),
                'amount' => $locked->total_amount,
                'status' => 'initiated',
                'gateway_created_at' => now()->format('YmdHis'),
            ]);
        });

        $createdAt = CarbonImmutable::createFromFormat('YmdHis', $payment->gateway_created_at, 'Asia/Ho_Chi_Minh');
        $params = [
            'vnp_Version' => config('services.vnpay.version'),
            'vnp_Command' => config('services.vnpay.command'),
            'vnp_TmnCode' => config('services.vnpay.tmn_code'),
            'vnp_Amount' => (string) ((int) round((float) $payment->amount * 100)),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $payment->txn_ref,
            'vnp_OrderInfo' => 'Thanh toan booking '.$booking->code,
            'vnp_OrderType' => config('services.vnpay.order_type'),
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => config('services.vnpay.return_url'),
            'vnp_IpAddr' => $ipAddress,
            'vnp_CreateDate' => $createdAt->format('YmdHis'),
            'vnp_ExpireDate' => $createdAt->addMinutes(15)->format('YmdHis'),
        ];

        $params['vnp_SecureHash'] = $this->secureHash($params);

        return rtrim((string) config('services.vnpay.url'), '?').'?'.http_build_query($params);
    }

    public function handleReturn(array $input): array
    {
        $this->assertConfigured();
        foreach ($input as $value) {
            if (! is_scalar($value)) {
                throw new InvalidArgumentException('Dữ liệu VNPay không hợp lệ.', 97);
            }
        }
        if (isset($input['vnp_TmnCode']) && $input['vnp_TmnCode'] !== config('services.vnpay.tmn_code')) {
            throw new InvalidArgumentException('Merchant không khớp.', 97);
        }

        $receivedHash = (string) ($input['vnp_SecureHash'] ?? '');
        if ($receivedHash === '') {
            throw new InvalidArgumentException('Thiếu chữ ký VNPay.', 97);
        }

        $hashInput = $input;
        unset($hashInput['vnp_SecureHash'], $hashInput['vnp_SecureHashType']);

        if (! hash_equals($this->secureHash($hashInput), $receivedHash)) {
            throw new InvalidArgumentException('Chữ ký VNPay không hợp lệ.', 97);
        }

        $txnRef = (string) ($input['vnp_TxnRef'] ?? '');
        $responseCode = (string) ($input['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string) ($input['vnp_TransactionStatus'] ?? '');
        $isPaid = $responseCode === '00' && $transactionStatus === '00';

        return DB::transaction(function () use ($txnRef, $isPaid, $input) {
            $reference = Payment::where('gateway', 'vnpay')->where('txn_ref', $txnRef)->firstOrFail();
            $booking = Booking::whereKey($reference->booking_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::where('gateway', 'vnpay')
                ->where('txn_ref', $txnRef)
                ->lockForUpdate()
                ->firstOrFail();

            $expectedAmount = (int) round((float) $payment->amount * 100);
            $receivedAmount = (int) ($input['vnp_Amount'] ?? 0);
            if ($receivedAmount !== $expectedAmount) {
                throw new InvalidArgumentException('Số tiền VNPay không khớp giao dịch.', 4);
            }

            $status = $isPaid ? 'paid' : 'failed';
            $wasAlreadyPaid = $payment->status === 'paid';

            if ($payment->status === 'refunded') {
                return ['payment' => $payment, 'booking' => $booking, 'success' => false, 'duplicate' => true];
            }
            if (! $wasAlreadyPaid) {
                if ($isPaid && (in_array($booking->status, ['cancelled', 'expired'], true)
                    || ($booking->hold_expires_at && $booking->hold_expires_at->lte(now())))) {
                    // Record settlement without consuming inventory that may now belong to another guest.
                    $payment->update(['status' => 'paid', 'paid_at' => now(), 'gateway_transaction_no' => $input['vnp_TransactionNo'] ?? null, 'refund_state' => 'required']);
                    app(BookingLifecycleService::class)->expireDue($booking->room_id);
                    $this->auditLogService->record($booking->guest, 'late_vnpay_settlement', 'payments', $payment->id, null, ['refund_required' => true]);

                    return ['payment' => $payment->fresh(), 'booking' => $booking->fresh(), 'success' => false];
                }
                app(BookingLifecycleService::class)->assertPayable($booking);
                if ($isPaid && $booking->payments()->where('status', 'paid')->exists()) {
                    throw new InvalidArgumentException('Booking đã có giao dịch thanh toán thành công khác.');
                }
                if ($isPaid) {
                    app(BookingLifecycleService::class)->confirmLocked($booking, $booking->guest);
                }
            }

            if (! $wasAlreadyPaid) {
                $payment->update([
                    'status' => $status,
                    'gateway_transaction_no' => $input['vnp_TransactionNo'] ?? null,
                    'gateway_response_code' => $input['vnp_ResponseCode'] ?? null,
                    'paid_at' => $isPaid ? now() : null,
                ]);
            }

            $guest = $booking->guest;
            if ($isPaid && $guest && ! $wasAlreadyPaid) {
                $this->loyaltyService->award($guest, (int) floor((float) $booking->total_amount / 10000), 'vnpay_booking_payment', $booking->id);
                $this->notificationService->send($guest, 'vnpay_payment_paid', 'Thanh toán VNPay thành công', 'Booking '.$booking->code.' đã được VNPay xác nhận.', 'booking', $booking->id);
            } elseif (! $isPaid && $guest) {
                $this->notificationService->send($guest, 'vnpay_payment_failed', 'Thanh toán VNPay thất bại', 'Booking '.$booking->code.' chưa được thanh toán thành công.', 'booking', $booking->id);
            }

            $this->auditLogService->record($guest, 'vnpay_payment_'.$status, 'payments', $payment->id, null, [
                'booking_id' => $booking->id,
                'txn_ref' => $txnRef,
                'response_code' => $input['vnp_ResponseCode'] ?? null,
                'transaction_status' => $input['vnp_TransactionStatus'] ?? null,
            ]);

            return [
                'payment' => $payment->fresh(),
                'booking' => $booking->fresh(),
                'success' => $isPaid && in_array($booking->status, ['confirmed', 'completed'], true),
                'duplicate' => $wasAlreadyPaid,
            ];
        });
    }

    private function secureHash(array $params): string
    {
        ksort($params);

        $hashData = [];
        foreach ($params as $key => $value) {
            if ($value !== null && $value !== '') {
                $hashData[] = urlencode((string) $key).'='.urlencode((string) $value);
            }
        }

        return hash_hmac('sha512', implode('&', $hashData), (string) config('services.vnpay.secret_key'));
    }

    private function newTxnRef(Booking $booking): string
    {
        return 'BK'.$booking->id.now('Asia/Ho_Chi_Minh')->format('YmdHis').strtoupper(\Illuminate\Support\Str::random(8));
    }

    private function assertConfigured(): void
    {
        if (parse_url(config('services.vnpay.url'), PHP_URL_HOST) !== 'sandbox.vnpayment.vn') {
            throw new InvalidArgumentException('Đồ án chỉ hỗ trợ VNPay sandbox.');
        }
        if (! config('services.vnpay.tmn_code') || ! config('services.vnpay.secret_key') || ! config('services.vnpay.return_url')) {
            throw new InvalidArgumentException('Chưa cấu hình VNPay.');
        }
    }
}
