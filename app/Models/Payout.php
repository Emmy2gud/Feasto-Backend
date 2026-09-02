<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'currency',
        'trigger_type',
        'reference',
        'provider',
        'provider_reference',
        'status',
        'failure_reason',
        'retry_count',
        'scheduled_at',
        'processed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
