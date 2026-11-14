<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\SettingController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create {--force : Force backup even if maintenance mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup';

    /**
     * Execute the console command.
     */
    public function handle(SettingController $controller)
    {
        $this->info('Starting database backup...');

        try {
            $result = $controller->createBackup();

            if ($result['success']) {
                $this->info('✓ ' . $result['message']);
                Log::info('Backup created successfully: ' . $result['file']);
                
                // Also log to activity logs
                \App\Models\ActivityLog::create([
                    'user_id' => null,
                    'user_name' => 'System',
                    'action' => 'backup',
                    'module' => 'settings',
                    'description' => "Automatic backup created: {$result['file']}",
                    'status' => 'success',
                    'performed_at' => now(),
                ]);
                
                return 0;
            } else {
                $this->error('✗ ' . $result['message']);
                Log::error('Backup failed: ' . $result['message']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Backup failed: ' . $e->getMessage());
            Log::error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }
}