<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     * Requires 'invoice.view' permission.
     */
    public function index()
    {
        if (!auth()->user()->can('invoice.view')) {
            abort(403, 'You do not have permission to view invoices.');
        }

        $invoices = Invoice::with(['customer', 'sale'])->latest()->paginate(15);

        $stats = [
            'total' => Invoice::count(),
            'pending' => Invoice::where('payment_status', 'pending')->count(),
            'partial' => Invoice::where('payment_status', 'partial')->count(),
            'paid' => Invoice::where('payment_status', 'paid')->count(),
            'overdue' => Invoice::where('payment_status', 'overdue')->count(),
        ];

        // Sales without invoices – for the create modal dropdown
        $salesWithoutInvoice = Sale::whereDoesntHave('invoice')
            ->whereIn('payment_status', ['paid', 'partial', 'pending'])
            ->with('customer')
            ->get();

        return view('admin.invoices.index', compact('invoices', 'stats', 'salesWithoutInvoice'));
    }

    // No create() method – handled by modal

    /**
     * Store a newly created invoice.
     * Requires 'invoice.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('invoice.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create invoices.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'sale_id' => 'required|exists:sales,id',
                'invoice_date' => 'required|date',
                'due_date' => 'nullable|date|after:invoice_date',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $sale = Sale::findOrFail($request->sale_id);

            // Check if invoice already exists for this sale
            if ($sale->invoice) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invoice already exists for this sale.'
                ], 422);
            }

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $sale->subtotal,
                'discount' => $sale->discount,
                'tax' => $sale->tax,
                'total' => $sale->total,
                'amount_paid' => $sale->amount_paid,
                'balance' => $sale->total - $sale->amount_paid,
                'payment_status' => $sale->payment_status,
                'notes' => $request->notes,
            ]);

            Log::info('Invoice created', [
                'invoice_id' => $invoice->id,
                'sale_id' => $sale->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice generated successfully!',
                'invoice' => $invoice,
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     * Requires 'invoice.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('invoice.view')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view invoices.'
                ], 403);
            }
            abort(403);
        }

        try {
            $invoice = Invoice::with(['customer', 'sale', 'sale.items.product'])->findOrFail($id);

            if (request()->ajax()) {
                return response()->json($invoice);
            }

            return view('admin.invoices.show', compact('invoice'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invoice not found.'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified invoice.
     * Returns JSON for modal.
     * Requires 'invoice.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('invoice.edit')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit invoices.'
                ], 403);
            }
            abort(403);
        }

        try {
            $invoice = Invoice::findOrFail($id);

            if (request()->ajax()) {
                return response()->json($invoice);
            }

            return view('admin.invoices.edit', compact('invoice'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invoice not found.'
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Update the specified invoice.
     * Requires 'invoice.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('invoice.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit invoices.'
            ], 403);
        }

        try {
            $invoice = Invoice::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'payment_status' => 'required|in:pending,partial,paid,overdue',
                'due_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $invoice->update([
                'payment_status' => $request->payment_status,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            // If paid, update related sale
            if ($request->payment_status === 'paid') {
                $sale = $invoice->sale;
                if ($sale) {
                    $sale->payment_status = 'paid';
                    $sale->amount_paid = $sale->total;
                    $sale->save();
                }
            }

            Log::info('Invoice updated', [
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice.
     * Requires 'invoice.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('invoice.delete')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete invoices.'
                ], 403);
            }
            abort(403);
        }

        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice deleted successfully!'
                ]);
            }

            return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete invoice.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete invoice.');
        }
    }

    /**
     * Mark invoice as paid.
     * Requires 'invoice.mark_paid' permission.
     */
    public function markPaid($id)
    {
        if (!auth()->user()->can('invoice.mark_paid')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to mark invoices as paid.'
            ], 403);
        }

        try {
            $invoice = Invoice::findOrFail($id);

            if ($invoice->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'error' => 'Invoice is already marked as paid.'
                ], 422);
            }

            $invoice->payment_status = 'paid';
            $invoice->amount_paid = $invoice->total;
            $invoice->save();

            // Update related sale
            $sale = $invoice->sale;
            if ($sale) {
                $sale->payment_status = 'paid';
                $sale->amount_paid = $sale->total;
                $sale->save();
            }

            Log::info('Invoice marked as paid', [
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Mark paid failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark invoice as paid.'
            ], 500);
        }
    }

    /**
     * Send payment reminder.
     * Requires 'invoice.send_reminder' permission.
     */
    public function sendReminder($id)
    {
        if (!auth()->user()->can('invoice.send_reminder')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to send reminders.'
            ], 403);
        }

        try {
            $invoice = Invoice::findOrFail($id);

            // Implement email logic here:
            // \Mail::to($invoice->customer->email)->send(new InvoiceReminder($invoice));

            Log::info('Invoice reminder sent', [
                'invoice_id' => $invoice->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment reminder sent successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Send reminder failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send payment reminder.'
            ], 500);
        }
    }

    /**
     * Print invoice.
     * Requires 'invoice.print' permission.
     */
    public function print($id)
    {
        if (!auth()->user()->can('invoice.print')) {
            abort(403, 'You do not have permission to print invoices.');
        }

        $invoice = Invoice::with(['customer', 'sale.items.product'])->findOrFail($id);

        return view('admin.invoices.print', compact('invoice'));
    }

    /**
     * Download invoice as PDF.
     * Requires 'invoice.pdf' permission.
     */
    public function pdf($id)
    {
        if (!auth()->user()->can('invoice.pdf')) {
            abort(403, 'You do not have permission to download invoice PDF.');
        }

        $invoice = Invoice::with(['customer', 'sale.items.product'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

        /**
         * Export invoices to Excel/CSV.
         * Requires 'invoice.export' permission.
         */
        public function export()
        {
            if (!auth()->user()->can('invoice.export')) {
                abort(403, 'You do not have permission to export invoices.');
            }

            return back()->with('info', 'Export feature coming soon!');
        }

        /**
         * Generate a unique invoice number.
         */
        private function generateInvoiceNumber()
        {
            $prefix = 'INV';
            $date = now()->format('Ymd');
            $last = Invoice::whereDate('created_at', today())->count() + 1;
            return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
        }
        /**
     * Record a payment against an invoice.
     */
    public function recordPayment(Request $request, $id)
    {
        if (!auth()->user()->can('sales_payment.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $invoice = Invoice::findOrFail($id);

        if ($invoice->payment_status === 'paid') {
            return response()->json(['error' => 'Invoice already fully paid.'], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank_transfer,cheque,mobile,credit',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $remaining = $invoice->total - $invoice->amount_paid;
        if ($validated['amount'] > $remaining) {
            return response()->json(['error' => 'Amount exceeds remaining balance.'], 422);
        }

        DB::transaction(function () use ($validated, $invoice) {
            SalesPayment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $validated['amount'],
                'currency_id' => $invoice->currency_id ?? 1,
                'exchange_rate' => 1, // could be from currency settings
                'payment_date' => $validated['payment_date'],
                'method' => $validated['method'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            $invoice->updatePaymentStatus();
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'invoice' => $invoice->fresh('salesPayments')
        ]);
    }
}