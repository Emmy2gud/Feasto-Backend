<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorBusinessHours extends Model
{
    protected $fillable = [
        'vendor_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_closed',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
