<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Experience;
use App\Models\Homestay;
use App\Models\HomestayVerification;
use App\Models\LocalProduct;
use App\Models\LoyaltyTransaction;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\User;
use App\Services\BookingService;
use App\Services\CashPaymentService;
use App\Services\ExperienceBookingService;
use App\Services\ProductOrderService;
use App\Services\VnPayPaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompletedFlowsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-18 09:00:00');
        config(['mail.notifications_enabled' => false]);
        Http::fake(['*' => Http::response([], 503)]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_hold_expiry_releases_only_held_inventory_and_allows_rebooking(): void
    {
        [$guest, $room] = $this->fixture();
        $booking = $this->hold($guest, $room);
        $this->assertSame('holding', $booking->status);
        $this->assertSame(1, (int) $room->availability()->first()->units_held);
        Carbon::setTestNow(now()->addMinutes(16));
        $this->artisan('bookings:expire')->assertSuccessful();
        $this->artisan('bookings:expire')->assertSuccessful();
        $this->assertSame('expired', $booking->fresh()->status);
        $this->assertSame(0, (int) $room->availability()->first()->units_held);
        $this->assertSame(0, (int) $room->availability()->first()->units_sold);
        $this->assertSame('holding', $this->hold($guest, $room)->status);
    }

    public function test_payment_converts_hold_once_and_rejects_expired_payment(): void
    {
        [$guest, $room] = $this->fixture();
        $booking = $this->hold($guest, $room);
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking), ['result' => 'fail'])->assertStatus(422);
        $this->assertSame(1, (int) $room->availability()->first()->units_held);
        $this->pay($guest, $booking);
        $this->assertSame(0, (int) $room->availability()->first()->units_held);
        $this->assertSame(1, (int) $room->availability()->first()->units_sold);
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertStatus(422);
        [$other, $otherRoom] = $this->fixture();
        $expired = $this->hold($other, $otherRoom);
        Carbon::setTestNow(now()->addMinutes(16));
        $this->actingAs($other)->postJson(route('guest.payments.cash', $expired))->assertStatus(422);
        $this->assertSame(0, $expired->payments()->count());
    }

    public function test_repeated_cancel_and_refund_do_not_return_inventory_or_points_twice(): void
    {
        [$guest, $room] = $this->fixture();
        $booking = $this->hold($guest, $room);
        $this->pay($guest, $booking);
        $this->actingAs($guest)->postJson(route('guest.bookings.cancel', $booking))->assertOk();
        $this->actingAs($guest)->postJson(route('guest.bookings.cancel', $booking))->assertOk();
        $this->assertSame(0, (int) $room->availability()->first()->units_sold);
        $this->actingAs($guest)->postJson(route('guest.bookings.refund', $booking))->assertStatus(422);
        $host = $room->homestay->owner;
        $this->actingAs($host)->postJson(route('host.bookings.cash-refunded', $booking), ['confirmed' => true])->assertOk();
        $this->postJson(route('host.bookings.cash-refunded', $booking), ['confirmed' => true])->assertOk();
        $this->assertSame('refunded', $booking->fresh()->status);
        $this->assertSame(0, (int) LoyaltyTransaction::where('user_id', $guest->id)->sum('points_delta'));
        $this->assertSame(1, $booking->payments()->where('status', 'refunded')->count());
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertStatus(422);
    }

    public function test_refund_unpaid_and_late_paid_cancel_are_rejected(): void
    {
        [$guest, $room] = $this->fixture();
        $booking = $this->hold($guest, $room);
        $this->actingAs($guest)->postJson(route('guest.bookings.refund', $booking))->assertStatus(422);
        $this->pay($guest, $booking);
        Carbon::setTestNow('2026-12-09 12:00:00');
        $this->actingAs($guest)->postJson(route('guest.bookings.cancel', $booking))->assertStatus(422);
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(1, (int) $room->availability()->first()->units_sold);
    }

    public function test_cancelling_same_hold_twice_does_not_release_another_guests_hold(): void
    {
        [$guest, $room] = $this->fixture();
        $room->availability()->update(['units_total' => 2]);
        $first = $this->hold($guest, $room);
        $second = $this->hold($this->user('guest'), $room);
        $this->actingAs($guest)->postJson(route('guest.bookings.cancel', $first))->assertOk();
        $this->postJson(route('guest.bookings.cancel', $first))->assertOk();
        $this->assertSame(1, (int) $room->availability()->first()->units_held);
        $this->assertSame('holding', $second->fresh()->status);
    }

    public function test_only_owner_host_can_complete_paid_stay_then_guest_can_review(): void
    {
        [$guest, $room, $host] = $this->fixture();
        $booking = $this->hold($guest, $room);
        $this->pay($guest, $booking);
        $otherHost = $this->user('host');
        $this->actingAs($otherHost)->postJson(route('host.bookings.complete', $booking))->assertForbidden();
        $this->actingAs($host)->postJson(route('host.bookings.complete', $booking))->assertStatus(422);
        Carbon::setTestNow('2026-12-11 13:00:00');
        $this->actingAs($host)->postJson(route('host.bookings.complete', $booking))->assertOk()->assertJsonPath('data.status', 'completed');
        $this->actingAs($guest)->postJson(route('guest.reviews.store', $booking), ['rating' => 5, 'content' => 'Completed stay'])->assertCreated();
        $this->actingAs($guest)->postJson(route('guest.reviews.store', $booking), ['rating' => 5])->assertStatus(422);
    }

    public function test_vnpay_callback_after_cancellation_cannot_revive_booking(): void
    {
        [$guest, $room] = $this->fixture();
        $booking = $this->hold($guest, $room);
        $params = $this->vnPayParams($guest, $booking);
        $this->actingAs($guest)->postJson(route('guest.bookings.cancel', $booking))->assertOk();
        $this->getJson(route('api.v1.payment.vnpay-callback', $params))->assertOk()->assertJsonPath('success', false);
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame(0, (int) $room->availability()->first()->units_sold);
    }

    public function test_vnpay_replayed_callback_keeps_completed_booking_and_single_points_award(): void
    {
        [$guest, $room, $host] = $this->fixture();
        $booking = $this->hold($guest, $room);
        $params = $this->vnPayParams($guest, $booking);
        $this->getJson(route('api.v1.payment.vnpay-callback', $params))->assertOk();
        Carbon::setTestNow('2026-12-11 13:00:00');
        $this->actingAs($host)->postJson(route('host.bookings.complete', $booking))->assertOk();
        $this->getJson(route('api.v1.payment.vnpay-callback', $params))->assertOk();
        $this->assertSame('completed', $booking->fresh()->status);
        $this->assertSame(1, LoyaltyTransaction::where('booking_id', $booking->id)->count());
    }

    public function test_search_paginates_after_actual_stay_price_filter_and_sort(): void
    {
        [$guest, $room] = $this->fixture();
        for ($i = 0; $i < 13; $i++) {
            $copy = $room->replicate();
            $copy->name = 'Search room '.$i;
            $copy->base_price = 1000 + $i;
            $copy->save();
            $availability = $room->availability()->first()->replicate();
            $availability->room_id = $copy->id;
            $availability->price_override = 200000 + $i;
            $availability->save();
        }
        $filters = ['location' => $room->homestay->province, 'checkin' => '2026-12-10', 'checkout' => '2026-12-11', 'guests' => 1,
            'min_price' => 200000, 'max_price' => 300000, 'sort' => 'price_desc'];
        $response = $this->getJson(route('search.results', $filters))->assertOk()->assertJsonPath('total', 13)->assertJsonCount(12, 'data');
        $this->assertSame('Search room 12', $response->json('data.0.name'));
        $this->getJson(route('search.results', $filters + ['page' => 2]))->assertOk()->assertJsonCount(1, 'data');
        $this->get(route('search.results', $filters))->assertOk()->assertSee('page=2', false);
    }

    public function test_host_submission_private_document_admin_review_and_ownership(): void
    {
        Storage::fake('local');
        $host = $this->user('host');
        $data = ['name' => 'Submitted homestay', 'slug' => 'submitted-'.uniqid(), 'province' => 'Lam Dong', 'address_line' => '1 Demo',
            'checkin_time' => '14:00', 'checkout_time' => '12:00', 'document_type' => 'ownership_proof',
            'document' => UploadedFile::fake()->create('proof.pdf', 20, 'application/pdf'), 'status' => 'approved', 'owner_id' => 1];
        $this->actingAs($host)->post(route('host.homestays.store'), $data)->assertRedirect();
        $homestay = Homestay::where('slug', $data['slug'])->firstOrFail();
        $this->assertSame('pending', $homestay->status);
        $this->assertSame((int) $host->id, (int) $homestay->owner_id);
        $verification = HomestayVerification::where('homestay_id', $homestay->id)->firstOrFail();
        Storage::disk('local')->assertExists($verification->document_ref);
        $this->actingAs($this->user('host'))->get(route('verification.document', $verification))->assertForbidden();
        $this->put(route('host.homestays.update', $homestay), $data)->assertForbidden();
        $this->actingAs($host)->get(route('host.homestays.edit', $homestay))->assertOk()->assertSee('document_type');
        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.verifications.index'))->assertOk()->assertSee($homestay->name);
        $this->post(route('admin.verifications.review', $verification), ['status' => 'rejected'])->assertSessionHasErrors('note');
        $this->post(route('admin.verifications.review', $verification), ['status' => 'approved', 'note' => 'Verified'])->assertRedirect();
        $this->assertSame('approved', $homestay->fresh()->status);
        $this->assertSame((int) $admin->id, (int) $verification->fresh()->reviewed_by);
        $this->get(route('verification.document', $verification))->assertOk();
        unset($data['document']);
        $this->actingAs($host)->put(route('host.homestays.update', $homestay), $data)->assertRedirect();
        $this->assertSame('pending', $homestay->fresh()->status);
    }

    public function test_product_cancellation_restocks_once_refunds_and_blocks_later_payment(): void
    {
        [$guest, $seller, $product] = $this->product();
        $order = app(ProductOrderService::class)->createOrder($guest, [['product_id' => $product->id, 'quantity' => 2]]);
        app(CashPaymentService::class)->select($order, $guest);
        app(CashPaymentService::class)->collect($order, $seller);
        $this->actingAs($guest)->postJson(route('guest.orders.status', $order), ['status' => 'cancelled'])->assertOk();
        $this->postJson(route('guest.orders.status', $order), ['status' => 'cancelled'])->assertStatus(422);
        $this->assertSame(10, (int) $product->fresh()->stock_qty);
        $this->assertSame(1, $order->payments()->where('status', 'paid')->count());
        $this->actingAs($seller)->postJson(route('seller.orders.cash-refunded', $order), ['confirmed' => true])->assertOk();
        $this->assertSame(1, $order->payments()->where('status', 'refunded')->count());
        $this->actingAs($guest)->postJson(route('guest.order-payments.cash', $order))->assertStatus(422);
    }

    public function test_product_points_are_awarded_once_at_delivery(): void
    {
        [$guest, $seller, $product] = $this->product();
        $service = app(ProductOrderService::class);
        $order = $service->createOrder($guest, [['product_id' => $product->id, 'quantity' => 2]]);
        app(CashPaymentService::class)->select($order, $guest);
        app(CashPaymentService::class)->collect($order, $seller);
        $this->assertSame(0, LoyaltyTransaction::where('order_id', $order->id)->count());
        $service->transition($order, $seller, 'packing');
        $service->transition($order, $seller, 'delivered');
        $this->assertSame(20, (int) LoyaltyTransaction::where('order_id', $order->id)->sum('points_delta'));
        $this->actingAs($seller)->postJson(route('seller.orders.status', $order), ['status' => 'delivered'])->assertStatus(422);
    }

    public function test_experience_history_cancel_and_refund_return_capacity_once(): void
    {
        [$guest, $room, $host] = $this->fixture();
        $experience = Experience::create(['homestay_id' => $room->homestay_id, 'title' => 'Culture test', 'start_at' => '2026-12-12 09:00:00', 'duration_minutes' => 60, 'capacity' => 2, 'booked_count' => 0, 'price' => 50000, 'status' => 'open']);
        $booking = app(ExperienceBookingService::class)->createBooking($guest, $experience, 2);
        app(CashPaymentService::class)->select($booking, $guest);
        app(CashPaymentService::class)->collect($booking, $host);
        $this->actingAs($guest)->get(route('guest.experience-bookings.index'))->assertOk()->assertSee('Culture test');
        $this->actingAs($host)->get(route('host.experience-bookings.index'))->assertOk()->assertSee($guest->full_name);
        $this->actingAs($this->user('guest'))->postJson(route('guest.experience-bookings.cancel', $booking))->assertForbidden();
        $this->actingAs($guest)->postJson(route('guest.experience-bookings.cancel', $booking))->assertOk();
        $this->postJson(route('guest.experience-bookings.cancel', $booking))->assertOk();
        $this->assertSame(0, (int) $experience->fresh()->booked_count);
        $this->assertSame('open', $experience->fresh()->status);
        $this->assertSame(1, $booking->payments()->where('status', 'paid')->count());
        $this->actingAs($host)->postJson(route('host.experience-bookings.cash-refunded', $booking), ['confirmed' => true])->assertOk();
        $this->assertSame(1, $booking->payments()->where('status', 'refunded')->count());
        $this->actingAs($guest)->postJson(route('guest.experience-payments.cash', $booking))->assertStatus(422);
    }

    public function test_csv_report_is_admin_only_filters_dates_and_escapes_formulas(): void
    {
        [$guest, $room] = $this->fixture();
        $room->update(['name' => '=HYPERLINK("bad")']);
        $booking = $this->hold($guest, $room);
        $this->actingAs($guest)->get(route('admin.reports.bookings'))->assertForbidden();
        $admin = $this->user('admin');
        $response = $this->actingAs($admin)->get(route('admin.reports.bookings', ['from' => '2026-12-10', 'to' => '2026-12-10']))->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString($booking->code, $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('data-analytics-chart="monthly"', false)->assertSee('data-analytics-chart="top"', false)->assertSee('js/admin-charts.js');
    }

    public function test_experience_completion_is_owner_scoped_and_requires_end_time(): void
    {
        [$guest, $room, $host] = $this->fixture();
        $experience = Experience::create(['homestay_id' => $room->homestay_id, 'title' => 'Finish culture', 'start_at' => '2026-12-12 09:00:00', 'duration_minutes' => 60, 'capacity' => 2, 'booked_count' => 0, 'price' => 50000, 'status' => 'open']);
        $booking = app(ExperienceBookingService::class)->createBooking($guest, $experience, 1);
        app(CashPaymentService::class)->select($booking, $guest);
        app(CashPaymentService::class)->collect($booking, $host);
        $this->actingAs($this->user('host'))->postJson(route('host.experience-bookings.complete', $booking))->assertForbidden();
        $this->actingAs($host)->postJson(route('host.experience-bookings.complete', $booking))->assertStatus(422);
        Carbon::setTestNow('2026-12-12 10:01:00');
        $this->postJson(route('host.experience-bookings.complete', $booking))->assertOk()->assertJsonPath('data.status', 'completed');
    }

    private function user(string $role): User
    {
        return User::create(['email' => uniqid('flows-').'@example.test', 'password_hash' => Hash::make('Dt07@2026!'), 'full_name' => 'Flows '.$role, 'role' => $role, 'status' => 'active']);
    }

    private function fixture(): array
    {
        $guest = $this->user('guest');
        $host = $this->user('host');
        $homestay = Homestay::create(['owner_id' => $host->id, 'name' => 'Flows Homestay', 'slug' => uniqid('flows-'), 'province' => uniqid('TestProvince-'), 'address_line' => '1 Demo', 'status' => 'approved']);
        $room = Room::create(['homestay_id' => $homestay->id, 'name' => 'Flows Room', 'room_type' => 'private_room', 'max_guests' => 2, 'bed_count' => 1, 'base_price' => 100000, 'quantity' => 1, 'active' => true]);
        RoomAvailability::create(['room_id' => $room->id, 'stay_date' => '2026-12-10', 'units_total' => 1, 'units_held' => 0, 'units_sold' => 0, 'status' => 'open']);

        return [$guest, $room->fresh('homestay'), $host];
    }

    private function hold($guest, $room): Booking
    {
        return app(BookingService::class)->createBooking($guest, $room, '2026-12-10', '2026-12-11', 1);
    }

    private function pay($guest, $booking): void
    {
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertCreated();
        $host = $booking->room->homestay->owner;
        $this->actingAs($host)->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => true])->assertOk();
        $this->actingAs($guest);
    }

    private function product(): array
    {
        $guest = $this->user('guest');
        $seller = $this->user('seller');
        $product = LocalProduct::create(['seller_id' => $seller->id, 'name' => 'Flow product', 'origin_place' => 'Da Lat', 'unit' => 'goi', 'price' => 100000, 'stock_qty' => 10, 'status' => 'published']);

        return [$guest, $seller, $product];
    }

    private function vnPayParams($guest, $booking): array
    {
        config(['services.vnpay.enabled' => true, 'services.vnpay.tmn_code' => 'TEST', 'services.vnpay.secret_key' => 'SECRET', 'services.vnpay.return_url' => 'http://localhost/api/v1/payment/vn-pay-callback']);
        app(VnPayPaymentService::class)->createPaymentUrl($booking, $guest, '127.0.0.1');
        $params = ['vnp_TxnRef' => $booking->payments()->latest('id')->value('txn_ref'), 'vnp_Amount' => '10000000', 'vnp_ResponseCode' => '00', 'vnp_TransactionStatus' => '00'];
        ksort($params);
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = urlencode($key).'='.urlencode($value);
        }
        $params['vnp_SecureHash'] = hash_hmac('sha512', implode('&', $parts), 'SECRET');

        return $params;
    }
}
