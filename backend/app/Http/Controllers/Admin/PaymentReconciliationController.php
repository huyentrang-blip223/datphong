<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\AuditLogService;
use App\Services\VnPayGatewayService;

class PaymentReconciliationController extends Controller
{
    public function __invoke(Payment $payment, VnPayGatewayService $service)
    {
        abort_unless($payment->gateway === 'vnpay', 422);
        try {
            $data = $service->reconcile($payment, request()->user());
            app(AuditLogService::class)->record(request()->user(), 'vnpay_reconciled', 'payments', $payment->id, null, ['response_code' => $data['vnp_ResponseCode'] ?? null, 'transaction_status' => $data['vnp_TransactionStatus'] ?? null]);

            return back()->with('status', 'VNPay: '.($data['vnp_ResponseCode'] ?? '').' / '.($data['vnp_TransactionStatus'] ?? '').' — '.($data['vnp_Message'] ?? ''));
        } catch (\Throwable $e) {
            return back()->withErrors(['payment' => 'Chưa xác thực được kết quả đối soát VNPay.']);
        }
    }
}
