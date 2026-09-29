<?php
namespace App\Policies;

use App\Models\Homestay;
use App\Models\User;

class HomestayPolicy
{
    public function view(User $user, Homestay $homestay): bool
    {
        return $user->role === 'admin' || ($user->role === 'host' && $homestay->owner_id === $user->id);
    }

    public function update(User $user, Homestay $homestay): bool
    {
        return $user->role === 'admin' || ($user->role === 'host' && $homestay->owner_id === $user->id);
    }
}
