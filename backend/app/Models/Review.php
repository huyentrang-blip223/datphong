<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'reviews';
    const UPDATED_AT = null;

    protected $fillable = [
        'booking_id',
        'from_user_id',
        'to_user_id',
        'direction',
        'rating',
        'content',
        'visible',
    ];

    protected $casts = [
        'visible' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
