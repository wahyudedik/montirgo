<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static factory()
 */
class WalletBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'frozen',
        'total_income',
        'total_withdrawn',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'frozen' => 'decimal:2',
        'total_income' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
