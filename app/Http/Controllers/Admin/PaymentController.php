<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('payment.view')) {
            abort(403, 'You do not have permission to view payments.');
        }

        $query = Payment::with(['paymentable', 'currency', 'createdBy']);

        if ($request->paymentable_type) {
            $query->where('paymentable_type', $request->paymentable_type);
        }
        if ($request->paymentable_id) {
            $query->where('paymentable_id', $request->paymentable_id);
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
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        $payments = $query->latest()->paginate(15);

        $stats = [
            'total' => Payment::count(),
            'total_amount' => Payment::sum('amount'),
            'by_method' => Payment::select('method', DB::raw('count(*) as count, sum(amount) as total'))
                ->groupBy('method')->get(),
            'today' => Payment::whereDate('payment_date', today())->count(),
            'this_month' => Payment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)->count(),
        ];

        $methods = ['cash', 'bank_transfer', 'cheque', 'mobile'];
        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();
        $entityTypes = [
            'App\Models\PurchaseOrder' => 'Purchase Order',
            'App\Models\Sale' => 'Sale',
        ];

        return view('admin.payments.index', compact(
            'payments', 'stats', 'methods', 'currencies', 'entityTypes'
        ));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->can('payment.create')) {
            abort(403, 'You do not have permission to create payments.');
        }

        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();
        $methods = ['cash', 'bank_transfer', 'cheque', 'mobile'];
        $entityTypes = [
            'App\Models\PurchaseOrder' => 'Purchase Order',
            'App\Models\Sale' => 'Sale',
        ];

        return view('admin.payments.create', compact('currencies', 'methods', 'entityTypes'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('payment.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validated = $request->validate([
            'paymentable_type' => ['required', 'string', Rule::in(['App\Models\PurchaseOrder', 'App\Models\Sale'])],
            'paymentable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank_transfer,cheque,mobile',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $parent = $this->getParentEntity($validated['paymentable_type'], $validated['paymentable_id']);
        if (!$parent) {
            return response()->json(['error' => 'Parent entity not found.'], 422);
        }

        $remaining = $parent->total_amount - $parent->paid_amount;
        if ($validated['amount'] > $remaining) {
            return response()->json(['error' => 'Amount exceeds remaining balance.'], 422);
        }

        $validated['exchange_rate'] = $validated['exchange_rate'] ?? 1;
        $validated['created_by'] = auth()->id();

        $payment = DB::transaction(function () use ($validated) {
            $payment = Payment::create($validated);
            Payment::recalcParent($payment);
            return $payment;
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'payment' => $payment->load('currency', 'createdBy')
            ]);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function show($id)
    {
        if (!auth()->user()->can('payment.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment = Payment::with(['paymentable', 'currency', 'createdBy'])->findOrFail($id);
        return response()->json($payment);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('payment.edit')) {
            abort(403, 'You do not have permission to edit payments.');
        }

        $payment = Payment::with('paymentable')->findOrFail($id);
        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();
        $methods = ['cash', 'bank_transfer', 'cheque', 'mobile'];

        return view('admin.payments.edit', compact('payment', 'currencies', 'methods'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('payment.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank_transfer,cheque,mobile',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $parent = $payment->paymentable;
        $oldAmount = $payment->amount;
        $remainingAfterOtherPayments = $parent->total_amount - ($parent->paid_amount - $oldAmount);
        if ($validated['amount'] > $remainingAfterOtherPayments) {
            return response()->json(['error' => 'Amount exceeds remaining balance.'], 422);
        }

        $validated['exchange_rate'] = $validated['exchange_rate'] ?? 1;

        DB::transaction(function () use ($payment, $validated) {
            $payment->update($validated);
            Payment::recalcParent($payment);
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully.',
                'payment' => $payment->fresh('currency', 'createdBy')
            ]);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('payment.delete')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment = Payment::findOrFail($id);
        DB::transaction(function () use ($payment) {
            $payment->delete();
            Payment::recalcParent($payment);
        });

        return response()->json(['success' => true, 'message' => 'Payment deleted successfully.']);
    }

    public function export()
    {
        if (!auth()->user()->can('payment.view')) {
            abort(403);
        }

        $payments = Payment::with(['paymentable', 'currency', 'createdBy'])->latest()->get();
        $filename = 'payments_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Entity', 'Entity ID', 'Amount', 'Currency', 'Date', 'Method', 'Reference', 'Created By', 'Created At']);
            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->id,
                    class_basename($p->paymentable_type) . ' #' . $p->paymentable_id,
                    $p->paymentable_id,
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

        public function getEntityDetails(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:App\Models\PurchaseOrder,App\Models\Sale',
            'id' => 'required|integer',
        ]);

        $entity = $this->getParentEntity($request->type, $request->id);
        if (!$entity) {
            return response()->json(['error' => 'Entity not found.'], 404);
        }

        return response()->json([
            'id' => $entity->id,
            'reference' => $entity->po_number ?? $entity->invoice_number ?? $entity->id,
            'total_amount' => $entity->total_amount,
            'paid_amount' => $entity->paid_amount,
            'remaining' => $entity->total_amount - $entity->paid_amount,
            'currency_id' => $entity->currency_id,
            'currency_symbol' => $entity->currency->symbol ?? '$',
        ]);
    }

    private function getParentEntity($type, $id)
    {
        switch ($type) {
            case 'App\Models\PurchaseOrder': return PurchaseOrder::find($id);
            case 'App\Models\Sale': return Sale::find($id);
            default: return null;
        }
    }
}