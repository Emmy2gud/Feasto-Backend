<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id',
        'amount',
        'currency',
        'payment_method',
        'provider',
        'provider_reference',
        'status',
        'paid_at',
        'failed_at',
        'failure_reason',
        'refund_amount',
        'refunded_at',
        'refund_reason',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
