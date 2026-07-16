<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Partner;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast saat partner memperbarui lokasi real-time.
 * Terkirim ke channel: order.{orderId}
 */
class PartnerLocationUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly Partner $partner,
        public readonly string $lat,
        public readonly string $lng,
        public readonly float $distanceKm,
        public readonly string $distanceFormatted,
        public readonly array $eta,
    ) {}

    public function broadcastAs(): string
    {
        return 'partner.location.updated';
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("order.{$this->orderId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'partner_id' => $this->partner->id,
            'partner_name' => $this->partner->workshop_name,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'distance_km' => $this->distanceKm,
            'distance_formatted' => $this->distanceFormatted,
            'eta' => $this->eta,
            'timestamp' => now()->timestamp,
        ];
    }
}
