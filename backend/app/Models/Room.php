<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $table = 'rooms';
    public $timestamps = false;

    protected $fillable = [
        'homestay_id',
        'name',
        'room_type',
        'max_guests',
        'bed_count',
        'base_price',
        'quantity',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'base_price' => 'decimal:2',
    ];

    public function homestay(): BelongsTo
    {
        return $this->belongsTo(Homestay::class, 'homestay_id');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(RoomAvailability::class, 'room_id');
    }

    public function seasonalPrices(): HasMany
    {
        return $this->hasMany(SeasonalPrice::class, 'room_id');
    }
}
