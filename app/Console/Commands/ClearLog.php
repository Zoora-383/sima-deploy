<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('logs:clear')]
#[Description('Membersihkan isi file laravel.log')]
class ClearLog extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            // Menggunakan fungsi bawaan PHP (lebih aman dibanding exec)
            file_put_contents($logPath, '');
            $this->info('Logs have been cleared successfully!');
        } else {
            $this->error('Log file tidak ditemukan.');
        }
    }
}