<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static factory()
 */
class InsuranceClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'insurance_partner_id',
        'claim_number',
        'claimed_amount',
        'approved_amount',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'claimed_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function insurancePartner(): BelongsTo
    {
        return $this->belongsTo(InsurancePartner::class);
    }
}
