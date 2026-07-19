<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\DispatchTimeoutJob;
use App\Models\Order;
use App\Models\Partner;
use Illuminate\Support\Facades\Log;

class EmergencyService
{
    public function __construct(
        private readonly GeolocationService $geolocation,
    ) {}

    /** Kategori darurat */
    public const SOS_CATEGORIES = [
        'flat_tire' => [
            'label' => 'Ban Pecah / Bocor',
            'icon' => '🛞',
            'description' => 'Ban kendaraan pecah atau bocor, butuh tambal atau ganti',
        ],
        'dead_battery' => [
            'label' => 'Aki Soak',
            'icon' => '🔋',
            'description' => 'Aki kendaraan soak, butuh jumper atau ganti baru',
        ],
        'out_of_fuel' => [
            'label' => 'Kehabisan Bensin',
            'icon' => '⛽',
            'description' => 'Kehabisan bahan bakar di jalan',
        ],
        'locked_keys' => [
            'label' => 'Kunci Tertinggal',
            'icon' => '🔑',
            'description' => 'Kunci tertinggal di dalam kendaraan',
        ],
        'overheat' => [
            'label' => 'Mesin Overheat',
            'icon' => '🌡️',
            'description' => 'Mesin overheat atau mogok total',
        ],
    ];

    /** Radius awal pencarian SOS (km) — lebih wide dari normal */
    private const SOS_INITIAL_RADIUS_KM = 10;

    /** Eskalasi radius per step (km) */
    private const ESCALATION_STEP_KM = 5;

    /** Radius maksimal pencarian SOS (km) */
    private const SOS_MAX_RADIUS_KM = 50;

    /** Timeout per partner — lebih cepat (detik) */
    private const PARTNER_TIMEOUT_SECONDS = 30;

    /** Prioritas SOS: langsung kirim ke multiple partner */
    private const SOS_BATCH_SIZE = 3;

    /**
     * Mulai proses dispatch SOS (priority).
     */
    public function startDispatch(Order $order): void
    {
        $order->update([
            'status' => 'dispatching',
            'dispatch_started_at' => now(),
            'dispatch_escalation' => 0,
            'is_sos' => true,
        ]);

        Log::critical("🚨 SOS DISPATCH started for order #{$order->code}", [
            'sos_type' => $order->sos_type,
            'lat' => $order->location_lat,
            'lng' => $order->location_lng,
            'user_id' => $order->user_id,
        ]);

        $this->findPartners($order);
    }

    /**
     * Cari partner terdekat — SOS mengirim ke multiple partner sekaligus.
     */
    public function findPartners(Order $order): void
    {
        $escalation = $order->dispatch_escalation;
        $radiusKm = self::SOS_INITIAL_RADIUS_KM + ($escalation * self::ESCALATION_STEP_KM);

        if ($radiusKm > self::SOS_MAX_RADIUS_KM) {
            $this->expireOrder($order);

            return;
        }

        Log::info("🚨 SOS searching partners within {$radiusKm}km for order #{$order->code}");

        $partners = $this->geolocation->findNearbyAvailablePartners(
            $order->location_lat,
            $order->location_lng,
            $radiusKm,
        );

        if ($partners->isEmpty()) {
            // Eskalasi radius
            $order->update(['dispatch_escalation' => $escalation + 1]);
            $this->findPartners($order);

            return;
        }

        // SOS: kirim ke batch partner sekaligus (prioritas lebih tinggi)
        $batch = $partners->take(self::SOS_BATCH_SIZE);
        foreach ($batch as $partner) {
            $this->sendToPartner($order, $partner);
        }
    }

    /**
     * Kirim order ke partner tertentu.
     */
    private function sendToPartner(Order $order, Partner $partner): void
    {
        $order->update([
            'partner_id' => $partner->id,
        ]);

        $partner->update(['is_available' => false]);

        Log::critical("🚨 SOS Order #{$order->code} sent to partner {$partner->workshop_name}", [
            'distance' => round($partner->distance_meters ?? 0),
            'partner_id' => $partner->id,
            'sos_type' => $order->sos_type,
        ]);

        // Dispatch timeout job — lebih cepat (30 detik untuk SOS)
        DispatchTimeoutJob::dispatch($order->id, $partner->id)
            ->delay(now()->addSeconds(self::PARTNER_TIMEOUT_SECONDS));
    }

    /**
     * Handle partner menerima order SOS.
     */
    public function acceptOrder(Order $order, Partner $partner): void
    {
        $order->update([
            'status' => 'accepted',
            'started_at' => now(),
        ]);

        Log::critical("🚨 SOS Order #{$order->code} accepted by {$partner->workshop_name}");
    }

    /**
     * Handle partner menolak order SOS.
     */
    public function rejectOrder(Order $order, Partner $partner): void
    {
        $partner->update(['is_available' => true]);

        Log::info("🚨 SOS Order #{$order->code} rejected by {$partner->workshop_name}");

        $this->findPartners($order);
    }

    /**
     * Handle timeout — partner tidak merespon dalam 30 detik.
     */
    public function handleTimeout(int $orderId, int $partnerId): void
    {
        $order = Order::find($orderId);
        $partner = Partner::find($partnerId);

        if (! $order || ! $partner) {
            return;
        }

        if ($order->status !== 'dispatching' || $order->partner_id !== $partnerId) {
            return;
        }

        $partner->update(['is_available' => true]);

        Log::critical("🚨 SOS Partner {$partner->workshop_name} timed out for order #{$order->code}");

        $this->findPartners($order);
    }

    /**
     * Expire order SOS jika tidak ada partner ditemukan.
     */
    private function expireOrder(Order $order): void
    {
        $order->update([
            'status' => 'expired',
            'cancelled_at' => now(),
            'cancel_reason' => 'Tidak ada mekanik SOS yang tersedia dalam radius '.self::SOS_MAX_RADIUS_KM.' km',
            'cancelled_by' => 'system',
        ]);

        Log::critical("🚨 SOS Order #{$order->code} expired — no partner found within ".self::SOS_MAX_RADIUS_KM.'km');
    }

    /**
     * Customer membatalkan order SOS.
     */
    public function cancelOrder(Order $order, string $reason = 'Dibatalkan oleh pengguna'): void
    {
        if ($order->partner_id) {
            Partner::where('id', $order->partner_id)->update(['is_available' => true]);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
            'cancelled_by' => 'user',
        ]);

        Log::critical("🚨 SOS Order #{$order->code} cancelled by user");
    }

    /**
     * Ambil label dari sos_type.
     */
    public static function getSosLabel(string $sosType): string
    {
        return self::SOS_CATEGORIES[$sosType]['label'] ?? $sosType;
    }

    /**
     * Ambil icon dari sos_type.
     */
    public static function getSosIcon(string $sosType): string
    {
        return self::SOS_CATEGORIES[$sosType]['icon'] ?? '🚨';
    }
}
