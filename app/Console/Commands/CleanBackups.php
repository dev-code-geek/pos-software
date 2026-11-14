<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanBackups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:clean {--keep=30 : Number of backups to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old database backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning old backups...');
        
        $keep = $this->option('keep');
        $backupPath = storage_path('app/backups');
        
        if (!File::exists($backupPath)) {
            $this->info('No backups directory found.');
            return 0;
        }

        $files = File::files($backupPath);
        $backupFiles = [];
        
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (strpos($filename, 'backup_') === 0 && strpos($filename, '.sql') !== false) {
                $backupFiles[] = [
                    'path' => $file->getRealPath(),
                    'name' => $filename,
                    'time' => $file->getMTime()
                ];
            }
        }

        // Sort by time descending (newest first)
        usort($backupFiles, function($a, $b) {
            return $b['time'] - $a['time'];
        });

        $total = count($backupFiles);
        $toDelete = array_slice($backupFiles, $keep);
        $deleted = count($toDelete);

        foreach ($toDelete as $file) {
            File::delete($file['path']);
            $this->line("Deleted: {$file['name']}");
        }

        $this->info("✓ Cleaned {$deleted} old backup(s). Kept {$keep} of {$total}.");
        
        return 0;
    }
}