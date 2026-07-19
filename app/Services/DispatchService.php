<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\DispatchTimeoutJob;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Symptom;
use Illuminate\Support\Facades\Log;

class DispatchService
{
    public function __construct(
        private readonly GeolocationService $geolocation,
        private readonly NotificationService $notificationService,
    ) {}

    /** Radius awal pencarian (km) */
    private const INITIAL_RADIUS_KM = 5;

    /** Eskalasi radius per step (km) */
    private const ESCALATION_STEP_KM = 5;

    /** Radius maksimal pencarian (km) */
    private const MAX_RADIUS_KM = 30;

    /** Timeout per partner (detik) */
    private const PARTNER_TIMEOUT_SECONDS = 60;

    /**
     * Mulai proses dispatch untuk order.
     */
    public function startDispatch(Order $order): void
    {
        $order->update([
            'status' => 'dispatching',
            'dispatch_started_at' => now(),
            'dispatch_escalation' => 0,
        ]);

        Log::info("Dispatch started for order #{$order->code}", [
            'lat' => $order->location_lat,
            'lng' => $order->location_lng,
            'vehicle_category' => $order->vehicle_category,
        ]);

        $this->findPartners($order);
    }

    /**
     * Cari partner terdekat dalam radius tertentu.
     *
     * Dispatch sekarang filter berdasarkan:
     * - Kategori bengkel (motorcycle/car/both) vs kategori kendaraan order
     * - Service radius per partner
     * - Status partner (online)
     */
    public function findPartners(Order $order): void
    {
        $escalation = $order->dispatch_escalation;
        $radiusKm = self::INITIAL_RADIUS_KM + ($escalation * self::ESCALATION_STEP_KM);

        if ($radiusKm > self::MAX_RADIUS_KM) {
            $this->expireOrder($order);

            return;
        }

        Log::info("Searching partners within {$radiusKm}km for order #{$order->code}", [
            'vehicle_category' => $order->vehicle_category,
        ]);

        // Ekstrak kategori kendaraan dari order
        $vehicleCategory = $order->vehicle_category;

        // Ekstrak kategori gejala dari selected_symptoms (jika ada)
        $symptomCategories = null;
        if ($order->selected_symptoms && is_array($order->selected_symptoms)) {
            $symptomCategories = $this->extractSymptomCategories($order->selected_symptoms);
        }

        // Gunakan findMatchingPartners jika ada gejala,否则 findNearbyAvailablePartners
        if ($symptomCategories) {
            $partners = $this->geolocation->findMatchingPartners(
                $order->location_lat,
                $order->location_lng,
                $radiusKm,
                $vehicleCategory,
                $symptomCategories,
            );
        } else {
            $partners = $this->geolocation->findNearbyAvailablePartners(
                $order->location_lat,
                $order->location_lng,
                $radiusKm,
                $vehicleCategory,
            );
        }

        if ($partners->isEmpty()) {
            // Tidak ada partner ditemukan, eskalasi radius
            $order->update(['dispatch_escalation' => $escalation + 1]);
            $this->findPartners($order);

            return;
        }

        // Kirim order ke partner terdekat pertama
        $this->sendToPartner($order, $partners->first());
    }

    /**
     * Kirim order ke partner tertentu.
     */
    private function sendToPartner(Order $order, Partner $partner): void
    {
        $order->update([
            'partner_id' => $partner->id,
        ]);

        // Set partner tidak available sementara
        $partner->update([
            'is_available' => false,
            'partner_status' => 'on_the_way',
        ]);

        Log::info("Order #{$order->code} sent to partner {$partner->workshop_name}", [
            'distance' => round($partner->distance_meters ?? 0),
            'partner_id' => $partner->id,
            'workshop_category' => $partner->workshop_category,
        ]);

        // Kirim push notification ke partner bahwa ada order baru
        $distanceKm = round(($partner->distance_meters ?? 0) / 1000, 1);
        $this->notificationService->notifyNewOrder(
            [$partner->id],
            $order->code,
            $distanceKm,
        );

        // Dispatch timeout job (60 detik)
        DispatchTimeoutJob::dispatch($order->id, $partner->id)
            ->delay(now()->addSeconds(self::PARTNER_TIMEOUT_SECONDS));
    }

    /**
     * Handle partner menerima order.
     */
    public function acceptOrder(Order $order, Partner $partner): void
    {
        $order->update([
            'status' => 'accepted',
            'started_at' => now(),
        ]);

        Log::info("Order #{$order->code} accepted by {$partner->workshop_name}");
    }

    /**
     * Handle partner menolak order.
     */
    public function rejectOrder(Order $order, Partner $partner): void
    {
        // Kembalikan partner ke available
        $partner->update([
            'is_available' => true,
            'partner_status' => 'online',
        ]);

        Log::info("Order #{$order->code} rejected by {$partner->workshop_name}");

        // Cari partner berikutnya
        $this->findPartners($order);
    }

    /**
     * Handle timeout — partner tidak merespon dalam 60 detik.
     */
    public function handleTimeout(int $orderId, int $partnerId): void
    {
        $order = Order::find($orderId);
        $partner = Partner::find($partnerId);

        if (! $order || ! $partner) {
            return;
        }

        // Cek apakah order masih dalam status dispatching dan partner masih sama
        if ($order->status !== 'dispatching' || $order->partner_id !== $partnerId) {
            return; // Order sudah di-accept atau sudah dipindah ke partner lain
        }

        // Kembalikan partner ke available
        $partner->update([
            'is_available' => true,
            'partner_status' => 'online',
        ]);

        Log::info("Partner {$partner->workshop_name} timed out for order #{$order->code}");

        // Cari partner berikutnya
        $this->findPartners($order);
    }

    /**
     * Expire order jika tidak ada partner yang ditemukan dalam radius maksimal.
     */
    private function expireOrder(Order $order): void
    {
        $order->update([
            'status' => 'expired',
            'cancelled_at' => now(),
            'cancel_reason' => 'Tidak ada mekanik yang tersedia dalam radius '.self::MAX_RADIUS_KM.' km',
            'cancelled_by' => 'system',
        ]);

        Log::warning("Order #{$order->code} expired — no partner found within ".self::MAX_RADIUS_KM.'km');
    }

    /**
     * Customer membatalkan order saat masih dispatching.
     */
    public function cancelOrder(Order $order, string $reason = 'Dibatalkan oleh pengguna'): void
    {
        // Kembalikan partner jika ada
        if ($order->partner_id) {
            Partner::where('id', $order->partner_id)->update([
                'is_available' => true,
                'partner_status' => 'online',
            ]);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
            'cancelled_by' => 'user',
        ]);

        Log::info("Order #{$order->code} cancelled by user");
    }

    /**
     * Ekstrak kategori layanan dari array symptom IDs.
     * Map symptom IDs ke kategori service (engine, electrical, tire, dll).
     *
     * @param  array<int>  $symptomIds
     * @return array<string>|null
     */
    private function extractSymptomCategories(array $symptomIds): ?array
    {
        if (empty($symptomIds)) {
            return null;
        }

        // Query symptoms untuk mendapatkan kategori
        $symptoms = Symptom::whereIn('id', $symptomIds)
            ->where('is_active', true)
            ->pluck('category')
            ->unique()
            ->values()
            ->toArray();

        return empty($symptoms) ? null : $symptoms;
    }
}
