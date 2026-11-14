<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('employee.view')) {
            abort(403, 'You do not have permission to view employees.');
        }

        $query = Employee::with(['user', 'company', 'branch', 'department']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('is_active', 1)->where('employment_status', 'Active')->count(),
            'inactive' => Employee::where('is_active', 0)->count(),
            'terminated' => Employee::where('employment_status', 'Terminated')->count(),
            'on_leave' => Employee::where('employment_status', 'On Leave')->count(),
        ];

        $companies = Company::where('is_active', 1)->get();
        $branches = Branch::where('is_active', 1)->get();
        $departments = Department::where('is_active', 1)->get();
        $statuses = ['Active', 'Inactive', 'Terminated', 'On Leave'];

        return view('admin.employees.index', compact(
            'employees', 'stats', 'companies', 'branches', 'departments', 'statuses'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('employee.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'gender' => 'nullable|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'employment_status' => 'required|in:Active,Inactive,Terminated,On Leave',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Generate employee code
        $validated['employee_code'] = $this->generateEmployeeCode();
        $validated['created_by'] = auth()->id();

        // Create user account if email provided and user doesn't exist
        if ($request->filled('email') && !User::where('email', $request->email)->exists()) {
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => bcrypt(Str::random(10)),
            ]);
            $validated['user_id'] = $user->id;
        }

        $employee = Employee::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully.',
                'employee' => $employee->load('company', 'branch', 'department')
            ]);
        }

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function show($id)
    {
        if (!auth()->user()->can('employee.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $employee = Employee::with(['user', 'company', 'branch', 'department', 'createdBy', 'updatedBy'])
            ->findOrFail($id);

        return response()->json($employee);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('employee.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $employee = Employee::findOrFail($id);
        $companies = Company::where('is_active', 1)->get();
        $branches = Branch::where('is_active', 1)->get();
        $departments = Department::where('is_active', 1)->get();

        if (request()->ajax()) {
            return response()->json([
                'employee' => $employee,
                'companies' => $companies,
                'branches' => $branches,
                'departments' => $departments,
            ]);
        }

        return view('admin.employees.edit', compact('employee', 'companies', 'branches', 'departments'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('employee.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'gender' => 'nullable|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'hire_date' => 'nullable|date',
            'employment_status' => 'required|in:Active,Inactive,Terminated,On Leave',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['updated_by'] = auth()->id();

        $employee->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully.',
                'employee' => $employee->fresh('company', 'branch', 'department')
            ]);
        }

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('employee.delete')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $employee = Employee::findOrFail($id);

        // Check for related records
        if ($employee->leaveBalances()->exists() || 
            $employee->leaveRequests()->exists() || 
            $employee->payrollCalculations()->exists() ||
            $employee->loans()->exists()) {
            return response()->json([
                'error' => 'Cannot delete employee with existing leave, payroll, or loan records.'
            ], 422);
        }

        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        if (!auth()->user()->can('employee.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $employee = Employee::findOrFail($id);
        $employee->is_active = !$employee->is_active;
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee status toggled.',
            'is_active' => $employee->is_active
        ]);
    }

    public function export()
    {
        if (!auth()->user()->can('employee.view')) {
            abort(403);
        }

        $employees = Employee::with(['company', 'branch', 'department'])->get();
        $filename = 'employees_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Employee Code', 'First Name', 'Last Name', 'Email', 'Phone',
                'Company', 'Branch', 'Department', 'Employment Status', 'Basic Salary',
                'Hire Date', 'Date of Birth', 'Gender', 'Active'
            ]);
            foreach ($employees as $e) {
                fputcsv($handle, [
                    $e->id,
                    $e->employee_code,
                    $e->first_name,
                    $e->last_name,
                    $e->email ?? '',
                    $e->phone ?? '',
                    $e->company->name ?? 'N/A',
                    $e->branch->name ?? 'N/A',
                    $e->department->name ?? 'N/A',
                    $e->employment_status,
                    $e->basic_salary ?? 0,
                    $e->hire_date ? $e->hire_date->format('Y-m-d') : '',
                    $e->date_of_birth ? $e->date_of_birth->format('Y-m-d') : '',
                    $e->gender ?? '',
                    $e->is_active ? 'Yes' : 'No'
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function generateEmployeeCode()
    {
        $prefix = 'EMP';
        $year = date('Y');
        $last = Employee::whereYear('created_at', $year)->count() + 1;
        return $prefix . '-' . $year . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    public function dropdown(Request $request)
    {
        if (!auth()->user()->can('employee.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $query = Employee::where('is_active', 1)->where('employment_status', 'Active');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('employee_code', 'like', "%{$request->search}%");
            });
        }

        $employees = $query->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_code']);

        return response()->json($employees);
    }
}