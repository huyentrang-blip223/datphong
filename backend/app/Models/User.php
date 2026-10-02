<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $fillable = ['email', 'password_hash', 'role', 'full_name', 'phone', 'status'];
    protected $hidden = ['password_hash'];

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }

    public function homestays(): HasMany
    {
        return $this->hasMany(Homestay::class, 'owner_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'guest_id');
    }
}
