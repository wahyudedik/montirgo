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
        'owner_name',
        'owner_phone',
        'workshop_address',
        'workshop_lat',
        'workshop_lng',
        'workshop_category',
        'service_radius',
        'ktp_number',
        'ktp_photo',
        'selfie_with_ktp',
        'workshop_photo',
        'front_workshop_photo',
        'inside_workshop_photo',
        'business_license',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'npwp',
        'nib',
        'status',
        'rejection_reason',
        'rating_avg',
        'total_orders',
        'total_reviews',
        'is_online',
        'is_available',
        'partner_status',
        'approved_at',
        'description',
        'operating_hours',
        'operational_schedule',
        'last_active_at',
    ];

    protected $casts = [
        'workshop_lat' => 'decimal:7',
        'workshop_lng' => 'decimal:7',
        'service_radius' => 'integer',
        'rating_avg' => 'decimal:2',
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'approved_at' => 'datetime',
        'operational_schedule' => 'array',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(PartnerService::class);
    }

    public function mechanics(): HasMany
    {
        return $this->hasMany(Mechanic::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function spareparts(): HasMany
    {
        return $this->hasMany(Sparepart::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Hitung persentase kelengkapan profil partner (100% untuk bisa online).
     */
    public function getProfileCompletionPercentage(): int
    {
        $requiredFields = [
            'workshop_name',
            'workshop_address',
            'workshop_lat',
            'workshop_lng',
            'workshop_category',
            'ktp_number',
            'ktp_photo',
            'workshop_photo',
            'description',
        ];

        $filled = 0;
        foreach ($requiredFields as $field) {
            $value = $this->{$field};
            if ($value !== null && $value !== '' && $value !== 0) {
                $filled++;
            }
        }

        return (int) round(($filled / count($requiredFields)) * 100);
    }

    /**
     * Cek apakah partner match dengan kategori kendaraan tertentu.
     */
    public function matchesVehicleCategory(string $vehicleCategory): bool
    {
        return $this->workshop_category === 'both'
            || $this->workshop_category === $vehicleCategory;
    }

    /**
     * Cek apakah partner sedang bisa menerima order baru.
     */
    public function canReceiveOrder(): bool
    {
        return $this->status === 'approved'
            && $this->partner_status === 'online'
            && $this->is_online
            && $this->is_available;
    }

    /**
     * Cek apakah partner sedang dalam jam operasional berdasarkan schedule + timezone WIB.
     */
    public function isCurrentlyOperating(): bool
    {
        $schedule = $this->operational_schedule;

        if (! $schedule || ! is_array($schedule)) {
            // Tidak ada jadwal = dianggap buka 24/7
            return true;
        }

        // Gunakan timezone Indonesia Barat (WIB = UTC+7)
        $now = now('Asia/Jakarta');
        $dayMap = [
            0 => 'sun',
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
            6 => 'sat',
        ];
        $todayKey = $dayMap[$now->dayOfWeek];

        $todaySchedule = $schedule[$todayKey] ?? null;

        // Jika hari ini null atau tidak ada jadwal = tutup
        if (! $todaySchedule || ! is_array($todaySchedule)) {
            return false;
        }

        $openTime = $todaySchedule['open'] ?? null;
        $closeTime = $todaySchedule['close'] ?? null;

        if (! $openTime || ! $closeTime) {
            return false;
        }

        $currentTime = $now->format('H:i');

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }
}
