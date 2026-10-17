<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    const UPDATED_AT = null;

    protected $fillable = [
        'booking_id',
        'gateway',
        'txn_ref',
        'amount',
        'status',
        'paid_at',
        'gateway_created_at', 'gateway_transaction_no', 'refund_request_id', 'refund_state', 'gateway_response_code',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
