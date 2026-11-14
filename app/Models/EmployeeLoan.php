<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'loan_number',
        'loan_type',
        'amount',
        'monthly_installment',
        'total_installments',
        'installments_paid',
        'remaining_balance',
        'disbursement_date',
        'deduction_start_date',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'disbursed_by',
        'disbursed_at',
        'suspended_by',
        'suspended_at',
        'suspension_reason',
        'resumed_by',
        'resumed_at',
        'completed_by',
        'completed_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'disbursement_date' => 'date',
        'deduction_start_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'suspended_at' => 'datetime',
        'resumed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const TYPE_SALARY_ADVANCE = 'Salary Advance';
    const TYPE_PERSONAL_LOAN = 'Personal Loan';
    const TYPE_EMERGENCY = 'Emergency';

    const STATUS_ACTIVE = 'Active';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_SUSPENDED = 'Suspended';
    const STATUS_REJECTED = 'Rejected';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function disbursedBy()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function suspendedBy()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function resumedBy()
    {
        return $this->belongsTo(User::class, 'resumed_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isSuspended()
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }
}