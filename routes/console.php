<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-offline partner yang tidak ada GPS update selama 10 menit
Schedule::command('montirgo:auto-offline-partners --threshold=10')->everyFiveMinutes();
