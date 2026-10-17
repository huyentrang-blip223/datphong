<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomestayVerification extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime'];

    public function homestay()
    {
        return $this->belongsTo(Homestay::class);
    }
}
