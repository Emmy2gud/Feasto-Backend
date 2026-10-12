<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItemAddons extends Model
{
    protected $fillable = [
        'cart_item_id',
        'addon_id',
        'price',
    ];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }

    public function addon()
    {
        return $this->belongsTo(ProductAddons::class, 'addon_id');
    }
}
