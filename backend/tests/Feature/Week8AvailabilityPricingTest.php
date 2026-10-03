<?php
namespace Tests\Feature;

use App\Models\Homestay;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\SeasonalPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Week8AvailabilityPricingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_host_can_update_owned_room_availability_range(): void
    {
        [$host, $room] = $this->roomForHost('w8-av-host@dt07.test');

        $this->actingAs($host)->post(route('host.availability.update', $room), [
            'start_date' => '2026-12-20',
            'end_date' => '2026-12-21',
            'units_total' => 3,
            'units_held' => 1,
            'price_override' => 250000,
            'status' => 'open',
        ])->assertRedirect();

        $this->assertDatabaseHas('room_availability', [
            'room_id' => $room->id,
            'stay_date' => '2026-12-20',
            'units_total' => 3,
            'units_held' => 1,
            'price_override' => 250000,
        ]);
        $this->assertSame(2, RoomAvailability::where('room_id', $room->id)->whereBetween('stay_date', ['2026-12-20', '2026-12-21'])->count());
    }

    public function test_host_cannot_update_another_hosts_availability(): void
    {
        $hostA = $this->user('w8-av-a@dt07.test', 'host');
        [, $roomB] = $this->roomForHost('w8-av-b@dt07.test');

        $this->actingAs($hostA)->post(route('host.availability.update', $roomB), [
            'start_date' => '2026-12-20',
            'end_date' => '2026-12-20',
            'units_total' => 2,
            'units_held' => 0,
            'status' => 'open',
        ])->assertForbidden();
    }

    public function test_availability_rejects_total_below_held_plus_sold(): void
    {
        [$host, $room] = $this->roomForHost('w8-av-guard@dt07.test');
        RoomAvailability::create([
            'room_id' => $room->id,
            'stay_date' => '2026-12-22',
            'units_total' => 2,
            'units_held' => 0,
            'units_sold' => 2,
            'status' => 'open',
        ]);

        $this->actingAs($host)->post(route('host.availability.update', $room), [
            'start_date' => '2026-12-22',
            'end_date' => '2026-12-22',
            'units_total' => 1,
            'units_held' => 0,
            'status' => 'open',
        ])->assertSessionHasErrors('availability');
    }

    public function test_host_can_create_seasonal_price_for_owned_room(): void
    {
        [$host, $room] = $this->roomForHost('w8-season-host@dt07.test');

        $this->actingAs($host)->post(route('host.seasonal-prices.store', $room), [
            'name' => 'Tet 2027',
            'start_date' => '2027-02-01',
            'end_date' => '2027-02-05',
            'price_per_night' => 350000,
            'priority' => 20,
        ])->assertRedirect();

        $this->assertDatabaseHas('seasonal_prices', [
            'room_id' => $room->id,
            'name' => 'Tet 2027',
            'price_per_night' => 350000,
            'priority' => 20,
        ]);
    }

    public function test_seasonal_price_rejects_invalid_date_range(): void
    {
        [$host, $room] = $this->roomForHost('w8-season-invalid@dt07.test');

        $this->actingAs($host)->post(route('host.seasonal-prices.store', $room), [
            'name' => 'Bad',
            'start_date' => '2027-02-05',
            'end_date' => '2027-02-01',
            'price_per_night' => 350000,
            'priority' => 20,
        ])->assertSessionHasErrors('end_date');
    }

    private function roomForHost(string $email): array
    {
        $host = $this->user($email, 'host');
        $homestay = Homestay::create([
            'owner_id' => $host->id,
            'name' => 'W8 Homestay ' . uniqid(),
            'slug' => 'w8-homestay-' . uniqid(),
            'province' => 'Lam Dong',
            'district' => 'Da Lat',
            'address_line' => '1 Week8',
            'status' => 'approved',
        ]);
        $room = Room::create([
            'homestay_id' => $homestay->id,
            'name' => 'W8 Room ' . uniqid(),
            'room_type' => 'private_room',
            'max_guests' => 4,
            'bed_count' => 2,
            'base_price' => 100000,
            'quantity' => 2,
            'active' => true,
        ]);

        return [$host, $room->fresh('homestay')];
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
