<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $partner_id
 * @property string $name
 * @property string|null $photo
 * @property string|null $phone
 * @property string $expertise
 * @property string|null $description
 * @property bool $is_active
 *
 * @method static factory()
 */
class Mechanic extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'photo',
        'phone',
        'expertise',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
