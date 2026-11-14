<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'entitled_days',
        'used_days',
        'carried_over_days',
        'remaining_days',
        'last_updated',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entitled_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'carried_over_days' => 'decimal:1',
        'remaining_days' => 'decimal:1',
        'last_updated' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getStats()
    {
        return [
            'total_employees' => Employee::where('is_active', true)->where('employment_status', 'Active')->count(),
            'total_balances' => self::count(),
            'current_year' => date('Y'),
        ];
    }
}