<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    /**
     * Handle Midtrans webhook notification callback.
     *
     * Endpoint publik yang dipanggil oleh Midtrans saat status transaksi berubah.
     * Verifikasi signature dilakukan untuk keamanan.
     */
    public function handlePaymentWebhook(Request $request): JsonResponse
    {
        try {
            $notification = $request->all();

            // Pastikan order_id ada
            if (empty($notification['order_id'])) {
                return response()->json(['message' => 'Missing order_id'], 400);
            }

            /** @var PaymentService $paymentService */
            $paymentService = app(PaymentService::class);
            $result = $paymentService->handleWebhookNotification($notification);

            return response()->json([
                'message' => 'Payment status updated',
                'order_code' => $result['order_code'],
                'status' => $result['status'],
                'midtrans_status' => $result['midtrans_status'],
            ]);
        } catch (\RuntimeException $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;

            return response()->json([
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Check payment status for an order.
     *
     * Endpoint publik untuk cek status pembayaran order.
     * Digunakan oleh mobile app untuk polling status setelah redirect.
     */
    public function paymentStatus(Request $request, string $orderCode): JsonResponse
    {
        $order = Order::where('code', $orderCode)
            ->with('payment')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'order_code' => $order->code,
            'payment_status' => $order->payment_status,
            'amount' => $order->total_amount,
            'callout_fee' => $order->callout_fee,
            'service_fee' => $order->service_fee,
            'method' => $order->payment_method,
            'paid_at' => $order->paid_at?->toISOString(),
            'transaction_id' => $order->payment?->transaction_id,
        ]);
    }

    /**
     * Buat payment (Snap Token) untuk order baru.
     *
     * Dipanggil oleh customer mobile app setelah membuat order
     * untuk mendapatkan Snap Token dan redirect URL.
     */
    public function createPayment(Request $request, Order $order): JsonResponse
    {
        // Hanya customer yang bisa melakukan pembayaran
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Order harus dalam status yang benar
        if (! in_array($order->status, ['pending', 'dispatching'])) {
            return response()->json([
                'message' => 'Order tidak dapat dibayar',
            ], 422);
        }

        // Sudah ada payment yang aktif?
        $existingPayment = $order->payment;
        if ($existingPayment && $existingPayment->status === 'paid') {
            return response()->json([
                'message' => 'Order sudah dibayar',
                'payment' => [
                    'status' => $existingPayment->status,
                    'amount' => $existingPayment->amount,
                ],
            ], 422);
        }

        try {
            /** @var PaymentService $paymentService */
            $paymentService = app(PaymentService::class);
            $result = $paymentService->createGatewayPayment($order);

            return response()->json([
                'message' => 'Payment token berhasil dibuat',
                'payment' => [
                    'id' => $result['payment']->id,
                    'amount' => $result['payment']->amount,
                    'status' => $result['payment']->status,
                ],
                'snap_token' => $result['token'],
                'redirect_url' => $result['redirect_url'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buat payment (Snap Token) untuk service fee.
     *
     * Dipanggil oleh partner mobile app saat order selesai
     * untuk membayar sisa biaya servis.
     */
    public function createServiceFeePayment(Request $request, Order $order): JsonResponse
    {
        $this->authorize('updateStatus', $order);

        // Order harus in_progress
        if ($order->status !== 'in_progress') {
            return response()->json([
                'message' => 'Order harus dalam status sedang dikerjakan',
            ], 422);
        }

        $validated = $request->validate([
            'service_fee' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            /** @var PaymentService $paymentService */
            $paymentService = app(PaymentService::class);
            $result = $paymentService->processServiceFeePayment(
                $order,
                (float) $validated['service_fee']
            );

            return response()->json([
                'message' => 'Service fee payment token berhasil dibuat',
                'payment' => [
                    'id' => $result['payment']->id,
                    'amount' => $result['payment']->amount,
                    'status' => $result['payment']->status,
                ],
                'snap_token' => $result['token'],
                'redirect_url' => $result['redirect_url'],
                'fee_breakdown' => [
                    'callout_fee' => $result['payment']->metadata['callout_fee'],
                    'service_fee' => $result['payment']->metadata['service_fee'],
                    'total_amount' => $result['payment']->amount,
                    'commission_percent' => $result['payment']->metadata['commission_percent'],
                    'platform_commission' => $result['payment']->metadata['platform_commission'],
                    'partner_earning' => $result['payment']->metadata['partner_earning'],
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
