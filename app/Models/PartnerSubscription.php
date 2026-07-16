<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static factory()
 */
class PartnerSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'plan',
        'amount',
        'status',
        'started_at',
        'expires_at',
        'features',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'features' => 'array',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at?->isFuture();
    }

    public function isPro(): bool
    {
        return $this->plan === 'pro' && $this->isActive();
    }

    public function isEnterprise(): bool
    {
        return $this->plan === 'enterprise' && $this->isActive();
    }
}
