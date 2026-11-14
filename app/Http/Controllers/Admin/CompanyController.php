<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('company.view')) {
            abort(403, 'You do not have permission to view companies.');
        }

        $query = Company::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $companies = $query->orderBy('name')->paginate(15);

        $stats = [
            'total' => Company::count(),
            'active' => Company::where('is_active', 1)->count(),
            'inactive' => Company::where('is_active', 0)->count(),
        ];

        $currencies = Currency::where('is_active', 1)->get();

        return view('admin.companies.index', compact('companies', 'stats', 'currencies'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('company.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100|unique:companies',
            'tax_id' => 'nullable|string|max:100|unique:companies',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency_id' => 'nullable|exists:currencies,id',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'default_tax' => 'nullable|numeric|min:0|max:100',
            'invoice_prefix' => 'nullable|string|max:50',
            'quotation_prefix' => 'nullable|string|max:50',
            'debit_note_prefix' => 'nullable|string|max:50',
            'credit_note_prefix' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->except(['_token', 'logo']);
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = 'company_logo_' . time() . '.' . $logo->getClientOriginalExtension();
                $logoPath = $logo->storeAs('company/logos', $logoName, 'public');
                $data['logo'] = $logoPath;
            }

            $company = Company::create($data);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Company created successfully.',
                    'company' => $company
                ]);
            }

            return redirect()->route('admin.companies.index')->with('success', 'Company created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create company: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('company.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $company = Company::with(['currency'])->findOrFail($id);
        return response()->json($company);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('company.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $company = Company::findOrFail($id);
        $currencies = Currency::where('is_active', 1)->get();

        return response()->json([
            'company' => $company,
            'currencies' => $currencies,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('company.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $company = Company::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100|unique:companies,registration_number,' . $id,
            'tax_id' => 'nullable|string|max:100|unique:companies,tax_id,' . $id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency_id' => 'nullable|exists:currencies,id',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'default_tax' => 'nullable|numeric|min:0|max:100',
            'invoice_prefix' => 'nullable|string|max:50',
            'quotation_prefix' => 'nullable|string|max:50',
            'debit_note_prefix' => 'nullable|string|max:50',
            'credit_note_prefix' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->except(['_token', 'logo', '_method']);
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                    Storage::disk('public')->delete($company->logo);
                }
                $logo = $request->file('logo');
                $logoName = 'company_logo_' . time() . '.' . $logo->getClientOriginalExtension();
                $logoPath = $logo->storeAs('company/logos', $logoName, 'public');
                $data['logo'] = $logoPath;
            }

            $company->update($data);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Company updated successfully.',
                    'company' => $company
                ]);
            }

            return redirect()->route('admin.companies.index')->with('success', 'Company updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update company: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('company.delete')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $company = Company::findOrFail($id);

        // Check for related records
        if ($company->branches()->exists() || 
            $company->departments()->exists() || 
            $company->employees()->exists() ||
            $company->leaveTypes()->exists() ||
            $company->payrollPeriods()->exists()) {
            return response()->json([
                'error' => 'Cannot delete company with existing branches, departments, employees, leave types, or payroll periods.'
            ], 422);
        }

        // Delete logo
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return response()->json(['success' => true, 'message' => 'Company deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        if (!auth()->user()->can('company.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $company = Company::findOrFail($id);
        $company->is_active = !$company->is_active;
        $company->save();

        return response()->json([
            'success' => true,
            'message' => 'Company status toggled.',
            'is_active' => $company->is_active
        ]);
    }

    public function export()
    {
        if (!auth()->user()->can('company.view')) {
            abort(403);
        }

        $companies = Company::all();
        $filename = 'companies_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($companies) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Short Name', 'Reg. Number', 'Tax ID', 'Email', 'Phone', 'City', 'Country', 'Active']);
            foreach ($companies as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->name,
                    $c->short_name ?? '',
                    $c->registration_number ?? '',
                    $c->tax_id ?? '',
                    $c->email ?? '',
                    $c->phone ?? '',
                    $c->city ?? '',
                    $c->country ?? '',
                    $c->is_active ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function dropdown(Request $request)
    {
        if (!auth()->user()->can('company.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $query = Company::where('is_active', 1);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $companies = $query->orderBy('name')->get(['id', 'name', 'short_name']);

        return response()->json($companies);
    }
}