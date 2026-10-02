<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusLog extends Model
{
    protected $table = 'booking_status_logs';
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'from_status',
        'to_status',
        'actor_id',
        'reason',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
