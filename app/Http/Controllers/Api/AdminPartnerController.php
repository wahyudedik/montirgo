<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminPartnerController extends Controller
{
    /**
     * Daftar semua partner (admin).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Partner::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('workshop_name', 'like', "%{$search}%")
                    ->orWhere('workshop_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('workshop_category')) {
            $query->where('workshop_category', $request->input('workshop_category'));
        }

        $partners = $query->latest()->paginate($request->input('per_page', 15));

        return PartnerResource::collection($partners);
    }

    /**
     * Detail partner (admin).
     */
    public function show(Partner $partner): PartnerResource
    {
        $partner->load(['user', 'services', 'mechanics', 'reviews' => function ($q) {
            $q->latest()->take(5);
        }]);

        return new PartnerResource($partner);
    }

    /**
     * Approve partner + kirim notifikasi.
     */
    public function approve(Partner $partner, NotificationService $notificationService): JsonResponse
    {
        $partner->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Kirim notifikasi ke partner
        $notificationService->sendInApp(
            $partner->user,
            'Akun Disetujui',
            'Selamat! Akun bengkel "'.$partner->workshop_name.'" Anda telah disetujui oleh admin. Anda sudah bisa menerima order.',
            ['partner_id' => $partner->id, 'status' => 'approved'],
            'partner_verification',
        );
        $notificationService->sendFcm(
            $partner->user,
            'Akun Disetujui',
            'Akun bengkel "'.$partner->workshop_name.'" Anda telah disetujui.',
            ['partner_id' => $partner->id, 'status' => 'approved'],
            'partner_verification',
        );

        return response()->json([
            'message' => "Partner {$partner->workshop_name} approved successfully.",
        ]);
    }

    /**
     * Reject partner + kirim notifikasi dengan alasan.
     */
    public function reject(Request $request, Partner $partner, NotificationService $notificationService): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $partner->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Kirim notifikasi ke partner
        $notificationService->sendInApp(
            $partner->user,
            'Akun Ditolak',
            'Akun bengkel "'.$partner->workshop_name.'" Anda ditolak oleh admin. Alasan: '.$validated['rejection_reason'],
            ['partner_id' => $partner->id, 'status' => 'rejected', 'reason' => $validated['rejection_reason']],
            'partner_verification',
        );
        $notificationService->sendFcm(
            $partner->user,
            'Akun Ditolak',
            'Akun bengkel "'.$partner->workshop_name.'" ditolak. '.$validated['rejection_reason'],
            ['partner_id' => $partner->id, 'status' => 'rejected'],
            'partner_verification',
        );

        return response()->json([
            'message' => "Partner {$partner->workshop_name} rejected.",
        ]);
    }

    /**
     * Suspend partner + kirim notifikasi.
     */
    public function suspend(Partner $partner, NotificationService $notificationService): JsonResponse
    {
        $partner->update(['status' => 'suspended']);

        // Kirim notifikasi ke partner
        $notificationService->sendInApp(
            $partner->user,
            'Akun Ditangguhkan',
            'Akun bengkel "'.$partner->workshop_name.'" Anda telah ditangguhkan oleh admin. Silakan hubungi admin untuk informasi lebih lanjut.',
            ['partner_id' => $partner->id, 'status' => 'suspended'],
            'partner_verification',
        );
        $notificationService->sendFcm(
            $partner->user,
            'Akun Ditangguhkan',
            'Akun bengkel "'.$partner->workshop_name.'" ditangguhkan.',
            ['partner_id' => $partner->id, 'status' => 'suspended'],
            'partner_verification',
        );

        return response()->json([
            'message' => "Partner {$partner->workshop_name} suspended.",
        ]);
    }
}
