<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'business_description',
        'business_phone',
        'business_email',
        'business_address',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'is_accepting_orders',
        'status',
        'verification_status',
        'profile_picture',
        'business_document',
        'nin_verification_status',
        'nin_verified_at',
        'bvn_verification_status',
        'bvn_verified_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(VendorBusinessHours::class);
    }

    public function productaddons(): HasMany
    {
        return $this->hasMany(ProductAddons::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    } 

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cartitems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
