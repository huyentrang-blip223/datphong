<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Homestay extends Model
{
    protected $table = 'homestays';
    public $timestamps = false;

    protected $fillable = [
        'owner_id','name','slug','province','district','address_line',
        'lat','lng','description','cover_image_path','checkin_time','checkout_time','status','avg_rating'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'homestay_id');
    }

    public function localProducts(): BelongsToMany
    {
        return $this->belongsToMany(LocalProduct::class, 'homestay_products', 'homestay_id', 'product_id')
            ->withPivot(['featured', 'display_order']);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class, 'homestay_id');
    }

    public function getCoverImageUrlAttribute(): string
    {
        if (!empty($this->cover_image_path) && Storage::disk('public')->exists($this->cover_image_path)) {
            return asset('storage/' . $this->cover_image_path);
        }

        $provinceImages = [
            'Lam Dong' => asset('images/banners/dalat-homestay.jpg'),
            'Lao Cai' => asset('images/banners/hero-banner.jpg'),
            'Quang Nam' => asset('images/banners/hoian-homestay.jpg'),
            'Ninh Binh' => asset('images/banners/ninhbinh-homestay.jpg'),
            'Ha Giang' => 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=800&q=80',
            'Thua Thien Hue' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?auto=format&fit=crop&w=800&q=80',
        ];

        if (isset($provinceImages[$this->province])) {
            return $provinceImages[$this->province];
        }

        return asset('images/homestays/default.svg');
    }
}
