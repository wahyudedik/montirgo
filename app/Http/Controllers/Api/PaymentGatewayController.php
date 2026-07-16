<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    /**
     * Handle payment gateway webhook callback.
     * Supports Midtrans Xendit format.
     */
    public function webhookUpdateClaim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_code' => 'required|string',
            'status' => 'required|in:paid,failed,refunded',
            'transaction_id' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'amount' => 'required|numeric',
        ]);

        $order = Order::where('code', $validated['order_code'])->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment = $order->payment;

        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'amount' => $validated['amount'],
                'status' => $validated['status'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'paid_at' => $validated['status'] === 'paid' ? now() : null,
            ]);
        } else {
            $payment->update([
                'status' => $validated['status'],
                'transaction_id' => $validated['transaction_id'] ?? $payment->transaction_id,
                'reference_number' => $validated['reference_number'] ?? $payment->reference_number,
                'paid_at' => $validated['status'] === 'paid' ? now() : $payment->paid_at,
            ]);
        }

        // Update order payment status
        $order->update([
            'payment_status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now() : $order->paid_at,
        ]);

        return response()->json([
            'message' => 'Payment status updated',
            'order_code' => $order->code,
            'status' => $validated['status'],
        ]);
    }

    /**
     * Check payment status for an order.
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
            'method' => $order->payment_method,
            'paid_at' => $order->paid_at?->toISOString(),
            'transaction_id' => $order->payment?->transaction_id,
        ]);
    }
}
