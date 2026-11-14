<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies.
     * Requires 'currency.view' permission.
     */
    public function index()
    {
        if (!auth()->user()->can('currency.view')) {
            abort(403, 'You do not have permission to view currencies.');
        }

        $currencies = Currency::orderBy('is_base', 'desc')->orderBy('code')->paginate(15);

        $stats = [
            'total' => Currency::count(),
            'active' => Currency::where('is_active', 1)->count(),
            'inactive' => Currency::where('is_active', 0)->count(),
            'base' => Currency::where('is_base', 1)->count(),
        ];

        return view('admin.currencies.index', compact('currencies', 'stats'));
    }

    // No create() method – handled by modal

    /**
     * Store a newly created currency.
     * Requires 'currency.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('currency.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create currencies.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:10|unique:currencies',
                'name' => 'required|string|max:100',
                'symbol' => 'required|string|max:10',
                'decimal_places' => 'nullable|integer|min:0|max:4',
                'exchange_rate' => 'nullable|numeric|min:0',
                'is_base' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['is_base'] = $request->has('is_base') ? 1 : 0;
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            // If this currency is set as base, unset all other base currencies
            if ($data['is_base']) {
                Currency::where('is_base', 1)->update(['is_base' => 0]);
            }

            // Set exchange rate to 1 if it's base
            if ($data['is_base']) {
                $data['exchange_rate'] = 1;
            }

            $currency = Currency::create($data);

            Log::info('Currency created', [
                'currency_id' => $currency->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Currency created successfully!',
                'currency' => $currency
            ]);

        } catch (\Exception $e) {
            Log::error('Currency creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create currency: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified currency.
     * Requires 'currency.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('currency.view')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view currencies.'
                ], 403);
            }
            abort(403);
        }

        try {
            $currency = Currency::findOrFail($id);
            if (request()->ajax()) {
                return response()->json($currency);
            }
            return view('admin.currencies.show', compact('currency'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency not found.'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified currency.
     * Returns JSON for modal.
     * Requires 'currency.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('currency.edit')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit currencies.'
                ], 403);
            }
            abort(403);
        }

        try {
            $currency = Currency::findOrFail($id);
            if (request()->ajax()) {
                return response()->json($currency);
            }
            return view('admin.currencies.edit', compact('currency'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency not found.'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Update the specified currency.
     * Requires 'currency.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('currency.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit currencies.'
            ], 403);
        }

        try {
            $currency = Currency::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:10|unique:currencies,code,' . $id,
                'name' => 'required|string|max:100',
                'symbol' => 'required|string|max:10',
                'decimal_places' => 'nullable|integer|min:0|max:4',
                'exchange_rate' => 'nullable|numeric|min:0',
                'is_base' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['is_base'] = $request->has('is_base') ? 1 : 0;
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            // If this currency is set as base, unset all other base currencies
            if ($data['is_base']) {
                Currency::where('is_base', 1)->where('id', '!=', $id)->update(['is_base' => 0]);
                $data['exchange_rate'] = 1;
            }

            $currency->update($data);

            Log::info('Currency updated', [
                'currency_id' => $currency->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Currency updated successfully!',
                'currency' => $currency
            ]);

        } catch (\Exception $e) {
            Log::error('Currency update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update currency: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified currency.
     * Requires 'currency.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('currency.delete')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete currencies.'
                ], 403);
            }
            abort(403);
        }

        try {
            $currency = Currency::findOrFail($id);

            if ($currency->is_base) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete the base currency.'
                ], 422);
            }

            $currency->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Currency deleted successfully!'
                ]);
            }

            return redirect()->route('admin.currencies.index')->with('success', 'Currency deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete currency.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete currency.');
        }
    }

    /**
     * Toggle currency active status.
     * Requires 'currency.toggle_status' permission.
     */
    public function toggleStatus($id)
    {
        if (!auth()->user()->can('currency.toggle_status')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to toggle currency status.'
            ], 403);
        }

        try {
            $currency = Currency::findOrFail($id);

            if ($currency->is_base) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot deactivate the base currency.'
                ], 422);
            }

            $currency->is_active = !$currency->is_active;
            $currency->save();

            return response()->json([
                'success' => true,
                'message' => 'Currency status toggled successfully!',
                'is_active' => $currency->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to toggle currency status.'
            ], 500);
        }
    }

    /**
     * Set a currency as the base currency.
     * Requires 'currency.set_base' permission.
     */
    public function setBase($id)
    {
        if (!auth()->user()->can('currency.set_base')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to set base currency.'
            ], 403);
        }

        try {
            $currency = Currency::findOrFail($id);

            // Unset current base
            Currency::where('is_base', 1)->update(['is_base' => 0]);

            // Set new base
            $currency->is_base = 1;
            $currency->exchange_rate = 1;
            $currency->is_active = 1;
            $currency->save();

            return response()->json([
                'success' => true,
                'message' => 'Base currency set successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to set base currency.'
            ], 500);
        }
    }

    /**
     * Get currencies dropdown list.
     * Requires 'currency.view' permission.
     */
    public function dropdown()
    {
        if (!auth()->user()->can('currency.view')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to view currencies.'
            ], 403);
        }

        $currencies = Currency::where('is_active', 1)->orderBy('code')->get(['id', 'code', 'name', 'symbol']);
        return response()->json($currencies);
    }

    /**
     * Export currencies.
     * Requires 'currency.export' permission.
     */
    public function export()
    {
        if (!auth()->user()->can('currency.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    /**
     * Import currencies.
     * Requires 'currency.import' permission.
     */
    public function import(Request $request)
    {
        if (!auth()->user()->can('currency.import')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to import currencies.'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Import feature coming soon!',
            'file' => $request->file('file')->store('temp', 'public')
        ]);
    }
}