<?php
namespace Tests\Feature;

use App\Exceptions\AvailabilityConflictException;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Homestay;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\SeasonalPrice;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class M2BookingFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_can_search_an_available_room_for_valid_date_range(): void
    {
        $room = $this->makeRoomWithAvailability();

        $this->getJson(route('search.results', [
            'location' => $room->homestay->province,
            'checkin' => '2026-12-10',
            'checkout' => '2026-12-12',
            'guests' => 2,
        ]))->assertOk()
            ->assertJsonFragment(['name' => $room->name]);
    }

    public function test_capacity_and_date_validation_reject_invalid_requests(): void
    {
        $room = $this->makeRoomWithAvailability(['max_guests' => 2]);

        $this->getJson(route('search.results', [
            'checkin' => '2026-12-12',
            'checkout' => '2026-12-10',
            'guests' => 2,
        ]))->assertUnprocessable();

        $guest = $this->makeUser('m2-validation-guest@dt07.test', 'guest');
        $this->actingAs($guest)->postJson(route('bookings.store'), [
            'room_id' => $room->id,
            'checkin' => '2026-12-10',
            'checkout' => '2026-12-11',
            'guests' => 3,
        ])->assertUnprocessable();
    }

    public function test_price_calculation_uses_override_seasonal_and_base_fallback(): void
    {
        $room = $this->makeRoomWithAvailability(['base_price' => 100000], [
            ['stay_date' => '2026-12-10', 'price_override' => 150000],
            ['stay_date' => '2026-12-11', 'price_override' => null],
            ['stay_date' => '2026-12-12', 'price_override' => null],
        ]);
        SeasonalPrice::create([
            'room_id' => $room->id,
            'name' => 'M2 Peak',
            'start_date' => '2026-12-11',
            'end_date' => '2026-12-11',
            'price_per_night' => 120000,
            'priority' => 10,
        ]);

        $quote = app(BookingService::class)->quote($room, '2026-12-10', '2026-12-13', 2);

        $this->assertSame([150000.0, 120000.0, 100000.0], array_column($quote['nightly'], 'price'));
        $this->assertSame(370000.0, $quote['total_amount']);
    }

    public function test_authenticated_guest_can_create_valid_booking(): void
    {
        $guest = $this->makeUser('m2-booking-guest@dt07.test', 'guest');
        $room = $this->makeRoomWithAvailability();

        $this->actingAs($guest)->postJson(route('bookings.store'), [
            'room_id' => $room->id,
            'checkin' => '2026-12-10',
            'checkout' => '2026-12-11',
            'guests' => 2,
        ])->assertCreated()
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('bookings', [
            'guest_id' => $guest->id,
            'room_id' => $room->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_booking_updates_expected_availability_counters(): void
    {
        $guest = $this->makeUser('m2-counter-guest@dt07.test', 'guest');
        $room = $this->makeRoomWithAvailability();

        app(BookingService::class)->createBooking($guest, $room, '2026-12-10', '2026-12-12', 2);

        $this->assertSame(1, RoomAvailability::where('room_id', $room->id)->where('stay_date', '2026-12-10')->value('units_sold'));
        $this->assertSame(1, RoomAvailability::where('room_id', $room->id)->where('stay_date', '2026-12-11')->value('units_sold'));
    }

    public function test_insufficient_availability_is_rejected(): void
    {
        $guest = $this->makeUser('m2-conflict-guest@dt07.test', 'guest');
        $room = $this->makeRoomWithAvailability([], [
            ['stay_date' => '2026-12-10', 'units_total' => 1, 'units_sold' => 1],
        ]);

        $this->actingAs($guest)->postJson(route('bookings.store'), [
            'room_id' => $room->id,
            'checkin' => '2026-12-10',
            'checkout' => '2026-12-11',
            'guests' => 2,
        ])->assertStatus(409);
    }

    public function test_host_cannot_mutate_another_hosts_room(): void
    {
        $hostA = $this->makeUser('m2-host-a@dt07.test', 'host');
        $hostB = $this->makeUser('m2-host-b@dt07.test', 'host');
        $room = $this->makeRoomWithAvailability([], [], $hostB);

        $this->actingAs($hostA)->put(route('host.rooms.update', $room), [
            'homestay_id' => $room->homestay_id,
            'name' => 'Illegal Update',
            'room_type' => $room->room_type,
            'max_guests' => $room->max_guests,
            'bed_count' => $room->bed_count,
            'base_price' => $room->base_price,
            'quantity' => $room->quantity,
            'active' => 1,
        ])->assertForbidden();
    }

    public function test_last_unit_conflict_does_not_create_two_successful_bookings(): void
    {
        $guestA = $this->makeUser('m2-last-a@dt07.test', 'guest');
        $guestB = $this->makeUser('m2-last-b@dt07.test', 'guest');
        $room = $this->makeRoomWithAvailability([], [
            ['stay_date' => '2026-12-10', 'units_total' => 1],
        ]);

        app(BookingService::class)->createBooking($guestA, $room, '2026-12-10', '2026-12-11', 2);

        $this->expectException(AvailabilityConflictException::class);
        try {
            app(BookingService::class)->createBooking($guestB, $room, '2026-12-10', '2026-12-11', 2);
        } finally {
            $this->assertSame(1, Booking::where('room_id', $room->id)->where('checkin_date', '2026-12-10')->count());
        }
    }

    public function test_booking_status_log_is_written(): void
    {
        $guest = $this->makeUser('m2-log-guest@dt07.test', 'guest');
        $room = $this->makeRoomWithAvailability();

        $booking = app(BookingService::class)->createBooking($guest, $room, '2026-12-10', '2026-12-11', 2);

        $this->assertSame(1, BookingStatusLog::where('booking_id', $booking->id)->where('to_status', 'confirmed')->count());
    }

    private function makeUser(string $email, string $role): User
    {
        return User::create([
            'email' => $email,
            'password_hash' => Hash::make('Dt07@2026!'),
            'role' => $role,
            'full_name' => ucfirst($role) . ' M2',
            'status' => 'active',
        ]);
    }

    private function makeRoomWithAvailability(array $roomOverrides = [], array $availabilityOverrides = [], ?User $host = null): Room
    {
        $host ??= $this->makeUser('m2-host-' . uniqid() . '@dt07.test', 'host');
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'M2 Homestay ' . uniqid(),
            'slug' => 'm2-homestay-' . uniqid(),
            'province' => 'Lam Dong',
            'district' => 'Da Lat',
            'address_line' => '1 M2 Street',
            'description' => 'M2 test homestay.',
            'checkin_time' => '14:00:00',
            'checkout_time' => '12:00:00',
            'status' => 'approved',
            'avg_rating' => 0,
        ]);

        $room = Room::create(array_merge([
            'homestay_id' => $homestay->id,
            'name' => 'M2 Room ' . uniqid(),
            'room_type' => 'private_room',
            'max_guests' => 4,
            'bed_count' => 2,
            'base_price' => 100000,
            'quantity' => 2,
            'active' => true,
        ], $roomOverrides));

        $defaults = [
            ['stay_date' => '2026-12-10'],
            ['stay_date' => '2026-12-11'],
            ['stay_date' => '2026-12-12'],
        ];
        $rows = $availabilityOverrides ?: $defaults;

        foreach ($rows as $row) {
            RoomAvailability::create(array_merge([
                'room_id' => $room->id,
                'stay_date' => '2026-12-10',
                'units_total' => 2,
                'units_held' => 0,
                'units_sold' => 0,
                'price_override' => null,
                'status' => 'open',
            ], $row));
        }

        return $room->fresh('homestay');
    }
}
