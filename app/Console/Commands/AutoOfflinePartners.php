<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoOfflinePartners extends Command
{
    protected $signature = 'montirgo:auto-offline-partners
                            {--threshold=10 : Menit tanpa GPS update sebelum partner ditandai offline}
                            {--dry-run : Tampilkan data tanpa mengubah}';

    protected $description = 'Tandai partner sebagai offline jika tidak ada GPS update selama threshold menit';

    public function handle(): int
    {
        $thresholdMinutes = (int) $this->option('threshold');
        $dryRun = $this->option('dry-run');

        // Cari partner yang online tapi tidak ada GPS update melebihi threshold
        $stalePartners = Partner::where('partner_status', 'online')
            ->where('is_online', true)
            ->where(function ($query) use ($thresholdMinutes) {
                // last_active_at NULL atau sudah lebih dari threshold menit
                $query->whereNull('last_active_at')
                    ->orWhere('last_active_at', '<', now()->subMinutes($thresholdMinutes));
            })
            ->get();

        if ($stalePartners->isEmpty()) {
            $this->info('Tidak ada partner yang perlu di-offline-kan.');

            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$stalePartners->count()} partner tanpa GPS update > {$thresholdMinutes} menit:");

        foreach ($stalePartners as $partner) {
            $lastActive = $partner->last_active_at
                ? $partner->last_active_at->diffForHumans()
                : 'Tidak pernah aktif';

            $this->line("  - {$partner->workshop_name} (ID: {$partner->id}) | Terakhir aktif: {$lastActive}");

            if (! $dryRun) {
                $partner->update([
                    'partner_status' => 'offline',
                    'is_online' => false,
                ]);

                Log::info("Auto-offline: Partner {$partner->workshop_name} (ID: {$partner->id}) ditandai offline — tidak ada GPS update selama {$thresholdMinutes} menit", [
                    'partner_id' => $partner->id,
                    'last_active_at' => $partner->last_active_at?->toIso8601String(),
                ]);
            }
        }

        if ($dryRun) {
            $this->warn('DRY RUN — Tidak ada perubahan yang dilakukan.');
        } else {
            $this->info("Berhasil menandai {$stalePartners->count()} partner sebagai offline.");
        }

        return Command::SUCCESS;
    }
}
