<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    /**
     * Daftar iklan aktif untuk mobile (public endpoint).
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'position' => ['sometimes', 'string', 'in:sidebar,feed,popup,banner'],
        ]);

        $query = Advertisement::currentlyActive();

        if (isset($validated['position'])) {
            $query->where('position', $validated['position']);
        }

        $advertisements = $query->latest()->limit(10)->get()->map(fn ($ad) => [
            'id' => $ad->id,
            'title' => $ad->title,
            'image_path' => $ad->image_path ? asset('storage/'.$ad->image_path) : null,
            'target_url' => $ad->target_url,
            'position' => $ad->position,
        ]);

        return response()->json(['data' => $advertisements]);
    }

    /**
     * Detail iklan (untuk tracking).
     */
    public function show(Advertisement $advertisement): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $advertisement->id,
                'title' => $advertisement->title,
                'image_path' => $advertisement->image_path ? asset('storage/'.$advertisement->image_path) : null,
                'target_url' => $advertisement->target_url,
                'position' => $advertisement->position,
            ],
        ]);
    }

    /**
     * Record impression.
     */
    public function trackImpression(Advertisement $advertisement): JsonResponse
    {
        $advertisement->increment('impressions');

        return response()->json(['success' => true]);
    }

    /**
     * Record click.
     */
    public function trackClick(Advertisement $advertisement): JsonResponse
    {
        $advertisement->increment('clicks');

        return response()->json([
            'success' => true,
            'target_url' => $advertisement->target_url,
        ]);
    }
}
