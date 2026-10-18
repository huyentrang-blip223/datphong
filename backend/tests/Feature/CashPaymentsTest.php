<?php

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\Homestay;
use App\Models\LocalProduct;
use App\Models\LoyaltyTransaction;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\User;
use App\Services\BookingService;
use App\Services\CashPaymentService;
use App\Services\ExperienceBookingService;
use App\Services\ProductOrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CashPaymentsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-18 09:00:00');
        config(['mail.notifications_enabled' => false, 'services.vnpay.enabled' => false]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_checkout_offers_cash_and_unavailable_vnpay_without_simulation_controls(): void
    {
        [$guest, $host, $booking] = $this->booking();
        $this->actingAs($guest)->get(route('guest.payments.show', $booking))->assertOk()
            ->assertSee('Thanh toán tiền mặt')->assertSee('Thanh toán VNPay')->assertSee('VNPay hiện chưa khả dụng.')
            ->assertDontSee('mô phỏng')->assertDontSee('payments/mock')->assertDontSee('name="result"', false)
            ->assertDontSee('payments.cash_booking');
        $this->postJson(route('guest.payments.vnpay', $booking))->assertStatus(422);
        $this->assertSame(0, $booking->payments()->count());
    }

    public function test_all_removed_mock_payment_urls_return_not_found(): void
    {
        [$guest, $host, $booking] = $this->booking();
        foreach (['bookings', 'orders', 'experience-bookings'] as $type) {
            $this->actingAs($guest)->postJson('/tai-khoan/'.$type.'/'.$booking->id.'/payments/mock', ['result' => 'success'])->assertNotFound();
        }
        $this->assertSame(0, $booking->payments()->count());
    }

    public function test_cash_reservation_survives_hold_expiry_without_marking_paid_or_awarding_points(): void
    {
        [$guest, $host, $booking, $room] = $this->booking();
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertCreated()->assertJsonPath('data.status', 'initiated');
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertNull($booking->fresh()->hold_expires_at);
        Carbon::setTestNow(now()->addMinutes(16));
        $this->artisan('bookings:expire')->assertSuccessful();
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame(1, (int) $room->availability()->first()->units_sold);
        $this->assertSame(0, (int) $room->availability()->first()->units_held);
        $this->assertNull($booking->payments()->first()->paid_at);
        $this->assertSame(0, LoyaltyTransaction::where('booking_id', $booking->id)->count());
        config(['services.vnpay.enabled' => true]);
        $this->postJson(route('guest.payments.vnpay', $booking))->assertStatus(422);
        $this->assertSame(1, $booking->payments()->count());
    }

    public function test_only_owning_host_can_confirm_cash_once_after_explicit_receipt_confirmation(): void
    {
        [$guest, $host, $booking] = $this->booking();
        $this->actingAs($this->user('guest'))->postJson(route('guest.payments.cash', $booking))->assertForbidden();
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking), ['status' => 'paid', 'amount' => 1])->assertStatus(422);
        $this->postJson(route('guest.payments.cash', $booking))->assertCreated();
        $this->actingAs($host)->get(route('host.bookings.index'))->assertOk()
            ->assertSee('Đã thực nhận đủ tiền mặt')->assertSee(route('host.bookings.cash-received', $booking), false);
        $this->actingAs($guest);
        $this->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => true])->assertForbidden();
        $this->actingAs($this->user('host'))->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => true])->assertForbidden();
        $this->actingAs($host)->postJson(route('host.bookings.cash-received', $booking))->assertStatus(422);
        $this->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => false])->assertStatus(422);
        $this->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => true])->assertOk()->assertJsonPath('data.status', 'paid');
        $this->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => true])->assertStatus(422);
        $this->assertSame(1, LoyaltyTransaction::where('booking_id', $booking->id)->count());
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $host->id, 'action' => 'cash_payment_paid', 'entity' => 'payments']);
    }

    public function test_cancelled_cash_reservation_cannot_be_collected_and_releases_inventory_once(): void
    {
        [$guest, $host, $booking, $room] = $this->booking();
        $this->actingAs($guest)->postJson(route('guest.payments.cash', $booking))->assertCreated();
        $this->postJson(route('guest.bookings.cancel', $booking))->assertOk();
        $this->postJson(route('guest.bookings.cancel', $booking))->assertOk();
        $this->actingAs($host)->postJson(route('host.bookings.cash-received', $booking), ['confirmed' => true])->assertStatus(422);
        $this->assertSame(0, (int) $room->availability()->first()->units_sold);
        $this->assertSame(0, $booking->payments()->where('status', 'paid')->count());
    }

    public function test_cash_refund_requires_owning_host_and_actual_return_confirmation(): void
    {
        [$guest, $host, $booking] = $this->booking();
        $cash = app(CashPaymentService::class);
        $cash->select($booking, $guest);
        $cash->collect($booking, $host);
        $this->actingAs($host)->postJson(route('host.bookings.cash-refunded', $booking), ['confirmed' => true])->assertStatus(422);
        $this->actingAs($guest)->postJson(route('guest.bookings.cancel', $booking))->assertOk();
        $this->actingAs($host)->get(route('host.bookings.index'))->assertOk()
            ->assertSee('Xác nhận đã hoàn tiền')->assertSee(route('host.bookings.cash-refunded', $booking), false);
        $this->actingAs($guest);
        $this->postJson(route('guest.bookings.refund', $booking))->assertStatus(422);
        $this->assertSame('paid', $booking->payments()->first()->status);
        $this->actingAs($this->user('host'))->postJson(route('host.bookings.cash-refunded', $booking), ['confirmed' => true])->assertForbidden();
        $this->actingAs($host)->postJson(route('host.bookings.cash-refunded', $booking))->assertStatus(422);
        $this->postJson(route('host.bookings.cash-refunded', $booking), ['confirmed' => true])->assertOk();
        $this->postJson(route('host.bookings.cash-refunded', $booking), ['confirmed' => true])->assertOk();
        $this->assertSame('refunded', $booking->fresh()->status);
        $this->assertSame(0, (int) LoyaltyTransaction::where('booking_id', $booking->id)->sum('points_delta'));
    }

    public function test_cash_on_delivery_can_ship_unpaid_and_awards_points_only_after_collection(): void
    {
        $guest = $this->user('guest');
        $seller = $this->user('seller');
        $product = LocalProduct::create(['seller_id' => $seller->id, 'name' => 'Cash product', 'origin_place' => 'Hue', 'unit' => 'goi', 'price' => 100000, 'stock_qty' => 5, 'status' => 'published']);
        $orders = app(ProductOrderService::class);
        $order = $orders->createOrder($guest, [['product_id' => $product->id, 'quantity' => 1]]);
        $this->actingAs($guest)->postJson(route('guest.order-payments.cash', $order))->assertCreated()->assertJsonPath('data.status', 'initiated');
        $this->actingAs($this->user('seller'))->postJson(route('seller.orders.cash-received', $order), ['confirmed' => true])->assertForbidden();
        $orders->transition($order, $seller, 'packing');
        $orders->transition($order, $seller, 'delivered');
        $this->actingAs($guest)->get(route('guest.order-payments.show', $order))->assertOk()->assertSee('chờ thanh toán trực tiếp');
        $this->assertSame(0, LoyaltyTransaction::where('order_id', $order->id)->count());
        $this->actingAs($seller)->postJson(route('seller.orders.cash-received', $order), ['confirmed' => true])->assertOk();
        $this->postJson(route('seller.orders.cash-received', $order), ['confirmed' => true])->assertStatus(422);
        $this->assertSame(10, (int) LoyaltyTransaction::where('order_id', $order->id)->sum('points_delta'));
    }

    public function test_experience_cash_is_collected_at_start_and_other_host_is_denied(): void
    {
        [$guest, $host, $roomBooking, $room] = $this->booking();
        $experience = Experience::create(['homestay_id' => $room->homestay_id, 'title' => 'Cash experience', 'start_at' => now()->addDays(3), 'duration_minutes' => 60, 'capacity' => 5, 'booked_count' => 0, 'price' => 50000, 'status' => 'open']);
        $booking = app(ExperienceBookingService::class)->createBooking($guest, $experience, 1);
        $this->actingAs($guest)->postJson(route('guest.experience-payments.cash', $booking))->assertCreated();
        Carbon::setTestNow($experience->start_at);
        $this->actingAs($this->user('host'))->postJson(route('host.experience-bookings.cash-received', $booking), ['confirmed' => true])->assertForbidden();
        $this->actingAs($host)->postJson(route('host.experience-bookings.cash-received', $booking), ['confirmed' => true])->assertOk();
        $this->assertSame('paid', $booking->payments()->first()->status);
    }

    private function user(string $role): User
    {
        return User::create(['email' => uniqid('cash-').'@example.test', 'password_hash' => Hash::make('Dt07@2026!'), 'full_name' => 'Cash '.$role, 'role' => $role, 'status' => 'active']);
    }

    private function booking(): array
    {
        $guest = $this->user('guest');
        $host = $this->user('host');
        $home = Homestay::create(['owner_id' => $host->id, 'name' => 'Cash homestay', 'slug' => uniqid('cash-'), 'province' => 'Hue', 'address_line' => '1 Cash', 'status' => 'approved']);
        $room = Room::create(['homestay_id' => $home->id, 'name' => 'Cash room', 'room_type' => 'private_room', 'max_guests' => 2, 'bed_count' => 1, 'base_price' => 100000, 'quantity' => 1, 'active' => true]);
        RoomAvailability::create(['room_id' => $room->id, 'stay_date' => now()->addDays(3)->toDateString(), 'units_total' => 1, 'units_held' => 0, 'units_sold' => 0, 'status' => 'open']);
        $booking = app(BookingService::class)->createBooking($guest, $room, now()->addDays(3)->toDateString(), now()->addDays(4)->toDateString(), 1);

        return [$guest, $host, $booking, $room];
    }
}
