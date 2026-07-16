<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    /**
     * Record an impression for an advertisement.
     */
    public function trackImpression(Advertisement $advertisement): JsonResponse
    {
        $advertisement->increment('impressions');

        return response()->json(['success' => true]);
    }

    /**
     * Record a click for an advertisement.
     */
    public function trackClick(Advertisement $advertisement): JsonResponse
    {
        $advertisement->increment('clicks');

        return response()->json([
            'success' => true,
            'target_url' => $advertisement->target_url,
        ]);
    }

    public function index(Request $request): View
    {
        $query = Advertisement::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('position')) {
            $query->where('position', $request->input('position'));
        }

        $advertisements = $query->latest()->paginate(10);

        return view('admin.advertisements.index', compact('advertisements'));
    }

    public function create(): View
    {
        return view('admin.advertisements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'target_url' => 'nullable|url|max:500',
            'position' => 'required|in:sidebar,feed,popup,banner',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $path = $request->file('image')->store('advertisements', 'public');

        Advertisement::create([
            'title' => $validated['title'],
            'image_path' => $path,
            'target_url' => $validated['target_url'] ?? null,
            'position' => $validated['position'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Iklan berhasil ditambahkan.');
    }

    public function edit(Advertisement $advertisement): View
    {
        return view('admin.advertisements.edit', compact('advertisement'));
    }

    public function update(Request $request, Advertisement $advertisement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'target_url' => 'nullable|url|max:500',
            'position' => 'required|in:sidebar,feed,popup,banner',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $data = $validated;

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($advertisement->image_path);
            $data['image_path'] = $request->file('image')->store('advertisements', 'public');
        }

        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');

        $advertisement->update($data);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Iklan berhasil diperbarui.');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        Storage::disk('public')->delete($advertisement->image_path);
        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Iklan berhasil dihapus.');
    }
}
