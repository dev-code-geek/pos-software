<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesPayment;
use App\Models\Sale;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('sales_payment.view')) {
            abort(403);
        }

        $query = SalesPayment::with(['invoice', 'customer', 'currency', 'createdBy']);

        if ($request->invoice_id) {
            $query->where('invoice_id', $request->invoice_id);
        }
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->method) {
            $query->where('method', $request->method);
        }
        if ($request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest()->paginate(15);

        $stats = [
            'total' => SalesPayment::count(),
            'total_amount' => SalesPayment::sum('amount'),
            'by_method' => SalesPayment::select('method', DB::raw('count(*) as count, sum(amount) as total'))
                ->groupBy('method')->get(),
        ];

        $customers = Customer::orderBy('name')->get();
        $methods = ['cash', 'bank_transfer', 'cheque', 'mobile', 'credit'];
        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();

        return view('admin.sales-payments.index', compact('payments', 'stats', 'customers', 'methods', 'currencies'));
    }

    /**
     * Resolve (or lazily create) the Invoice tied to a given Sale, so every
     * caller that records a sales payment writes to the same place.
     *
     * Previously this controller wrote the *sale's own id* into
     * SalesPayment.invoice_id, while SaleController@pay wrote the *real*
     * Invoice id into the same column. Since both code paths write to the
     * same invoice_id column, that meant SalesPayment.invoice_id held two
     * different kinds of value depending on which endpoint was used, and
     * any `SalesPayment::with('invoice')`/`where('invoice_id', ...)` query
     * would silently resolve to the wrong (or a nonexistent) record for
     * payments recorded here. This mirrors the fallback-creation logic in
     * SaleController@pay so there is exactly one source of truth.
     */
    private function resolveInvoiceForSale(Sale $sale): Invoice
    {
        $invoice = Invoice::where('sale_id', $sale->id)->first();

        if (!$invoice) {
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . $sale->invoice_number,
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'invoice_date' => $sale->sale_date,
                'due_date' => $sale->due_date ?? now()->addDays(30),
                'currency_id' => $sale->currency_id,
                'exchange_rate' => $sale->exchange_rate ?? 1,
                'subtotal' => $sale->subtotal,
                'discount' => $sale->discount,
                'tax' => $sale->tax,
                'total' => $sale->total,
                'amount_paid' => 0,
                'balance' => $sale->total,
                'payment_status' => 'pending',
                'notes' => 'Auto-generated from sale: ' . $sale->invoice_number,
            ]);
            $sale->update(['invoice_id' => $invoice->id]);
        }

        return $invoice;
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('sales_payment.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        // The person calling this endpoint identifies the sale being paid
        // (that's what the UI knows about), so we validate against sales.id
        // here and resolve the real Invoice ourselves below - the caller
        // should never need to know or guess an invoice id.
        $validated = $request->validate([
            'sale_id' => 'required_without:invoice_id|exists:sales,id',
            'invoice_id' => 'required_without:sale_id|exists:sales,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank_transfer,cheque,mobile,credit',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0',
        ]);

        $saleId = $validated['sale_id'] ?? $validated['invoice_id'];
        $sale = Sale::findOrFail($saleId);

        if ($sale->payment_status === 'paid') {
            return response()->json(['error' => 'Sale is already paid.'], 422);
        }
        if ($sale->payment_status === 'cancelled') {
            return response()->json(['error' => 'Cancelled sales cannot receive payments.'], 422);
        }

        // Check remaining balance
        $total = (float) $sale->total;
        $currentPaid = (float) ($sale->amount_paid ?? 0);
        $remaining = $total - $currentPaid;
        $amount = (float) $validated['amount'];

        if ($amount > $remaining + 0.0001) {
            return response()->json(['error' => 'Amount exceeds remaining balance of ' . number_format($remaining, 2) . '.'], 422);
        }

        $exchangeRate = $validated['exchange_rate'] ?? 1;

        $payment = DB::transaction(function () use ($validated, $sale, $amount, $exchangeRate, $total, $currentPaid) {
            $invoice = $this->resolveInvoiceForSale($sale);

            $payment = SalesPayment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $sale->customer_id,
                'currency_id' => $validated['currency_id'],
                'exchange_rate' => $exchangeRate,
                'amount' => $amount,
                'payment_date' => $validated['payment_date'],
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Keep the sale's own running totals/status in sync with the
            // ledger, mirroring what SaleController@pay does.
            $newPaid = $currentPaid + $amount;
            $sale->amount_paid = $newPaid;
            $sale->payment_status = $newPaid >= $total ? 'paid' : 'partial';
            $sale->save();

            // Keep the invoice in sync too, since it's now the record of truth.
            $invoice->amount_paid = $newPaid;
            $invoice->balance = $total - $newPaid;
            $invoice->payment_status = $newPaid >= $total
                ? 'paid'
                : ($newPaid > 0 ? 'partial' : 'pending');
            $invoice->save();

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'payment' => $payment->load('currency', 'createdBy'),
            'payment_status' => $sale->payment_status,
            'balance' => round($sale->total - $sale->amount_paid, 2),
        ]);
    }

    public function show($id)
    {
        if (!auth()->user()->can('sales_payment.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }
        $payment = SalesPayment::with(['invoice', 'customer', 'currency', 'createdBy'])->findOrFail($id);
        return response()->json($payment);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('sales_payment.delete')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment = SalesPayment::findOrFail($id);

        // invoice_id now always points at a real Invoice row (see store()
        // above), so we resolve the Sale via the invoice's sale_id rather
        // than assuming invoice_id itself is a sale id.
        $invoice = Invoice::find($payment->invoice_id);
        $sale = $invoice ? Sale::find($invoice->sale_id) : null;

        DB::transaction(function () use ($payment, $sale, $invoice) {
            $payment->delete();

            if ($sale) {
                $sale->amount_paid = max(0, $sale->amount_paid - $payment->amount);
                $sale->payment_status = $sale->amount_paid <= 0
                    ? 'pending'
                    : ($sale->amount_paid >= $sale->total ? 'paid' : 'partial');
                $sale->save();
            }

            if ($invoice) {
                $invoice->amount_paid = max(0, ($invoice->amount_paid ?? 0) - $payment->amount);
                $invoice->balance = ($invoice->total ?? 0) - $invoice->amount_paid;
                $invoice->payment_status = $invoice->amount_paid <= 0
                    ? 'pending'
                    : ($invoice->amount_paid >= ($invoice->total ?? 0) ? 'paid' : 'partial');
                $invoice->save();
            }
        });

        return response()->json(['success' => true, 'message' => 'Payment deleted successfully.']);
    }

    public function export()
    {
        if (!auth()->user()->can('sales_payment.view')) {
            abort(403);
        }
        // Stub – implement later
        return back()->with('info', 'Export coming soon.');
    }
}