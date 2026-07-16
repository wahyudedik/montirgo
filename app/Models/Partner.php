<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static factory()
 */
class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workshop_name',
        'workshop_address',
        'workshop_lat',
        'workshop_lng',
        'ktp_number',
        'ktp_photo',
        'workshop_photo',
        'business_license',
        'status',
        'rejection_reason',
        'rating_avg',
        'total_orders',
        'is_online',
        'is_available',
        'approved_at',
    ];

    protected $casts = [
        'workshop_lat' => 'decimal:7',
        'workshop_lng' => 'decimal:7',
        'rating_avg' => 'decimal:2',
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(PartnerService::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
