<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of exchange rates.
     * Requires 'exchange_rate.view' permission.
     */
    public function index()
    {
        if (!auth()->user()->can('exchange_rate.view')) {
            abort(403, 'You do not have permission to view exchange rates.');
        }

        $exchangeRates = ExchangeRate::with(['fromCurrency', 'toCurrency', 'createdBy'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => ExchangeRate::count(),
            'today' => ExchangeRate::whereDate('created_at', today())->count(),
            'this_week' => ExchangeRate::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'currencies' => Currency::where('is_active', 1)->count(),
        ];

        $currencies = Currency::where('is_active', 1)->get();

        return view('admin.exchange-rates.index', compact('exchangeRates', 'stats', 'currencies'));
    }

    // No create() method – handled by modal

    /**
     * Store a newly created exchange rate.
     * Requires 'exchange_rate.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('exchange_rate.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create exchange rates.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'from_currency_id' => 'required|exists:currencies,id|different:to_currency_id',
                'to_currency_id' => 'required|exists:currencies,id|different:from_currency_id',
                'rate' => 'required|numeric|min:0.000001',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if rate already exists for this pair on this date
            $existing = ExchangeRate::where('from_currency_id', $request->from_currency_id)
                ->where('to_currency_id', $request->to_currency_id)
                ->whereDate('date', $request->date)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'error' => 'Exchange rate for this currency pair already exists on this date.'
                ], 422);
            }

            $exchangeRate = ExchangeRate::create([
                'from_currency_id' => $request->from_currency_id,
                'to_currency_id' => $request->to_currency_id,
                'rate' => $request->rate,
                'date' => $request->date,
                'created_by' => auth()->id(),
            ]);

            Log::info('Exchange rate created', [
                'exchange_rate_id' => $exchangeRate->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exchange rate created successfully!',
                'exchange_rate' => $exchangeRate
            ]);

        } catch (\Exception $e) {
            Log::error('Exchange rate creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create exchange rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified exchange rate.
     * Requires 'exchange_rate.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('exchange_rate.view')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view exchange rates.'
                ], 403);
            }
            abort(403);
        }

        try {
            $exchangeRate = ExchangeRate::with(['fromCurrency', 'toCurrency', 'createdBy'])->findOrFail($id);
            if (request()->ajax()) {
                return response()->json($exchangeRate);
            }
            return view('admin.exchange-rates.show', compact('exchangeRate'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Exchange rate not found.'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified exchange rate.
     * Returns JSON for modal.
     * Requires 'exchange_rate.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('exchange_rate.edit')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit exchange rates.'
                ], 403);
            }
            abort(403);
        }

        try {
            $exchangeRate = ExchangeRate::findOrFail($id);
            $currencies = Currency::where('is_active', 1)->get();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'exchange_rate' => $exchangeRate,
                    'currencies' => $currencies,
                ]);
            }

            return view('admin.exchange-rates.edit', compact('exchangeRate', 'currencies'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Exchange rate not found.'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Update the specified exchange rate.
     * Requires 'exchange_rate.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('exchange_rate.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit exchange rates.'
            ], 403);
        }

        try {
            $exchangeRate = ExchangeRate::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'rate' => 'required|numeric|min:0.000001',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $exchangeRate->update([
                'rate' => $request->rate,
                'date' => $request->date,
            ]);

            Log::info('Exchange rate updated', [
                'exchange_rate_id' => $exchangeRate->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exchange rate updated successfully!',
                'exchange_rate' => $exchangeRate
            ]);

        } catch (\Exception $e) {
            Log::error('Exchange rate update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update exchange rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified exchange rate.
     * Requires 'exchange_rate.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('exchange_rate.delete')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete exchange rates.'
                ], 403);
            }
            abort(403);
        }

        try {
            $exchangeRate = ExchangeRate::findOrFail($id);
            $exchangeRate->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exchange rate deleted successfully!'
                ]);
            }

            return redirect()->route('admin.exchange-rates.index')->with('success', 'Exchange rate deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete exchange rate.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete exchange rate.');
        }
    }

    /**
     * Fetch latest exchange rates from external API.
     * Requires 'exchange_rate.fetch_latest' permission.
     */
    public function fetchLatest()
    {
        if (!auth()->user()->can('exchange_rate.fetch_latest')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to fetch latest rates.'
            ], 403);
        }

        try {
            // This is a placeholder – you can integrate with an external API like exchangerate-api.com
            // For now, return a success message
            return response()->json([
                'success' => true,
                'message' => 'Exchange rates fetched successfully! (Integration coming soon)'
            ]);
        } catch (\Exception $e) {
            Log::error('Fetch exchange rates failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch exchange rates.'
            ], 500);
        }
    }

    /**
     * Export exchange rates.
     * Requires 'exchange_rate.export' permission.
     */
    public function export()
    {
        if (!auth()->user()->can('exchange_rate.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }
}