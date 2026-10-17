<?php

namespace App\Services;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoomSearchService
{
    public function search(array $filters)
    {
        app(BookingLifecycleService::class)->expireDue();
        $nights = Carbon::parse($filters['checkin'])->diffInDays(Carbon::parse($filters['checkout']));
        if ($nights > 90) {
            throw \Illuminate\Validation\ValidationException::withMessages(['checkout' => 'Tìm kiếm tối đa 90 đêm.']);
        }
        $price = DB::table('room_availability as a')
            ->selectRaw('AVG(COALESCE(a.price_override, (SELECT sp.price_per_night FROM seasonal_prices sp WHERE sp.room_id = rooms.id AND sp.start_date <= a.stay_date AND sp.end_date >= a.stay_date ORDER BY sp.priority DESC, sp.id DESC LIMIT 1), rooms.base_price))')
            ->whereColumn('a.room_id', 'rooms.id')
            ->where('a.stay_date', '>=', $filters['checkin'])->where('a.stay_date', '<', $filters['checkout']);
        $query = Room::with('homestay')->select('rooms.*')->selectSub($price, 'stay_price')
            ->where('active', true)->where('max_guests', '>=', $filters['guests'])
            ->whereHas('homestay', function ($q) use ($filters) {
                $q->where('status', 'approved');
                if (! empty($filters['location'])) {
                    $q->where(fn ($q) => $q->where('province', 'like', '%'.$filters['location'].'%')
                        ->orWhere('district', 'like', '%'.$filters['location'].'%'));
                }
            })
            ->whereHas('availability', fn ($q) => $q->where('stay_date', '>=', $filters['checkin'])
                ->where('stay_date', '<', $filters['checkout'])->where('status', 'open')
                ->whereRaw('units_total > units_held + units_sold'), '=', $nights);
        foreach (['min_price' => '>=', 'max_price' => '<='] as $filter => $operator) {
            if (isset($filters[$filter])) {
                $query->having('stay_price', $operator, $filters[$filter]);
            }
        }
        if (! empty($filters['room_type'])) {
            $query->where('room_type', $filters['room_type']);
        }
        $sort = $filters['sort'] ?? 'price_asc';
        $query->orderBy($sort === 'newest' ? 'rooms.id' : 'stay_price', $sort === 'price_asc' ? 'asc' : 'desc')->orderBy('rooms.id');
        $rooms = $query->paginate(12)->withQueryString();
        foreach ($rooms as $room) {
            $room->quote = app(BookingService::class)->quote($room, $filters['checkin'], $filters['checkout'], (int) $filters['guests']);
        }

        return $rooms;
    }
}
