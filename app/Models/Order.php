<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'idempotency_key',
        'customer_id',
        'vendor_id',
        'rider_id',
        'rider_assigned_at',
        'subtotal',
        'delivery_fee',
        'service_fee',
        'tip',
        'discount',
        'promo_code',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'distance_km',
        'dropoff_preference',
        'recipient_phone',
        'notes',
        'placed_at',
        'accepted_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rider_assigned_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'tip' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'distance_km' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }
}
