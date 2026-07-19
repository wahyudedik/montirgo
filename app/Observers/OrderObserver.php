<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Services\NotificationService;

class OrderObserver
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    /**
     * Handle order updated events — kirim notifikasi saat status berubah.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $statusLabel = $order->status_label;
        $newStatus = $order->status;

        // Notify customer
        if ($order->user) {
            $this->notificationService->notifyOrderStatus(
                $order->user,
                $order->code,
                $newStatus,
                $statusLabel,
            );
        }

        // Notify partner (jika order sudah assigned)
        if ($order->partner && $order->partner->user) {
            $this->notificationService->notifyOrderStatus(
                $order->partner->user,
                $order->code,
                $newStatus,
                $statusLabel,
            );
        }
    }
}
