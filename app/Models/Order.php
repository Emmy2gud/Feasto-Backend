<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [

    'order_number',
    'customer_id',
    'vendor_id',
    'rider_id',          // nullable — not assigned yet when order is placed
    'rider_assigned_at', // nullable, timestamp of when a rider accepted
    'subtotal',
    'delivery_fee',
    'discount',
    'total',
    'status',            // pending, accepted, preparing, ready, picked_up, delivered, cancelled
    'payment_status',    // pending, paid, failed
    'delivery_address',
    'delivery_latitude',
    'delivery_longitude',
    'recipient_phone',
    'notes',
    'placed_at',
    'accepted_at',
    'completed_at',
    'cancelled_at',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

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
}
