<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAddons extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'price',
        'is_available',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
