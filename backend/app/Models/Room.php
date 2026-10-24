<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'image_path',
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

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities', 'room_id', 'amenity_id')
            ->withPivot(['is_highlighted', 'note']);
    }

    public function getImageUrlAttribute(): string
    {
        if (!empty($this->image_path)) {
            return asset('storage/' . $this->image_path);
        }

        $typeImages = [
            'private_room' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80',
            'family_room' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80',
            'dorm' => 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80',
            'bungalow' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80',
            'whole_house' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80',
        ];

        if (isset($typeImages[$this->room_type])) {
            return $typeImages[$this->room_type];
        }

        if ($this->homestay && !empty($this->homestay->cover_image_path)) {
            $path = public_path('storage/' . $this->homestay->cover_image_path);
            if (file_exists($path)) {
                return asset('storage/' . $this->homestay->cover_image_path);
            }
        }

        return asset('images/rooms/default.svg');
    }
}
