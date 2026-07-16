<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Order;
use App\Models\Partner;
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
    public function updateProfile(Request $request): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $validated = $request->validate([
            'workshop_name' => ['sometimes', 'string', 'max:255'],
            'workshop_address' => ['sometimes', 'nullable', 'string'],
            'workshop_lat' => ['sometimes', 'nullable', 'numeric'],
            'workshop_lng' => ['sometimes', 'nullable', 'numeric'],
            'description' => ['sometimes', 'nullable', 'string'],
            'operating_hours' => ['sometimes', 'nullable', 'string'],
        ]);

        $partner->update($validated);

        return response()->json([
            'message' => 'Profil partner berhasil diperbarui',
            'partner' => new PartnerResource($partner->fresh()),
        ]);
    }

    /**
     * Toggle status online/offline partner.
     */
    public function toggleOnline(Request $request): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $partner->update([
            'is_online' => ! $partner->is_online,
            'is_available' => ! $partner->is_online ? false : true,
        ]);

        return response()->json([
            'message' => $partner->is_online ? 'Status: Online' : 'Status: Offline',
            'is_online' => $partner->is_online,
            'is_available' => $partner->is_available,
        ]);
    }

    /**
     * Daftar partner terdekat (public, untuk pencarian customer).
     */
    public function nearby(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'radius' => ['sometimes', 'numeric', 'min:1', 'max:50'],
        ]);

        $radiusKm = $validated['radius'] ?? 30;
        $radiusMeters = $radiusKm * 1000;
        $lat = $validated['lat'];
        $lng = $validated['lng'];

        $partners = Partner::query()
            ->where('status', 'approved')
            ->where('is_online', true)
            ->whereNotNull('workshop_lat')
            ->whereNotNull('workshop_lng')
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
            ->having('distance_meters', '<=', $radiusMeters)
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
    public function updateLocation(Request $request, LocationTrackingService $locationService): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $locationService->updatePartnerLocation($partner, $validated['lat'], $validated['lng']);

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
        $partner = $request->user()->partner;

        if (! $partner || $order->partner_id !== $partner->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
}
