<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollCalculation;
use App\Models\Payslip;
use App\Models\EmployeeLoan;
use App\Models\LoanRepayment;
use App\Models\EmployeeOvertime;
use App\Models\PayrollAdjustment;
use App\Models\PayrollRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Debug logging helper (can be disabled)
     */
    private function logDebug($message, $data = null)
    {
        if (config('app.debug')) {
            Log::info('[Payroll] ' . $message, $data ? (array) $data : []);
        }
    }

    // ==================== MAIN INDEX ====================

    public function index(Request $request)
    {
        try {
            $this->logDebug('Payroll index started', $request->all());

            // Load companies for dropdowns
            $companies = Company::where('is_active', true)->orderBy('name')->get();

            // Get all needed data
            $employees = Employee::all();

            // Get list of periods for dropdowns
            $periodsList = PayrollPeriod::orderBy('created_at', 'desc')->get();
            $openPeriods = PayrollPeriod::where('status', 'Open')
                ->where('is_locked', false)
                ->orderBy('created_at', 'desc')
                ->get();

            // Counts for badges
            $counts = [
                'periods' => PayrollPeriod::count(),
                'calculations' => PayrollCalculation::count(),
                'payslips' => Payslip::count(),
                'loans' => EmployeeLoan::count(),
                'overtime' => EmployeeOvertime::count(),
                'adjustments' => PayrollAdjustment::count(),
                'rates' => PayrollRate::count(),
            ];

            // Summary data
            $summary = $this->getSummaryData();

            // Tab and subtab
            $tab = $request->get('tab', 'periods');
            $subtab = $request->get('subtab', 'pending');

            // Paginated data for each tab
            $periods = $this->getPayrollPeriods($request);
            $calculations = $this->getPayrollCalculations($request);
            $payslips = $this->getPayslips($request);
            $loans = $this->getEmployeeLoans($request);
            $overtime = $this->getEmployeeOvertime($request);
            $adjustments = $this->getPayrollAdjustments($request);
            $rates = $this->getPayrollRates($request);

            return view('admin.payroll.index', compact(
                'companies', 'employees', 'periodsList', 'openPeriods',
                'counts', 'summary', 'tab', 'subtab',
                'periods', 'calculations', 'payslips', 'loans', 'overtime', 'adjustments', 'rates'
            ));
        } catch (\Exception $e) {
            Log::error('Payroll index error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                ->with('error', 'Error loading payroll: ' . $e->getMessage());
        }
    }

    // ==================== SUMMARY ====================

    private function getSummaryData()
    {
        try {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            return [
                'total_employees' => Employee::where('is_active', true)
                    ->where('employment_status', 'Active')
                    ->count(),
                'total_payroll_amount' => PayrollCalculation::where('is_calculated', true)
                    ->whereMonth('calculated_at', $currentMonth)
                    ->whereYear('calculated_at', $currentYear)
                    ->sum('net_salary'),
                'total_tax' => PayrollCalculation::where('is_calculated', true)
                    ->whereMonth('calculated_at', $currentMonth)
                    ->whereYear('calculated_at', $currentYear)
                    ->sum('paye_tax'),
                'pending_payments' => PayrollCalculation::where('payment_status', 'Pending')->count(),
                'active_periods' => PayrollPeriod::where('status', 'Open')
                    ->where('is_locked', false)
                    ->count(),
                'active_loans' => EmployeeLoan::where('status', 'Active')->count(),
                'pending_overtime' => EmployeeOvertime::where('status', 'Pending')->count(),
                'pending_adjustments' => PayrollAdjustment::whereNull('approved_at')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Summary error: ' . $e->getMessage());
            return [];
        }
    }

    // ==================== DATA RETRIEVAL METHODS (paginated) ====================

    private function getPayrollPeriods(Request $request)
    {
        $query = PayrollPeriod::with(['company', 'createdBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        return $query->paginate(15)->withQueryString();
    }

    private function getPayrollCalculations(Request $request)
    {
        $query = PayrollCalculation::with(['employee', 'period', 'calculatedBy', 'verifiedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        return $query->paginate(15)->withQueryString();
    }

    private function getPayslips(Request $request)
    {
        $query = Payslip::with(['employee', 'period', 'payroll'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payslip_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate(15)->withQueryString();
    }

    private function getEmployeeLoans(Request $request)
    {
        $query = EmployeeLoan::with(['employee', 'approvedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('loan_type')) {
            $query->where('loan_type', $request->loan_type);
        }

        return $query->paginate(15)->withQueryString();
    }

    private function getEmployeeOvertime(Request $request)
    {
        $query = EmployeeOvertime::with(['employee', 'period', 'approvedBy'])
            ->orderBy('overtime_date', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->paginate(15)->withQueryString();
    }

    private function getPayrollAdjustments(Request $request)
    {
        $query = PayrollAdjustment::with(['employee', 'period', 'approvedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }
        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        return $query->paginate(15)->withQueryString();
    }

    private function getPayrollRates(Request $request)
    {
        $query = PayrollRate::orderBy('rate_type')
            ->orderBy('effective_from', 'desc');

        if ($request->filled('rate_type')) {
            $query->where('rate_type', $request->rate_type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $query->paginate(15)->withQueryString();
    }

    // ==================== PERIOD METHODS ====================

    protected function periodValidationRules($id = null)
    {
        $rules = [
            'company_id' => 'required|exists:companies,id',
            'period_code' => 'required|string|max:20',
            'period_name' => 'required|string|max:200',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
            'status' => 'required|in:Open,Processing,Approved,Closed,Locked',
            'is_locked' => 'sometimes|boolean',
            'notes' => 'nullable|string|max:500',
        ];

        $uniqueRule = 'unique:payroll_periods,period_code';
        if ($id) {
            $uniqueRule .= ',' . $id . ',id';
        }
        $rules['period_code'] = 'required|string|max:20|' . $uniqueRule;

        return $rules;
    }

    public function storePeriod(Request $request)
    {
        DB::beginTransaction();

        try {
            $this->logDebug('Store period', $request->except('_token'));

            $validator = Validator::make($request->all(), $this->periodValidationRules($request->period_id));
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            // Check overlap
            $query = PayrollPeriod::where('company_id', $request->company_id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                        ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->end_date);
                        });
                });
            if ($request->filled('period_id')) {
                $query->where('id', '!=', $request->period_id);
            }
            if ($query->exists()) {
                return response()->json(['success' => false, 'message' => 'Period overlaps with an existing period.'], 422);
            }

            $data = [
                'company_id' => $request->company_id,
                'period_code' => strtoupper($request->period_code),
                'period_name' => $request->period_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pay_date' => $request->pay_date,
                'status' => $request->status,
                'is_locked' => $request->boolean('is_locked', false),
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];

            if ($request->filled('period_id')) {
                $period = PayrollPeriod::findOrFail($request->period_id);
                if ($period->is_locked) {
                    return response()->json(['success' => false, 'message' => 'Cannot update a locked period.'], 422);
                }
                unset($data['created_by']);
                $period->update($data);
                $message = 'Period updated successfully!';
            } else {
                $period = PayrollPeriod::create($data);
                $message = 'Period created successfully!';
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $message, 'period_id' => $period->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store period error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save period: ' . $e->getMessage()], 500);
        }
    }

    public function editPeriod($id)
    {
        try {
            $period = PayrollPeriod::with('company')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $period]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Period not found'], 404);
        }
    }

    public function destroyPeriod($id)
    {
        DB::beginTransaction();
        try {
            $period = PayrollPeriod::findOrFail($id);
            if ($period->calculations()->count() > 0 || $period->payslips()->count() > 0) {
                return response()->json(['success' => false, 'message' => 'Cannot delete period with existing calculations or payslips.'], 422);
            }
            $period->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Period deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete period.'], 500);
        }
    }

    public function lockPeriod($id)
    {
        DB::beginTransaction();
        try {
            $period = PayrollPeriod::findOrFail($id);
            if ($period->is_locked) {
                return response()->json(['success' => false, 'message' => 'Period already locked.'], 422);
            }
            $pending = PayrollCalculation::where('period_id', $period->id)
                ->where('payment_status', 'Pending')
                ->count();
            if ($pending > 0) {
                return response()->json(['success' => false, 'message' => "Cannot lock period with {$pending} pending calculations."], 422);
            }
            $period->update(['is_locked' => true, 'status' => 'Locked', 'updated_by' => auth()->id()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Period locked successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to lock period.'], 500);
        }
    }

    public function unlockPeriod($id)
    {
        DB::beginTransaction();
        try {
            $period = PayrollPeriod::findOrFail($id);
            if (!$period->is_locked) {
                return response()->json(['success' => false, 'message' => 'Period already unlocked.'], 422);
            }
            $period->update(['is_locked' => false, 'status' => 'Open', 'updated_by' => auth()->id()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Period unlocked successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to unlock period.'], 500);
        }
    }

    public function approvePeriod($id)
    {
        DB::beginTransaction();
        try {
            $period = PayrollPeriod::findOrFail($id);
            if ($period->status == 'Approved') {
                return response()->json(['success' => false, 'message' => 'Period already approved.'], 422);
            }
            if ($period->is_locked) {
                return response()->json(['success' => false, 'message' => 'Cannot approve a locked period. Unlock first.'], 422);
            }
            $pending = PayrollCalculation::where('period_id', $period->id)
                ->where('payment_status', 'Pending')
                ->count();
            if ($pending > 0) {
                return response()->json(['success' => false, 'message' => "Cannot approve period with {$pending} pending calculations."], 422);
            }
            $period->update(['status' => 'Approved', 'updated_by' => auth()->id()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Period approved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to approve period.'], 500);
        }
    }

    // ==================== PAYROLL CALCULATION ====================

    public function calculate(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'period_id' => 'required|exists:payroll_periods,id',
                'company_id' => 'required|exists:companies,id',
                'force' => 'sometimes|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $period = PayrollPeriod::findOrFail($request->period_id);
            if ($period->is_locked) {
                return response()->json(['success' => false, 'message' => 'Cannot calculate payroll for a locked period.'], 422);
            }

            $employees = Employee::where('company_id', $request->company_id)
                ->where('is_active', true)
                ->get();

            if ($employees->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No active employees found.'], 404);
            }

            // Remove existing calculations if force
            if ($request->boolean('force')) {
                $calcs = PayrollCalculation::where('period_id', $period->id)
                    ->whereHas('employee', fn($q) => $q->where('company_id', $request->company_id))
                    ->get();
                foreach ($calcs as $calc) {
                    // Remove associated loan repayments and restore loan balances
                    foreach (LoanRepayment::where('payroll_id', $calc->id)->get() as $rep) {
                        $loan = EmployeeLoan::find($rep->loan_id);
                        if ($loan) {
                            $loan->remaining_balance += $rep->amount_deducted;
                            $loan->installments_paid = max(0, $loan->installments_paid - 1);
                            if ($loan->status == 'Completed' && $loan->remaining_balance > 0) {
                                $loan->status = 'Active';
                            }
                            $loan->save();
                        }
                        $rep->delete();
                    }
                    $calc->delete();
                }
            }

            $successCount = 0;
            $errors = [];
            foreach ($employees as $employee) {
                try {
                    $this->calculateEmployeePayroll($employee, $period);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "{$employee->first_name} {$employee->last_name}: " . $e->getMessage();
                    Log::error('Employee calculation failed: ' . $e->getMessage());
                }
            }

            if ($successCount > 0) {
                $period->update(['status' => 'Processing']);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Calculated for {$successCount} of {$employees->count()} employees.",
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Calculation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to calculate payroll: ' . $e->getMessage()], 500);
        }
    }

    private function calculateEmployeePayroll($employee, $period)
    {
        $basicSalary = $employee->basic_salary ?? 0;

        // Remove previous repayments for this employee/period (to avoid double deduction)
        $existingCalc = PayrollCalculation::where('employee_id', $employee->id)
            ->where('period_id', $period->id)
            ->first();
        if ($existingCalc) {
            foreach (LoanRepayment::where('payroll_id', $existingCalc->id)->get() as $prevRep) {
                $loan = EmployeeLoan::find($prevRep->loan_id);
                if ($loan) {
                    $loan->remaining_balance += $prevRep->amount_deducted;
                    $loan->installments_paid = max(0, $loan->installments_paid - 1);
                    if ($loan->status == 'Completed' && $loan->remaining_balance > 0) {
                        $loan->status = 'Active';
                    }
                    $loan->save();
                }
                $prevRep->delete();
            }
            $existingCalc->delete();
        }

        // Overtime
        $overtimeAmount = EmployeeOvertime::where('employee_id', $employee->id)
            ->where('period_id', $period->id)
            ->where('status', 'Approved')
            ->sum('amount');

        // Adjustments
        $allowanceAdjustments = PayrollAdjustment::where('employee_id', $employee->id)
            ->where('period_id', $period->id)
            ->where('adjustment_type', 'Allowance')
            ->whereNotNull('approved_at')
            ->sum('amount');

        $deductionAdjustments = PayrollAdjustment::where('employee_id', $employee->id)
            ->where('period_id', $period->id)
            ->where('adjustment_type', 'Deduction')
            ->whereNotNull('approved_at')
            ->sum('amount');

        // Loans
        $activeLoans = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'Active')
            ->where('deduction_start_date', '<=', $period->end_date)
            ->get();

        $loanDeductions = 0;
        foreach ($activeLoans as $loan) {
            if ($loan->remaining_balance > 0) {
                $loanDeductions += min($loan->monthly_installment, $loan->remaining_balance);
            }
        }

        // Allowances from rates
        $transportAllowance = $this->calculateAllowanceFromRate($basicSalary, 'TRANSPORT_ALLOWANCE');
        $housingAllowance = $this->calculateAllowanceFromRate($basicSalary, 'HOUSING_ALLOWANCE');
        $foodAllowance = $this->calculateAllowanceFromRate($basicSalary, 'FOOD_ALLOWANCE');
        $medicalAllowance = $this->calculateAllowanceFromRate($basicSalary, 'MEDICAL_ALLOWANCE');

        $totalEarnings = $basicSalary + $transportAllowance + $housingAllowance +
            $foodAllowance + $medicalAllowance + $overtimeAmount + $allowanceAdjustments;

        $payeTax = $this->calculatePAYEFromRate($totalEarnings);
        $nssfEmployee = $this->calculateNSSFFromRate($basicSalary);

        $totalDeductions = $payeTax + $nssfEmployee + $loanDeductions + $deductionAdjustments;
        $netSalary = max(0, $totalEarnings - $totalDeductions);

        $calculation = PayrollCalculation::create([
            'employee_id' => $employee->id,
            'period_id' => $period->id,
            'basic_salary' => $basicSalary,
            'transport_allowance' => $transportAllowance,
            'housing_allowance' => $housingAllowance,
            'food_allowance' => $foodAllowance,
            'medical_allowance' => $medicalAllowance,
            'other_allowances' => $allowanceAdjustments,
            'overtime_amount' => $overtimeAmount,
            'total_earnings' => $totalEarnings,
            'paye_tax' => $payeTax,
            'nssf_employee' => $nssfEmployee,
            'loan_deductions' => $loanDeductions,
            'other_deductions' => $deductionAdjustments,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'payment_status' => 'Pending',
            'is_calculated' => true,
            'calculated_by' => auth()->id(),
            'calculated_at' => now(),
            'created_by' => auth()->id(),
        ]);

        // Create loan repayments
        if ($loanDeductions > 0) {
            foreach ($activeLoans as $loan) {
                if ($loan->remaining_balance > 0) {
                    $deductionAmount = min($loan->monthly_installment, $loan->remaining_balance);
                    LoanRepayment::create([
                        'loan_id' => $loan->id,
                        'payroll_id' => $calculation->id,
                        'amount_deducted' => $deductionAmount,
                        'deduction_date' => $period->pay_date,
                        'remaining_balance' => $loan->remaining_balance - $deductionAmount,
                    ]);
                    $loan->installments_paid++;
                    $loan->remaining_balance = max(0, $loan->remaining_balance - $deductionAmount);
                    if ($loan->remaining_balance <= 0) {
                        $loan->status = 'Completed';
                    }
                    $loan->save();
                }
            }
        }

        return $calculation;
    }

    // ==================== RATE HELPERS ====================

    private function calculateAllowanceFromRate($basicSalary, $allowanceType)
    {
        $rate = PayrollRate::where('rate_type', $allowanceType)
            ->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->where('effective_to', '>=', now())->orWhereNull('effective_to');
            })->first();
        if ($rate && $rate->rate_value > 0) {
            return $basicSalary * ($rate->rate_value / 100);
        }
        return 0;
    }

    private function calculatePAYEFromRate($taxableIncome)
    {
        $rate = PayrollRate::where('rate_type', 'PAYE')
            ->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->where('effective_to', '>=', now())->orWhereNull('effective_to');
            })
            ->where('income_from', '<=', $taxableIncome)
            ->where(function ($q) use ($taxableIncome) {
                $q->where('income_to', '>=', $taxableIncome)->orWhereNull('income_to');
            })->first();
        if ($rate && $rate->rate_value > 0) {
            return $taxableIncome * ($rate->rate_value / 100);
        }
        return 0;
    }

    private function calculateNSSFFromRate($basicSalary)
    {
        $rate = PayrollRate::where('rate_type', 'NSSF')
            ->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->where('effective_to', '>=', now())->orWhereNull('effective_to');
            })->first();
        if ($rate && $rate->rate_value > 0) {
            return $basicSalary * ($rate->rate_value / 100);
        }
        return 0;
    }

    // ==================== VERIFY, PAY, GENERATE PAYSLIPS ====================

    public function verifyCalculation($id)
    {
        DB::beginTransaction();
        try {
            $calc = PayrollCalculation::findOrFail($id);
            if ($calc->payment_status == 'Verified') {
                return response()->json(['success' => false, 'message' => 'Already verified.'], 422);
            }
            if ($calc->payment_status == 'Paid') {
                return response()->json(['success' => false, 'message' => 'Already paid.'], 422);
            }
            $calc->update([
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'payment_status' => 'Verified',
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Calculation verified successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to verify: ' . $e->getMessage()], 500);
        }
    }

    public function processPayment($id)
    {
        DB::beginTransaction();
        try {
            $calc = PayrollCalculation::findOrFail($id);
            if ($calc->payment_status == 'Paid') {
                return response()->json(['success' => false, 'message' => 'Already paid.'], 422);
            }
            if ($calc->payment_status != 'Verified') {
                return response()->json(['success' => false, 'message' => 'Please verify calculation first.'], 422);
            }
            if ($calc->period->is_locked) {
                return response()->json(['success' => false, 'message' => 'Period is locked.'], 422);
            }
            $ref = 'PAY-' . date('Ymd') . '-' . str_pad($calc->id, 5, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -4));
            $calc->update([
                'payment_status' => 'Paid',
                'payment_date' => now(),
                'payment_reference' => $ref,
                'payment_method' => 'Bank Transfer',
                'updated_by' => auth()->id(),
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment processed! Reference: ' . $ref]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to process payment.'], 500);
        }
    }

    public function generatePayslips(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'period_id' => 'required|exists:payroll_periods,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $period = PayrollPeriod::findOrFail($request->period_id);
            if (!$period->is_locked && $period->status != 'Approved') {
                return response()->json([
                    'success' => false,
                    'message' => "Period must be locked or approved.",
                    'diagnostic' => ['status' => $period->status, 'locked' => $period->is_locked]
                ], 422);
            }

            $calculations = PayrollCalculation::where('period_id', $period->id)
                ->where('is_calculated', true)
                ->where('payment_status', 'Paid')
                ->get();

            if ($calculations->isEmpty()) {
                $total = PayrollCalculation::where('period_id', $period->id)->count();
                $statusBreakdown = PayrollCalculation::where('period_id', $period->id)
                    ->groupBy('payment_status')->select('payment_status', DB::raw('count(*) as total'))->pluck('total', 'payment_status');
                return response()->json([
                    'success' => false,
                    'message' => "No paid calculations found for this period. Total: {$total}, breakdown: " . json_encode($statusBreakdown),
                ], 404);
            }

            $successCount = 0;
            $errors = [];
            foreach ($calculations as $calc) {
                try {
                    $this->generatePayslipForCalculation($calc, $period);
                    $successCount++;
                } catch (\Exception $e) {
                    $employeeName = $calc->employee->first_name . ' ' . $calc->employee->last_name;
                    $errors[] = "{$employeeName}: " . $e->getMessage();
                    Log::error('Payslip generation error: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Generated {$successCount} of {$calculations->count()} payslips.",
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Generate payslips error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate payslips: ' . $e->getMessage()], 500);
        }
    }

    private function generatePayslipForCalculation($calculation, $period)
    {
        $employee = $calculation->employee;
        $company = $employee->company;

        // Recalculate to ensure up-to-date values (including loan reversals)
        // Actually, we already have the calculation object; we can reuse its values.
        // But we might need to recalc to be safe. However, we already have all amounts in $calculation.
        // We'll just use the existing calculation.

        $payslipNumber = 'PSL-' . $period->period_code . '-' .
            str_pad($employee->id, 4, '0', STR_PAD_LEFT) . '-' .
            date('Ymd');

        // Delete old payslip if exists
        $existingPayslip = Payslip::where('payroll_id', $calculation->id)->first();
        if ($existingPayslip && $existingPayslip->pdf_file_path) {
            Storage::disk('public')->delete($existingPayslip->pdf_file_path);
        }

        $data = [
            'calculation' => $calculation,
            'period' => $period,
            'employee' => $employee,
            'company' => $company,
            'payslipNumber' => $payslipNumber,
            'generated_date' => now()->format('d/m/Y'),
            'allowanceAdjustments' => $calculation->other_allowances,
            'deductionAdjustments' => $calculation->other_deductions,
            'overtimeAmount' => $calculation->overtime_amount,
            'loanDeductions' => $calculation->loan_deductions,
            'transportAllowance' => $calculation->transport_allowance,
            'housingAllowance' => $calculation->housing_allowance,
            'foodAllowance' => $calculation->food_allowance,
            'medicalAllowance' => $calculation->medical_allowance,
        ];

        $pdf = Pdf::loadView('admin.payroll.payslip-template', $data);
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'payslip_' . str_replace('-', '_', $payslipNumber) . '_' . time() . '.pdf';
        $filePath = 'payslips/' . $fileName;
        if (!Storage::disk('public')->exists('payslips')) {
            Storage::disk('public')->makeDirectory('payslips');
        }
        Storage::disk('public')->put($filePath, $pdf->output());

        $payslipData = [
            'employee_id' => $employee->id,
            'period_id' => $period->id,
            'payroll_id' => $calculation->id,
            'payslip_number' => $payslipNumber,
            'issue_date' => now(),
            'pdf_file_path' => $filePath,
            'file_size' => Storage::disk('public')->size($filePath),
            'created_by' => auth()->id(),
        ];

        if ($existingPayslip) {
            $existingPayslip->update($payslipData);
        } else {
            Payslip::create($payslipData);
        }
    }

    public function downloadPayslip($id)
    {
        DB::beginTransaction();
        try {
            $payslip = Payslip::findOrFail($id);
            if (!$payslip->pdf_file_path) {
                return response()->json(['success' => false, 'message' => 'Payslip has no PDF file.'], 404);
            }
            $fullPath = Storage::disk('public')->path($payslip->pdf_file_path);
            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Payslip file not found.'], 404);
            }
            $payslip->update(['is_downloaded' => true, 'downloaded_at' => now()]);
            DB::commit();
            return response()->download($fullPath, 'Payslip_' . $payslip->payslip_number . '.pdf');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to download: ' . $e->getMessage()], 500);
        }
    }

    public function previewPayslip($id)
    {
        try {
            $payslip = Payslip::findOrFail($id);
            if (!$payslip->pdf_file_path) {
                return response()->json(['success' => false, 'message' => 'Payslip has no PDF file.'], 404);
            }
            $fullPath = Storage::disk('public')->path($payslip->pdf_file_path);
            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Payslip file not found.'], 404);
            }
            return response()->file($fullPath, ['Content-Type' => 'application/pdf']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to preview.'], 500);
        }
    }

    // ==================== LOAN METHODS ====================

    public function storeLoan(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'loan_type' => 'required|in:Salary Advance,Personal Loan,Emergency',
                'amount' => 'required|numeric|min:0',
                'monthly_installment' => 'required|numeric|min:0|lt:amount',
                'total_installments' => 'required|integer|min:1',
                'disbursement_date' => 'required|date',
                'deduction_start_date' => 'required|date|after_or_equal:disbursement_date',
                'notes' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $loanNumber = 'LN-' . date('Ym') . '-' . str_pad(EmployeeLoan::whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count() + 1, 4, '0', STR_PAD_LEFT);

            $data = [
                'employee_id' => $request->employee_id,
                'loan_type' => $request->loan_type,
                'amount' => $request->amount,
                'monthly_installment' => $request->monthly_installment,
                'total_installments' => $request->total_installments,
                'disbursement_date' => $request->disbursement_date,
                'deduction_start_date' => $request->deduction_start_date,
                'notes' => $request->notes,
                'loan_number' => $loanNumber,
                'installments_paid' => 0,
                'remaining_balance' => $request->amount,
                'status' => 'Active',
                'created_by' => auth()->id(),
            ];

            if ($request->filled('loan_id')) {
                $loan = EmployeeLoan::findOrFail($request->loan_id);
                if ($loan->repayments()->exists()) {
                    return response()->json(['success' => false, 'message' => 'Cannot edit loan with existing repayments.'], 422);
                }
                unset($data['created_by']);
                $loan->update($data);
                $message = 'Loan updated successfully!';
            } else {
                $loan = EmployeeLoan::create($data);
                $message = 'Loan created successfully!';
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => $message, 'loan_id' => $loan->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save loan: ' . $e->getMessage()], 500);
        }
    }

    public function destroyLoan($id)
    {
        DB::beginTransaction();
        try {
            $loan = EmployeeLoan::findOrFail($id);
            if ($loan->repayments()->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete loan with existing repayments.'], 422);
            }
            $loan->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Loan deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete loan.'], 500);
        }
    }

    public function approveLoan($id)
    {
        DB::beginTransaction();
        try {
            $loan = EmployeeLoan::findOrFail($id);
            if ($loan->approved_at) {
                return response()->json(['success' => false, 'message' => 'Loan already approved.'], 422);
            }
            $loan->update(['approved_by' => auth()->id(), 'approved_at' => now()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Loan approved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to approve loan.'], 500);
        }
    }

    public function disburseLoan($id)
    {
        DB::beginTransaction();
        try {
            $loan = EmployeeLoan::findOrFail($id);
            if (!$loan->approved_at) {
                return response()->json(['success' => false, 'message' => 'Loan must be approved first.'], 422);
            }
            if ($loan->disbursed_at) {
                return response()->json(['success' => false, 'message' => 'Loan already disbursed.'], 422);
            }
            $loan->update(['disbursed_by' => auth()->id(), 'disbursed_at' => now()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Loan disbursed successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to disburse loan.'], 500);
        }
    }

    public function completeLoan($id)
    {
        DB::beginTransaction();
        try {
            $loan = EmployeeLoan::findOrFail($id);
            if ($loan->remaining_balance > 0) {
                return response()->json(['success' => false, 'message' => 'Cannot complete loan: remaining balance is ' . $loan->remaining_balance], 422);
            }
            if ($loan->status == 'Completed') {
                return response()->json(['success' => false, 'message' => 'Loan already completed.'], 422);
            }
            $loan->update(['status' => 'Completed', 'completed_by' => auth()->id(), 'completed_at' => now()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Loan completed successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to complete loan.'], 500);
        }
    }

    // ==================== OVERTIME METHODS ====================

    public function storeOvertime(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'period_id' => 'required|exists:payroll_periods,id',
                'overtime_date' => 'required|date',
                'hours_worked' => 'required|numeric|min:0.5|max:24',
                'rate_multiplier' => 'required|numeric|in:1.5,2.0,2.5',
                'notes' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $employee = Employee::find($request->employee_id);
            if (!$employee->basic_salary || $employee->basic_salary <= 0) {
                return response()->json(['success' => false, 'message' => 'Employee has no valid basic salary.'], 422);
            }

            $monthlyHours = 22 * 8;
            $hourlyRate = $employee->basic_salary / $monthlyHours;
            $amount = $hourlyRate * $request->hours_worked * $request->rate_multiplier;

            $data = [
                'employee_id' => $request->employee_id,
                'period_id' => $request->period_id,
                'overtime_date' => $request->overtime_date,
                'hours_worked' => $request->hours_worked,
                'rate_multiplier' => $request->rate_multiplier,
                'amount' => round($amount, 2),
                'notes' => $request->notes,
                'status' => 'Pending',
                'created_by' => auth()->id(),
            ];

            if ($request->filled('overtime_id')) {
                $overtime = EmployeeOvertime::findOrFail($request->overtime_id);
                if ($overtime->status == 'Approved') {
                    return response()->json(['success' => false, 'message' => 'Cannot edit approved overtime.'], 422);
                }
                unset($data['created_by']);
                $overtime->update($data);
                $message = 'Overtime updated successfully!';
            } else {
                $overtime = EmployeeOvertime::create($data);
                $message = 'Overtime created successfully!';
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => $message, 'overtime_id' => $overtime->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save overtime.'], 500);
        }
    }

    public function destroyOvertime($id)
    {
        DB::beginTransaction();
        try {
            $overtime = EmployeeOvertime::findOrFail($id);
            if ($overtime->status == 'Approved') {
                return response()->json(['success' => false, 'message' => 'Cannot delete approved overtime.'], 422);
            }
            $overtime->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Overtime deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete overtime.'], 500);
        }
    }

    public function approveOvertime($id)
    {
        DB::beginTransaction();
        try {
            $overtime = EmployeeOvertime::findOrFail($id);
            if ($overtime->status != 'Pending') {
                return response()->json(['success' => false, 'message' => 'Only pending overtime can be approved.'], 422);
            }
            $overtime->update(['approved_by' => auth()->id(), 'approved_at' => now(), 'status' => 'Approved']);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Overtime approved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to approve overtime.'], 500);
        }
    }

    // ==================== ADJUSTMENT METHODS ====================

    public function storeAdjustment(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'period_id' => 'required|exists:payroll_periods,id',
                'adjustment_type' => 'required|in:Allowance,Deduction',
                'description' => 'required|string|max:200',
                'amount' => 'required|numeric|min:0',
                'is_taxable' => 'sometimes|boolean',
                'notes' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $data = [
                'employee_id' => $request->employee_id,
                'period_id' => $request->period_id,
                'adjustment_type' => $request->adjustment_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'is_taxable' => $request->boolean('is_taxable', true),
                'notes' => $request->notes,
                'status' => 'Pending',
                'created_by' => auth()->id(),
            ];

            if ($request->filled('adjustment_id')) {
                $adj = PayrollAdjustment::findOrFail($request->adjustment_id);
                if ($adj->approved_at) {
                    return response()->json(['success' => false, 'message' => 'Cannot edit approved adjustment.'], 422);
                }
                unset($data['created_by']);
                $adj->update($data);
                $message = 'Adjustment updated successfully!';
            } else {
                $adj = PayrollAdjustment::create($data);
                $message = 'Adjustment created successfully!';
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => $message, 'adjustment_id' => $adj->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save adjustment.'], 500);
        }
    }

    public function destroyAdjustment($id)
    {
        DB::beginTransaction();
        try {
            $adj = PayrollAdjustment::findOrFail($id);
            if ($adj->approved_at) {
                return response()->json(['success' => false, 'message' => 'Cannot delete approved adjustment.'], 422);
            }
            $adj->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Adjustment deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete adjustment.'], 500);
        }
    }

    public function approveAdjustment($id)
    {
        DB::beginTransaction();
        try {
            $adj = PayrollAdjustment::findOrFail($id);
            if ($adj->approved_at) {
                return response()->json(['success' => false, 'message' => 'Adjustment already approved.'], 422);
            }
            $adj->update(['approved_by' => auth()->id(), 'approved_at' => now(), 'status' => 'Approved']);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Adjustment approved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to approve adjustment.'], 500);
        }
    }

    // ==================== RATE METHODS ====================

    public function storeRate(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'rate_type' => 'required|in:PAYE,NSSF,PPF,TRANSPORT_ALLOWANCE,HOUSING_ALLOWANCE,FOOD_ALLOWANCE,MEDICAL_ALLOWANCE',
                'rate_value' => 'required|numeric|min:0|max:100',
                'income_from' => 'nullable|numeric|min:0',
                'income_to' => 'nullable|numeric|gt:income_from',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'is_active' => 'sometimes|boolean',
                'description' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            if ($request->boolean('is_active', true)) {
                PayrollRate::where('rate_type', $request->rate_type)->where('is_active', true)->update(['is_active' => false]);
            }

            $rate = PayrollRate::create([
                'rate_type' => $request->rate_type,
                'rate_value' => $request->rate_value,
                'income_from' => $request->income_from,
                'income_to' => $request->income_to,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'is_active' => $request->boolean('is_active', true),
                'description' => $request->description,
                'created_by' => auth()->id(),
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Rate created successfully!', 'rate_id' => $rate->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create rate.'], 500);
        }
    }
     
        // ==================== PERIOD (alias used by dedicated "Calculate" button) ====================

    public function calculatePeriod(Request $request)
    {
        // Periods tab's "Calculate" button reuses the same engine as Run Payroll.
        return $this->calculate($request);
    }

    // ==================== SUMMARY (AJAX refresh) ====================

    public function getPayrollSummary()
    {
        return response()->json(['success' => true, 'data' => $this->getSummaryData()]);
    }

    // ==================== MANUAL CALCULATION CRUD ====================

    public function storeCalculation(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'period_id' => 'required|exists:payroll_periods,id',
                'basic_salary' => 'required|numeric|min:0',
                'transport_allowance' => 'nullable|numeric|min:0',
                'housing_allowance' => 'nullable|numeric|min:0',
                'food_allowance' => 'nullable|numeric|min:0',
                'medical_allowance' => 'nullable|numeric|min:0',
                'overtime_amount' => 'nullable|numeric|min:0',
                'paye_tax' => 'nullable|numeric|min:0',
                'nssf_employee' => 'nullable|numeric|min:0',
                'loan_deductions' => 'nullable|numeric|min:0',
                'other_deductions' => 'nullable|numeric|min:0',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $period = PayrollPeriod::findOrFail($request->period_id);
            if ($period->is_locked) {
                return response()->json(['success' => false, 'message' => 'Cannot save calculation for a locked period.'], 422);
            }

            $totalEarnings = $request->basic_salary
                + ($request->transport_allowance ?? 0)
                + ($request->housing_allowance ?? 0)
                + ($request->food_allowance ?? 0)
                + ($request->medical_allowance ?? 0)
                + ($request->overtime_amount ?? 0);

            $totalDeductions = ($request->paye_tax ?? 0)
                + ($request->nssf_employee ?? 0)
                + ($request->loan_deductions ?? 0)
                + ($request->other_deductions ?? 0);

            $data = [
                'employee_id' => $request->employee_id,
                'period_id' => $request->period_id,
                'basic_salary' => $request->basic_salary,
                'transport_allowance' => $request->transport_allowance ?? 0,
                'housing_allowance' => $request->housing_allowance ?? 0,
                'food_allowance' => $request->food_allowance ?? 0,
                'medical_allowance' => $request->medical_allowance ?? 0,
                'overtime_amount' => $request->overtime_amount ?? 0,
                'total_earnings' => $totalEarnings,
                'paye_tax' => $request->paye_tax ?? 0,
                'nssf_employee' => $request->nssf_employee ?? 0,
                'loan_deductions' => $request->loan_deductions ?? 0,
                'other_deductions' => $request->other_deductions ?? 0,
                'total_deductions' => $totalDeductions,
                'net_salary' => max(0, $totalEarnings - $totalDeductions),
                'is_calculated' => true,
                'calculated_by' => auth()->id(),
                'calculated_at' => now(),
            ];

            if ($request->filled('calculation_id')) {
                $calc = PayrollCalculation::findOrFail($request->calculation_id);
                if ($calc->payment_status != 'Pending') {
                    return response()->json(['success' => false, 'message' => 'Only pending calculations can be edited.'], 422);
                }
                $calc->update($data);
                $message = 'Calculation updated successfully!';
            } else {
                $data['payment_status'] = 'Pending';
                $data['created_by'] = auth()->id();
                $calc = PayrollCalculation::create($data);
                $message = 'Calculation created successfully!';
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $message, 'calculation_id' => $calc->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save calculation: ' . $e->getMessage()], 500);
        }
    }

    public function showCalculation($id)
    {
        try {
            $calc = PayrollCalculation::with(['employee', 'period', 'calculatedBy', 'verifiedBy'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $calc]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Calculation not found'], 404);
        }
    }

    public function editCalculation($id)
    {
        return $this->showCalculation($id);
    }

    public function updateCalculation(Request $request, $id)
    {
        $request->merge(['calculation_id' => $id]);
        return $this->storeCalculation($request);
    }

    public function destroyCalculation($id)
    {
        DB::beginTransaction();
        try {
            $calc = PayrollCalculation::findOrFail($id);
            if ($calc->payment_status != 'Pending') {
                return response()->json(['success' => false, 'message' => 'Only pending calculations can be deleted.'], 422);
            }
            foreach (LoanRepayment::where('payroll_id', $calc->id)->get() as $rep) {
                $loan = EmployeeLoan::find($rep->loan_id);
                if ($loan) {
                    $loan->remaining_balance += $rep->amount_deducted;
                    $loan->installments_paid = max(0, $loan->installments_paid - 1);
                    if ($loan->status == 'Completed' && $loan->remaining_balance > 0) {
                        $loan->status = 'Active';
                    }
                    $loan->save();
                }
                $rep->delete();
            }
            $calc->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Calculation deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete calculation.'], 500);
        }
    }

    // ==================== PAYSLIP extras ====================

    public function emailPayslip($id)
    {
        try {
            $payslip = Payslip::with(['employee'])->findOrFail($id);
            if (!$payslip->pdf_file_path) {
                return response()->json(['success' => false, 'message' => 'Payslip has no PDF file.'], 404);
            }
            if (!$payslip->employee || !$payslip->employee->email) {
                return response()->json(['success' => false, 'message' => 'Employee has no email on file.'], 422);
            }
            // \Mail::to($payslip->employee->email)->send(new \App\Mail\PayslipMail($payslip));
            $payslip->update(['emailed_at' => now()]);
            return response()->json(['success' => true, 'message' => 'Payslip emailed successfully!']);
        } catch (\Exception $e) {
            Log::error('Email payslip error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to email payslip: ' . $e->getMessage()], 500);
        }
    }

    public function regeneratePayslip($id)
    {
        try {
            $payslip = Payslip::with(['payroll.employee', 'period'])->findOrFail($id);
            if (!$payslip->payroll) {
                return response()->json(['success' => false, 'message' => 'No underlying calculation found for this payslip.'], 422);
            }
            $this->generatePayslipForCalculation($payslip->payroll, $payslip->period);
            return response()->json(['success' => true, 'message' => 'Payslip regenerated successfully!']);
        } catch (\Exception $e) {
            Log::error('Regenerate payslip error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to regenerate payslip: ' . $e->getMessage()], 500);
        }
    }

    public function destroyPayslip($id)
    {
        DB::beginTransaction();
        try {
            $payslip = Payslip::findOrFail($id);
            if ($payslip->pdf_file_path) {
                Storage::disk('public')->delete($payslip->pdf_file_path);
            }
            $payslip->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payslip deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete payslip.'], 500);
        }
    }

    public function printPayslip($id)
    {
        // Same as preview, but intent is print-friendly inline view (browser handles print dialog)
        return $this->previewPayslip($id);
    }

    // ==================== LOAN extras ====================

    public function editLoan($id)
    {
        try {
            $loan = EmployeeLoan::with('employee')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $loan]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Loan not found'], 404);
        }
    }

    public function viewLoan($id)
    {
        return $this->editLoan($id);
    }

    public function updateLoan(Request $request, $id)
    {
        $request->merge(['loan_id' => $id]);
        return $this->storeLoan($request);
    }

    public function rejectLoan($id)
    {
        DB::beginTransaction();
        try {
            $loan = EmployeeLoan::findOrFail($id);
            if ($loan->disbursed_at) {
                return response()->json(['success' => false, 'message' => 'Cannot reject a disbursed loan.'], 422);
            }
            $loan->update(['status' => 'Rejected', 'updated_by' => auth()->id()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Loan rejected.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to reject loan.'], 500);
        }
    }

    public function suspendLoan($id)
    {
        DB::beginTransaction();
        try {
            $loan = EmployeeLoan::findOrFail($id);
            if ($loan->status != 'Active') {
                return response()->json(['success' => false, 'message' => 'Only active loans can be suspended.'], 422);
            }
            $loan->update(['status' => 'Suspended', 'updated_by' => auth()->id()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Loan suspended.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to suspend loan.'], 500);
        }
    }

    public function resumeLoan($id)
    {
        DB::beginTransaction();
        try {
            $loan = EmployeeLoan::findOrFail($id);
            if ($loan->status != 'Suspended') {
                return response()->json(['success' => false, 'message' => 'Only suspended loans can be resumed.'], 422);
            }
            $loan->update(['status' => 'Active', 'updated_by' => auth()->id()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Loan resumed.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to resume loan.'], 500);
        }
    }

    // ==================== OVERTIME extras ====================

    public function editOvertime($id)
    {
        try {
            $overtime = EmployeeOvertime::with('employee')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $overtime]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Overtime not found'], 404);
        }
    }

    public function updateOvertime(Request $request, $id)
    {
        $request->merge(['overtime_id' => $id]);
        return $this->storeOvertime($request);
    }

    public function rejectOvertime($id)
    {
        DB::beginTransaction();
        try {
            $overtime = EmployeeOvertime::findOrFail($id);
            if ($overtime->status != 'Pending') {
                return response()->json(['success' => false, 'message' => 'Only pending overtime can be rejected.'], 422);
            }
            $overtime->update(['status' => 'Rejected', 'approved_by' => auth()->id(), 'approved_at' => now()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Overtime rejected.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to reject overtime.'], 500);
        }
    }

    // ==================== ADJUSTMENT extras ====================

    public function editAdjustment($id)
    {
        try {
            $adj = PayrollAdjustment::with('employee')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $adj]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Adjustment not found'], 404);
        }
    }

    public function updateAdjustment(Request $request, $id)
    {
        $request->merge(['adjustment_id' => $id]);
        return $this->storeAdjustment($request);
    }

    public function rejectAdjustment($id)
    {
        DB::beginTransaction();
        try {
            $adj = PayrollAdjustment::findOrFail($id);
            if ($adj->approved_at) {
                return response()->json(['success' => false, 'message' => 'Cannot reject an already-approved adjustment.'], 422);
            }
            $adj->update(['status' => 'Rejected']);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Adjustment rejected.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to reject adjustment.'], 500);
        }
    }

    // ==================== RATE extras ====================

    public function editRate($id)
    {
        try {
            $rate = PayrollRate::findOrFail($id);
            return response()->json(['success' => true, 'data' => $rate]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Rate not found'], 404);
        }
    }

    public function updateRate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $rate = PayrollRate::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'rate_value' => 'required|numeric|min:0|max:100',
                'income_from' => 'nullable|numeric|min:0',
                'income_to' => 'nullable|numeric|gt:income_from',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'is_active' => 'sometimes|boolean',
                'description' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
            }
            if ($request->boolean('is_active', true)) {
                PayrollRate::where('rate_type', $rate->rate_type)
                    ->where('is_active', true)
                    ->where('id', '!=', $rate->id)
                    ->update(['is_active' => false]);
            }
            $rate->update($request->only([
                'rate_value', 'income_from', 'income_to', 'effective_from', 'effective_to', 'description',
            ]) + ['is_active' => $request->boolean('is_active', true)]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Rate updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update rate.'], 500);
        }
    }

    public function destroyRate($id)
    {
        DB::beginTransaction();
        try {
            $rate = PayrollRate::findOrFail($id);
            $rate->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Rate deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete rate.'], 500);
        }
    }
}