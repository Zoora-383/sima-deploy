<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogClearCommand extends Command
{
    protected $signature = 'log:clear';
    protected $description = 'Clear all application log files';

    public function handle()
    {
        $logPath = storage_path('logs');

        // Find all .log files in the storage/logs folder
        $files = File::glob("$logPath/*.log");

        foreach ($files as $file) {
            File::put($file, ''); // Empties the file without breaking open permissions
        }

        $this->info('Application logs cleared successfully!');
    }
}
