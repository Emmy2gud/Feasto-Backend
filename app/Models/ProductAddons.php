<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAddons extends Model
{
    protected $fillable = [
        'product_id',
        'addon_type',
        'addon_name',
        'addon_price',
        'addon_quantity',
        'is_available',
        'name',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor and mutator for backward compatibility with 'name'
    public function getNameAttribute(): ?string
    {
        return $this->attributes['addon_name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['addon_name'] = $value;
    }

    // Accessor and mutator for backward compatibility with 'price'
    public function getPriceAttribute(): float
    {
        return (float) ($this->attributes['addon_price'] ?? 0);
    }

    public function setPriceAttribute($value): void
    {
        $this->attributes['addon_price'] = $value;
    }
}

