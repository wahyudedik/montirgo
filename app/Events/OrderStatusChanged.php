<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast saat status order berubah.
 * Terkirim ke channel: order.{orderId}
 */
class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {}

    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("order.{$this->order->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->code,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'status_label' => $this->order->status_label,
            'status_color' => $this->order->status_color,
            'partner_id' => $this->order->partner_id,
            'partner_name' => $this->order->partner?->workshop_name,
            'updated_at' => now()->toISOString(),
        ];
    }
}
