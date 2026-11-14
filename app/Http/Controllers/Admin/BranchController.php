<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('branch.view')) {
            abort(403, 'You do not have permission to view branches.');
        }

        $query = Branch::with(['company', 'warehouse', 'manager']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $branches = $query->orderBy('name')->paginate(15);

        $stats = [
            'total' => Branch::count(),
            'active' => Branch::where('is_active', 1)->count(),
            'inactive' => Branch::where('is_active', 0)->count(),
        ];

        $companies = Company::where('is_active', 1)->get();
        $warehouses = Warehouse::where('is_active', 1)->get();
        $managers = User::role(['admin', 'manager'])->get();

        return view('admin.branches.index', compact('branches', 'stats', 'companies', 'warehouses', 'managers'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('branch.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['_token']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $branch = Branch::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully.',
                'branch' => $branch->load('company')
            ]);
        }

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }

    public function show($id)
    {
        if (!auth()->user()->can('branch.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $branch = Branch::with(['company', 'warehouse', 'manager'])->findOrFail($id);
        return response()->json($branch);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('branch.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $branch = Branch::findOrFail($id);
        $companies = Company::where('is_active', 1)->get();
        $warehouses = Warehouse::where('is_active', 1)->get();
        $managers = User::role(['admin', 'manager'])->get();

        return response()->json([
            'branch' => $branch,
            'companies' => $companies,
            'warehouses' => $warehouses,
            'managers' => $managers,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('branch.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $branch = Branch::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['_token', '_method']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $branch->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Branch updated successfully.',
                'branch' => $branch->load('company')
            ]);
        }

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('branch.delete')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $branch = Branch::findOrFail($id);

        // Check for related records
        if ($branch->departments()->exists() || 
            $branch->employees()->exists() || 
            $branch->sales()->exists()) {
            return response()->json([
                'error' => 'Cannot delete branch with existing departments, employees, or sales.'
            ], 422);
        }

        $branch->delete();

        return response()->json(['success' => true, 'message' => 'Branch deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        if (!auth()->user()->can('branch.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $branch = Branch::findOrFail($id);
        $branch->is_active = !$branch->is_active;
        $branch->save();

        return response()->json([
            'success' => true,
            'message' => 'Branch status toggled.',
            'is_active' => $branch->is_active
        ]);
    }

    public function export()
    {
        if (!auth()->user()->can('branch.view')) {
            abort(403);
        }

        $branches = Branch::with(['company', 'warehouse', 'manager'])->get();
        $filename = 'branches_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($branches) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Code', 'Company', 'Warehouse', 'Manager', 'City', 'Phone', 'Email', 'Active']);
            foreach ($branches as $b) {
                fputcsv($handle, [
                    $b->id,
                    $b->name,
                    $b->code,
                    $b->company->name ?? 'N/A',
                    $b->warehouse->name ?? 'N/A',
                    $b->manager->name ?? 'N/A',
                    $b->city ?? '',
                    $b->phone ?? '',
                    $b->email ?? '',
                    $b->is_active ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function dropdown(Request $request)
    {
        if (!auth()->user()->can('branch.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $query = Branch::where('is_active', 1);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $branches = $query->orderBy('name')->get(['id', 'name', 'code']);

        return response()->json($branches);
    }

    public function getManagers(Request $request)
    {
        if (!auth()->user()->can('branch.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $request->validate(['company_id' => 'required|exists:companies,id']);

        $managers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'manager']);
        })->whereHas('employee', function($q) use ($request) {
            $q->where('company_id', $request->company_id)->where('is_active', 1);
        })->get(['id', 'name']);

        return response()->json($managers);
    }
}