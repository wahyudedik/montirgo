<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static factory()
 */
class InsurancePartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'api_key',
        'api_secret',
        'api_url',
        'config',
        'status',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function claims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
