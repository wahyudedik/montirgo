<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\DispatchService;
use App\Services\EmergencyService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    /**
     * Daftar order user yang sedang login.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->orders()->with(['partner', 'vehicle']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(15);

        return OrderResource::collection($orders);
    }

    /**
     * Detail order.
     */
    public function show(Request $request, Order $order): OrderResource|JsonResponse
    {
        if ($order->user_id !== $request->user()->id && $order->partner_id !== $request->user()->partner?->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->load(['partner', 'vehicle', 'payment', 'review']);

        return new OrderResource($order);
    }

    /**
     * Buat order baru (customer).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'service_type' => ['required', 'string', 'max:255'],
            'problem_description' => ['nullable', 'string'],
            'location_lat' => ['required', 'numeric'],
            'location_lng' => ['required', 'numeric'],
            'location_address' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:cash,wallet,qris,card'],
        ]);

        /** @var Order $order */
        $order = Order::create([
            'user_id' => $request->user()->id,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'service_type' => $validated['service_type'],
            'problem_description' => $validated['problem_description'] ?? null,
            'location_lat' => $validated['location_lat'],
            'location_lng' => $validated['location_lng'],
            'location_address' => $validated['location_address'] ?? null,
            'payment_method' => $validated['payment_method'],
            'callout_fee' => 25000,
        ]);

        // Mulai dispatch
        app(DispatchService::class)->startDispatch($order);

        return response()->json([
            'message' => 'Order berhasil dibuat',
            'order' => new OrderResource($order->load(['partner', 'vehicle'])),
        ], 201);
    }

    /**
     * Batalkan order (customer).
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (! in_array($order->status, ['pending', 'dispatching'])) {
            return response()->json(['message' => 'Order tidak dapat dibatalkan'], 422);
        }

        $reason = $request->input('cancel_reason', 'Dibatalkan oleh pengguna');
        app(DispatchService::class)->cancelOrder($order, $reason);

        return response()->json([
            'message' => 'Order berhasil dibatalkan',
            'order' => new OrderResource($order->fresh()),
        ]);
    }

    /**
     * Buat order SOS (customer) — flow disederhanakan.
     */
    public function sosStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sos_type' => ['required', 'string', 'in:flat_tire,dead_battery,out_of_fuel,locked_keys,overheat'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'location_lat' => ['required', 'numeric'],
            'location_lng' => ['required', 'numeric'],
            'location_address' => ['nullable', 'string'],
        ]);

        $sosLabel = EmergencyService::getSosLabel($validated['sos_type']);
        $sosIcon = EmergencyService::getSosIcon($validated['sos_type']);

        /** @var Order $order */
        $order = Order::create([
            'user_id' => $request->user()->id,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'service_type' => "SOS Darurat - {$sosLabel}",
            'problem_description' => "{$sosIcon} SOS {$sosLabel}: Darurat, butuh penanganan segera!",
            'location_lat' => $validated['location_lat'],
            'location_lng' => $validated['location_lng'],
            'location_address' => $validated['location_address'] ?? null,
            'callout_fee' => 0,
            'total_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'pending',
            'is_sos' => true,
            'sos_type' => $validated['sos_type'],
        ]);

        // Mulai dispatch SOS (priority — wider radius, batch send)
        app(EmergencyService::class)->startDispatch($order);

        return response()->json([
            'message' => "🚨 SOS {$sosLabel} berhasil dikirim! Sedang mencari mekanik terdekat...",
            'order' => new OrderResource($order->load(['partner', 'vehicle'])),
            'sos_categories' => EmergencyService::SOS_CATEGORIES,
        ], 201);
    }

    // ─── Partner Actions ─────────────────────────────────

    /**
     * Daftar order untuk partner.
     */
    public function partnerOrders(Request $request): AnonymousResourceCollection
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return OrderResource::collection(collect()->paginate(0));
        }

        $query = Order::where('partner_id', $partner->id)->with(['user', 'vehicle']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return OrderResource::collection($query->latest()->paginate(15));
    }

    /**
     * Partner accept order.
     */
    public function accept(Request $request, Order $order): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner || $order->partner_id !== $partner->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'dispatching') {
            return response()->json(['message' => 'Order tidak dapat diterima'], 422);
        }

        app(DispatchService::class)->acceptOrder($order, $partner);

        return response()->json([
            'message' => 'Order berhasil diterima',
            'order' => new OrderResource($order->fresh()),
        ]);
    }

    /**
     * Partner reject order.
     */
    public function reject(Request $request, Order $order): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner || $order->partner_id !== $partner->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'dispatching') {
            return response()->json(['message' => 'Order tidak dapat ditolak'], 422);
        }

        app(DispatchService::class)->rejectOrder($order, $partner);

        return response()->json([
            'message' => 'Order ditolak',
        ]);
    }

    /**
     * Partner update status order.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $partner = $request->user()->partner;

        if (! $partner || $order->partner_id !== $partner->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:on_the_way,arrived,in_progress,completed'],
            'service_fee' => ['required_if:status,completed', 'nullable', 'numeric', 'min:0'],
        ]);

        $newStatus = $validated['status'];

        $allowedTransitions = [
            'accepted' => ['on_the_way'],
            'on_the_way' => ['arrived'],
            'arrived' => ['in_progress'],
            'in_progress' => ['completed'],
        ];

        $allowed = $allowedTransitions[$order->status] ?? [];

        if (! in_array($newStatus, $allowed)) {
            return response()->json(['message' => 'Transisi status tidak valid'], 422);
        }

        if ($newStatus === 'completed' && (empty($validated['service_fee']) || $validated['service_fee'] <= 0)) {
            return response()->json(['message' => 'Biaya servis harus diisi'], 422);
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === 'on_the_way') {
            $updates['started_at'] = now();
        } elseif ($newStatus === 'completed') {
            $updates['completed_at'] = now();

            $paymentService = app(PaymentService::class);
            $paymentService->processCompletion($order, (float) $validated['service_fee']);

            if ($order->payment_method === 'cash') {
                $payment = $order->payment;
                if ($payment) {
                    $paymentService->confirmPayment($payment);
                }
            }
        }

        $oldStatus = $order->status;
        $order->update($updates);

        // Broadcast status change real-time
        broadcast(new OrderStatusChanged($order, $oldStatus, $newStatus));

        // Kirim notifikasi ke customer
        if ($order->user) {
            $statusLabel = match ($newStatus) {
                'on_the_way' => 'Dalam Perjalanan',
                'arrived' => 'Tiba di Lokasi',
                'in_progress' => 'Sedang Dikerjakan',
                'completed' => 'Selesai',
                default => $newStatus,
            };

            app(NotificationService::class)->notifyOrderStatus(
                $order->user,
                $order->code,
                $newStatus,
                $statusLabel,
            );
        }

        return response()->json([
            'message' => 'Status berhasil diperbarui',
            'order' => new OrderResource($order->fresh()),
        ]);
    }
}
