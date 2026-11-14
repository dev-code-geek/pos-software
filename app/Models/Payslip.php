<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period_id',
        'payroll_id',
        'payslip_number',
        'issue_date',
        'pdf_file_path',
        'file_size',
        'is_downloaded',
        'downloaded_at',
        'emailed',
        'emailed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'file_size' => 'integer',
        'is_downloaded' => 'boolean',
        'emailed' => 'boolean',
        'downloaded_at' => 'datetime',
        'emailed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function payroll()
    {
        return $this->belongsTo(PayrollCalculation::class, 'payroll_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function hasPdf()
    {
        return !empty($this->pdf_file_path) && \Storage::disk('public')->exists($this->pdf_file_path);
    }
}