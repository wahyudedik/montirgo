<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static factory()
 */
class Sparepart extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'description',
        'category',
        'price',
        'stock',
        'photo_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
