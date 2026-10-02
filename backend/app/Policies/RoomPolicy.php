<?php
namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        return $this->ownsRoom($user, $room);
    }

    public function update(User $user, Room $room): bool
    {
        return $this->ownsRoom($user, $room);
    }

    private function ownsRoom(User $user, Room $room): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'host' && (int) $room->homestay->owner_id === (int) $user->id);
    }
}
