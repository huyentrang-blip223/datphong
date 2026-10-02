<?php
namespace Database\Seeders;

use App\Models\Homestay;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\SeasonalPrice;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class M2TourismDatasetSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $hosts = $this->seedUsers('host', 6);
            $guests = $this->seedUsers('guest', 20);
            $sellers = $this->seedUsers('seller', 4);

            $homestays = $this->seedHomestays($hosts);
            $rooms = $this->seedRooms($homestays);
            $this->seedAvailabilityAndPrices($rooms);
            $productIds = $this->seedLocalProducts($sellers, $homestays);
            $this->seedProductOrders($productIds, $guests, $homestays);
            $this->seedExperiences($homestays, $guests);
            $this->seedBookingsAndReviews($rooms, $guests, $hosts);
        });
    }

    private function seedUsers(string $role, int $count): array
    {
        $users = [];

        for ($i = 1; $i <= $count; $i++) {
            $users[] = User::updateOrCreate(
                ['email' => sprintf('m2-%s-%02d@dt07.test', $role, $i)],
                [
                    'password_hash' => Hash::make('Dt07@2026!'),
                    'role' => $role,
                    'full_name' => sprintf('M2 %s %02d', ucfirst($role), $i),
                    'phone' => sprintf('090%07d', $i),
                    'status' => 'active',
                ]
            );
        }

        return $users;
    }

    private function seedHomestays(array $hosts): array
    {
        $provinces = [
            ['Lam Dong', 'Da Lat'],
            ['Lao Cai', 'Sa Pa'],
            ['Quang Nam', 'Hoi An'],
            ['Ninh Binh', 'Hoa Lu'],
            ['Ha Giang', 'Dong Van'],
            ['Thua Thien Hue', 'Hue'],
        ];

        $homestays = [];
        for ($i = 1; $i <= 12; $i++) {
            [$province, $district] = $provinces[($i - 1) % count($provinces)];
            $host = $hosts[($i - 1) % count($hosts)];

            $homestays[] = Homestay::updateOrCreate(
                ['slug' => sprintf('m2-community-homestay-%02d', $i)],
                [
                    'owner_id' => $host->id,
                    'name' => sprintf('M2 Community Homestay %02d', $i),
                    'province' => $province,
                    'district' => $district,
                    'address_line' => sprintf('%d Duong Ban Dia Phuong', $i),
                    'lat' => 10 + $i / 100,
                    'lng' => 106 + $i / 100,
                    'description' => 'Du lieu demo M2 cho san luu tru cong dong gan voi san pham dia phuong.',
                    'checkin_time' => '14:00:00',
                    'checkout_time' => '12:00:00',
                    'status' => 'approved',
                    'avg_rating' => 4.20,
                ]
            );
        }

        return $homestays;
    }

    private function seedRooms(array $homestays): array
    {
        $roomTypes = ['private_room', 'family_room', 'dorm'];
        $rooms = [];

        foreach ($homestays as $homestayIndex => $homestay) {
            for ($i = 1; $i <= 3; $i++) {
                $roomName = sprintf('M2 Room %02d-%02d', $homestayIndex + 1, $i);
                $room = Room::where('homestay_id', $homestay->id)->where('name', $roomName)->first();

                $payload = [
                    'homestay_id' => $homestay->id,
                    'name' => $roomName,
                    'room_type' => $roomTypes[$i - 1],
                    'max_guests' => $i === 3 ? 6 : $i + 1,
                    'bed_count' => $i,
                    'base_price' => 180000 + (($homestayIndex + $i) * 25000),
                    'quantity' => $i === 3 ? 4 : 2,
                    'active' => true,
                ];

                if ($room) {
                    $room->update($payload);
                } else {
                    $room = Room::create($payload);
                }

                $rooms[] = $room->fresh();
            }
        }

        return $rooms;
    }

    private function seedAvailabilityAndPrices(array $rooms): void
    {
        $startDate = CarbonImmutable::create(2026, 10, 10);

        foreach ($rooms as $roomIndex => $room) {
            SeasonalPrice::updateOrCreate(
                [
                    'room_id' => $room->id,
                    'name' => 'M2 Weekend Local Festival',
                    'start_date' => '2026-10-14',
                    'end_date' => '2026-10-16',
                ],
                [
                    'price_per_night' => (float) $room->base_price + 50000,
                    'priority' => 10,
                ]
            );

            for ($day = 0; $day < 10; $day++) {
                $date = $startDate->addDays($day)->toDateString();
                RoomAvailability::updateOrCreate(
                    ['room_id' => $room->id, 'stay_date' => $date],
                    [
                        'units_total' => $room->quantity,
                        'units_held' => 0,
                        'units_sold' => $day < 2 && $roomIndex < 12 ? 1 : 0,
                        'price_override' => $day === 6 ? (float) $room->base_price + 80000 : null,
                        'status' => 'open',
                    ]
                );
            }
        }
    }

    private function seedLocalProducts(array $sellers, array $homestays): array
    {
        $productIds = [];

        for ($i = 1; $i <= 24; $i++) {
            $seller = $sellers[($i - 1) % count($sellers)];
            $product = DB::table('local_products')
                ->where('seller_id', $seller->id)
                ->where('name', sprintf('M2 Local Product %02d', $i))
                ->first();

            $payload = [
                'seller_id' => $seller->id,
                'name' => sprintf('M2 Local Product %02d', $i),
                'origin_place' => $homestays[($i - 1) % count($homestays)]->province,
                'description' => 'Dac san dia phuong demo cho M2.',
                'unit' => 'goi',
                'price' => 45000 + ($i * 3000),
                'stock_qty' => 100 + $i,
                'status' => 'published',
            ];

            if ($product) {
                DB::table('local_products')->where('id', $product->id)->update($payload);
                $productId = $product->id;
            } else {
                $productId = DB::table('local_products')->insertGetId($payload + ['created_at' => now()]);
            }

            DB::table('homestay_products')->updateOrInsert(
                [
                    'homestay_id' => $homestays[($i - 1) % count($homestays)]->id,
                    'product_id' => $productId,
                ],
                [
                    'featured' => $i % 3 === 0,
                    'display_order' => $i,
                ]
            );

            $productIds[] = $productId;
        }

        return $productIds;
    }

    private function seedProductOrders(array $productIds, array $guests, array $homestays): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $firstProduct = DB::table('local_products')->where('id', $productIds[($i - 1) % count($productIds)])->first();
            $secondProduct = DB::table('local_products')->where('id', $productIds[$i % count($productIds)])->first();
            $firstQty = 1 + ($i % 2);
            $secondQty = 1;
            $subtotal = ($firstProduct->price * $firstQty) + ($secondProduct->price * $secondQty);
            $code = sprintf('M2PO%08d', $i);

            DB::table('product_orders')->updateOrInsert(
                ['code' => $code],
                [
                    'buyer_id' => $guests[($i - 1) % count($guests)]->id,
                    'homestay_id' => $homestays[($i - 1) % count($homestays)]->id,
                    'status' => 'confirmed',
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal,
                    'created_at' => now(),
                ]
            );

            $order = DB::table('product_orders')->where('code', $code)->first();
            DB::table('product_order_items')->updateOrInsert(
                ['order_id' => $order->id, 'product_id' => $firstProduct->id],
                [
                    'quantity' => $firstQty,
                    'unit_price' => $firstProduct->price,
                    'line_total' => $firstProduct->price * $firstQty,
                ]
            );
            DB::table('product_order_items')->updateOrInsert(
                ['order_id' => $order->id, 'product_id' => $secondProduct->id],
                [
                    'quantity' => $secondQty,
                    'unit_price' => $secondProduct->price,
                    'line_total' => $secondProduct->price * $secondQty,
                ]
            );
        }
    }

    private function seedExperiences(array $homestays, array $guests): void
    {
        for ($i = 1; $i <= 24; $i++) {
            $homestay = $homestays[($i - 1) % count($homestays)];
            $title = sprintf('M2 Cultural Experience %02d', $i);
            $startAt = CarbonImmutable::create(2026, 10, 20)->addDays($i)->setTime(9, 0);
            $experience = DB::table('experiences')
                ->where('homestay_id', $homestay->id)
                ->where('title', $title)
                ->first();

            $payload = [
                'homestay_id' => $homestay->id,
                'title' => $title,
                'description' => 'Trai nghiem van hoa ban dia demo cho M2.',
                'start_at' => $startAt->toDateTimeString(),
                'duration_minutes' => 120,
                'capacity' => 12,
                'booked_count' => 1,
                'price' => 120000 + ($i * 5000),
                'status' => 'open',
            ];

            if ($experience) {
                DB::table('experiences')->where('id', $experience->id)->update($payload);
                $experienceId = $experience->id;
            } else {
                $experienceId = DB::table('experiences')->insertGetId($payload);
            }

            DB::table('experience_bookings')->updateOrInsert(
                [
                    'experience_id' => $experienceId,
                    'guest_id' => $guests[($i - 1) % count($guests)]->id,
                ],
                [
                    'people_count' => 1,
                    'total_amount' => $payload['price'],
                    'status' => 'confirmed',
                    'created_at' => now(),
                ]
            );
        }
    }

    private function seedBookingsAndReviews(array $rooms, array $guests, array $hosts): void
    {
        for ($i = 1; $i <= 24; $i++) {
            $room = $rooms[($i - 1) % count($rooms)];
            $guest = $guests[($i - 1) % count($guests)];
            $checkin = CarbonImmutable::create(2026, 10, 10)->addDays(($i - 1) % 4);
            $checkout = $checkin->addDays(2);
            $unitPrice = (float) $room->base_price;
            $code = sprintf('M2BK%08d', $i);

            $booking = DB::table('bookings')->where('code', $code)->first();
            $payload = [
                'code' => $code,
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'checkin_date' => $checkin->toDateString(),
                'checkout_date' => $checkout->toDateString(),
                'guest_count' => min(2, $room->max_guests),
                'unit_price' => $unitPrice,
                'nights' => 2,
                'total_amount' => $unitPrice * 2,
                'status' => 'completed',
                'hold_expires_at' => null,
                'cancel_reason' => null,
            ];

            if ($booking) {
                DB::table('bookings')->where('id', $booking->id)->update($payload);
                $bookingId = $booking->id;
            } else {
                $bookingId = DB::table('bookings')->insertGetId($payload + ['created_at' => now()]);
            }

            DB::table('booking_status_logs')->updateOrInsert(
                ['booking_id' => $bookingId, 'to_status' => 'completed'],
                [
                    'from_status' => 'confirmed',
                    'actor_id' => $guest->id,
                    'reason' => 'm2_seed_completed_booking',
                    'created_at' => now(),
                ]
            );

            if ($i <= 20) {
                DB::table('reviews')->updateOrInsert(
                    ['booking_id' => $bookingId, 'direction' => 'guest_to_host'],
                    [
                        'from_user_id' => $guest->id,
                        'to_user_id' => $hosts[($i - 1) % count($hosts)]->id,
                        'rating' => 4 + ($i % 2),
                        'content' => 'Danh gia demo M2 gan voi booking da hoan thanh.',
                        'visible' => true,
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
