<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateSparepartRequest;
use App\Http\Requests\Api\UpdateSparepartRequest;
use App\Http\Resources\SparepartResource;
use App\Models\Sparepart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SparepartController extends Controller
{
    /**
     * Daftar sparepart milik partner.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $partner = $request->user()->partner;

        $query = Sparepart::where('partner_id', $partner->id)->orderByDesc('created_at');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        return SparepartResource::collection($query->paginate(15));
    }

    /**
     * Simpan sparepart baru.
     */
    public function store(CreateSparepartRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $partner = $request->user()->partner;

        abort_if(! $partner, 403, 'Akun ini bukan partner.');

        $sparepart = Sparepart::create([
            'partner_id' => $partner->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'photo_url' => $validated['photo_url'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Sparepart berhasil ditambahkan.',
            'data' => new SparepartResource($sparepart),
        ], 201);
    }

    /**
     * Detail sparepart.
     */
    public function show(Request $request, Sparepart $sparepart): SparepartResource|JsonResponse
    {
        $this->authorize('view', $sparepart);

        return new SparepartResource($sparepart);
    }

    /**
     * Update sparepart.
     */
    public function update(UpdateSparepartRequest $request, Sparepart $sparepart): JsonResponse
    {
        $this->authorize('update', $sparepart);

        $validated = $request->validated();

        $sparepart->update($validated);

        return response()->json([
            'message' => 'Sparepart berhasil diperbarui.',
            'data' => new SparepartResource($sparepart),
        ]);
    }

    /**
     * Hapus sparepart.
     */
    public function destroy(Request $request, Sparepart $sparepart): JsonResponse
    {
        $this->authorize('delete', $sparepart);

        $sparepart->delete();

        return response()->json([
            'message' => 'Sparepart berhasil dihapus.',
        ]);
    }

    /**
     * Toggle status aktif sparepart.
     */
    public function toggleActive(Request $request, Sparepart $sparepart): JsonResponse
    {
        $this->authorize('toggleActive', $sparepart);

        $sparepart->update([
            'is_active' => ! $sparepart->is_active,
        ]);

        return response()->json([
            'message' => 'Status sparepart berhasil diubah.',
            'data' => new SparepartResource($sparepart),
        ]);
    }

    /**
     * Cek apakah sparepart milik partner yang sedang login.
     */
}
