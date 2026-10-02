<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomAvailability extends Model
{
    protected $table = 'room_availability';
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'stay_date',
        'units_total',
        'units_held',
        'units_sold',
        'price_override',
        'status',
    ];

    protected $casts = [
        'stay_date' => 'date',
        'price_override' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
