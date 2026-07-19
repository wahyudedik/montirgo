<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SymptomResource;
use App\Models\Symptom;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SymptomController extends Controller
{
    /**
     * Daftar gejala berdasarkan kategori kendaraan.
     *
     * GET /v1/symptoms?vehicle_category=motorcycle
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'vehicle_category' => 'required|in:motorcycle,car',
            'category' => 'nullable|string',
        ]);

        $query = Symptom::forVehicleCategory($validated['vehicle_category']);

        if (isset($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        $symptoms = $query->get();

        return SymptomResource::collection($symptoms);
    }
}
