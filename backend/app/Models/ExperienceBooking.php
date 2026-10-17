<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExperienceBooking extends Model
{
    protected $table = 'experience_bookings';
    const UPDATED_AT = null;

    protected $fillable = [
        'experience_id',
        'guest_id',
        'people_count',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class, 'experience_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ExperienceBookingPayment::class, 'experience_booking_id');
    }
}
