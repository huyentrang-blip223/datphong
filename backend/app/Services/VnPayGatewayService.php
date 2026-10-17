<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class VnPayGatewayService
{
    // All outbound financial requests are restricted to the academic sandbox.
    private const ENDPOINT = 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction';

    public function reconcile(Payment $payment, User $actor): array
    {
        $data = $this->query($payment);
        if (($data['vnp_ResponseCode'] ?? '') !== '00' || ($data['vnp_TransactionStatus'] ?? '') !== '00') {
            return $data;
        }
        if (($data['vnp_TransactionType'] ?? '') === '02') {
            DB::transaction(function () use ($payment) {
                Booking::whereKey($payment->booking_id)->lockForUpdate()->firstOrFail();
                Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail()->update(['refund_state' => 'succeeded']);
            });
            app(BookingLifecycleService::class)->refund($payment->booking, $actor);
        } elseif (($data['vnp_TransactionType'] ?? '') === '01') {
            // The query response has already passed gateway signature verification.
            // Reuse the same settlement path as IPN so stock/loyalty stay consistent.
            $settlement = collect($data)->only(['vnp_TxnRef', 'vnp_Amount', 'vnp_TmnCode', 'vnp_TransactionNo', 'vnp_TransactionStatus'])->all();
            $settlement['vnp_ResponseCode'] = '00';
            ksort($settlement);
            $parts = [];
            foreach ($settlement as $key => $value) {
                $parts[] = urlencode($key).'='.urlencode($value);
            }
            $settlement['vnp_SecureHash'] = hash_hmac('sha512', implode('&', $parts), config('services.vnpay.secret_key'));
            app(VnPayPaymentService::class)->handleReturn($settlement);
        }

        return $data;
    }

    public function query(Payment $payment): array
    {
        $data = $this->base($payment, 'querydr');
        $data['vnp_SecureHash'] = $this->sign($data, ['RequestId', 'Version', 'Command', 'TmnCode', 'TxnRef', 'TransactionDate', 'CreateDate', 'IpAddr', 'OrderInfo']);

        return $this->send($data, $payment, 'querydr');
    }

    public function refund(Payment $payment, User $actor): bool
    {
        return Cache::lock('vnpay-refund:'.$payment->id, 60)->block(10, function () use ($payment, $actor) {
            $prepared = DB::transaction(function () use ($payment) {
                $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->firstOrFail();
                $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if ($locked->status === 'refunded') {
                    return $locked;
                }
                if (! in_array($booking->status, ['cancelled', 'expired'], true) || $locked->status !== 'paid') {
                    throw new InvalidArgumentException('Chỉ hoàn giao dịch đã thanh toán của booking đã hủy/hết hạn.');
                }
                if ($locked->refund_state === 'succeeded') {
                    return $locked;
                }
                if (in_array($locked->refund_state, ['submitted', 'unknown', 'processing'], true)) {
                    throw new InvalidArgumentException('Yêu cầu hoàn tiền đang chờ đối soát; không gửi lặp.');
                }
                if (! $locked->gateway_transaction_no || ! $locked->gateway_created_at) {
                    throw new InvalidArgumentException('Thiếu dữ liệu gốc VNPay để hoàn tiền; cần đối soát.');
                }
                $locked->update(['refund_request_id' => strtoupper(Str::random(32)), 'refund_state' => 'submitted']);

                return $locked;
            });
            if ($prepared->status === 'refunded' || $prepared->refund_state === 'succeeded') {
                return true;
            }
            $data = $this->base($prepared, 'refund');
            $data['vnp_RequestId'] = $prepared->refund_request_id;
            $data['vnp_TransactionType'] = '02';
            $data['vnp_Amount'] = (string) (int) round((float) $prepared->amount * 100);
            $data['vnp_TransactionNo'] = $prepared->gateway_transaction_no;
            $data['vnp_CreateBy'] = 'user'.$actor->id;
            $data['vnp_SecureHash'] = $this->sign($data, ['RequestId', 'Version', 'Command', 'TmnCode', 'TransactionType', 'TxnRef', 'Amount', 'TransactionNo', 'TransactionDate', 'CreateBy', 'CreateDate', 'IpAddr', 'OrderInfo']);
            try {
                $response = $this->send($data, $prepared, 'refund');
            } catch (\Throwable $e) {
                $prepared->update(['refund_state' => 'unknown']);
                throw new InvalidArgumentException('Không xác nhận được kết quả VNPay; cần đối soát trước khi gửi lại.');
            }
            $success = ($response['vnp_ResponseCode'] ?? '') === '00' && ($response['vnp_TransactionStatus'] ?? '') === '00';
            $processing = in_array($response['vnp_TransactionStatus'] ?? '', ['05', '06'], true) || ($response['vnp_ResponseCode'] ?? '') === '94';
            $prepared->update(['refund_state' => $success ? 'succeeded' : ($processing ? 'processing' : 'failed'), 'gateway_response_code' => $response['vnp_ResponseCode'] ?? null]);
            if (! $success) {
                throw new InvalidArgumentException($processing ? 'VNPay đang xử lý hoàn tiền; vui lòng đối soát sau.' : 'VNPay chưa chấp nhận hoàn tiền.');
            }

            return true;
        });
    }

    private function base(Payment $payment, string $command): array
    {
        if (! config('services.vnpay.tmn_code') || ! config('services.vnpay.secret_key')) {
            throw new InvalidArgumentException('Chưa cấu hình VNPay.');
        }

        return ['vnp_RequestId' => strtoupper(Str::random(32)), 'vnp_Version' => '2.1.0', 'vnp_Command' => $command,
            'vnp_TmnCode' => config('services.vnpay.tmn_code'), 'vnp_TxnRef' => $payment->txn_ref,
            'vnp_TransactionDate' => $payment->gateway_created_at ?: $payment->created_at->format('YmdHis'),
            'vnp_CreateDate' => now()->format('YmdHis'), 'vnp_IpAddr' => request()?->ip() ?: '127.0.0.1', 'vnp_OrderInfo' => $command.' booking '.$payment->booking_id];
    }

    private function sign(array $data, array $fields): string
    {
        return hash_hmac('sha512', implode('|', array_map(fn ($f) => (string) ($data['vnp_'.$f] ?? ''), $fields)), config('services.vnpay.secret_key'));
    }

    private function send(array $data, Payment $payment, string $command): array
    {
        $result = Http::timeout(15)->acceptJson()->post(self::ENDPOINT, $data)->throw()->json();
        if (! is_array($result)) {
            throw new InvalidArgumentException('Phản hồi VNPay không hợp lệ.');
        }
        $fields = ['ResponseId', 'Command', 'ResponseCode', 'Message', 'TmnCode', 'TxnRef', 'Amount', 'BankCode', 'PayDate', 'TransactionNo', 'TransactionType', 'TransactionStatus', 'OrderInfo'];
        if ($command === 'querydr') {
            $fields = array_merge($fields, ['PromotionCode', 'PromotionAmount']);
        }
        if (! hash_equals($this->sign($result, $fields), (string) ($result['vnp_SecureHash'] ?? ''))
            || ($result['vnp_TxnRef'] ?? '') !== $payment->txn_ref || ($result['vnp_TmnCode'] ?? '') !== config('services.vnpay.tmn_code')
            || (int) ($result['vnp_Amount'] ?? -1) !== (int) round((float) $payment->amount * 100)) {
            throw new InvalidArgumentException('Không xác thực được phản hồi VNPay.');
        }

        return $result;
    }
}
