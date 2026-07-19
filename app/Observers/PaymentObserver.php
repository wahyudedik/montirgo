<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Payment;
use App\Services\NotificationService;
use App\Services\WalletService;

class PaymentObserver
{
    public function __construct(
        private WalletService $walletService,
        private NotificationService $notificationService,
    ) {}

    /**
     * Handle payment updated — credit partner wallet saat pembayaran dikonfirmasi.
     */
    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status') || $payment->status !== 'paid') {
            return;
        }

        $order = $payment->order;

        if (! $order || ! $order->partner) {
            return;
        }

        // Hitung earning partner (total - komisi platform)
        $totalAmount = (float) $payment->amount;
        $commissionRate = config('services.montirgo.additional_commission_rate', 0.10);
        $partnerEarning = $totalAmount * (1 - $commissionRate);

        // Credit partner wallet
        $this->walletService->creditPartnerEarning(
            $order->partner->id,
            $order->id,
            $partnerEarning,
            "Pembayaran order #{$order->code} diterima",
        );

        // Notify partner — in-app + push notification
        if ($order->partner->user) {
            $title = 'Pembayaran Diterima';
            $body = "Pembayaran untuk order #{$order->code} sebesar Rp".number_format($partnerEarning, 0, ',', '.').' telah masuk ke saldo.';
            $data = [
                'order_id' => $order->id,
                'amount' => $partnerEarning,
            ];

            $this->notificationService->sendInApp(
                $order->partner->user,
                $title,
                $body,
                $data,
                'payment',
            );

            $this->notificationService->sendFcm(
                $order->partner->user,
                $title,
                $body,
                array_merge($data, ['type' => 'payment']),
                'payment',
            );
        }

        // Notify customer bahwa pembayaran berhasil
        if ($order->user) {
            $this->notificationService->sendFcm(
                $order->user,
                'Pembayaran Berhasil',
                "Pembayaran untuk order #{$order->code} telah dikonfirmasi.",
                [
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'type' => 'payment_confirmed',
                ],
                'payment_confirmed',
            );
        }
    }
}
