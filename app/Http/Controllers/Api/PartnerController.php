<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\NearbyPartnersRequest;
use App\Http\Requests\Api\UpdatePartnerLocationRequest;
use App\Http\Requests\Api\UpdatePartnerProfileRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Order;
use App\Models\Partner;
use App\Services\AnalyticsService;
use App\Services\FileUploadService;
use App\Services\GeolocationService;
use App\Services\LocationTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    /**
     * Profil partner yang sedang login.
     */
    public function profile(Request $request): PartnerResource|JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        return new PartnerResource($partner);
    }

    /**
     * Update profil partner.
     */
    /**
     * File fields yang perlu di-upload ke storage.
     */
    private const FILE_FIELDS = [
        'ktp_photo',
        'selfie_with_ktp',
        'workshop_photo',
        'front_workshop_photo',
        'inside_workshop_photo',
        'business_license',
    ];

    public function updateProfile(UpdatePartnerProfileRequest $request, FileUploadService $fileService): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $validated = $request->validated();

        // Handle file uploads — replace old files with new ones
        foreach (self::FILE_FIELDS as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $fileService->replace(
                    $partner->{$field},
                    $request->file($field),
                    'partner-documents',
                );
            } elseif (array_key_exists($field, $validated) && is_null($validated[$field])) {
                // Explicitly nullify field if sent as null
                $fileService->delete($partner->{$field});
                $validated[$field] = null;
            } else {
                // Remove non-file fields from validated to avoid overwriting
                unset($validated[$field]);
            }
        }

        $partner->update($validated);

        return response()->json([
            'message' => 'Profil partner berhasil diperbarui',
            'partner' => new PartnerResource($partner->fresh()),
        ]);
    }

    /**
     * Toggle status online/offline partner.
     */
    public function toggleOnline(Request $request, LocationTrackingService $locationService): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        if ($partner->status !== 'approved') {
            return response()->json([
                'message' => 'Akun partner belum disetujui admin',
            ], 403);
        }

        // Toggle between 'online' and 'offline'
        $newStatus = $partner->partner_status === 'online' ? 'offline' : 'online';
        $isOnline = $newStatus === 'online';
        $lat = null;
        $lng = null;

        // GPS validation: wajib menyertakan lokasi saat ingin Online
        if ($isOnline) {
            $lat = $request->input('current_lat');
            $lng = $request->input('current_lng');

            if (is_null($lat) || is_null($lng)) {
                return response()->json([
                    'message' => 'GPS harus aktif saat ingin Online. Sertakan current_lat dan current_lng.',
                ], 422);
            }

            if (! is_numeric($lat) || ! is_numeric($lng)) {
                return response()->json([
                    'message' => 'Format GPS tidak valid.',
                ], 422);
            }

            $lat = (string) $lat;
            $lng = (string) $lng;

            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                return response()->json([
                    'message' => 'Koordinat GPS di luar batas yang valid.',
                ], 422);
            }
        }

        // Cek jam operasional saat ingin Online
        $outsideOperatingHours = false;
        if ($isOnline && ! $partner->isCurrentlyOperating()) {
            $outsideOperatingHours = true;
        }

        $updateData = [
            'partner_status' => $newStatus,
            'is_online' => $isOnline,
            'is_available' => $isOnline,
            'last_active_at' => now(),
        ];

        // Simpan lokasi GPS saat Online
        if ($isOnline) {
            $locationService->updatePartnerLocation($partner, $lat, $lng);
        }

        $partner->update($updateData);

        return response()->json([
            'message' => $isOnline ? 'Status: Online' : 'Status: Offline',
            'partner_status' => $partner->partner_status,
            'is_online' => $partner->is_online,
            'is_available' => $partner->is_available,
            'outside_operating_hours' => $outsideOperatingHours,
        ]);
    }

    /**
     * Daftar partner terdekat (public, untuk pencarian customer).
     */
    public function nearby(NearbyPartnersRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $validated = $request->validated();

        $radiusKm = $validated['radius'] ?? 30;
        $radiusMeters = $radiusKm * 1000;
        $lat = $validated['lat'];
        $lng = $validated['lng'];
        $vehicleCategory = $validated['vehicle_category'] ?? null;

        $query = Partner::query()
            ->where('status', 'approved')
            ->where('partner_status', 'online')
            ->whereNotNull('workshop_lat')
            ->whereNotNull('workshop_lng');

        // Filter by workshop category if vehicle_category is specified
        if ($vehicleCategory) {
            $query->where(function ($q) use ($vehicleCategory) {
                $q->where('workshop_category', $vehicleCategory)
                    ->orWhere('workshop_category', 'both');
            });
        }

        $partners = $query
            ->select([
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
            ->havingRaw('distance_meters <= LEAST(?, service_radius * 1000)', [$radiusMeters])
            ->orderBy('distance_meters')
            ->limit(20)
            ->get();

        return PartnerResource::collection($partners);
    }

    /**
     * Toggle availability partner.
     */
    public function toggleAvailability(Request $request): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $partner->update([
            'is_available' => ! $partner->is_available,
        ]);

        return response()->json([
            'message' => $partner->is_available ? 'Tersedia' : 'Tidak Tersedia',
            'is_available' => $partner->is_available,
        ]);
    }

    /**
     * Update lokasi partner (real-time tracking).
     */
    public function updateLocation(UpdatePartnerLocationRequest $request, LocationTrackingService $locationService): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $validated = $request->validated();

        $locationService->updatePartnerLocation($partner, $validated['lat'], $validated['lng']);

        // Update last_active_at setiap kali lokasi dikirim
        $partner->update(['last_active_at' => now()]);

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui',
            'location' => [
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
            ],
        ]);
    }

    /**
     * Dapatkan info tracking untuk order tertentu (distance + ETA).
     */
    public function trackOrder(Request $request, Order $order): JsonResponse
    {
        $this->authorize('track', $order);

        $partner = $request->user()->partner;

        $geoService = app(GeolocationService::class);

        $distanceKm = $geoService->calculateDistance(
            $order->location_lat,
            $order->location_lng,
            $partner->workshop_lat,
            $partner->workshop_lng,
        );

        $eta = $geoService->estimateArrival(
            $partner->workshop_lat,
            $partner->workshop_lng,
            $order->location_lat,
            $order->location_lng,
        );

        return response()->json([
            'order_code' => $order->code,
            'order_status' => $order->status,
            'customer_location' => [
                'lat' => $order->location_lat,
                'lng' => $order->location_lng,
                'address' => $order->location_address,
            ],
            'partner_location' => [
                'lat' => $partner->workshop_lat,
                'lng' => $partner->workshop_lng,
            ],
            'distance_km' => $distanceKm,
            'distance_formatted' => $geoService->formatDistance($distanceKm),
            'eta' => $eta,
        ]);
    }

    /**
     * Dashboard stats untuk partner yang sedang login.
     */
    public function stats(Request $request, AnalyticsService $analytics): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        return response()->json([
            'data' => $analytics->getPartnerStats($partner),
        ]);
    }

    /**
     * Update partner_status (granular: online, resting, closed).
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        if ($partner->status !== 'approved') {
            return response()->json([
                'message' => 'Akun partner belum disetujui admin',
            ], 403);
        }

        $validated = $request->validate([
            'partner_status' => 'required|string|in:online,resting,closed',
        ]);

        $newStatus = $validated['partner_status'];

        $updates = ['partner_status' => $newStatus];

        // When going online, set is_online = true
        if ($newStatus === 'online') {
            $updates['is_online'] = true;
            $updates['is_available'] = true;
        } else {
            // resting or closed — not available for dispatch
            $updates['is_online'] = false;
            $updates['is_available'] = false;
        }

        $partner->update($updates);

        return response()->json([
            'message' => 'Status partner berhasil diperbarui',
            'partner_status' => $partner->partner_status,
            'is_online' => $partner->is_online,
            'is_available' => $partner->is_available,
        ]);
    }

    /**
     * Get profile completion percentage for partner.
     */
    public function profileCompletion(Request $request): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        return response()->json([
            'partner_completion' => $partner->getProfileCompletionPercentage(),
            'user_completion' => $request->user()->getProfileCompletionPercentage(),
            'is_approved' => $partner->isApproved(),
            'status' => $partner->status,
        ]);
    }
}
