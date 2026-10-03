<?php
namespace App\Services;

use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\SeasonalPrice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AvailabilityService
{
    public function updateRange(Room $room, array $data): void
    {
        $dates = $this->dates($data['start_date'], $data['end_date']);

        DB::transaction(function () use ($room, $data, $dates) {
            $existing = RoomAvailability::where('room_id', $room->id)
                ->whereIn('stay_date', $dates)
                ->orderBy('stay_date')
                ->lockForUpdate()
                ->get()
                ->keyBy(fn($row) => $row->stay_date->toDateString());

            foreach ($dates as $date) {
                $row = $existing->get($date) ?: new RoomAvailability([
                    'room_id' => $room->id,
                    'stay_date' => $date,
                    'units_sold' => 0,
                ]);

                $unitsTotal = (int) $data['units_total'];
                $unitsHeld = (int) $data['units_held'];
                $unitsSold = (int) ($row->units_sold ?? 0);

                if ($unitsTotal < $unitsHeld + $unitsSold) {
                    throw new InvalidArgumentException('units_total không được nhỏ hơn units_held + units_sold.');
                }

                $row->fill([
                    'units_total' => $unitsTotal,
                    'units_held' => $unitsHeld,
                    'price_override' => $data['price_override'] ?? null,
                    'status' => $data['status'],
                ]);
                $row->save();
            }
        });
    }

    public function createSeasonalPrice(Room $room, array $data): SeasonalPrice
    {
        return $room->seasonalPrices()->create($data);
    }

    private function dates(string $start, string $end): array
    {
        $from = CarbonImmutable::parse($start)->startOfDay();
        $to = CarbonImmutable::parse($end)->startOfDay();

        if ($to->lessThan($from)) {
            throw new InvalidArgumentException('Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.');
        }

        $dates = [];
        for ($date = $from; $date->lessThanOrEqualTo($to); $date = $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        return $dates;
    }
}
