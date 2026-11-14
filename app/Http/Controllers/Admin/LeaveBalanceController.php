<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveBalanceController extends Controller
{
     public function index(Request $request)
    {
        if (!auth()->user()->can('leave_balance.view')) {
            abort(403, 'You do not have permission to view leave balances.');
        }

        try {
            $query = LeaveBalance::with(['employee', 'leaveType'])
                ->orderBy('year', 'desc')
                ->orderBy('employee_id');

            // Apply filters
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('leave_type_id')) {
                $query->where('leave_type_id', $request->leave_type_id);
            }

            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            $balances = $query->get(); // Changed from paginate to get for simplicity

            $employees = Employee::where('is_active', 1)
                ->where('employment_status', 'Active')
                ->get();

            $leaveTypes = LeaveType::where('is_active', 1)
                ->orderBy('leave_name')
                ->get();

            $years = range(date('Y') - 2, date('Y') + 2);

            $stats = [
                'total_employees' => $employees->count(),
                'total_balances' => $balances->count(),
                'current_year' => date('Y'),
            ];

            return view('admin.leave-balances.index', compact(
                'balances',
                'employees',
                'leaveTypes',
                'years',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Leave balance index error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load leave balances: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('leave_balance.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $balance = LeaveBalance::findOrFail($id);

        $validated = $request->validate([
            'entitled_days' => 'required|numeric|min:0',
            'used_days' => 'required|numeric|min:0',
            'carried_over_days' => 'required|numeric|min:0',
        ]);

        $validated['remaining_days'] = $validated['entitled_days'] - $validated['used_days'] + $validated['carried_over_days'];
        $validated['last_updated'] = Carbon::now();
        $validated['updated_by'] = auth()->id();

        $balance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Leave balance updated successfully.'
        ]);
    }

    public function initializeYear(Request $request)
    {
        if (!auth()->user()->can('leave_balance.initialize')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $request->validate([
            'year' => 'required|integer|min:' . date('Y') . '|max:' . (date('Y') + 5),
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        DB::beginTransaction();

        try {
            $created = 0;
            $skipped = 0;
            $errors = [];

            foreach ($request->employee_ids as $employeeId) {
                $leaveTypes = LeaveType::where('is_active', 1)->get();

                foreach ($leaveTypes as $leaveType) {
                    try {
                        $exists = LeaveBalance::where('employee_id', $employeeId)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('year', $request->year)
                            ->exists();

                        if (!$exists) {
                            $previousBalance = LeaveBalance::where('employee_id', $employeeId)
                                ->where('leave_type_id', $leaveType->id)
                                ->where('year', $request->year - 1)
                                ->first();

                            $carryOver = $previousBalance ? $previousBalance->remaining_days : 0;

                            LeaveBalance::create([
                                'employee_id' => $employeeId,
                                'leave_type_id' => $leaveType->id,
                                'year' => $request->year,
                                'entitled_days' => $leaveType->days_per_year,
                                'used_days' => 0,
                                'carried_over_days' => $carryOver,
                                'remaining_days' => $leaveType->days_per_year + $carryOver,
                                'last_updated' => Carbon::now(),
                                'created_by' => auth()->id(),
                            ]);

                            $created++;
                        } else {
                            $skipped++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Employee ID {$employeeId}, Type {$leaveType->leave_code}: " . $e->getMessage();
                    }
                }
            }

            DB::commit();

            $message = "Initialized {$created} balance(s). {$skipped} already existed.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Balance initialization failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to initialize balances: ' . $e->getMessage()], 500);
        }
    }

    public function employeeBalance($employeeId, Request $request)
    {
        if (!auth()->user()->can('leave_balance.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $leaveTypeId = $request->get('leave_type_id');
        $year = $request->get('year', date('Y'));

        if (!$leaveTypeId) {
            return response()->json(['error' => 'Leave type ID required.'], 422);
        }

        $balance = LeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        return response()->json([
            'success' => true,
            'balance' => $balance ? $balance->remaining_days : 0
        ]);
    }

    public function export(Request $request)
    {
        if (!auth()->user()->can('leave_balance.view')) {
            abort(403);
        }

        $query = LeaveBalance::with(['employee', 'leaveType']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $data = $query->orderBy('year', 'desc')->orderBy('employee_id')->get();

        $filename = 'leave_balances_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Leave Type', 'Year', 'Entitled', 'Used', 'Carried Over', 'Remaining']);
            foreach ($data as $item) {
                fputcsv($handle, [
                    $item->employee->full_name ?? 'N/A',
                    $item->leaveType->leave_name ?? 'N/A',
                    $item->year,
                    $item->entitled_days,
                    $item->used_days,
                    $item->carried_over_days,
                    $item->remaining_days,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}