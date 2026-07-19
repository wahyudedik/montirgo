<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InsuranceClaim;
use App\Models\InsurancePartner;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    /**
     * Daftar insurance partners yang aktif.
     */
    public function partners(): JsonResponse
    {
        $partners = InsurancePartner::where('status', 'active')
            ->select('id', 'name', 'code')
            ->get();

        return response()->json(['data' => $partners]);
    }

    /**
     * Buat klaim asuransi untuk order.
     */
    public function createClaim(Request $request, Order $order): JsonResponse
    {
        $this->authorize('createClaim', $order);

        $request->validate([
            'insurance_partner_id' => 'required|exists:insurance_partners,id',
            'claimed_amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if claim already exists
        abort_if(
            InsuranceClaim::where('order_id', $order->id)->exists(),
            422,
            'Klaim asuransi untuk order ini sudah ada',
        );

        $claim = InsuranceClaim::create([
            'order_id' => $order->id,
            'insurance_partner_id' => $request->insurance_partner_id,
            'claim_number' => 'CLM-'.strtoupper(uniqid()),
            'claimed_amount' => $request->claimed_amount,
            'status' => 'submitted',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Klaim asuransi berhasil dibuat.',
            'data' => $claim,
        ], 201);
    }

    /**
     * Status klaim asuransi.
     */
    public function claimStatus(Request $request, InsuranceClaim $claim): JsonResponse
    {
        $this->authorize('viewClaim', $claim->order);

        $claim->load('insurancePartner');

        return response()->json(['data' => $claim]);
    }

    /**
     * Webhook callback dari insurance partner (update status klaim).
     * Endpoint ini akan dipanggil oleh sistem asuransi partner.
     */
    public function webhookUpdateClaim(Request $request): JsonResponse
    {
        $request->validate([
            'claim_number' => 'required|string',
            'status' => 'required|in:approved,rejected,paid',
            'approved_amount' => 'nullable|numeric|min:0',
            'metadata' => 'nullable|array',
        ]);

        $claim = InsuranceClaim::where('claim_number', $request->claim_number)
            ->firstOrFail();

        $claim->update([
            'status' => $request->status,
            'approved_amount' => $request->approved_amount ?? $claim->claimed_amount,
            'metadata' => $request->metadata ?? [],
            'processed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status klaim berhasil diperbarui.',
        ]);
    }
}
