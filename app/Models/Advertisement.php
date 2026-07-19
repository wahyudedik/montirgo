<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'target_url',
        'position',
        'start_date',
        'end_date',
        'is_active',
        'impressions',
        'clicks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'impressions' => 'integer',
        'clicks' => 'integer',
    ];

    /**
     * Check if the ad is currently active (within date range).
     */
    public function isActive(): bool
    {
        $now = now();

        return $this->is_active
            && $this->start_date->lte($now)
            && $this->end_date->gte($now);
    }

    /**
     * Get the click-through rate (CTR).
     */
    public function getCtrAttribute(): float
    {
        if ($this->impressions === 0) {
            return 0.0;
        }

        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    /**
     * Scope to only active ads.
     */
    public function scopeCurrentlyActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }
}
