<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SparepartController extends Controller
{
    /**
     * Daftar sparepart partner.
     */
    public function index(Request $request): View
    {
        $partner = $request->user()->partner;

        $spareparts = Sparepart::where('partner_id', $partner->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('partner.spareparts.index', compact('spareparts'));
    }

    /**
     * Form tambah sparepart.
     */
    public function create(): View
    {
        return view('partner.spareparts.create');
    }

    /**
     * Simpan sparepart baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'photo' => 'nullable|image|max:2048',
        ]);

        $partner = $request->user()->partner;

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = $request->file('photo')->store('spareparts', 'public');
        }

        Sparepart::create([
            'partner_id' => $partner->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'photo_url' => $photoUrl,
            'is_active' => true,
        ]);

        return redirect()
            ->route('partner.spareparts.index')
            ->with('success', 'Sparepart berhasil ditambahkan.');
    }

    /**
     * Form edit sparepart.
     */
    public function edit(Sparepart $sparepart): View
    {
        abort_if($sparepart->partner_id !== auth()->user()->partner?->id, 403);

        return view('partner.spareparts.edit', compact('sparepart'));
    }

    /**
     * Update sparepart.
     */
    public function update(Request $request, Sparepart $sparepart): RedirectResponse
    {
        abort_if($sparepart->partner_id !== auth()->user()->partner?->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $photoUrl = $sparepart->photo_url;
        if ($request->hasFile('photo')) {
            $photoUrl = $request->file('photo')->store('spareparts', 'public');
        }

        $sparepart->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'photo_url' => $photoUrl,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('partner.spareparts.index')
            ->with('success', 'Sparepart berhasil diperbarui.');
    }

    /**
     * Hapus sparepart.
     */
    public function destroy(Sparepart $sparepart): RedirectResponse
    {
        abort_if($sparepart->partner_id !== auth()->user()->partner?->id, 403);

        $sparepart->delete();

        return redirect()
            ->route('partner.spareparts.index')
            ->with('success', 'Sparepart berhasil dihapus.');
    }
}
