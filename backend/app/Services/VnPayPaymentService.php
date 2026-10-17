<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusLog;
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

            if ($locked->payments()->where('status', 'paid')->exists()) {
                throw new InvalidArgumentException('Booking đã được thanh toán.');
            }

            return Payment::create([
                'booking_id' => $locked->id,
                'gateway' => 'vnpay',
                'txn_ref' => $this->newTxnRef($locked),
                'amount' => $locked->total_amount,
                'status' => 'initiated',
            ]);
        });

        $createdAt = CarbonImmutable::now('Asia/Ho_Chi_Minh');
        $params = [
            'vnp_Version' => config('services.vnpay.version'),
            'vnp_Command' => config('services.vnpay.command'),
            'vnp_TmnCode' => config('services.vnpay.tmn_code'),
            'vnp_Amount' => (string) ((int) round((float) $payment->amount * 100)),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $payment->txn_ref,
            'vnp_OrderInfo' => 'Thanh toan booking ' . $booking->code,
            'vnp_OrderType' => config('services.vnpay.order_type'),
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => config('services.vnpay.return_url'),
            'vnp_IpAddr' => $ipAddress,
            'vnp_CreateDate' => $createdAt->format('YmdHis'),
            'vnp_ExpireDate' => $createdAt->addMinutes(15)->format('YmdHis'),
        ];

        $params['vnp_SecureHash'] = $this->secureHash($params);

        return rtrim((string) config('services.vnpay.url'), '?') . '?' . http_build_query($params);
    }

    public function handleReturn(array $input): array
    {
        $this->assertConfigured();

        $receivedHash = (string) ($input['vnp_SecureHash'] ?? '');
        if ($receivedHash === '') {
            throw new InvalidArgumentException('Thiếu chữ ký VNPay.');
        }

        $hashInput = $input;
        unset($hashInput['vnp_SecureHash'], $hashInput['vnp_SecureHashType']);

        if (!hash_equals($this->secureHash($hashInput), $receivedHash)) {
            throw new InvalidArgumentException('Chữ ký VNPay không hợp lệ.');
        }

        $txnRef = (string) ($input['vnp_TxnRef'] ?? '');
        $responseCode = (string) ($input['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string) ($input['vnp_TransactionStatus'] ?? '');
        $isPaid = $responseCode === '00' && $transactionStatus === '00';

        return DB::transaction(function () use ($txnRef, $isPaid, $input) {
            $payment = Payment::where('gateway', 'vnpay')
                ->where('txn_ref', $txnRef)
                ->lockForUpdate()
                ->firstOrFail();

            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->firstOrFail();
            $expectedAmount = (int) round((float) $payment->amount * 100);
            $receivedAmount = (int) ($input['vnp_Amount'] ?? 0);
            if ($receivedAmount !== $expectedAmount) {
                throw new InvalidArgumentException('Số tiền VNPay không khớp giao dịch.');
            }

            $status = $isPaid ? 'paid' : 'failed';
            $wasAlreadyPaid = $payment->status === 'paid';

            if (! $wasAlreadyPaid) {
                $payment->update([
                    'status' => $status,
                    'paid_at' => $isPaid ? now() : null,
                ]);
            }

            $guest = $booking->guest;
            if ($isPaid && $booking->status !== 'confirmed') {
                $from = $booking->status;
                $booking->update(['status' => 'confirmed']);
                BookingStatusLog::create([
                    'booking_id' => $booking->id,
                    'from_status' => $from,
                    'to_status' => 'confirmed',
                    'actor_id' => $booking->guest_id,
                    'reason' => 'vnpay_payment_success',
                ]);
            }

            if ($isPaid && $guest && ! $wasAlreadyPaid) {
                $this->loyaltyService->award($guest, (int) floor((float) $booking->total_amount / 10000), 'vnpay_booking_payment', $booking->id);
                $this->notificationService->send($guest, 'vnpay_payment_paid', 'Thanh toán VNPay thành công', 'Booking ' . $booking->code . ' đã được VNPay xác nhận.', 'booking', $booking->id);
            } elseif (! $isPaid && $guest) {
                $this->notificationService->send($guest, 'vnpay_payment_failed', 'Thanh toán VNPay thất bại', 'Booking ' . $booking->code . ' chưa được thanh toán thành công.', 'booking', $booking->id);
            }

            $this->auditLogService->record($guest, 'vnpay_payment_' . $status, 'payments', $payment->id, null, [
                'booking_id' => $booking->id,
                'txn_ref' => $txnRef,
                'response_code' => $input['vnp_ResponseCode'] ?? null,
                'transaction_status' => $input['vnp_TransactionStatus'] ?? null,
            ]);

            return [
                'payment' => $payment->fresh(),
                'booking' => $booking->fresh(),
                'success' => $isPaid,
            ];
        });
    }

    private function secureHash(array $params): string
    {
        ksort($params);

        $hashData = [];
        foreach ($params as $key => $value) {
            if ($value !== null && $value !== '') {
                $hashData[] = urlencode((string) $key) . '=' . urlencode((string) $value);
            }
        }

        return hash_hmac('sha512', implode('&', $hashData), (string) config('services.vnpay.secret_key'));
    }

    private function newTxnRef(Booking $booking): string
    {
        return 'BK' . $booking->id . now('Asia/Ho_Chi_Minh')->format('YmdHis');
    }

    private function assertConfigured(): void
    {
        if (!config('services.vnpay.tmn_code') || !config('services.vnpay.secret_key') || !config('services.vnpay.return_url')) {
            throw new InvalidArgumentException('Chưa cấu hình VNPay.');
        }
    }
}
