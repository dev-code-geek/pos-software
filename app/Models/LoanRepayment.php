<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'payroll_id',
        'amount_deducted',
        'deduction_date',
        'remaining_balance',
    ];

    protected $casts = [
        'amount_deducted' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'deduction_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(EmployeeLoan::class);
    }

    public function payroll()
    {
        return $this->belongsTo(PayrollCalculation::class, 'payroll_id');
    }
}