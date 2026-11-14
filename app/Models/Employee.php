<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'department_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'gender',
        'date_of_birth',
        'hire_date',
        'employment_status',
        'basic_salary',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Leave relationships
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Attendance
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Payroll
    public function payrollCalculations()
    {
        return $this->hasMany(PayrollCalculation::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    // Loans
    public function loans()
    {
        return $this->hasMany(EmployeeLoan::class);
    }

    // Overtime
    public function overtimes()
    {
        return $this->hasMany(EmployeeOvertime::class);
    }

    // Adjustments
    public function adjustments()
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->employment_status) {
            'Active' => 'success',
            'Inactive' => 'secondary',
            'Terminated' => 'danger',
            'On Leave' => 'warning',
            default => 'secondary'
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('employment_status', 'Active');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}