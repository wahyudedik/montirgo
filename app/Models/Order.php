<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property string $code
 * @property int $user_id
 * @property int|null $partner_id
 * @property int|null $vehicle_id
 * @property string $service_type
 * @property string|null $problem_description
 * @property array|null $photo_urls
 * @property string $location_lat
 * @property string $location_lng
 * @property string|null $location_address
 * @property string $status
 * @property string $callout_fee
 * @property string $service_fee
 * @property string $total_amount
 * @property string $platform_commission
 * @property string $partner_earning
 * @property string $payment_method
 * @property string $payment_status
 * @property Carbon|null $paid_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancel_reason
 * @property string|null $cancelled_by
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $dispatch_started_at
 * @property int $dispatch_escalation
 *
 * @method static factory()
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'partner_id',
        'vehicle_id',
        'service_type',
        'problem_description',
        'photo_urls',
        'location_lat',
        'location_lng',
        'location_address',
        'status',
        'callout_fee',
        'service_fee',
        'total_amount',
        'platform_commission',
        'partner_earning',
        'payment_method',
        'payment_status',
        'paid_at',
        'cancelled_at',
        'cancel_reason',
        'cancelled_by',
        'started_at',
        'completed_at',
        'dispatch_started_at',
        'dispatch_escalation',
        'is_sos',
        'sos_type',
    ];

    protected $casts = [
        'photo_urls' => 'array',
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
        'callout_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'partner_earning' => 'decimal:2',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'dispatch_started_at' => 'datetime',
        'is_sos' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->code)) {
                $order->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'MTG-'.strtoupper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(OrderPhoto::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDispatching(): bool
    {
        return $this->status === 'dispatching';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isOnTheWay(): bool
    {
        return $this->status === 'on_the_way';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress']);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'dispatching' => 'Mencari Mekanik',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            'on_the_way' => 'Mekanik Dalam Perjalanan',
            'arrived' => 'Mekanik Tiba',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'dispatching' => 'blue',
            'accepted' => 'indigo',
            'rejected' => 'red',
            'on_the_way' => 'purple',
            'arrived' => 'cyan',
            'in_progress' => 'orange',
            'completed' => 'green',
            'cancelled' => 'red',
            'expired' => 'gray',
            default => 'gray',
        };
    }

    public function getTotalDisplayAttribute(): string
    {
        return 'Rp '.number_format((float) $this->total_amount, 0, ',', '.');
    }
}
