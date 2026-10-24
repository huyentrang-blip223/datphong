<?php
namespace Tests\Feature;

use App\Mail\BusinessNotificationMail;
use App\Models\Booking;
use App\Models\Homestay;
use App\Models\LocalProduct;
use App\Models\ProductOrder;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Week8OrderPaymentReviewTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seller_can_crud_only_owned_products(): void
    {
        $seller = $this->user('w8-seller@dt07.test', 'seller');
        $other = $this->user('w8-seller-other@dt07.test', 'seller');
        $otherProduct = $this->productForSeller($other);

        $this->actingAs($seller)->post(route('seller.products.store'), [
            'name' => 'Mat ong rung',
            'origin_place' => 'Da Lat',
            'description' => 'San pham test',
            'unit' => 'chai',
            'price' => 120000,
            'stock_qty' => 8,
            'status' => 'published',
        ])->assertRedirect();
        $this->assertDatabaseHas('local_products', ['seller_id' => $seller->id, 'name' => 'Mat ong rung']);

        $this->actingAs($seller)->put(route('seller.products.update', $otherProduct), [
            'name' => 'Illegal',
            'origin_place' => 'Hue',
            'description' => 'No',
            'unit' => 'goi',
            'price' => 1,
            'stock_qty' => 1,
            'status' => 'published',
        ])->assertForbidden();
    }

    public function test_guest_order_uses_database_price_and_decrements_stock(): void
    {
        $guest = $this->user('w8-order-guest@dt07.test', 'guest');
        $product = $this->productForSeller($this->user('w8-order-seller@dt07.test', 'seller'), ['price' => 50000, 'stock_qty' => 5]);

        $this->actingAs($guest)->postJson(route('guest.orders.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1]],
        ])->assertCreated()
            ->assertJsonPath('data.total_amount', '100000.00');

        $this->assertSame(3, (int) $product->fresh()->stock_qty);
    }

    public function test_web_product_order_redirects_to_payment_and_can_be_paid(): void
    {
        $guest = $this->user('w8-order-payment-guest@dt07.test', 'guest');
        $other = $this->user('w8-order-payment-other@dt07.test', 'guest');
        $product = $this->productForSeller($this->user('w8-order-payment-seller@dt07.test', 'seller'), ['price' => 70000, 'stock_qty' => 5]);

        $this->actingAs($guest)->post(route('guest.orders.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertRedirect(route('guest.order-payments.show', ProductOrder::latest('id')->first()));

        $order = ProductOrder::latest('id')->firstOrFail();

        $this->actingAs($other)->get(route('guest.order-payments.show', $order))->assertForbidden();
        $this->actingAs($guest)
            ->get(route('guest.order-payments.show', $order))
            ->assertOk()
            ->assertSee('Thanh toán đơn ' . $order->code)
            ->assertSee('Thanh toán tiền mặt');

        $this->actingAs($guest)
            ->postJson(route('guest.order-payments.cash', $order))
            ->assertCreated()
            ->assertJsonPath('data.status', 'initiated');

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertDatabaseHas('product_order_payments', [
            'order_id' => $order->id,
            'status' => 'initiated',
        ]);
    }

    public function test_order_rejects_insufficient_stock(): void
    {
        $guest = $this->user('w8-stock-guest@dt07.test', 'guest');
        $product = $this->productForSeller($this->user('w8-stock-seller@dt07.test', 'seller'), ['stock_qty' => 1]);

        $this->actingAs($guest)->postJson(route('guest.orders.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertStatus(409);
    }

    public function test_seller_can_update_valid_order_transition_but_not_invalid(): void
    {
        $seller = $this->user('w8-transition-seller@dt07.test', 'seller');
        $guest = $this->user('w8-transition-guest@dt07.test', 'guest');
        $product = $this->productForSeller($seller);
        $this->actingAs($guest)->postJson(route('guest.orders.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $order = ProductOrder::latest('id')->first();

        $this->actingAs($seller)->postJson(route('seller.orders.status', $order), ['status' => 'delivered'])->assertStatus(422);
        $this->actingAs($seller)->postJson(route('seller.orders.status', $order), ['status' => 'confirmed'])->assertOk();
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_cash_selection_does_not_mark_paid_and_is_idempotent(): void
    {
        [$guest, $booking] = $this->bookingForGuest('w8-pay-guest@dt07.test');

        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking), ['result' => 'success'])->assertStatus(422);
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertCreated()
            ->assertJsonPath('data.status', 'initiated');
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertCreated();
        $this->assertSame(1, $booking->payments()->count());
        $this->assertNull($booking->payments()->first()->paid_at);
    }

    public function test_guest_can_open_payment_checkout_for_own_booking_only(): void
    {
        [$guest, $booking] = $this->bookingForGuest('w8-payment-page-guest@dt07.test');
        $other = $this->user('w8-payment-page-other@dt07.test', 'guest');

        $this->actingAs($guest)
            ->get(route('guest.payments.show', $booking))
            ->assertOk()
            ->assertSee('Thanh toán booking ' . $booking->code)
            ->assertSee('Thanh toán tiền mặt')
            ->assertSee(number_format($booking->total_amount) . ' đ');

        $this->actingAs($other)
            ->get(route('guest.payments.show', $booking))
            ->assertForbidden();
    }

    public function test_vnpay_payment_url_and_callback_mark_booking_payment_paid(): void
    {
        Mail::fake();
        config([
            'services.vnpay.enabled' => true, 'services.vnpay.url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
            'services.vnpay.tmn_code' => 'TESTCODE',
            'services.vnpay.secret_key' => 'TESTSECRET',
            'services.vnpay.return_url' => 'http://localhost:8080/api/v1/payment/vn-pay-callback',
            'services.vnpay.version' => '2.1.0',
            'services.vnpay.command' => 'pay',
            'services.vnpay.order_type' => 'other',
            'mail.notifications_enabled' => true,
        ]);
        [$guest, $booking] = $this->bookingForGuest('w8-vnpay-guest@dt07.test');
        $host = $booking->room->homestay->owner;

        $response = $this->actingAs($guest)->postJson(route('guest.payments.vnpay', $booking));

        $response->assertOk()->assertJsonStructure(['payment_url']);
        $this->assertStringStartsWith('https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?', $response->json('payment_url'));
        $payment = Payment::where('booking_id', $booking->id)->where('gateway', 'vnpay')->firstOrFail();
        $this->assertSame('initiated', $payment->status);

        $callbackParams = $this->signedVnPayParams([
            'vnp_Amount' => (string) ((int) round((float) $payment->amount * 100)),
            'vnp_BankCode' => 'NCB',
            'vnp_OrderInfo' => 'Thanh toan booking ' . $booking->code,
            'vnp_PayDate' => '20261008120000',
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => 'TESTCODE',
            'vnp_TransactionNo' => '12345678',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $payment->txn_ref,
        ], 'TESTSECRET');

        $this->getJson(route('api.v1.payment.vnpay-callback', $callbackParams))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('payment_status', 'paid');

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('confirmed', $booking->fresh()->status);
        Mail::assertSent(BusinessNotificationMail::class, fn ($mail) => $mail->hasTo($guest->email)
            && $mail->title === 'Thanh toán VNPay thành công');
        Mail::assertSent(BusinessNotificationMail::class, fn ($mail) => $mail->hasTo($host->email)
            && $mail->title === 'Booking đã thanh toán qua VNPay');
    }

    public function test_web_booking_redirects_to_payment_checkout(): void
    {
        $guest = $this->user('w8-booking-payment-redirect@dt07.test', 'guest');
        $host = $this->user('w8-booking-payment-redirect-host@dt07.test', 'host');
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'W8 Payment Redirect Homestay',
            'slug' => 'w8-payment-redirect-' . uniqid(),
            'province' => 'Lam Dong',
            'address_line' => '1 Redirect',
            'status' => 'approved',
        ]);
        $room = Room::create([
            'homestay_id' => $homestay->id,
            'name' => 'W8 Payment Redirect Room',
            'room_type' => 'private_room',
            'max_guests' => 2,
            'bed_count' => 1,
            'base_price' => 100000,
            'quantity' => 1,
            'active' => true,
        ]);
        RoomAvailability::create([
            'room_id' => $room->id,
            'stay_date' => '2026-12-26',
            'units_total' => 1,
            'units_held' => 0,
            'units_sold' => 0,
            'status' => 'open',
        ]);

        $this->actingAs($guest)->post(route('bookings.store'), [
            'room_id' => $room->id,
            'checkin' => '2026-12-26',
            'checkout' => '2026-12-27',
            'guests' => 1,
        ])->assertRedirect(route('guest.payments.show', Booking::latest('id')->first()));
    }

    public function test_guest_can_cancel_own_booking_and_cannot_cancel_others(): void
    {
        [$guest, $booking] = $this->bookingForGuest('w8-cancel-guest@dt07.test');
        $other = $this->user('w8-cancel-other@dt07.test', 'guest');

        $this->actingAs($other)->post(route('guest.bookings.cancel', $booking))->assertForbidden();
        $this->actingAs($guest)->post(route('guest.bookings.cancel', $booking))->assertRedirect();
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_review_requires_completed_own_booking(): void
    {
        [$guest, $booking] = $this->bookingForGuest('w8-review-guest@dt07.test');

        $this->actingAs($guest)->postJson(route('guest.reviews.store', $booking), [
            'rating' => 5,
            'content' => 'Tot',
        ])->assertStatus(422);

        $booking->update(['status' => 'completed']);
        $this->actingAs($guest)->postJson(route('guest.reviews.store', $booking), [
            'rating' => 5,
            'content' => 'Tot',
        ])->assertCreated();
    }

    public function test_review_rating_bounds_are_validated(): void
    {
        [$guest, $booking] = $this->bookingForGuest('w8-review-bound@dt07.test');
        $booking->update(['status' => 'completed']);

        $this->actingAs($guest)->postJson(route('guest.reviews.store', $booking), [
            'rating' => 6,
        ])->assertUnprocessable();
    }

    private function bookingForGuest(string $email): array
    {
        $guest = $this->user($email, 'guest');
        $host = $this->user($email . '.host', 'host');
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'W8 Booking Homestay ' . uniqid(),
            'slug' => 'w8-booking-' . uniqid(),
            'province' => 'Lam Dong',
            'address_line' => '1 Week8',
            'status' => 'approved',
        ]);
        $room = Room::create([
            'homestay_id' => $homestay->id,
            'name' => 'W8 Booking Room',
            'room_type' => 'private_room',
            'max_guests' => 2,
            'bed_count' => 1,
            'base_price' => 100000,
            'quantity' => 1,
            'active' => true,
        ]);
        RoomAvailability::create([
            'room_id' => $room->id,
            'stay_date' => '2026-12-25',
            'units_total' => 1,
            'units_held' => 0,
            'units_sold' => 0,
            'status' => 'open',
        ]);

        return [$guest, app(BookingService::class)->createBooking($guest, $room, '2026-12-25', '2026-12-26', 1)];
    }

    private function productForSeller(User $seller, array $overrides = []): LocalProduct
    {
        return LocalProduct::create(array_merge([
            'seller_id' => $seller->id,
            'name' => 'W8 Product ' . uniqid(),
            'origin_place' => 'Da Lat',
            'description' => 'Week8 product',
            'unit' => 'goi',
            'price' => 100000,
            'stock_qty' => 10,
            'status' => 'published',
        ], $overrides));
    }

    private function signedVnPayParams(array $params, string $secret): array
    {
        ksort($params);
        $hashData = [];
        foreach ($params as $key => $value) {
            $hashData[] = urlencode((string) $key) . '=' . urlencode((string) $value);
        }
        $params['vnp_SecureHash'] = hash_hmac('sha512', implode('&', $hashData), $secret);

        return $params;
    }

    private function user(string $email, string $role): User
    {
        return User::create([
            'email' => $email,
            'password_hash' => Hash::make('Dt07@2026!'),
            'role' => $role,
            'full_name' => 'Week8 ' . $role,
            'status' => 'active',
        ]);
    }
}
