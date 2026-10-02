<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LocalProduct extends Model
{
    protected $table = 'local_products';
    const UPDATED_AT = null;

    protected $fillable = [
        'seller_id',
        'name',
        'origin_place',
        'description',
        'unit',
        'price',
        'stock_qty',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function homestays(): BelongsToMany
    {
        return $this->belongsToMany(Homestay::class, 'homestay_products', 'product_id', 'homestay_id')
            ->withPivot(['featured', 'display_order']);
    }
}
