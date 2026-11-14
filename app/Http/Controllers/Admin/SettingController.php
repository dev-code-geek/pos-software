<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Settings hub page (the card grid).
     */
    public function index()
    {
        if (!auth()->user()->can('settings.view')) {
            abort(403, 'You do not have permission to view settings.');
        }

        // Get backup status
        $backupStatus = $this->getBackupStatus();

        return view('admin.settings.index', compact('backupStatus'));
    }

    // ============================================
    // GENERAL SETTINGS
    // ============================================
    public function general()
    {
        if (!auth()->user()->can('settings.edit')) {
            abort(403, 'You do not have permission to edit settings.');
        }

        $settings = Setting::getAll();

        $defaults = [
            'app_name' => config('app.name', 'ICT Inventory'),
            'timezone' => config('app.timezone', 'UTC'),
            'locale' => config('app.locale', 'en'),
            'currency' => 'USD',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'items_per_page' => 15,
            'enable_registration' => false,
            'maintenance_mode' => false,
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }

        $timezones = \DateTimeZone::listIdentifiers();
        $locales = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'sw' => 'Swahili'];

        return view('admin.settings.general', compact('settings', 'timezones', 'locales'));
    }

    public function updateGeneral(Request $request)
    {
        if (!auth()->user()->can('settings.edit')) {
            abort(403, 'You do not have permission to edit settings.');
        }

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'currency' => 'required|string|max:10',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'items_per_page' => 'required|integer|min:5|max:100',
            'enable_registration' => 'nullable|boolean',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        // Log activity
        $this->logActivity('updated', 'settings', 'General settings updated');

        return redirect()
            ->route('admin.settings.general')
            ->with('success', 'General settings updated successfully.');
    }

    // ============================================
    // BACKUP
    // ============================================
    public function backup()
    {
        if (!auth()->user()->can('settings.backup') && !auth()->user()->can('backup.view')) {
            abort(403, 'You do not have permission to manage backups.');
        }

        $backups = [];
        $backupPath = storage_path('app/backups');

        if (File::exists($backupPath)) {
            $files = File::files($backupPath);
            foreach ($files as $file) {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                    'path' => $file->getRealPath(),
                    'timestamp' => $file->getMTime(),
                ];
            }
            // Sort by date descending
            usort($backups, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
        }

        $backupStats = [
            'total' => count($backups),
            'total_size' => $this->formatBytes(array_sum(array_map(function($b) {
                return File::size($b['path']);
            }, $backups))),
            'last_backup' => count($backups) > 0 ? $backups[0]['date'] : 'Never',
        ];

        $backupSchedule = Setting::get('backup_frequency', 'daily');
        $backupTime = Setting::get('backup_time', '00:00');
        $backupKeepDays = Setting::get('backup_keep_days', 30);

        return view('admin.settings.backup', compact('backups', 'backupStats', 'backupSchedule', 'backupTime', 'backupKeepDays'));
    }

    public function runBackup(Request $request)
    {
        if (!auth()->user()->can('settings.backup_create') && !auth()->user()->can('backup.create')) {
            return response()->json(['error' => 'You do not have permission to create backups.'], 403);
        }

        try {
            $result = $this->createBackup();
            
            if ($request->ajax()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return redirect()
                    ->route('admin.settings.backup')
                    ->with('success', $result['message']);
            } else {
                return redirect()
                    ->route('admin.settings.backup')
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()
                ->route('admin.settings.backup')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function createBackup()
    {
        try {
            $backupPath = storage_path('app/backups');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = "backup_{$timestamp}.sql";

            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --add-drop-table --routines --triggers --single-transaction %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($backupPath . '/' . $backupFile)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $this->logActivity('backup', 'settings', "Database backup created: {$backupFile}");
                $this->cleanOldBackups(Setting::get('backup_keep_days', 30));
                
                return [
                    'success' => true,
                    'message' => "Backup created successfully: {$backupFile}",
                    'file' => $backupFile
                ];
            } else {
                // Fallback to Laravel schema dump
                Artisan::call('schema:dump');
                $this->logActivity('backup', 'settings', 'Backup created using Laravel schema dump');
                
                return [
                    'success' => true,
                    'message' => 'Backup created using Laravel schema dump.',
                    'file' => 'schema_dump'
                ];
            }
        } catch (\Exception $e) {
            $this->logActivity('backup_failed', 'settings', 'Backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }

    private function cleanOldBackups($keep = 30)
    {
        try {
            $backupPath = storage_path('app/backups');
            if (!File::exists($backupPath)) return;

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

            usort($backupFiles, function($a, $b) {
                return $b['time'] - $a['time'];
            });

            $toDelete = array_slice($backupFiles, $keep);
            foreach ($toDelete as $file) {
                File::delete($file['path']);
            }
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    public function downloadBackup($filename)
    {
        if (!auth()->user()->can('settings.backup_download') && !auth()->user()->can('backup.download')) {
            abort(403, 'You do not have permission to download backups.');
        }

        $backupPath = storage_path('app/backups/' . $filename);
        
        if (!File::exists($backupPath)) {
            return redirect()
                ->route('admin.settings.backup')
                ->with('error', 'Backup file not found.');
        }

        $this->logActivity('downloaded', 'settings', "Backup downloaded: {$filename}");

        return response()->download($backupPath, $filename);
    }

    public function deleteBackup($filename)
    {
        if (!auth()->user()->can('settings.backup_delete') && !auth()->user()->can('backup.delete')) {
            return response()->json(['error' => 'You do not have permission to delete backups.'], 403);
        }

        $backupPath = storage_path('app/backups/' . $filename);
        
        if (File::exists($backupPath)) {
            File::delete($backupPath);
            $this->logActivity('deleted', 'settings', "Backup deleted: {$filename}");
            return redirect()
                ->route('admin.settings.backup')
                ->with('success', "Backup {$filename} deleted successfully.");
        }

        return redirect()
            ->route('admin.settings.backup')
            ->with('error', 'Backup file not found.');
    }

    public function updateBackupSchedule(Request $request)
    {
        if (!auth()->user()->can('settings.backup_schedule') && !auth()->user()->can('backup.schedule')) {
            return response()->json(['error' => 'You do not have permission to schedule backups.'], 403);
        }

        $validated = $request->validate([
            'frequency' => 'required|in:daily,weekly,monthly',
            'time' => 'required|date_format:H:i',
            'keep_days' => 'required|integer|min:1|max:365',
        ]);

        Setting::set('backup_frequency', $validated['frequency']);
        Setting::set('backup_time', $validated['time']);
        Setting::set('backup_keep_days', $validated['keep_days']);

        $this->logActivity('updated', 'settings', 'Backup schedule updated');

        return redirect()
            ->route('admin.settings.backup')
            ->with('success', 'Backup schedule updated successfully.');
    }

    private function getBackupStatus()
    {
        $backupPath = storage_path('app/backups');
        $lastBackup = null;
        $backupCount = 0;

        if (File::exists($backupPath)) {
            $files = File::files($backupPath);
            $backupCount = count($files);
            
            foreach ($files as $file) {
                if (!$lastBackup || $file->getMTime() > $lastBackup['time']) {
                    $lastBackup = [
                        'name' => $file->getFilename(),
                        'time' => $file->getMTime(),
                        'size' => $file->getSize(),
                    ];
                }
            }
        }

        return [
            'exists' => $backupCount > 0,
            'count' => $backupCount,
            'last_backup' => $lastBackup ? date('Y-m-d H:i:s', $lastBackup['time']) : null,
            'last_backup_hours' => $lastBackup ? round((time() - $lastBackup['time']) / 3600, 1) : null,
            'last_backup_file' => $lastBackup ? $lastBackup['name'] : null,
        ];
    }

    // ============================================
    // SYSTEM LOGS
    // ============================================
    public function logs(Request $request)
    {
        if (!auth()->user()->can('settings.view')) {
            abort(403, 'You do not have permission to view logs.');
        }

        $logPath = storage_path('logs/laravel.log');
        $lines = [];
        $exists = File::exists($logPath);

        if ($exists) {
            $limit = $request->limit ?? 500;
            $handle = fopen($logPath, 'r');
            $buffer = [];
            $maxLines = min($limit, 5000);

            while (($line = fgets($handle)) !== false) {
                $buffer[] = $line;
                if (count($buffer) > $maxLines) {
                    array_shift($buffer);
                }
            }
            fclose($handle);

            $lines = array_reverse($buffer);
        }

        $logInfo = [
            'exists' => $exists,
            'path' => $logPath,
            'size' => $exists ? $this->formatBytes(File::size($logPath)) : 'N/A',
            'modified' => $exists ? date('Y-m-d H:i:s', File::lastModified($logPath)) : 'N/A',
        ];

        $errorCount = 0;
        $errorLines = [];
        foreach ($lines as $line) {
            if (str_contains($line, 'ERROR') || str_contains($line, 'error')) {
                $errorCount++;
                if (count($errorLines) < 10) {
                    $errorLines[] = $line;
                }
            }
        }

        return view('admin.settings.logs', compact('lines', 'logInfo', 'errorCount', 'errorLines'));
    }

    public function clearLogs()
    {
        if (!auth()->user()->can('settings.manage')) {
            abort(403, 'You do not have permission to clear logs.');
        }

        $logPath = storage_path('logs/laravel.log');
        
        if (File::exists($logPath)) {
            File::put($logPath, '');
            $this->logActivity('cleared', 'settings', 'System logs cleared');
            return redirect()
                ->route('admin.settings.logs')
                ->with('success', 'Logs cleared successfully.');
        }

        return redirect()
            ->route('admin.settings.logs')
            ->with('error', 'Log file not found.');
    }

    public function downloadLogs()
    {
        if (!auth()->user()->can('settings.view')) {
            abort(403, 'You do not have permission to download logs.');
        }

        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            return redirect()
                ->route('admin.settings.logs')
                ->with('error', 'Log file not found.');
        }

        return response()->download($logPath, 'laravel-' . date('Y-m-d') . '.log');
    }

    // ============================================
    // EMAIL SETTINGS
    // ============================================
    public function email()
    {
        if (!auth()->user()->can('settings.edit')) {
            abort(403, 'You do not have permission to edit email settings.');
        }

        $settings = Setting::getAll();

        $defaults = [
            'mail_mailer' => config('mail.default', 'smtp'),
            'mail_host' => config('mail.mailers.smtp.host', 'smtp.mailtrap.io'),
            'mail_port' => config('mail.mailers.smtp.port', 2525),
            'mail_from_address' => config('mail.from.address', 'noreply@example.com'),
            'mail_from_name' => config('mail.from.name', config('app.name')),
            'mail_encryption' => config('mail.mailers.smtp.encryption', 'tls'),
            'mail_username' => config('mail.mailers.smtp.username', ''),
            'mail_password' => config('mail.mailers.smtp.password', ''),
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }

        $mailers = ['smtp', 'sendmail', 'mailgun', 'ses', 'postmark', 'log', 'array'];
        $encryptions = ['tls', 'ssl', null];

        return view('admin.settings.email', compact('settings', 'mailers', 'encryptions'));
    }

    public function updateEmail(Request $request)
    {
        if (!auth()->user()->can('settings.edit')) {
            abort(403, 'You do not have permission to edit email settings.');
        }

        $validated = $request->validate([
            'mail_mailer' => 'required|string|max:50',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|numeric|min:1|max:65535',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_encryption' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
        ]);

        // Only update password if provided
        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        $this->logActivity('updated', 'settings', 'Email settings updated');

        return redirect()
            ->route('admin.settings.email')
            ->with('success', 'Email settings updated successfully.');
    }

    public function testEmail(Request $request)
    {
        if (!auth()->user()->can('settings.edit')) {
            return response()->json(['error' => 'You do not have permission to test email.'], 403);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            \Mail::raw('This is a test email from your ICT Inventory System.', function($message) use ($request) {
                $message->to($request->email)
                        ->subject('Test Email from ICT Inventory');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $request->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================
    // SECURITY
    // ============================================
    public function security()
    {
        if (!auth()->user()->can('settings.edit')) {
            abort(403, 'You do not have permission to edit security settings.');
        }

        $settings = Setting::getAll();

        $defaults = [
            'session_lifetime' => config('session.lifetime', 120),
            'password_min_length' => 8,
            'require_2fa' => false,
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'session_expire_on_close' => false,
            'password_expiry_days' => 90,
            'remember_me_days' => 30,
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }

        return view('admin.settings.security', compact('settings'));
    }

    public function updateSecurity(Request $request)
    {
        if (!auth()->user()->can('settings.edit')) {
            abort(403, 'You do not have permission to edit security settings.');
        }

        $validated = $request->validate([
            'session_lifetime' => 'required|integer|min:5|max:1440',
            'password_min_length' => 'required|integer|min:6|max:64',
            'require_2fa' => 'nullable|boolean',
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration' => 'required|integer|min:1|max:60',
            'session_expire_on_close' => 'nullable|boolean',
            'password_expiry_days' => 'required|integer|min:0|max:365',
            'remember_me_days' => 'required|integer|min:0|max:365',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        $this->logActivity('updated', 'settings', 'Security settings updated');

        return redirect()
            ->route('admin.settings.security')
            ->with('success', 'Security settings updated successfully.');
    }

    // ============================================
    // SYSTEM INFO
    // ============================================
    public function systemInfo()
    {
        if (!auth()->user()->can('settings.view')) {
            abort(403, 'You do not have permission to view system information.');
        }

        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'timezone' => config('app.timezone'),
            'default_db' => config('database.default'),
            'db_connection' => config('database.connections.mysql.driver'),
            'db_host' => config('database.connections.mysql.host'),
            'db_database' => config('database.connections.mysql.database'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'disk_free_space' => $this->formatBytes(disk_free_space(base_path())),
            'disk_total_space' => $this->formatBytes(disk_total_space(base_path())),
            'storage_used' => $this->formatBytes(disk_total_space(base_path()) - disk_free_space(base_path())),
            'php_extensions' => implode(', ', get_loaded_extensions()),
        ];

        try {
            $tables = DB::select('SHOW TABLES');
            $info['db_tables'] = count($tables);
        } catch (\Exception $e) {
            $info['db_tables'] = 'N/A';
        }

        return view('admin.settings.system-info', compact('info'));
    }

    // ============================================
    // CACHE MANAGEMENT
    // ============================================
    public function cache()
    {
        if (!auth()->user()->can('settings.manage')) {
            abort(403, 'You do not have permission to manage cache.');
        }

        $cacheInfo = [
            'config_cached' => app()->configurationIsCached(),
            'routes_cached' => app()->routesAreCached(),
            'views_cached' => File::exists(base_path('bootstrap/cache/views.php')),
            'cache_driver' => config('cache.default'),
        ];

        return view('admin.settings.cache', compact('cacheInfo'));
    }

    public function clearCache(Request $request)
    {
        if (!auth()->user()->can('settings.manage')) {
            abort(403, 'You do not have permission to clear cache.');
        }

        $type = $request->type;

        try {
            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    $message = 'Configuration cache cleared successfully!';
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    $message = 'Route cache cleared successfully!';
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    $message = 'View cache cleared successfully!';
                    break;
                case 'all':
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('optimize:clear');
                    $message = 'All caches cleared successfully!';
                    break;
                default:
                    return redirect()
                        ->route('admin.settings.cache')
                        ->with('error', 'Invalid cache type.');
            }

            $this->logActivity('cleared', 'settings', "{$type} cache cleared");

            return redirect()
                ->route('admin.settings.cache')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.settings.cache')
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    // ============================================
    // MAINTENANCE MODE
    // ============================================
    public function maintenance()
    {
        if (!auth()->user()->can('settings.manage')) {
            abort(403, 'You do not have permission to manage maintenance mode.');
        }

        $inMaintenance = app()->isDownForMaintenance();
        $secret = Setting::get('maintenance_secret');

        return view('admin.settings.maintenance', compact('inMaintenance', 'secret'));
    }

    public function toggleMaintenance(Request $request)
    {
        if (!auth()->user()->can('settings.manage')) {
            abort(403, 'You do not have permission to toggle maintenance mode.');
        }

        $request->validate([
            'secret' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:500',
        ]);

        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                $status = 'disabled';
                Setting::set('maintenance_mode', false);
            } else {
                $options = [];
                if ($request->secret) {
                    $options['--secret'] = $request->secret;
                    Setting::set('maintenance_secret', $request->secret);
                }
                Artisan::call('down', $options);
                $status = 'enabled';
                Setting::set('maintenance_mode', true);
            }

            $this->logActivity('maintenance', 'settings', "Maintenance mode {$status}");

            return redirect()
                ->route('admin.settings.maintenance')
                ->with('success', "Maintenance mode {$status} successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.settings.maintenance')
                ->with('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }

    // ============================================
    // HELPER METHODS
    // ============================================
    private function formatBytes($bytes, $precision = 2)
    {
        if (!is_numeric($bytes) || $bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function logActivity($action, $module, $description)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'System',
                'user_email' => auth()->user()->email ?? 'system@example.com',
                'user_role' => auth()->user()->roles->first()->name ?? 'admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'status' => 'success',
                'performed_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silent fail for logging
        }
    }
}