<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchasePayment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('purchase_payment.view')) {
            abort(403, 'You do not have permission to view purchase payments.');
        }

        $query = PurchasePayment::with(['purchaseOrder', 'supplier', 'currency', 'createdBy']);

        if ($request->purchase_order_id) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
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
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%")
                  ->orWhereHas('purchaseOrder', fn($s) => $s->where('po_number', 'LIKE', "%{$search}%"));
            });
        }

        $payments = $query->latest()->paginate(15);

        $stats = [
            'total' => PurchasePayment::count(),
            'total_amount' => PurchasePayment::sum('amount'),
            'by_method' => PurchasePayment::select('method', DB::raw('count(*) as count, sum(amount) as total'))
                ->groupBy('method')->get(),
            'today' => PurchasePayment::whereDate('payment_date', today())->count(),
            'this_month' => PurchasePayment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)->count(),
        ];

        $methods = ['cash', 'bank_transfer', 'cheque', 'mobile'];
        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();
        $suppliers = Supplier::where('is_active', 1)->orderBy('name')->get();

        return view('admin.purchase-payments.index', compact(
            'payments', 'stats', 'methods', 'currencies', 'suppliers'
        ));
    }

    public function create()
    {
        if (!auth()->user()->can('purchase_payment.create')) {
            abort(403, 'You do not have permission to create purchase payments.');
        }

        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();
        $methods = ['cash', 'bank_transfer', 'cheque', 'mobile'];
        $suppliers = Supplier::where('is_active', 1)->orderBy('name')->get();

        return view('admin.purchase-payments.create', compact('currencies', 'methods', 'suppliers'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('purchase_payment.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank_transfer,cheque,mobile',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $po = PurchaseOrder::find($validated['purchase_order_id']);
        if (!$po) {
            return response()->json(['error' => 'Purchase Order not found.'], 422);
        }

        $remaining = $po->total_amount - $po->paid_amount;
        if ($validated['amount'] > $remaining) {
            return response()->json(['error' => 'Amount exceeds remaining balance.'], 422);
        }

        $validated['exchange_rate'] = $validated['exchange_rate'] ?? 1;
        $validated['created_by'] = auth()->id();

        $payment = DB::transaction(function () use ($validated) {
            $payment = PurchasePayment::create($validated);
            PurchasePayment::recalcPurchaseOrder($payment);
            return $payment;
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase payment recorded successfully.',
                'payment' => $payment->load('currency', 'createdBy')
            ]);
        }

        return redirect()->route('admin.purchase-payments.index')->with('success', 'Purchase payment recorded successfully.');
    }

    public function show($id)
    {
        if (!auth()->user()->can('purchase_payment.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment = PurchasePayment::with(['purchaseOrder', 'supplier', 'currency', 'createdBy'])->findOrFail($id);
        return response()->json($payment);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('purchase_payment.edit')) {
            abort(403, 'You do not have permission to edit purchase payments.');
        }

        $payment = PurchasePayment::findOrFail($id);
        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();
        $methods = ['cash', 'bank_transfer', 'cheque', 'mobile'];
        $suppliers = Supplier::where('is_active', 1)->orderBy('name')->get();

        return view('admin.purchase-payments.edit', compact('payment', 'currencies', 'methods', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_payment.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment = PurchasePayment::findOrFail($id);

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank_transfer,cheque,mobile',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $po = PurchaseOrder::find($validated['purchase_order_id']);
        $oldAmount = $payment->amount;
        $remainingAfterOtherPayments = $po->total_amount - ($po->paid_amount - $oldAmount);
        if ($validated['amount'] > $remainingAfterOtherPayments) {
            return response()->json(['error' => 'Amount exceeds remaining balance.'], 422);
        }

        $validated['exchange_rate'] = $validated['exchange_rate'] ?? 1;

        DB::transaction(function () use ($payment, $validated) {
            $payment->update($validated);
            PurchasePayment::recalcPurchaseOrder($payment);
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase payment updated successfully.',
                'payment' => $payment->fresh('currency', 'createdBy')
            ]);
        }

        return redirect()->route('admin.purchase-payments.index')->with('success', 'Purchase payment updated successfully.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('purchase_payment.delete')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment = PurchasePayment::findOrFail($id);
        DB::transaction(function () use ($payment) {
            $payment->delete();
            PurchasePayment::recalcPurchaseOrder($payment);
        });

        return response()->json(['success' => true, 'message' => 'Purchase payment deleted successfully.']);
    }

    public function export()
    {
        if (!auth()->user()->can('purchase_payment.view')) {
            abort(403);
        }

        $payments = PurchasePayment::with(['purchaseOrder', 'supplier', 'currency', 'createdBy'])->latest()->get();
        $filename = 'purchase_payments_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'PO Number', 'Supplier', 'Amount', 'Currency', 'Date', 'Method', 'Reference', 'Created By', 'Created At']);
            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->purchaseOrder->po_number ?? 'N/A',
                    $p->supplier->name ?? 'N/A',
                    number_format($p->amount, 2),
                    $p->currency->code ?? 'N/A',
                    $p->payment_date->format('Y-m-d'),
                    $p->method,
                    $p->reference ?? '',
                    $p->createdBy->name ?? 'N/A',
                    $p->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function getPurchaseOrderDetails(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
        ]);

        $po = PurchaseOrder::with('supplier')->find($request->purchase_order_id);
        return response()->json([
            'id' => $po->id,
            'po_number' => $po->po_number,
            'total' => $po->total_amount,
            'paid' => $po->paid_amount,
            'remaining' => $po->total_amount - $po->paid_amount,
            'supplier_id' => $po->supplier_id,
            'currency_id' => $po->currency_id ?? null,
        ]);
    }
}