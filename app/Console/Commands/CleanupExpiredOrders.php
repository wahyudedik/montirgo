<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CleanupExpiredOrders extends Command
{
    protected $signature = 'montirgo:cleanup-expired-orders
                            {--minutes=60 : Timeout order dalam menit}
                            {--dry-run : Tampilkan jumlah data tanpa mengubah}';

    protected $description = 'Expire order pending/dispatching yang sudah terlalu lama tanpa respons partner';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $dryRun = $this->option('dry-run');

        $cutoffTime = now()->subMinutes($minutes);

        $expiredOrders = Order::whereIn('status', ['pending', 'dispatching'])
            ->where('created_at', '<', $cutoffTime)
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info("Tidak ada order expired (pending/dispatching lebih dari {$minutes} menit).");

            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->info("DRY RUN: {$expiredOrders->count()} order akan di-expire:");
            foreach ($expiredOrders as $order) {
                $this->line("  - #{$order->code} (status: {$order->status}, dibuat: {$order->created_at->format('Y-m-d H:i')})");
            }

            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($expiredOrders as $order) {
            $order->update([
                'status' => 'cancelled',
                'cancel_reason' => 'Order expired — tidak ada partner yang merespons dalam {$minutes} menit',
                'cancelled_at' => now(),
            ]);
            $count++;
        }

        $this->info("Berhasil expire {$count} order yang sudah timeout.");

        return Command::SUCCESS;
    }
}
