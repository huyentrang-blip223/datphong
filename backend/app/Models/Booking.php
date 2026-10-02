<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table = 'bookings';
    const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'guest_id',
        'room_id',
        'checkin_date',
        'checkout_date',
        'guest_count',
        'unit_price',
        'nights',
        'total_amount',
        'status',
        'hold_expires_at',
        'cancel_reason',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class, 'booking_id');
    }
}
