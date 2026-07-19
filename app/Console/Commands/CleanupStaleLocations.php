<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LocationTrackingService;
use Illuminate\Console\Command;

class CleanupStaleLocations extends Command
{
    protected $signature = 'montirgo:cleanup-stale-locations
                            {--dry-run : Tampilkan jumlah data tanpa menghapus}';

    protected $description = 'Bersihkan data lokasi partner/user yang sudah stale (tidak update > 24 jam)';

    public function handle(LocationTrackingService $locationService): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            // Hitung lokasi stale tanpa menghapus
            $staleCount = $this->countStaleLocations();
            $this->info("DRY RUN: {$staleCount} lokasi stale akan dihapus.");

            return Command::SUCCESS;
        }

        $deleted = $locationService->clearStaleLocations();

        $this->info("Berhasil membersihkan {$deleted} data lokasi stale.");

        return Command::SUCCESS;
    }

    private function countStaleLocations(): int
    {
        $staleThreshold = now()->subHours(24)->timestamp;

        $count = 0;

        // Count partner locations
        $partnerLocations = cache()->get('partner_locations', []);
        foreach ($partnerLocations as $partnerId => $data) {
            if (isset($data['updated_at']) && $data['updated_at'] < $staleThreshold) {
                $count++;
            }
        }

        // Count user locations
        $userLocations = cache()->get('user_locations', []);
        foreach ($userLocations as $userId => $data) {
            if (isset($data['updated_at']) && $data['updated_at'] < $staleThreshold) {
                $count++;
            }
        }

        return $count;
    }
}
