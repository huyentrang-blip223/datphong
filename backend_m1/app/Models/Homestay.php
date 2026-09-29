<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Homestay extends Model
{
    protected $table = 'homestays';
    public $timestamps = false;

    protected $fillable = [
        'owner_id','name','slug','province','district','address_line',
        'lat','lng','description','checkin_time','checkout_time','status','avg_rating'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
