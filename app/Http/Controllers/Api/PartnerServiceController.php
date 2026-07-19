<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePartnerServiceRequest;
use App\Http\Requests\Api\UpdatePartnerServiceRequest;
use App\Models\PartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerServiceController extends Controller
{
    /**
     * Daftar layanan milik partner yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $services = $partner->services()->latest()->get();

        return response()->json(['data' => $services]);
    }

    /**
     * Tambah layanan baru.
     */
    public function store(CreatePartnerServiceRequest $request): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $validated = $request->validated();

        $service = $partner->services()->create($validated);

        return response()->json([
            'message' => 'Layanan berhasil ditambahkan',
            'service' => $service,
        ], 201);
    }

    /**
     * Perbarui layanan.
     */
    public function update(UpdatePartnerServiceRequest $request, PartnerService $service): JsonResponse
    {
        $this->authorize('update', $service);

        $validated = $request->validated();

        $service->update($validated);

        return response()->json([
            'message' => 'Layanan berhasil diperbarui',
            'service' => $service->fresh(),
        ]);
    }

    /**
     * Hapus layanan.
     */
    public function destroy(PartnerService $service): JsonResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return response()->json(['message' => 'Layanan berhasil dihapus']);
    }

    /**
     * Toggle status aktif/nonaktif layanan.
     */
    public function toggleActive(PartnerService $service): JsonResponse
    {
        $this->authorize('toggleActive', $service);

        $service->update(['is_active' => ! $service->is_active]);

        return response()->json([
            'message' => $service->is_active ? 'Layanan diaktifkan' : 'Layanan dinonaktifkan',
            'is_active' => $service->is_active,
        ]);
    }
}
