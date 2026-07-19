<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $vehicle_category
 * @property string $label
 * @property string|null $description
 * @property string|null $icon
 * @property string $category
 * @property int $sort_order
 * @property bool $is_active
 *
 * @method static factory()
 */
class Symptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_category',
        'label',
        'description',
        'icon',
        'category',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: gejala aktif untuk kategori kendaraan tertentu.
     */
    public function scopeForVehicleCategory($query, string $vehicleCategory)
    {
        return $query->where('vehicle_category', $vehicleCategory)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
