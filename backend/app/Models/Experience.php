<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Experience extends Model
{
    protected $table = 'experiences';
    public $timestamps = false;

    protected $fillable = [
        'homestay_id',
        'title',
        'description',
        'start_at',
        'duration_minutes',
        'capacity',
        'booked_count',
        'price',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function homestay(): BelongsTo
    {
        return $this->belongsTo(Homestay::class, 'homestay_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ExperienceBooking::class, 'experience_id');
    }
}
