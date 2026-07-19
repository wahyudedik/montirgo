<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\NotificationLog;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    protected $signature = 'montirgo:cleanup-notifications
                            {--days=30 : Hapus notifikasi lebih lama dari N hari}
                            {--dry-run : Tampilkan jumlah data tanpa menghapus}';

    protected $description = 'Hapus notifikasi log yang sudah tua untuk menjaga performa database';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $cutoffDate = now()->subDays($days);
        $count = NotificationLog::where('created_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info("Tidak ada notifikasi yang perlu dihapus (lebih lama dari {$days} hari).");

            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->info("DRY RUN: {$count} notifikasi akan dihapus (sebelum {$cutoffDate->format('Y-m-d')}).");

            return Command::SUCCESS;
        }

        $deleted = NotificationLog::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Berhasil menghapus {$deleted} notifikasi log (lebih lama dari {$days} hari).");

        return Command::SUCCESS;
    }
}
