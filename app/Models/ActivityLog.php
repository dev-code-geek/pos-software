<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'ip_address',
        'user_agent',
        'action',
        'module',
        'description',
        'old_data',
        'new_data',
        'url',
        'method',
        'status',
        'duration',
        'performed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include activities of a specific module.
     */
    public function scopeModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope a query to only include activities of a specific action.
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include activities of a specific user.
     */
    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include activities within a date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('performed_at', [$from, $to]);
    }

    /**
     * Get the activity type badge color.
     */
    public function getTypeColorAttribute()
    {
        $colors = [
            'login' => 'success',
            'logout' => 'secondary',
            'created' => 'primary',
            'updated' => 'warning',
            'deleted' => 'danger',
            'viewed' => 'info',
            'exported' => 'info',
            'imported' => 'info',
            'assigned' => 'primary',
            'returned' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'forwarded' => 'primary',
            'resolved' => 'success',
        ];

        return $colors[$this->action] ?? 'secondary';
    }

    /**
     * Get the activity icon.
     */
    public function getIconAttribute()
    {
        $icons = [
            'login' => 'fa-sign-in-alt',
            'logout' => 'fa-sign-out-alt',
            'created' => 'fa-plus-circle',
            'updated' => 'fa-edit',
            'deleted' => 'fa-trash',
            'viewed' => 'fa-eye',
            'exported' => 'fa-file-export',
            'imported' => 'fa-file-import',
            'assigned' => 'fa-hand-over',
            'returned' => 'fa-undo-alt',
            'approved' => 'fa-check-circle',
            'rejected' => 'fa-times-circle',
            'forwarded' => 'fa-arrow-right',
            'resolved' => 'fa-check',
        ];

        return $icons[$this->action] ?? 'fa-circle';
    }

    /**
     * Get formatted duration.
     */
    public function getDurationFormattedAttribute()
    {
        if (!$this->duration) {
            return 'N/A';
        }

        if ($this->duration < 60) {
            return round($this->duration, 2) . 's';
        }

        if ($this->duration < 3600) {
            return round($this->duration / 60, 2) . 'm';
        }

        return round($this->duration / 3600, 2) . 'h';
    }
}