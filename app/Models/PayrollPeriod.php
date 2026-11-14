<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'period_code',
        'period_name',
        'start_date',
        'end_date',
        'pay_date',
        'status',
        'is_locked',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pay_date' => 'date',
        'is_locked' => 'boolean',
    ];

    const STATUS_OPEN = 'Open';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_APPROVED = 'Approved';
    const STATUS_CLOSED = 'Closed';
    const STATUS_LOCKED = 'Locked';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Explicit foreign key ('period_id') is required here: Eloquent's default
    // guess for a hasMany on PayrollPeriod would be 'payroll_period_id', but
    // the actual column on payroll_calculations (and everywhere else in
    // PayrollController) is 'period_id'. Without this, calls like
    // $period->calculations()->count() throw:
    //   Unknown column 'payroll_calculations.payroll_period_id'
    public function calculations()
    {
        return $this->hasMany(PayrollCalculation::class, 'period_id');
    }

    // Same fix: the payslips table's actual column is 'period_id', not the
    // Eloquent-guessed 'payroll_period_id'.
    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'period_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isOpen()
    {
        return $this->status === self::STATUS_OPEN && !$this->is_locked;
    }

    public function isLocked()
    {
        return $this->is_locked || $this->status === self::STATUS_LOCKED;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN)->where('is_locked', false);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true)->orWhere('status', self::STATUS_LOCKED);
    }

}