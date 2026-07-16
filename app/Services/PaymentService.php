<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /** Biaya panggilan tetap (callout fee) */
    private const CALLOUT_FEE = 25000.00;

    /** Persentase bagi hasil callout fee untuk partner */
    private const PARTNER_CALLOUT_PERCENT = 80;

    /** Persentase komisi platform untuk service fee (non-cash) */
    private const SERVICE_COMMISSION_PERCENT = 10;

    /**
     * Hitung rincian biaya order.
     *
     * @return array{callout_fee: float, partner_callout_share: float, platform_callout_share: float, service_fee: float, platform_commission: float, partner_earning: float, total_amount: float}
     */
    public function calculateFees(float $serviceFee, string $paymentMethod): array
    {
        $calloutFee = self::CALLOUT_FEE;
        $partnerCalloutShare = $calloutFee * (self::PARTNER_CALLOUT_PERCENT / 100);
        $platformCalloutShare = $calloutFee - $partnerCalloutShare;

        // Komisi platform hanya untuk pembayaran non-cash
        $isNonCash = $paymentMethod !== 'cash';
        $platformCommission = $isNonCash
            ? $serviceFee * (self::SERVICE_COMMISSION_PERCENT / 100)
            : 0.00;

        $partnerEarning = $serviceFee - $platformCommission;
        $totalAmount = $calloutFee + $serviceFee;

        return [
            'callout_fee' => $calloutFee,
            'partner_callout_share' => $partnerCalloutShare,
            'platform_callout_share' => $platformCalloutShare,
            'service_fee' => $serviceFee,
            'platform_commission' => $platformCommission,
            'partner_earning' => $partnerEarning,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Proses pembayaran saat order selesai.
     * Dipanggil dari Partner/OrderController saat status = completed.
     */
    public function processCompletion(Order $order, float $serviceFee): Payment
    {
        return DB::transaction(function () use ($order, $serviceFee) {
            $fees = $this->calculateFees($serviceFee, $order->payment_method);

            // Update order dengan rincian biaya
            $order->update([
                'callout_fee' => $fees['callout_fee'],
                'service_fee' => $fees['service_fee'],
                'total_amount' => $fees['total_amount'],
                'platform_commission' => $fees['platform_commission'],
                'partner_earning' => $fees['partner_earning'],
                'payment_status' => $order->payment_method === 'cash' ? 'pending' : 'pending',
            ]);

            // Buat record payment
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'amount' => $fees['total_amount'],
                'status' => $order->payment_method === 'cash' ? 'pending' : 'pending',
                'metadata' => [
                    'callout_fee' => $fees['callout_fee'],
                    'service_fee' => $fees['service_fee'],
                    'platform_commission' => $fees['platform_commission'],
                    'partner_earning' => $fees['partner_earning'],
                    'partner_callout_share' => $fees['partner_callout_share'],
                    'platform_callout_share' => $fees['platform_callout_share'],
                ],
            ]);

            Log::info("Payment created for order #{$order->code}", [
                'total' => $fees['total_amount'],
                'partner_earning' => $fees['partner_earning'],
                'platform_commission' => $fees['platform_commission'],
            ]);

            return $payment;
        });
    }

    /**
     * Konfirmasi pembayaran (cash: otomatis, non-cash: setelah gateway callback).
     */
    public function confirmPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $order = $payment->order;
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            // Credit partner wallet
            $walletService = app(WalletService::class);
            $walletService->creditPartnerEarning(
                $order->partner_id,
                $order->id,
                $payment->metadata['partner_earning'] ?? $order->partner_earning,
                "Pembayaran order #{$order->code}"
            );

            Log::info("Payment confirmed for order #{$order->code}", [
                'payment_id' => $payment->id,
                'method' => $payment->method,
            ]);
        });
    }

    /**
     * Refund pembayaran.
     */
    public function refundPayment(Payment $payment, string $reason = 'Order dibatalkan'): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $payment->update([
                'status' => 'refunded',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refund_reason' => $reason,
                    'refunded_at' => now()->toIso8601String(),
                ]),
            ]);

            $order = $payment->order;
            $order->update([
                'payment_status' => 'refunded',
            ]);

            Log::info("Payment refunded for order #{$order->code}", [
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Hitung total pendapatan partner dari order tertentu.
     */
    public function getPartnerTotalEarning(int $partnerId): float
    {
        return (float) DB::table('orders')
            ->where('partner_id', $partnerId)
            ->where('payment_status', 'paid')
            ->sum('partner_earning');
    }

    /**
     * Hitung total komisi platform dari order tertentu.
     */
    public function getPlatformTotalCommission(): float
    {
        return (float) DB::table('orders')
            ->where('payment_status', 'paid')
            ->sum('platform_commission');
    }
}
