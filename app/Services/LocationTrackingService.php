<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\PartnerLocationUpdated;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LocationTrackingService
{
    private GeolocationService $geolocation;

    public function __construct(GeolocationService $geolocation)
    {
        $this->geolocation = $geolocation;
    }

    /**
     * Update lokasi partner.
     */
    public function updatePartnerLocation(Partner $partner, string $lat, string $lng): void
    {
        $partner->update([
            'workshop_lat' => $lat,
            'workshop_lng' => $lng,
        ]);

        // Cache lokasi untuk quick access
        Cache::put("partner_location:{$partner->id}", [
            'lat' => $lat,
            'lng' => $lng,
            'updated_at' => now()->timestamp,
        ], now()->addMinutes(30));

        // Broadcast lokasi ke semua order aktif partner ini
        $this->broadcastPartnerLocation($partner, $lat, $lng);

        Log::info("Partner {$partner->workshop_name} location updated", [
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    /**
     * Broadcast lokasi partner ke semua order aktif yang sedang ditangani.
     */
    private function broadcastPartnerLocation(Partner $partner, string $lat, string $lng): void
    {
        $activeOrders = Order::where('partner_id', $partner->id)
            ->whereIn('status', ['accepted', 'on_the_way'])
            ->get();

        $geolocation = app(GeolocationService::class);

        foreach ($activeOrders as $order) {
            $distanceKm = $geolocation->calculateDistance(
                $lat,
                $lng,
                $order->location_lat,
                $order->location_lng,
            );

            $eta = $geolocation->estimateArrival($lat, $lng, $order->location_lat, $order->location_lng);

            broadcast(new PartnerLocationUpdated(
                orderId: $order->id,
                partner: $partner,
                lat: $lat,
                lng: $lng,
                distanceKm: $distanceKm,
                distanceFormatted: $geolocation->formatDistance($distanceKm),
                eta: $eta,
            ));
        }
    }

    /**
     * Update lokasi user/customer.
     */
    public function updateUserLocation(User $user, string $lat, string $lng): void
    {
        $user->update([
            'location_lat' => $lat,
            'location_lng' => $lng,
            'last_active_at' => now(),
        ]);

        Cache::put("user_location:{$user->id}", [
            'lat' => $lat,
            'lng' => $lng,
            'updated_at' => now()->timestamp,
        ], now()->addMinutes(30));
    }

    /**
     * Dapatkan lokasi partner dari cache atau database.
     */
    public function getPartnerLocation(Partner $partner): ?array
    {
        $cached = Cache::get("partner_location:{$partner->id}");

        if ($cached) {
            return $cached;
        }

        if ($partner->workshop_lat && $partner->workshop_lng) {
            $location = [
                'lat' => $partner->workshop_lat,
                'lng' => $partner->workshop_lng,
                'updated_at' => $partner->updated_at?->timestamp ?? now()->timestamp,
            ];

            Cache::put("partner_location:{$partner->id}", $location, now()->addMinutes(30));

            return $location;
        }

        return null;
    }

    /**
     * Dapatkan lokasi user dari cache atau database.
     */
    public function getUserLocation(User $user): ?array
    {
        $cached = Cache::get("user_location:{$user->id}");

        if ($cached) {
            return $cached;
        }

        if ($user->location_lat && $user->location_lng) {
            $location = [
                'lat' => $user->location_lat,
                'lng' => $user->location_lng,
                'updated_at' => $user->last_active_at?->timestamp ?? now()->timestamp,
            ];

            Cache::put("user_location:{$user->id}", $location, now()->addMinutes(30));

            return $location;
        }

        return null;
    }

    /**
     * Dapatkan posisi partner saat ini untuk order tracking.
     *
     * @return array{lat: string, lng: string, distance_km: float, eta: array}|null
     */
    public function getPartnerPositionForOrder(Order $order): ?array
    {
        if (! $order->partner) {
            return null;
        }

        $partnerLocation = $this->getPartnerLocation($order->partner);

        if (! $partnerLocation) {
            return null;
        }

        $distanceKm = $this->geolocation->calculateDistance(
            $order->location_lat,
            $order->location_lng,
            $partnerLocation['lat'],
            $partnerLocation['lng'],
        );

        $eta = $this->geolocation->estimateArrival(
            $partnerLocation['lat'],
            $partnerLocation['lng'],
            $order->location_lat,
            $order->location_lng,
        );

        return [
            'lat' => $partnerLocation['lat'],
            'lng' => $partnerLocation['lng'],
            'distance_km' => $distanceKm,
            'distance_formatted' => $this->geolocation->formatDistance($distanceKm),
            'eta' => $eta,
            'updated_at' => $partnerLocation['updated_at'],
        ];
    }

    /**
     * Cari partner terdekat dalam radius (dengan lokasi real-time).
     */
    public function findNearestPartners(
        string $lat,
        string $lng,
        float $radiusKm = 10,
        int $limit = 10,
    ): Collection {
        return $this->geolocation->findNearbyAvailablePartners($lat, $lng, $radiusKm, $limit)
            ->map(function (Partner $partner) {
                $partner->distance_km = round($partner->distance_meters / 1000, 2);
                $partner->distance_formatted = $this->geolocation->formatDistance($partner->distance_meters / 1000);

                return $partner;
            });
    }

    /**
     * Bersihkan cache lokasi yang sudah expired.
     */
    public function clearStaleLocations(): int
    {
        $cutoff = now()->subHour()->timestamp;
        $cleared = 0;

        // Partner locations
        Partner::where('is_online', true)
            ->where('updated_at', '<', now()->subHour())
            ->update(['is_online' => false]);

        return $cleared;
    }
}
