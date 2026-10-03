<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOrderItem extends Model
{
    protected $table = 'product_order_items';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ProductOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(LocalProduct::class, 'product_id');
    }
}
