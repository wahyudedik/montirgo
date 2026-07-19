<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @method static factory()
 */
#[Fillable(['name', 'email', 'password', 'role', 'phone', 'avatar', 'date_of_birth', 'address', 'fcm_token', 'notification_preferences', 'location_lat', 'location_lng', 'is_active', 'last_active_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /**
     * @use HasApiTokens<PersonalAccessToken>
     * @use HasFactory<UserFactory>
     */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'location_lat' => 'decimal:7',
            'location_lng' => 'decimal:7',
            'is_active' => 'boolean',
            'last_active_at' => 'datetime',
            'notification_preferences' => 'array',
        ];
    }

    // ─── Notification Preferences ──────────────────────

    /**
     * Cek apakah user mengizinkan notifikasi tipe tertentu.
     */
    public function isNotificationAllowed(string $type): bool
    {
        $prefs = $this->notification_preferences ?? [
            'push_enabled' => true,
            'chat' => true,
            'order_status' => true,
            'payment' => true,
            'new_order' => true,
        ];

        if (! ($prefs['push_enabled'] ?? true)) {
            return false;
        }

        return $prefs[$type] ?? true;
    }

    // ─── Role Checks ────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPartner(): bool
    {
        return $this->role === 'partner';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // ─── Relationships ──────────────────────────────────

    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Hitung persentase kelengkapan profil customer (minimal 80% untuk bisa order).
     */
    public function getProfileCompletionPercentage(): int
    {
        $requiredFields = [
            'name' => true,
            'email' => true,
            'phone' => true,
            'avatar' => false,
            'date_of_birth' => false,
            'address' => false,
        ];

        $totalWeight = 0;
        $filledWeight = 0;

        foreach ($requiredFields as $field => $isRequired) {
            $weight = $isRequired ? 2 : 1; // Required fields bobot lebih besar
            $totalWeight += $weight;

            $value = $this->{$field};
            if ($value !== null && $value !== '') {
                $filledWeight += $weight;
            }
        }

        // Cek juga apakah punya minimal 1 kendaraan
        $totalWeight += 2;
        if ($this->vehicles()->count() > 0) {
            $filledWeight += 2;
        }

        return (int) round(($filledWeight / $totalWeight) * 100);
    }

    /**
     * Cek apakah profil sudah cukup lengkap untuk bisa order.
     */
    public function isProfileComplete(): bool
    {
        return $this->getProfileCompletionPercentage() >= 80;
    }

    public function walletBalance(): HasOne
    {
        return $this->hasOne(WalletBalance::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function withdrawRequests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function notificationsLog(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(UserFcmToken::class);
    }

    /**
     * Register atau update FCM token untuk device tertentu.
     */
    public function registerFcmToken(string $token, ?string $deviceName = null, ?string $platform = null): UserFcmToken
    {
        return UserFcmToken::updateOrCreate(
            ['user_id' => $this->id, 'token' => $token],
            [
                'device_name' => $deviceName,
                'platform' => $platform,
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * Hapus FCM token tertentu (saat logout).
     */
    public function removeFcmToken(string $token): int
    {
        return UserFcmToken::where('user_id', $this->id)
            ->where('token', $token)
            ->delete();
    }

    /**
     * Hapus semua FCM token (saat delete account).
     */
    public function removeAllFcmTokens(): int
    {
        return UserFcmToken::where('user_id', $this->id)->delete();
    }

    /**
     * Dapatkan semua FCM token aktif untuk multi-device push.
     */
    public function getActiveFcmTokens(): Collection
    {
        return UserFcmToken::where('user_id', $this->id)
            ->whereNotNull('last_used_at')
            ->where('last_used_at', '>=', now()->subDays(30))
            ->get();
    }
}
