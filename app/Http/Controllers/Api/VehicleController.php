<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateVehicleRequest;
use App\Http\Requests\Api\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VehicleController extends Controller
{
    public function __construct(
        public VehicleService $vehicleService,
    ) {}

    /**
     * Daftar kendaraan milik user yang sedang login.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $vehicles = $this->vehicleService->listForUser($request->user());

        return VehicleResource::collection($vehicles);
    }

    /**
     * Simpan kendaraan baru.
     */
    public function store(CreateVehicleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $vehicle = $this->vehicleService->create($request->user(), $validated);

        return response()->json([
            'message' => 'Kendaraan berhasil ditambahkan',
            'vehicle' => new VehicleResource($vehicle),
        ], 201);
    }

    /**
     * Detail kendaraan.
     */
    public function show(Request $request, Vehicle $vehicle): VehicleResource|JsonResponse
    {
        $found = $this->vehicleService->getById($request->user(), $vehicle->id);

        if (! $found) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new VehicleResource($found);
    }

    /**
     * Perbarui kendaraan.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        if ($vehicle->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $updated = $this->vehicleService->update($request->user(), $vehicle, $validated);

        return response()->json([
            'message' => 'Kendaraan berhasil diperbarui',
            'vehicle' => new VehicleResource($updated),
        ]);
    }

    /**
     * Hapus kendaraan.
     */
    public function destroy(Request $request, Vehicle $vehicle): JsonResponse
    {
        if ($vehicle->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($this->vehicleService->hasActiveOrder($vehicle)) {
            return response()->json(['message' => 'Tidak bisa menghapus kendaraan yang sedang dalam order aktif'], 422);
        }

        $this->vehicleService->delete($request->user(), $vehicle);

        return response()->json(['message' => 'Kendaraan berhasil dihapus']);
    }

    /**
     * Atur kendaraan sebagai default.
     */
    public function setDefault(Request $request, Vehicle $vehicle): JsonResponse
    {
        if ($vehicle->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $updated = $this->vehicleService->setDefault($request->user(), $vehicle);

        return response()->json([
            'message' => 'Kendaraan berhasil diatur sebagai default',
            'vehicle' => new VehicleResource($updated),
        ]);
    }
}
