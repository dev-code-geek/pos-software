<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period_id',
        'basic_salary',
        'transport_allowance',
        'housing_allowance',
        'food_allowance',
        'medical_allowance',
        'other_allowances',
        'overtime_amount',
        'total_earnings',
        'paye_tax',
        'nssf_employee',
        'loan_deductions',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_reference',
        'payment_method',
        'is_calculated',
        'calculated_by',
        'calculated_at',
        'verified_by',
        'verified_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'food_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'paye_tax' => 'decimal:2',
        'nssf_employee' => 'decimal:2',
        'loan_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
        'is_calculated' => 'boolean',
        'calculated_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    const PAYMENT_PENDING = 'Pending';
    const PAYMENT_VERIFIED = 'Verified';
    const PAYMENT_PAID = 'Paid';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class, 'payroll_id');
    }

    public function loanRepayments()
    {
        return $this->hasMany(LoanRepayment::class, 'payroll_id');
    }

    public function calculatedBy()
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isPending()
    {
        return $this->payment_status === self::PAYMENT_PENDING;
    }

    public function isVerified()
    {
        return $this->payment_status === self::PAYMENT_VERIFIED;
    }

    public function isPaid()
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', self::PAYMENT_PENDING);
    }

    public function scopeVerified($query)
    {
        return $query->where('payment_status', self::PAYMENT_VERIFIED);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }
    
}