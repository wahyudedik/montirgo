<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeolocationService
{
    /**
     * Hitung jarak antara 2 titik menggunakan Haversine formula.
     *
     * @return float Jarak dalam kilometer
     */
    public function calculateDistance(string $lat1, string $lng1, string $lat2, string $lng2): float
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad((float) $lat1);
        $latTo = deg2rad((float) $lat2);
        $latDiff = deg2rad((float) $lat2 - (float) $lat1);
        $lngDiff = deg2rad((float) $lng2 - (float) $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2)
            + cos($latFrom) * cos($latTo)
            * sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Hitung jarak dalam meter.
     */
    public function calculateDistanceMeters(string $lat1, string $lng1, string $lat2, string $lng2): float
    {
        return $this->calculateDistance($lat1, $lng1, $lat2, $lng2) * 1000;
    }

    /**
     * Estimasi waktu tempuh (ETA) berdasarkan jarak dan kecepatan rata-rata.
     *
     * @return array{minutes: float, distance_km: float, formatted: string}
     */
    public function estimateArrival(
        string $fromLat,
        string $fromLng,
        string $toLat,
        string $toLng,
        ?string $vehicleType = null,
    ): array {
        $distanceKm = $this->calculateDistance($fromLat, $fromLng, $toLat, $toLng);

        $avgSpeed = config("maps.avg_speed_kmh.{$vehicleType}", config('maps.avg_speed_kmh.default'));

        $minutes = ($distanceKm / $avgSpeed) * 60;

        return [
            'minutes' => round($minutes, 1),
            'distance_km' => $distanceKm,
            'formatted' => $this->formatEta($minutes),
        ];
    }

    /**
     * Format ETA menjadi string yang mudah dibaca.
     */
    public function formatEta(float $minutes): string
    {
        if ($minutes < 1) {
            return 'Kurang dari 1 menit';
        }

        if ($minutes < 60) {
            return round($minutes).' menit';
        }

        $hours = (int) ($minutes / 60);
        $remainingMinutes = round($minutes % 60);

        if ($remainingMinutes === 0) {
            return $hours.' jam';
        }

        return $hours.' jam '.$remainingMinutes.' menit';
    }

    /**
     * Format jarak menjadi string yang mudah dibaca.
     */
    public function formatDistance(float $distanceKm): string
    {
        if ($distanceKm < 1) {
            return round($distanceKm * 1000).' m';
        }

        return round($distanceKm, 1).' km';
    }

    /**
     * Hitung bearing (arah) dari titik A ke titik B.
     *
     * @return float Derajat (0-360)
     */
    public function calculateBearing(string $lat1, string $lng1, string $lat2, string $lng2): float
    {
        $latFrom = deg2rad((float) $lat1);
        $latTo = deg2rad((float) $lat2);
        $lngDiff = deg2rad((float) $lng2 - (float) $lng1);

        $y = sin($lngDiff) * cos($latTo);
        $x = cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lngDiff);

        $bearing = rad2deg(atan2($y, $x));

        return fmod(($bearing + 360), 360);
    }

    /**
     * Cek apakah titik berada dalam radius tertentu.
     */
    public function isWithinRadius(
        string $centerLat,
        string $centerLng,
        string $pointLat,
        string $pointLng,
        float $radiusKm,
    ): bool {
        $distance = $this->calculateDistance($centerLat, $centerLng, $pointLat, $pointLng);

        return $distance <= $radiusKm;
    }

    /**
     * Hitung bounding box untuk area tertentu.
     *
     * @return array{min_lat: float, max_lat: float, min_lng: float, max_lng: float}
     */
    public function getBoundingBox(string $lat, string $lng, float $radiusKm): array
    {
        $latDegrees = $radiusKm / 111.0;
        $lngDegrees = $radiusKm / (111.0 * cos(deg2rad((float) $lat)));

        return [
            'min_lat' => round((float) $lat - $latDegrees, 7),
            'max_lat' => round((float) $lat + $latDegrees, 7),
            'min_lng' => round((float) $lng - $lngDegrees, 7),
            'max_lng' => round((float) $lng + $lngDegrees, 7),
        ];
    }

    /**
     * Cari partner terdekat yang available menggunakan Haversine formula via SQL.
     *
     * Filter berdasarkan:
     * - Status approved
     * - Partner status online
     * - is_online & is_available
     * - Workshop category match dengan vehicle category (motorcycle/car/both)
     * - Service radius per partner
     * - Jam operasional (jika ada operational_schedule)
     *
     * @return Collection<int, Partner & object{distance_meters: float}>
     */
    public function findNearbyAvailablePartners(
        string $lat,
        string $lng,
        float $radiusKm,
        ?string $vehicleCategory = null,
        int $limit = 10,
    ): Collection {
        $radiusMeters = $radiusKm * 1000;

        $query = Partner::query()
            ->where('status', 'approved')
            ->where('partner_status', 'online')
            ->where('is_online', true)
            ->where('is_available', true)
            ->whereNotNull('workshop_lat')
            ->whereNotNull('workshop_lng')
            ->where('workshop_lat', '!=', 0)
            ->where('workshop_lng', '!=', 0);

        // Filter berdasarkan kategori bengkel vs kategori kendaraan
        if ($vehicleCategory && $vehicleCategory !== 'other') {
            $query->where(function ($q) use ($vehicleCategory) {
                $q->where('workshop_category', $vehicleCategory)
                    ->orWhere('workshop_category', 'both');
            });
        }

        $query->select([
            'partners.*',
            DB::raw("(
                6371000 * acos(
                    cos(radians({$lat}))
                    * cos(radians(workshop_lat))
                    * cos(radians(workshop_lng) - radians({$lng}))
                    + sin(radians({$lat}))
                    * sin(radians(workshop_lat))
                )
            ) AS distance_meters"),
        ])
            ->having('distance_meters', '<=', $radiusMeters)
            ->orderBy('distance_meters')
            ->limit($limit);

        $partners = $query->get();

        // Filter lagi berdasarkan service_radius per partner + jam operasional
        return $partners->filter(function ($partner) {
            $distanceKm = $partner->distance_meters / 1000;

            return $distanceKm <= ($partner->service_radius ?? 30)
                && $partner->isCurrentlyOperating();
        })->values();
    }

    /**
     * Cari partner terdekat yang punya layanan spesifik (untuk matching gejala).
     *
     * @return Collection<int, Partner & object{distance_meters: float}>
     */
    public function findMatchingPartners(
        string $lat,
        string $lng,
        float $radiusKm,
        ?string $vehicleCategory = null,
        ?array $symptomCategories = null,
        int $limit = 10,
    ): Collection {
        // Cari semua partner yang match kategori kendaraan
        $partners = $this->findNearbyAvailablePartners(
            $lat,
            $lng,
            $radiusKm,
            $vehicleCategory,
            50, // Ambil lebih banyak dulu untuk filtering
        );

        // Jika tidak ada symptom filter, return semua
        if (empty($symptomCategories)) {
            return $partners->take($limit);
        }

        // Filter partner yang punya layanan matching dengan gejala
        $partnerIds = $partners->pluck('id');

        $matchingPartnerIds = PartnerService::query()
            ->whereIn('partner_id', $partnerIds)
            ->where('is_active', true)
            ->whereIn('category', $symptomCategories)
            ->where(function ($q) use ($vehicleCategory) {
                if ($vehicleCategory && $vehicleCategory !== 'other') {
                    $q->where('vehicle_category', $vehicleCategory)
                        ->orWhere('vehicle_category', 'both');
                }
            })
            ->pluck('partner_id')
            ->unique();

        // Prioritaskan partner yang punya layanan matching, lalu sisanya
        $matched = $partners->filter(fn ($p) => $matchingPartnerIds->contains($p->id))->values();
        $unmatched = $partners->filter(fn ($p) => ! $matchingPartnerIds->contains($p->id))->values();

        return $matched->concat($unmatched)->take($limit);
    }

    /**
     * Reverse geocoding — dapatkan alamat dari koordinat.
     * Menggunakan OpenStreetMap Nominatim (gratis) atau Google Maps.
     */
    public function reverseGeocode(string $lat, string $lng): ?string
    {
        $provider = config('maps.provider');

        if ($provider === 'google' && config('maps.google_api_key')) {
            return $this->googleReverseGeocode($lat, $lng);
        }

        return $this->nominatimReverseGeocode($lat, $lng);
    }

    /**
     * Reverse geocoding menggunakan OpenStreetMap Nominatim (gratis).
     */
    private function nominatimReverseGeocode(string $lat, string $lng): ?string
    {
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";

            $response = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'header' => "User-Agent: MontirGo/1.0\r\n",
                ],
            ]));

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);

            return $data['display_name'] ?? null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Reverse geocoding menggunakan Google Maps Geocoding API.
     */
    private function googleReverseGeocode(string $lat, string $lng): ?string
    {
        try {
            $apiKey = config('maps.google_api_key');
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&key={$apiKey}";

            $response = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 5,
                ],
            ]));

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);

            if ($data['status'] === 'OK' && ! empty($data['results'][0]['formatted_address'])) {
                return $data['results'][0]['formatted_address'];
            }

            return null;
        } catch (\Exception) {
            return null;
        }
    }
}
