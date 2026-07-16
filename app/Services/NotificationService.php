<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Partner;
use App\Models\User;
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
        $fcmToken = $user->fcm_token ?? null;

        $log = NotificationLog::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'channel' => 'fcm',
            'status' => 'pending',
        ]);

        if (! $fcmToken) {
            $log->update(['status' => 'failed']);

            return $log;
        }

        try {
            $accessToken = $this->getFcmAccessToken();

            if (! $accessToken) {
                $log->update(['status' => 'failed']);

                return $log;
            }

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->getProjectId()}/messages:send", [
                    'message' => [
                        'token' => $fcmToken,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => collect($data)->mapWithKeys(fn ($value, $key) => [$key => (string) $value])->toArray(),
                        'android' => [
                            'priority' => 'high',
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => '10',
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $log->update(['status' => 'sent', 'sent_at' => now()]);
            } else {
                Log::warning('FCM send failed', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $log->update(['status' => 'failed']);
            }
        } catch (\Exception $e) {
            Log::error('FCM send error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            $log->update(['status' => 'failed']);
        }

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

            $this->sendFcm($partner->user, $title, $body, [
                'order_code' => $orderCode,
                'type' => 'new_order',
            ], 'new_order');
        }
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
