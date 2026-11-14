<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LeaveTypeController extends Controller
{
     public function index(Request $request)
    {
        if (!auth()->user()->can('leave_type.view')) {
            abort(403, 'You do not have permission to view leave types.');
        }

        $query = LeaveType::with(['company', 'createdBy', 'updatedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('leave_code', 'like', "%{$search}%")
                ->orWhere('leave_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $leaveTypes = $query->orderBy('leave_code')->paginate(15);
        
        // IMPORTANT: Get companies for the filter dropdown
        $companies = Company::where('is_active', 1)->get();

        return view('admin.leave-types.index', compact('leaveTypes', 'companies'));
    }     

    public function store(Request $request)
    {
        if (!auth()->user()->can('leave_type.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'leave_code' => 'required|string|max:20|unique:leave_types,leave_code',
            'leave_name' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'days_per_year' => 'required|numeric|min:0|max:365',
            'is_paid' => 'nullable|boolean',
            'requires_approval' => 'nullable|boolean',
            'requires_medical_certificate' => 'nullable|boolean',
            'max_consecutive_days' => 'nullable|integer|min:1|max:365',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_paid'] = $request->has('is_paid') ? 1 : 0;
        $validated['requires_approval'] = $request->has('requires_approval') ? 1 : 0;
        $validated['requires_medical_certificate'] = $request->has('requires_medical_certificate') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['created_by'] = auth()->id();

        $leaveType = LeaveType::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Leave type created successfully.',
                'leave_type' => $leaveType
            ]);
        }

        return redirect()->route('admin.leave-types.index')->with('success', 'Leave type created successfully.');
    }

    public function show($id)
    {
        if (!auth()->user()->can('leave_type.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $leaveType = LeaveType::with(['company', 'createdBy', 'updatedBy'])->findOrFail($id);
        return response()->json($leaveType);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('leave_type.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $leaveType = LeaveType::findOrFail($id);
        $companies = Company::where('is_active', 1)->get();

        return response()->json([
            'leave_type' => $leaveType,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('leave_type.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $leaveType = LeaveType::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'leave_code' => 'required|string|max:20|unique:leave_types,leave_code,' . $id,
            'leave_name' => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'days_per_year' => 'required|numeric|min:0|max:365',
            'is_paid' => 'nullable|boolean',
            'requires_approval' => 'nullable|boolean',
            'requires_medical_certificate' => 'nullable|boolean',
            'max_consecutive_days' => 'nullable|integer|min:1|max:365',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_paid'] = $request->has('is_paid') ? 1 : 0;
        $validated['requires_approval'] = $request->has('requires_approval') ? 1 : 0;
        $validated['requires_medical_certificate'] = $request->has('requires_medical_certificate') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['updated_by'] = auth()->id();

        $leaveType->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Leave type updated successfully.',
                'leave_type' => $leaveType
            ]);
        }

        return redirect()->route('admin.leave-types.index')->with('success', 'Leave type updated successfully.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('leave_type.delete')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $leaveType = LeaveType::findOrFail($id);

        if ($leaveType->balances()->exists() || $leaveType->requests()->exists()) {
            return response()->json([
                'error' => 'Cannot delete leave type with existing balances or requests.'
            ], 422);
        }

        $leaveType->delete();

        return response()->json(['success' => true, 'message' => 'Leave type deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        if (!auth()->user()->can('leave_type.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $leaveType = LeaveType::findOrFail($id);
        $leaveType->is_active = !$leaveType->is_active;
        $leaveType->save();

        return response()->json([
            'success' => true,
            'message' => 'Leave type status toggled.',
            'is_active' => $leaveType->is_active
        ]);
    }

    public function export()
    {
        if (!auth()->user()->can('leave_type.view')) {
            abort(403);
        }

        $leaveTypes = LeaveType::with('company')->get();
        $filename = 'leave_types_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($leaveTypes) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Code', 'Name', 'Company', 'Days/Year', 'Paid', 'Medical Cert', 'Active', 'Description']);
            foreach ($leaveTypes as $lt) {
                fputcsv($handle, [
                    $lt->id,
                    $lt->leave_code,
                    $lt->leave_name,
                    $lt->company->name ?? 'N/A',
                    $lt->days_per_year,
                    $lt->is_paid ? 'Yes' : 'No',
                    $lt->requires_medical_certificate ? 'Yes' : 'No',
                    $lt->is_active ? 'Yes' : 'No',
                    $lt->description ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}