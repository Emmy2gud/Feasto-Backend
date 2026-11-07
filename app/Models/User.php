<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fullname',
        'phone_number',
        'email',
        'password',
        'email_verified_at',
        'role',
        'status',
        'address',
        'contry',
        'profile_picture',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->attributes['fullname'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['fullname'] = $value;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function vendorOrders()
    {
        return $this->hasManyThrough(Order::class, Vendor::class, 'user_id', 'vendor_id');
    }
}
