<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'dish_id',
        'name',
        'price',
        'quantity',
        'special_instructions',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function addons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItemAddons::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
