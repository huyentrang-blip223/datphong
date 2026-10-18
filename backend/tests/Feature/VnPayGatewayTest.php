<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Homestay;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VnPayGatewayTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.vnpay.tmn_code' => 'TEST', 'services.vnpay.secret_key' => 'SECRET', 'services.vnpay.enabled' => true, 'services.vnpay.url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html']);
    }

    public function test_refund_requires_signed_matching_gateway_success_before_marking_refunded(): void
    {
        [$guest,$booking,$payment] = $this->fixture();
        Http::fake(fn ($request) => Http::response($this->response($payment, 'refund'), 200));
        $this->actingAs($guest)->postJson(route('guest.bookings.refund', $booking))->assertOk()->assertJsonPath('data.status', 'refunded');
        $this->assertSame('refunded', $payment->fresh()->status);
        Http::assertSent(function ($request) {
            $fields = ['RequestId', 'Version', 'Command', 'TmnCode', 'TransactionType', 'TxnRef', 'Amount', 'TransactionNo', 'TransactionDate', 'CreateBy', 'CreateDate', 'IpAddr', 'OrderInfo'];
            $data = $request->data();
            $expected = hash_hmac('sha512', implode('|', array_map(fn ($f) => (string) ($data['vnp_'.$f] ?? ''), $fields)), 'SECRET');

            return $request->url() === 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction' && $data['vnp_SecureHash'] === $expected && $data['vnp_Amount'] === '10000000';
        });
        $this->postJson(route('guest.bookings.refund', $booking))->assertOk();
        Http::assertSentCount(1);
    }

    public function test_unverified_refund_response_remains_unknown_and_cannot_be_sent_twice(): void
    {
        [$guest,$booking,$payment] = $this->fixture();
        Http::fake(fn () => Http::response(['vnp_ResponseCode' => '00', 'vnp_SecureHash' => 'bad'], 200));
        $this->actingAs($guest)->postJson(route('guest.bookings.refund', $booking))->assertUnprocessable();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('unknown', $payment->fresh()->refund_state);
        $this->postJson(route('guest.bookings.refund', $booking))->assertUnprocessable();
        Http::assertSentCount(1);
    }

    public function test_reconciliation_finalizes_processing_refund_without_second_financial_request(): void
    {
        [$guest,$booking,$payment] = $this->fixture();
        $payment->update(['refund_state' => 'processing', 'refund_request_id' => str_repeat('A', 32)]);
        $admin = User::create(['email' => uniqid().'@test.example', 'full_name' => 'Admin', 'password_hash' => Hash::make('Dt07@2026!'), 'role' => 'admin', 'status' => 'active']);
        Http::fake(fn () => Http::response($this->response($payment, 'querydr'), 200));
        $this->actingAs($admin)->post(route('admin.payments.reconcile', $payment))->assertRedirect();
        $this->assertSame('refunded', $booking->fresh()->status);
        Http::assertSentCount(1);
    }

    public function test_ipn_invalid_signature_and_array_parameters_return_protocol_errors(): void
    {
        $this->getJson(route('api.v1.payment.vnpay-ipn', ['vnp_SecureHash' => 'bad']))->assertOk()->assertJsonPath('RspCode', '97');
        $this->getJson(route('api.v1.payment.vnpay-ipn', ['vnp_Amount' => ['bad'], 'vnp_SecureHash' => 'bad']))->assertOk()->assertJsonPath('RspCode', '97');
    }

    public function test_query_reconciliation_records_late_settlement_without_reviving_booking(): void
    {
        [$guest,$booking,$payment] = $this->fixture();
        $payment->update(['status' => 'initiated']);
        Http::fake(fn () => Http::response($this->response($payment, 'querydr', '01'), 200));
        $admin = User::create(['email' => uniqid().'@test.example', 'full_name' => 'Admin', 'password_hash' => Hash::make('Dt07@2026!'), 'role' => 'admin', 'status' => 'active']);
        $this->actingAs($guest)->post(route('admin.payments.reconcile', $payment))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.payments.reconcile', $payment))->assertRedirect();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('required', $payment->fresh()->refund_state);
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    private function response(Payment $payment, string $command, string $type = '02'): array
    {
        $data = ['vnp_ResponseId' => 'RESPONSE', 'vnp_Command' => $command, 'vnp_ResponseCode' => '00', 'vnp_Message' => 'OK', 'vnp_TmnCode' => 'TEST', 'vnp_TxnRef' => $payment->txn_ref, 'vnp_Amount' => '10000000', 'vnp_BankCode' => 'NCB', 'vnp_PayDate' => now()->format('YmdHis'), 'vnp_TransactionNo' => '12345', 'vnp_TransactionType' => $type, 'vnp_TransactionStatus' => '00', 'vnp_OrderInfo' => 'Refund'];
        $fields = ['ResponseId', 'Command', 'ResponseCode', 'Message', 'TmnCode', 'TxnRef', 'Amount', 'BankCode', 'PayDate', 'TransactionNo', 'TransactionType', 'TransactionStatus', 'OrderInfo'];
        if ($command === 'querydr') {
            $fields = array_merge($fields, ['PromotionCode', 'PromotionAmount']);
        }
        $data['vnp_SecureHash'] = hash_hmac('sha512', implode('|', array_map(fn ($f) => (string) ($data['vnp_'.$f] ?? ''), $fields)), 'SECRET');

        return $data;
    }

    private function fixture(): array
    {
        $guest = User::create(['email' => uniqid().'@test.example', 'full_name' => 'Guest', 'password_hash' => Hash::make('Dt07@2026!'), 'role' => 'guest', 'status' => 'active']);
        $host = User::create(['email' => uniqid().'@test.example', 'full_name' => 'Host', 'password_hash' => Hash::make('Dt07@2026!'), 'role' => 'host', 'status' => 'active']);
        $homestay = Homestay::create(['owner_id' => $host->id, 'name' => 'Gateway Home', 'slug' => uniqid(), 'province' => 'Test', 'address_line' => '1 Test', 'status' => 'approved']);
        $room = Room::create(['homestay_id' => $homestay->id, 'name' => 'Gateway Room', 'room_type' => 'private_room', 'max_guests' => 2, 'bed_count' => 1, 'base_price' => 100000, 'quantity' => 1, 'active' => true]);
        $booking = Booking::create(['guest_id' => $guest->id, 'room_id' => $room->id, 'code' => strtoupper(substr(uniqid(), 0, 12)), 'checkin_date' => now()->addDays(3)->toDateString(), 'checkout_date' => now()->addDays(4)->toDateString(), 'guest_count' => 1, 'unit_price' => 100000, 'nights' => 1, 'total_amount' => 100000, 'status' => 'cancelled']);
        $payment = Payment::create(['booking_id' => $booking->id, 'gateway' => 'vnpay', 'txn_ref' => uniqid('REFUND'), 'amount' => 100000, 'status' => 'paid', 'paid_at' => now(), 'gateway_transaction_no' => '12345', 'gateway_created_at' => now()->format('YmdHis')]);

        return [$guest, $booking, $payment];
    }
}
