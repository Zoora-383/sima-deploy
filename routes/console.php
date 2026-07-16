<?php

use App\Console\Commands\CleanupExpiredSessions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Bersihkan sesi pengguna tidak aktif setiap hari pukul 00:00
Schedule::command(CleanupExpiredSessions::class, ['--hours=24'])
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// Bersihkan data SIMA yang kedaluwarsa secara berkala setiap hari pukul 01:00
Schedule::command('sima:clean-expired-data')
    ->daily()
    ->at('01:00')
    ->withoutOverlapping()
    ->runInBackground();

