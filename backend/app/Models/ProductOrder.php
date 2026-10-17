<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOrder extends Model
{
    protected $table = 'product_orders';

    const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'buyer_id',
        'homestay_id',
        'status',
        'subtotal',
        'total_amount',
        'points_redeemed',
        'discount_amount',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function homestay(): BelongsTo
    {
        return $this->belongsTo(Homestay::class, 'homestay_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductOrderItem::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ProductOrderPayment::class, 'order_id');
    }
}
