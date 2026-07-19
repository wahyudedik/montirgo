<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanStaleFcmTokens extends Command
{
    protected $signature = 'montirgo:clean-stale-tokens
                            {--days=30 : Hapus token user yang tidak aktif selama N hari}
                            {--dry-run : Tampilkan jumlah data tanpa menghapus}';

    protected $description = 'Hapus FCM token user yang sudah tidak aktif untuk menjaga kualitas push notification';

    public function handle(NotificationService $notificationService): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info("DRY RUN: Membersihkan FCM token user yang tidak aktif selama {$days}+ hari...");

            $count = User::whereNotNull('fcm_token')
                ->where(function ($query) use ($days) {
                    $query->whereNull('last_active_at')
                        ->orWhere('last_active_at', '<', now()->subDays($days));
                })
                ->count();

            $this->info("{$count} token akan dibersihkan.");

            return Command::SUCCESS;
        }

        $cleaned = $notificationService->cleanStaleTokens($days);

        $this->info("Berhasil membersihkan {$cleaned} FCM token yang sudah stale (>{$days} hari tidak aktif).");

        return Command::SUCCESS;
    }
}
