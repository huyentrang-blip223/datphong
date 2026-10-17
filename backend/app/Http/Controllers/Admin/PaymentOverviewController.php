<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExperienceBookingPayment;
use App\Models\Payment;
use App\Models\ProductOrderPayment;

class PaymentOverviewController extends Controller
{
    public function index()
    {
        $bookingPayments = Payment::with('booking.guest')->latest('id')->take(25)->get()->map(fn ($payment) => [
            'type' => 'booking',
            'booking_id' => $payment->booking_id,
            'payment_id' => $payment->id,
            'refund_state' => $payment->refund_state,
            'booking_status' => $payment->booking?->status,
            'code' => $payment->booking?->code ?? ('#'.$payment->booking_id),
            'customer' => $payment->booking?->guest?->full_name ?? 'N/A',
            'gateway' => $payment->gateway,
            'txn_ref' => $payment->txn_ref,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at,
            'created_at' => $payment->created_at,
        ]);

        $orderPayments = ProductOrderPayment::with('order.buyer')->latest('id')->take(25)->get()->map(fn ($payment) => [
            'type' => 'product_order',
            'code' => $payment->order?->code ?? ('#'.$payment->order_id),
            'customer' => $payment->order?->buyer?->full_name ?? 'N/A',
            'gateway' => $payment->gateway,
            'txn_ref' => $payment->txn_ref,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at,
            'created_at' => $payment->created_at,
        ]);

        $experiencePayments = ExperienceBookingPayment::with('experienceBooking.guest')->latest('id')->take(25)->get()->map(fn ($payment) => [
            'type' => 'experience_booking',
            'code' => '#'.$payment->experience_booking_id,
            'customer' => $payment->experienceBooking?->guest?->full_name ?? 'N/A',
            'gateway' => $payment->gateway,
            'txn_ref' => $payment->txn_ref,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at,
            'created_at' => $payment->created_at,
        ]);

        $payments = $bookingPayments
            ->concat($orderPayments)
            ->concat($experiencePayments)
            ->sortByDesc('created_at')
            ->take(50)
            ->values();

        return view('admin.payments.index', compact('payments'));
    }
}
