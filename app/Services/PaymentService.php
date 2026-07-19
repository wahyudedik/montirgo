<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymentService
{
    /** Biaya panggilan tetap (callout fee) */
    private const CALLOUT_FEE = 30000.00;

    /** Minimum persentase komisi platform untuk service fee */
    private const MIN_COMMISSION_PERCENT = 5;

    /** Maximum persentase komisi platform untuk service fee */
    private const MAX_COMMISSION_PERCENT = 10;

    /**
     * Hitung rincian biaya order.
     *
     * @return array{callout_fee: float, service_fee: float, commission_percent: int, platform_commission: float, partner_earning: float, total_amount: float}
     */
    public function calculateFees(float $serviceFee): array
    {
        $calloutFee = self::CALLOUT_FEE;

        // Komisi platform 5-10% dari service fee
        $commissionPercent = self::MIN_COMMISSION_PERCENT;
        if ($serviceFee >= 500000) {
            $commissionPercent = self::MAX_COMMISSION_PERCENT;
        } elseif ($serviceFee >= 200000) {
            $commissionPercent = 7;
        }

        $platformCommission = $serviceFee * ($commissionPercent / 100);
        $partnerEarning = $serviceFee - $platformCommission;
        $totalAmount = $calloutFee + $serviceFee;

        return [
            'callout_fee' => $calloutFee,
            'service_fee' => $serviceFee,
            'commission_percent' => $commissionPercent,
            'platform_commission' => $platformCommission,
            'partner_earning' => $partnerEarning,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Dapatkan callout fee default.
     */
    public function getCalloutFee(): float
    {
        return self::CALLOUT_FEE;
    }

    /**
     * Proses pembayaran saat order selesai.
     * Dipanggil dari Partner/OrderController saat status = completed.
     */
    public function processCompletion(Order $order, float $serviceFee): Payment
    {
        return DB::transaction(function () use ($order, $serviceFee) {
            $fees = $this->calculateFees($serviceFee);

            // Update order dengan rincian biaya
            $order->update([
                'callout_fee' => $fees['callout_fee'],
                'service_fee' => $fees['service_fee'],
                'total_amount' => $fees['total_amount'],
                'platform_commission' => $fees['platform_commission'],
                'partner_earning' => $fees['partner_earning'],
                'payment_status' => 'pending',
            ]);

            // Buat record payment — semua pembayaran melalui payment gateway
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'amount' => $fees['total_amount'],
                'status' => 'pending',
                'metadata' => [
                    'callout_fee' => $fees['callout_fee'],
                    'service_fee' => $fees['service_fee'],
                    'commission_percent' => $fees['commission_percent'],
                    'platform_commission' => $fees['platform_commission'],
                    'partner_earning' => $fees['partner_earning'],
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
     * Buat payment record dan generate Midtrans Snap Token.
     *
     * Dipanggil dari OrderController saat customer membuat order baru.
     * Callout fee dibayar di awal, service fee ditambahkan saat order selesai.
     *
     * @return array{payment: Payment, token: string, redirect_url: string}
     */
    public function createGatewayPayment(Order $order): array
    {
        $midtransService = app(MidtransService::class);

        if (! $midtransService->isConfigured()) {
            throw new RuntimeException('Payment gateway belum terkonfigurasi. Silakan hubungi admin.', 500);
        }

        return DB::transaction(function () use ($order, $midtransService) {
            // Hitung callout fee
            $calloutFee = $this->getCalloutFee();

            // Update order
            $order->update([
                'callout_fee' => $calloutFee,
                'total_amount' => $calloutFee,
                'payment_status' => 'pending',
            ]);

            // Buat payment record untuk callout fee
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'amount' => $calloutFee,
                'status' => 'pending',
                'metadata' => [
                    'callout_fee' => $calloutFee,
                    'service_fee' => 0,
                    'payment_type' => 'callout_fee',
                    'commission_percent' => 0,
                    'platform_commission' => 0,
                    'partner_earning' => 0,
                ],
            ]);

            // Generate Midtrans Snap Token
            $result = $midtransService->createPaymentToken($order, $payment);

            Log::info("Gateway payment created for order #{$order->code}", [
                'payment_id' => $payment->id,
                'amount' => $calloutFee,
                'method' => $order->payment_method,
            ]);

            return [
                'payment' => $payment,
                'token' => $result['token'],
                'redirect_url' => $result['redirect_url'],
            ];
        });
    }

    /**
     * Proses service fee payment setelah order selesai.
     * Dipanggil saat partner menyelesaikan order dan input biaya servis.
     */
    public function processServiceFeePayment(Order $order, float $serviceFee): array
    {
        $midtransService = app(MidtransService::class);

        if (! $midtransService->isConfigured()) {
            throw new RuntimeException('Payment gateway belum terkonfigurasi.', 500);
        }

        return DB::transaction(function () use ($order, $serviceFee, $midtransService) {
            $fees = $this->calculateFees($serviceFee);

            // Update order dengan rincian biaya
            $order->update([
                'service_fee' => $fees['service_fee'],
                'total_amount' => $fees['total_amount'],
                'platform_commission' => $fees['platform_commission'],
                'partner_earning' => $fees['partner_earning'],
            ]);

            // Buat payment record untuk service fee
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'amount' => $fees['total_amount'],
                'status' => 'pending',
                'metadata' => [
                    'callout_fee' => $fees['callout_fee'],
                    'service_fee' => $fees['service_fee'],
                    'commission_percent' => $fees['commission_percent'],
                    'platform_commission' => $fees['platform_commission'],
                    'partner_earning' => $fees['partner_earning'],
                    'payment_type' => 'service_fee',
                ],
            ]);

            // Generate Midtrans Snap Token
            $result = $midtransService->createPaymentToken($order, $payment);

            Log::info("Service fee payment created for order #{$order->code}", [
                'payment_id' => $payment->id,
                'total_amount' => $fees['total_amount'],
                'service_fee' => $serviceFee,
            ]);

            return [
                'payment' => $payment,
                'token' => $result['token'],
                'redirect_url' => $result['redirect_url'],
            ];
        });
    }

    /**
     * Konfirmasi pembayaran setelah gateway callback.
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
            $partnerEarning = $payment->metadata['partner_earning'] ?? $order->partner_earning;
            if ($partnerEarning > 0 && $order->partner_id) {
                $walletService = app(WalletService::class);
                $walletService->creditPartnerEarning(
                    $order->partner_id,
                    $order->id,
                    (float) $partnerEarning,
                    "Pembayaran order #{$order->code}"
                );
            }

            Log::info("Payment confirmed for order #{$order->code}", [
                'payment_id' => $payment->id,
                'method' => $payment->method,
                'amount' => $payment->amount,
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
     * Handle Midtrans webhook notification.
     *
     * Memproses callback dari Midtrans dan update status payment accordingly.
     */
    public function handleWebhookNotification(array $notification): array
    {
        $midtransService = app(MidtransService::class);

        // Verifikasi signature
        if (! $midtransService->verifyCallbackSignature($notification)) {
            Log::warning('Midtrans webhook signature mismatch', [
                'order_id' => $notification['order_id'] ?? 'unknown',
            ]);
            throw new RuntimeException('Invalid webhook signature', 401);
        }

        $orderId = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'] ?? 'pending';
        $fraudStatus = $notification['fraud_status'] ?? null;

        // Cari order
        $order = Order::where('code', $orderId)->first();
        if (! $order) {
            throw new RuntimeException("Order #{$orderId} not found", 404);
        }

        // Map status Midtrans ke internal status
        $internalStatus = $midtransService->mapTransactionStatus($transactionStatus, $fraudStatus);

        // Cari payment record
        $payment = $order->payment;
        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'amount' => (float) $notification['gross_amount'] ?? 0,
                'status' => $internalStatus,
                'transaction_id' => $notification['transaction_id'] ?? null,
                'paid_at' => $internalStatus === 'paid' ? now() : null,
            ]);
        } else {
            $payment->update([
                'status' => $internalStatus,
                'transaction_id' => $notification['transaction_id'] ?? $payment->transaction_id,
                'paid_at' => $internalStatus === 'paid' ? now() : $payment->paid_at,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'midtrans_notification' => $notification,
                    'updated_at' => now()->toIso8601String(),
                ]),
            ]);
        }

        // Update order payment status
        $order->update([
            'payment_status' => $internalStatus,
            'paid_at' => $internalStatus === 'paid' ? now() : $order->paid_at,
        ]);

        // Jika pembayaran berhasil, credit partner wallet
        if ($internalStatus === 'paid') {
            $this->confirmPayment($payment->fresh());
        }

        Log::info("Midtrans webhook processed for order #{$orderId}", [
            'midtrans_status' => $transactionStatus,
            'internal_status' => $internalStatus,
            'fraud_status' => $fraudStatus,
        ]);

        return [
            'order_code' => $orderId,
            'status' => $internalStatus,
            'midtrans_status' => $transactionStatus,
        ];
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
