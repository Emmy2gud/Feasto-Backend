<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_type',
        'plate_number',
        'documents',
        'nin_verified_at',
        'nin_status',
        'approval_status',
        'rejection_reason',
        'phone_number',
        'email',
        'emergency_name',
        'emergency_phone',
        'is_online',
        'latitude',
        'longitude',
        'location_updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'user_id');
    }
}
