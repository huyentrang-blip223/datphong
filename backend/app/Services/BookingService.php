<?php
namespace App\Services;

use App\Exceptions\AvailabilityConflictException;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\SeasonalPrice;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BookingService
{
    public function quote(Room $room, string $checkin, string $checkout, int $guests): array
    {
        $dates = $this->nightDates($checkin, $checkout);

        if ($guests < 1 || $guests > 20 || $guests > (int) $room->max_guests) {
            throw new InvalidArgumentException('Số khách không phù hợp với sức chứa phòng.');
        }

        $availability = RoomAvailability::where('room_id', $room->id)
            ->whereIn('stay_date', $dates)
            ->get()
            ->keyBy(fn($row) => $row->stay_date->toDateString());

        $seasonalPrices = SeasonalPrice::where('room_id', $room->id)
            ->where('start_date', '<=', end($dates))
            ->where('end_date', '>=', reset($dates))
            ->orderByDesc('priority')
            ->get();

        $nights = [];
        $total = 0.0;

        foreach ($dates as $date) {
            $row = $availability->get($date);
            $availableUnits = $row ? (int) $row->units_total - (int) $row->units_held - (int) $row->units_sold : 0;
            $price = $this->nightPrice($room, $row, $seasonalPrices, $date);
            $total += $price;

            $nights[] = [
                'date' => $date,
                'available_units' => $availableUnits,
                'price' => $price,
                'status' => $row?->status,
            ];
        }

        return [
            'nights' => count($dates),
            'nightly' => $nights,
            'total_amount' => round($total, 2),
            'average_unit_price' => round($total / count($dates), 2),
        ];
    }

    public function createBooking(User $guest, Room $room, string $checkin, string $checkout, int $guests): Booking
    {
        if ($guest->role !== 'guest') {
            throw new InvalidArgumentException('Chỉ tài khoản guest được tạo booking.');
        }

        return DB::transaction(function () use ($guest, $room, $checkin, $checkout, $guests) {
            $dates = $this->nightDates($checkin, $checkout);

            if ($guests > (int) $room->max_guests) {
                throw new InvalidArgumentException('Số khách vượt quá sức chứa phòng.');
            }

            $lockedRows = RoomAvailability::where('room_id', $room->id)
                ->whereIn('stay_date', $dates)
                ->orderBy('stay_date')
                ->lockForUpdate()
                ->get()
                ->keyBy(fn($row) => $row->stay_date->toDateString());

            foreach ($dates as $date) {
                $row = $lockedRows->get($date);
                $availableUnits = $row ? (int) $row->units_total - (int) $row->units_held - (int) $row->units_sold : 0;

                if (!$row || $row->status !== 'open' || $availableUnits < 1) {
                    throw new AvailabilityConflictException('Phòng không còn đủ tồn cho ngày đã chọn.');
                }
            }

            $quote = $this->quote($room, $checkin, $checkout, $guests);
            $booking = Booking::create([
                'code' => $this->newBookingCode(),
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'checkin_date' => $checkin,
                'checkout_date' => $checkout,
                'guest_count' => $guests,
                'unit_price' => $quote['average_unit_price'],
                'nights' => $quote['nights'],
                'total_amount' => $quote['total_amount'],
                'status' => 'confirmed',
            ]);

            foreach ($lockedRows as $row) {
                $row->increment('units_sold');
            }

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'from_status' => null,
                'to_status' => 'confirmed',
                'actor_id' => $guest->id,
                'reason' => 'guest_booking_created',
            ]);

            return $booking;
        });
    }

    private function nightPrice(Room $room, ?RoomAvailability $availability, $seasonalPrices, string $date): float
    {
        if ($availability && $availability->price_override !== null) {
            return (float) $availability->price_override;
        }

        $seasonal = $seasonalPrices->first(function (SeasonalPrice $price) use ($date) {
            return $price->start_date->toDateString() <= $date && $price->end_date->toDateString() >= $date;
        });

        return $seasonal ? (float) $seasonal->price_per_night : (float) $room->base_price;
    }

    private function nightDates(string $checkin, string $checkout): array
    {
        $start = CarbonImmutable::parse($checkin)->startOfDay();
        $end = CarbonImmutable::parse($checkout)->startOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            throw new InvalidArgumentException('Ngày check-out phải sau ngày check-in.');
        }

        $dates = [];
        for ($date = $start; $date->lessThan($end); $date = $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        return $dates;
    }

    private function newBookingCode(): string
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (Booking::where('code', $code)->exists());

        return $code;
    }
}
