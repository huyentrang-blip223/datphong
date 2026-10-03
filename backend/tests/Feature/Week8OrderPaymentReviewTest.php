<?php
namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Homestay;
use App\Models\LocalProduct;
use App\Models\ProductOrder;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
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

    public function test_mock_payment_success_failure_and_duplicate_guard(): void
    {
        [$guest, $booking] = $this->bookingForGuest('w8-pay-guest@dt07.test');

        $this->actingAs($guest)->postJson(route('guest.payments.mock', $booking), ['result' => 'fail'])->assertOk()
            ->assertJsonPath('data.status', 'failed');
        $this->actingAs($guest)->postJson(route('guest.payments.mock', $booking), ['result' => 'success'])->assertCreated()
            ->assertJsonPath('data.status', 'paid');
        $this->actingAs($guest)->postJson(route('guest.payments.mock', $booking), ['result' => 'success'])->assertStatus(422);
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
