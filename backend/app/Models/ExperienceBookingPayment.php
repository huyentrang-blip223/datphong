<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceBookingPayment extends Model
{
    protected $table = 'experience_booking_payments';
    const UPDATED_AT = null;

    protected $fillable = [
        'experience_booking_id',
        'gateway',
        'txn_ref',
        'amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function experienceBooking(): BelongsTo
    {
        return $this->belongsTo(ExperienceBooking::class, 'experience_booking_id');
    }
}
