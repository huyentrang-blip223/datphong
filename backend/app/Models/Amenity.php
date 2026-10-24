<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    protected $table = 'amenities';

    public $timestamps = false;

    protected $fillable = ['name', 'icon_key', 'category'];

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_amenities', 'amenity_id', 'room_id')
            ->withPivot(['is_highlighted', 'note']);
    }
}
