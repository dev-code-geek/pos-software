<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     * Requires 'customer.view' permission.
     */
    public function index()
    {
        if (!auth()->user()->can('customer.view')) {
            abort(403, 'You do not have permission to view customers.');
        }

        $customers = Customer::withCount(['sales'])->latest()->paginate(15);

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('is_active', true)->count(),
            'inactive' => Customer::where('is_active', false)->count(),
            'with_sales' => Customer::has('sales')->count(),
            'without_sales' => Customer::doesntHave('sales')->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    /**
     * Store a newly created customer (AJAX).
     * Requires 'customer.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('customer.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create customers.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'name'  => 'required|string|max:255',
                'email' => 'nullable|email|unique:customers,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'tax_id' => 'nullable|string|max:50',
                'type'   => 'nullable|in:individual,company',
                'credit_limit' => 'nullable|numeric|min:0',
                //'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = Customer::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'address'      => $request->address,
                'tax_id'       => $request->tax_id,
                'type'         => $request->type,
                'credit_limit' => $request->credit_limit ?? 0,
                'is_active'    => $request->has('is_active') ? 1 : 0,
            ]);

            Log::info('Customer created', ['customer_id' => $customer->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'customer' => $customer
            ]);

        } catch (\Exception $e) {
            Log::error('Customer creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified customer (AJAX for view modal).
     * Requires 'customer.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('customer.view')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view customers.'
                ], 403);
            }
            abort(403);
        }

        try {
            $customer = Customer::withCount(['sales'])->findOrFail($id);
            $totalSales = Sale::where('customer_id', $id)->sum('total_amount') ?? 0;

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'customer' => $customer,
                    'total_sales' => $totalSales,
                ]);
            }

            return view('admin.customers.show', compact('customer'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Customer not found.'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Show the form for editing the customer (AJAX).
     * Requires 'customer.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('customer.edit')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit customers.'
                ], 403);
            }
            abort(403);
        }

        try {
            $customer = Customer::findOrFail($id);
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'customer' => $customer,
                ]);
            }
            return view('admin.customers.edit', compact('customer'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Customer not found.'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Update the specified customer (AJAX).
     * Requires 'customer.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('customer.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit customers.'
            ], 403);
        }

        try {
            $customer = Customer::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name'  => 'required|string|max:255',
                'email' => 'nullable|email|unique:customers,email,' . $customer->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'tax_id' => 'nullable|string|max:50',
                'type'   => 'nullable|in:individual,company',
                'credit_limit' => 'nullable|numeric|min:0',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer->update([
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'address'      => $request->address,
                'tax_id'       => $request->tax_id,
                'type'         => $request->type,
                'credit_limit' => $request->credit_limit ?? 0,
                'is_active'    => $request->has('is_active') ? 1 : 0,
            ]);

            Log::info('Customer updated', ['customer_id' => $customer->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Customer update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified customer (AJAX).
     * Requires 'customer.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('customer.delete')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete customers.'
                ], 403);
            }
            abort(403);
        }

        try {
            $customer = Customer::findOrFail($id);

            // Prevent deletion if customer has sales
            if ($customer->sales()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete customer with existing sales.'
                ], 422);
            }

            $customer->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer deleted successfully!'
                ]);
            }

            return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete customer.'
                ], 500);
            }
            return back()->with('error', 'Failed to delete customer.');
        }
    }

    /**
     * Toggle active status (AJAX).
     * Requires 'customer.edit' permission.
     */
    public function toggleStatus($id)
    {
        if (!auth()->user()->can('customer.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to change customer status.'
            ], 403);
        }

        try {
            $customer = Customer::findOrFail($id);
            $customer->is_active = !$customer->is_active;
            $customer->save();

            return response()->json([
                'success' => true,
                'message' => 'Customer status updated successfully!',
                'is_active' => $customer->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update status.'
            ], 500);
        }
    }

    /**
     * Export customers (placeholder).
     * Requires 'customer.export' permission.
     */
    public function export()
    {
        if (!auth()->user()->can('customer.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    /**
     * Bulk delete customers (AJAX).
     * Requires 'customer.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('customer.delete')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete customers.'
            ], 403);
        }

        try {
            $ids = $request->customer_ids ?? [];
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No customers selected.'
                ], 422);
            }

            // Check if any have sales
            $hasSales = Customer::whereIn('id', $ids)->has('sales')->exists();
            if ($hasSales) {
                return response()->json([
                    'success' => false,
                    'error' => 'Some selected customers have sales and cannot be deleted.'
                ], 422);
            }

            Customer::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' customer(s) deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete selected customers.'
            ], 500);
        }
    }
}