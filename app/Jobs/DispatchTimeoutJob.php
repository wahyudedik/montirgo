<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\DispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchTimeoutJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $orderId,
        public int $partnerId,
    ) {
        $this->onQueue('orders');
    }

    public function handle(DispatchService $dispatchService): void
    {
        $dispatchService->handleTimeout($this->orderId, $this->partnerId);
    }
}
