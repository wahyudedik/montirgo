<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Partner;
use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Kirim notifikasi in-app dan log ke database.
     */
    public function sendInApp(User $user, string $title, string $body, array $data = [], string $type = 'general'): NotificationLog
    {
        return NotificationLog::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'channel' => 'in_app',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Kirim push notification via Firebase Cloud Messaging (FCM).
     */
    public function sendFcm(User $user, string $title, string $body, array $data = [], string $type = 'general'): NotificationLog
    {
        // Cek user notification preferences — skip jika user nonaktifkan tipe ini
        if (! $user->isNotificationAllowed($type)) {
            return NotificationLog::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'channel' => 'fcm',
                'status' => 'skipped',
                'sent_at' => now(),
            ]);
        }

        $log = NotificationLog::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'channel' => 'fcm',
            'status' => 'pending',
        ]);

        $tokens = $this->getTargetTokens($user);

        if ($tokens->isEmpty()) {
            $log->update(['status' => 'failed']);

            return $log;
        }

        $accessToken = $this->getFcmAccessToken();
        $anySuccess = false;

        foreach ($tokens as $tokenRecord) {
            $fcmToken = is_string($tokenRecord) ? $tokenRecord : $tokenRecord->token;
            $sent = $this->sendFcmToToken($accessToken, $fcmToken, $title, $body, $data, $user, 'montirgo_default', 'default');
            if ($sent) {
                $anySuccess = true;
            }
        }

        $log->update([
            'status' => $anySuccess ? 'sent' : 'failed',
            'sent_at' => $anySuccess ? now() : null,
        ]);

        return $log;
    }

    /**
     * Kirim notifikasi order status change.
     */
    public function notifyOrderStatus(User $user, string $orderCode, string $newStatus, string $statusLabel): void
    {
        $title = "Status Order {$orderCode}";
        $body = "Order kamu sekarang: {$statusLabel}";

        $this->sendInApp($user, $title, $body, [
            'order_code' => $orderCode,
            'status' => $newStatus,
        ], 'order_status');

        // Juga kirim FCM jika user punya token
        $this->sendFcm($user, $title, $body, [
            'order_code' => $orderCode,
            'status' => $newStatus,
            'type' => 'order_status',
        ], 'order_status');
    }

    /**
     * Kirim notifikasi chat baru.
     */
    public function notifyNewMessage(User $user, string $senderName, string $preview): void
    {
        $title = "Pesan dari {$senderName}";
        $body = $preview;

        $this->sendInApp($user, $title, $body, [
            'sender_name' => $senderName,
        ], 'chat_message');

        $this->sendFcm($user, $title, $body, [
            'sender_name' => $senderName,
            'type' => 'chat_message',
        ], 'chat_message');
    }

    /**
     * Kirim notifikasi order baru untuk partner.
     */
    public function notifyNewOrder(array $partnerIds, string $orderCode, float $distanceKm): void
    {
        foreach ($partnerIds as $partnerId) {
            $partner = Partner::find($partnerId);

            if (! $partner || ! $partner->user) {
                continue;
            }

            $title = 'Order Baru!';
            $body = "Order {$orderCode} - Jarak {$distanceKm} km dari lokasi kamu";

            $this->sendInApp($partner->user, $title, $body, [
                'order_code' => $orderCode,
                'distance_km' => $distanceKm,
            ], 'new_order');

            // Gunakan sound/vibration untuk notifikasi order baru (priority tinggi)
            $this->sendFcmWithSound($partner->user, $title, $body, [
                'order_code' => $orderCode,
                'type' => 'new_order',
            ], 'new_order', 'order_alert');
        }
    }

    /**
     * Kirim notifikasi SOS emergency — priority tertinggi dengan sound khusus.
     */
    public function notifySosEmergency(User $user, string $orderCode, string $category): void
    {
        $categoryLabels = [
            'flat_tire' => 'Ban Bocor',
            'dead_battery' => 'Aki Mati',
            'out_of_fuel' => 'Kehabisan BBM',
            'locked_keys' => 'Kunci Tertinggal',
            'overheat' => 'Mesin Overheat',
        ];
        $categoryLabel = $categoryLabels[$category] ?? $category;

        $title = 'SOS Darurat!';
        $body = "Order {$orderCode} - {$categoryLabel}";

        $this->sendInApp($user, $title, $body, [
            'order_code' => $orderCode,
            'category' => $category,
        ], 'sos');

        // SOS menggunakan sound emergency dengan vibration pattern khusus
        $this->sendFcmWithSound($user, $title, $body, [
            'order_code' => $orderCode,
            'category' => $category,
            'type' => 'sos',
        ], 'sos', 'sos_emergency');
    }

    /**
     * Kirim notifikasi saat partner mendapat income dari order.
     */
    public function notifyWalletCredit(User $user, float $amount, string $orderCode): void
    {
        $formatted = 'Rp'.number_format($amount, 0, ',', '.');
        $title = 'Pendapatan Baru';
        $body = "Anda mendapat pendapatan {$formatted} dari order {$orderCode}.";

        $this->sendFcm($user, $title, $body, [
            'type' => 'wallet',
            'action' => 'credit',
            'amount' => (string) $amount,
            'order_code' => $orderCode,
        ], 'wallet');
    }

    /**
     * Kirim notifikasi saat withdraw request disetujui admin.
     */
    public function notifyWithdrawApproved(User $user, float $amount, string $bankName): void
    {
        $formatted = 'Rp'.number_format($amount, 0, ',', '.');
        $title = 'Penarikan Disetujui';
        $body = "Penarikan {$formatted} ke {$bankName} telah disetujui dan akan segera diproses.";

        $this->sendFcm($user, $title, $body, [
            'type' => 'wallet',
            'action' => 'withdraw_approved',
            'amount' => (string) $amount,
            'bank_name' => $bankName,
        ], 'wallet');
    }

    /**
     * Kirim notifikasi saat withdraw request ditolak admin.
     */
    public function notifyWithdrawRejected(User $user, float $amount, string $reason): void
    {
        $formatted = 'Rp'.number_format($amount, 0, ',', '.');
        $title = 'Penarikan Ditolak';
        $body = "Penarikan {$formatted} ditolak. Alasan: {$reason}. Saldo telah dikembalikan.";

        $this->sendFcm($user, $title, $body, [
            'type' => 'wallet',
            'action' => 'withdraw_rejected',
            'amount' => (string) $amount,
            'reason' => $reason,
        ], 'wallet');
    }

    /**
     * Dapatkan notifikasi unread count untuk user.
     */
    public function getUnreadCount(User $user): int
    {
        return NotificationLog::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca.
     */
    public function markAllAsRead(User $user): int
    {
        return NotificationLog::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Hapus FCM token yang sudah tidak aktif (stale) untuk menjaga
     * kualitas pengiriman push notification.
     *
     * Token dianggap stale jika user tidak login selama N hari.
     */
    public function cleanStaleTokens(int $inactiveDays = 30): int
    {
        $cutoffDate = now()->subDays($inactiveDays);

        // Bersihkan dari tabel user_fcm_tokens
        $deletedFromTable = UserFcmToken::where('last_used_at', '<', $cutoffDate)
            ->orWhere(function ($query) use ($cutoffDate) {
                $query->whereNull('last_used_at')
                    ->where('created_at', '<', $cutoffDate);
            })
            ->delete();

        // Bersihkan legacy fcm_token field juga
        $updatedLegacy = User::whereNotNull('fcm_token')
            ->where(function ($query) use ($cutoffDate) {
                $query->whereNull('last_active_at')
                    ->orWhere('last_active_at', '<', $cutoffDate);
            })
            ->update(['fcm_token' => null]);

        return $deletedFromTable + $updatedLegacy;
    }

    /**
     * Kirim notifikasi dengan sound & vibration untuk priority tinggi.
     */
    public function sendFcmWithSound(User $user, string $title, string $body, array $data = [], string $type = 'general', string $sound = 'default'): NotificationLog
    {
        if (! $user->isNotificationAllowed($type)) {
            return NotificationLog::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'channel' => 'fcm',
                'status' => 'skipped',
                'sent_at' => now(),
            ]);
        }

        $log = NotificationLog::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'channel' => 'fcm',
            'status' => 'pending',
        ]);

        $tokens = $this->getTargetTokens($user);

        if ($tokens->isEmpty()) {
            $log->update(['status' => 'failed']);

            return $log;
        }

        $accessToken = $this->getFcmAccessToken();
        $anySuccess = false;

        foreach ($tokens as $tokenRecord) {
            $fcmToken = is_string($tokenRecord) ? $tokenRecord : $tokenRecord->token;
            $sent = $this->sendFcmToToken($accessToken, $fcmToken, $title, $body, $data, $user, 'montirgo_high_priority', $sound, true);
            if ($sent) {
                $anySuccess = true;
            }
        }

        $log->update([
            'status' => $anySuccess ? 'sent' : 'failed',
            'sent_at' => $anySuccess ? now() : null,
        ]);

        return $log;
    }

    /**
     * Dapatkan semua target FCM token untuk user (multi-device + legacy fallback).
     *
     * @return Collection<int, string>
     */
    private function getTargetTokens(User $user): Collection
    {
        // Ambil dari tabel user_fcm_tokens (multi-device)
        $tokens = $user->getActiveFcmTokens()->pluck('token');

        // Fallback: tambahkan legacy fcm_token jika belum ada di collection
        if ($user->fcm_token && ! $tokens->contains($user->fcm_token)) {
            $tokens->push($user->fcm_token);
        }

        return $tokens;
    }

    /**
     * Kirim FCM ke satu token.
     */
    private function sendFcmToToken(
        ?string $accessToken,
        string $fcmToken,
        string $title,
        string $body,
        array $data,
        User $user,
        string $channelId,
        string $sound,
        bool $withVibration = false,
    ): bool {
        if (! $accessToken) {
            return false;
        }

        try {
            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => collect($data)->mapWithKeys(fn ($value, $key) => [$key => (string) $value])->toArray(),
                    'android' => [
                        'priority' => 'high',
                        'notification' => array_merge([
                            'channel_id' => $channelId,
                            'sound' => $sound,
                        ], $withVibration ? ['vibrate_timings_ms' => [0, 250, 250, 250]] : []),
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                        'payload' => [
                            'aps' => [
                                'sound' => $sound,
                                'badge' => $this->getUnreadCount($user),
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->getProjectId()}/messages:send", $payload);

            if ($response->successful()) {
                return true;
            }

            Log::warning('FCM send failed for token', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FCM send error for token', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function getProjectId(): ?string
    {
        return config('services.fcm.project_id');
    }

    private function getFcmAccessToken(): ?string
    {
        $privateKey = config('services.fcm.private_key');
        $clientEmail = config('services.fcm.client_email');

        if (! $privateKey || ! $clientEmail) {
            return null;
        }

        // Simple JWT generation for FCM
        $now = time();
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $dataToSign = "{$header}.{$payload}";

        if (openssl_sign($dataToSign, $signature, $privateKey, 'SHA256')) {
            $jwt = "{$dataToSign}.".base64_encode($signature);

            $response = Http::post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }
        }

        return null;
    }
}
