<?php
namespace Tests\Feature;

use App\Models\Homestay;
use App\Models\Experience;
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

    public function test_host_can_manage_owned_experiences(): void
    {
        [$host, $room] = $this->roomForHost('w8-exp-host@dt07.test');
        $homestay = $room->homestay;

        $this->actingAs($host)->post(route('host.experiences.store'), [
            'homestay_id' => $homestay->id,
            'title' => 'Lam banh truyen thong',
            'description' => 'Trai nghiem lam banh cung nguoi dan dia phuong.',
            'start_at' => '2027-03-01 09:00:00',
            'duration_minutes' => 120,
            'capacity' => 12,
            'price' => 150000,
            'status' => 'open',
        ])->assertRedirect(route('host.experiences.index'));

        $experience = Experience::where('title', 'Lam banh truyen thong')->firstOrFail();

        $this->actingAs($host)->put(route('host.experiences.update', $experience), [
            'homestay_id' => $homestay->id,
            'title' => 'Lam banh va thuong tra',
            'description' => 'Cap nhat noi dung trai nghiem.',
            'start_at' => '2027-03-02 09:00:00',
            'duration_minutes' => 150,
            'capacity' => 15,
            'price' => 180000,
            'status' => 'open',
        ])->assertRedirect(route('host.experiences.index'));

        $this->assertDatabaseHas('experiences', [
            'id' => $experience->id,
            'title' => 'Lam banh va thuong tra',
            'capacity' => 15,
            'price' => 180000,
        ]);
    }

    public function test_host_cannot_manage_another_hosts_experience(): void
    {
        $hostA = $this->user('w8-exp-a@dt07.test', 'host');
        [, $roomB] = $this->roomForHost('w8-exp-b@dt07.test');
        $experience = Experience::create([
            'homestay_id' => $roomB->homestay_id,
            'title' => 'Other host experience',
            'description' => 'Private',
            'start_at' => '2027-03-03 09:00:00',
            'duration_minutes' => 120,
            'capacity' => 10,
            'booked_count' => 0,
            'price' => 100000,
            'status' => 'open',
        ]);

        $this->actingAs($hostA)->get(route('host.experiences.edit', $experience))->assertForbidden();
        $this->actingAs($hostA)->put(route('host.experiences.update', $experience), [
            'homestay_id' => $roomB->homestay_id,
            'title' => 'Bad update',
            'start_at' => '2027-03-03 09:00:00',
            'duration_minutes' => 120,
            'capacity' => 10,
            'price' => 100000,
            'status' => 'open',
        ])->assertForbidden();
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
