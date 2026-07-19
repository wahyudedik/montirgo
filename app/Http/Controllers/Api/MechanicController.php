<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MechanicResource;
use App\Models\Mechanic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MechanicController extends Controller
{
    /**
     * Daftar mekanik milik partner yang sedang login.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json([
                'data' => [],
            ]);
        }

        $mechanics = $partner->mechanics()->latest()->get();

        return MechanicResource::collection($mechanics);
    }

    /**
     * Tambah mekanik baru.
     */
    public function store(Request $request): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'expertise' => 'required|in:motorcycle,car,both',
            'description' => 'nullable|string|max:1000',
        ]);

        $mechanic = $partner->mechanics()->create($validated);

        return response()->json([
            'message' => 'Mekanik berhasil ditambahkan',
            'mechanic' => new MechanicResource($mechanic),
        ], 201);
    }

    /**
     * Update data mekanik.
     */
    public function update(Request $request, Mechanic $mechanic): JsonResponse
    {
        $this->authorizeMechanic($request, $mechanic);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'photo' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'expertise' => 'sometimes|in:motorcycle,car,both',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $mechanic->update($validated);

        return response()->json([
            'message' => 'Data mekanik berhasil diperbarui',
            'mechanic' => new MechanicResource($mechanic->fresh()),
        ]);
    }

    /**
     * Hapus mekanik.
     */
    public function destroy(Request $request, Mechanic $mechanic): JsonResponse
    {
        $this->authorizeMechanic($request, $mechanic);

        $mechanic->delete();

        return response()->json([
            'message' => 'Mekanik berhasil dihapus',
        ]);
    }

    /**
     * Cek apakah mekanik milik partner yang sedang login.
     */
    private function authorizeMechanic(Request $request, Mechanic $mechanic): void
    {
        $partner = $request->user()->partner;

        if (! $partner || $mechanic->partner_id !== $partner->id) {
            abort(403, 'Tidak memiliki akses ke data mekanik ini');
        }
    }
}
