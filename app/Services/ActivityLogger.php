<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(string $action, string $module, string $description, array $extra = []): ActivityLog
    {
        $user = auth()->user();

        return ActivityLog::create(array_merge([
            'user_id'      => $user?->id,
            'user_name'    => $user?->name ?? 'System',
            'user_email'   => $user?->email,
            'user_role'    => $user?->roles->first()?->name,
            'action'       => $action,
            'module'       => $module,
            'description'  => $description,
            'status'       => 'success',
            'ip_address'   => request()->ip(),
            'url'          => request()->fullUrl(),
            'method'       => request()->method(),
            'user_agent'   => request()->userAgent(),
            'performed_at' => now(),
        ], $extra));
    }

    public static function logModelChange(string $action, Model $model, string $module, ?array $old = null): ActivityLog
    {
        return self::log($action, $module, ucfirst($action) . " {$module} #{$model->getKey()}", [
            'old_data' => $old ? json_encode($old) : null,
            'new_data' => json_encode($model->getAttributes()),
        ]);
    }
}