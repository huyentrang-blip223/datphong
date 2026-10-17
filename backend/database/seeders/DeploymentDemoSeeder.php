<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Room;
use App\Models\RoomAvailability;
use Illuminate\Database\Seeder;

class DeploymentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(M2TourismDatasetSeeder::class);
        Room::where('name', 'like', 'M2 Room %')->chunkById(100, function ($rooms) {
            foreach ($rooms as $room) {
                for ($day = 0; $day < 90; $day++) {
                    RoomAvailability::firstOrCreate(['room_id' => $room->id, 'stay_date' => today()->addDays($day)->toDateString()],
                        ['units_total' => $room->quantity, 'units_held' => 0, 'units_sold' => 0, 'status' => 'open']);
                }
            }
        });
        Experience::where('title', 'like', 'M2 Cultural Experience %')->get()->each(function ($experience) {
            $experience->update(['start_at' => now()->addDays(($experience->id % 24) + 2)->setTime(9, 0)]);
        });
    }
}
