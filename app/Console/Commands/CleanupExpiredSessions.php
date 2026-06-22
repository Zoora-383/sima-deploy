<?php

namespace App\Console\Commands;

use App\Models\UserSession;
use Illuminate\Console\Command;

class CleanupExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup
                            {--hours=24 : Hapus sesi yang tidak aktif lebih dari X jam}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus sesi pengguna yang sudah tidak aktif (stale sessions) dari database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        $deletedCount = UserSession::where('last_activity', '<', now()->subHours($hours))->delete();

        $this->info("✅ Berhasil menghapus {$deletedCount} sesi tidak aktif (lebih dari {$hours} jam).");

        return self::SUCCESS;
    }
}
