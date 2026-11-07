<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemAddons extends Model
{
    protected $fillable = [
        'order_item_id',
        'addon_id',
        'name',
        'price',
    ];

    public function orderItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
