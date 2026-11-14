<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesPayment;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Warehouse;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('sale.view')) {
            abort(403, 'You do not have permission to view sales.');
        }

        $sales = Sale::with(['customer', 'user', 'currency'])->latest()->paginate(15);

        $stats = [
            'total' => Sale::count(),
            'pending' => Sale::where('payment_status', 'pending')->count(),
            'partial' => Sale::where('payment_status', 'partial')->count(),
            'paid' => Sale::where('payment_status', 'paid')->count(),
            'cancelled' => Sale::where('payment_status', 'cancelled')->count(),
            'overdue' => Sale::where('due_date', '<', now())->whereIn('payment_status', ['pending', 'partial'])->count(),
        ];

        $customers = Customer::where('is_active', 1)->get();
        $products = Product::where('is_active', 1)->get();
        $currencies = Currency::where('is_active', 1)->get();
        $warehouses = Warehouse::where('is_active', 1)->get();

        return view('admin.sales.index', compact('sales', 'stats', 'customers', 'products', 'currencies', 'warehouses'));
    }

   public function store(Request $request)
{
    if (!auth()->user()->can('sale.create')) {
        return response()->json(['error' => 'You do not have permission to create sales.'], 403);
    }

    try {
        // 1. Validate
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'sale_date' => 'required|date',
            'due_date' => 'nullable|date|after:sale_date',
            'payment_method' => 'required|in:cash,card,mobile,credit,bank_transfer,cheque',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        // 2. Create sale
        $sale = Sale::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'customer_id' => $request->customer_id,
            'user_id' => auth()->id(),
            'warehouse_id' => $request->warehouse_id,
            'currency_id' => $request->currency_id,
            'exchange_rate' => 1,
            'sale_date' => $request->sale_date,
            'due_date' => $request->due_date,
            'discount_type' => $request->discount_type ?? 'percentage',
            'discount' => $request->discount ?? 0,
            'tax' => $request->tax ?? 0,
            'shipping_cost' => $request->shipping_cost ?? 0,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'amount_paid' => 0,
            'notes' => $request->notes,
            'internal_notes' => $request->internal_notes,
            'subtotal' => 0,
            'total' => 0,
        ]);

        $subtotal = 0;
        foreach ($request->items as $index => $itemData) {
            $product = Product::find($itemData['product_id']);
            if (!$product) {
                throw new \Exception("Product not found at index {$index}: ID {$itemData['product_id']}");
            }
            if ($product->quantity < $itemData['quantity']) {
                throw new \Exception("Insufficient stock for product: {$product->name} (Available: {$product->quantity}, Requested: {$itemData['quantity']})");
            }

            $total = $itemData['quantity'] * $itemData['unit_price'];
            $discountAmount = $itemData['discount'] ?? 0;
            $discountedTotal = $total - $discountAmount;
            $taxRate = $product->tax_rate ?? 0;
            $taxAmount = $discountedTotal * ($taxRate / 100);

            // 3. Create SaleItem – ensure all fields match the table
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $itemData['product_id'],
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'discount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => $discountedTotal + $taxAmount,
            ]);

            // 4. Update stock
            $oldQty = $product->quantity;
            $product->quantity -= $itemData['quantity'];
            $product->save();

            // 5. Log stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'sale',
                'quantity_change' => -$itemData['quantity'],
                'previous_quantity' => $oldQty,
                'new_quantity' => $product->quantity,
                'reference' => $sale->invoice_number,
                'notes' => "Sale via POS - {$sale->invoice_number}",
            ]);

            $subtotal += $discountedTotal;
        }

        // 6. Calculate totals
        $discountValue = ($request->discount_type === 'fixed') ? ($request->discount ?? 0) : ($subtotal * ($request->discount / 100));
        $taxAmount = $subtotal * ($request->tax / 100);
        $total = $subtotal - $discountValue + $taxAmount + ($request->shipping_cost ?? 0);

        $sale->update([
            'subtotal' => $subtotal,
            'discount' => $discountValue,
            'tax' => $taxAmount,
            'total' => $total,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Sale created successfully!',
            'sale_id' => $sale->id,
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollBack();
        // Database specific error – shows the SQL and the column
        return response()->json([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage(),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
        ], 500);
    } catch (\Exception $e) {
        DB::rollBack();
        // Log the full trace (for debugging)
        \Log::error('Sale creation failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
        // Return a detailed error to the client
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(), // Only for development; remove in production
        ], 500);
    }
}

    public function show($id)
    {
        if (!auth()->user()->can('sale.view')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You do not have permission to view sales.'], 403);
            }
            abort(403);
        }

        try {
            $sale = Sale::with(['customer', 'user', 'currency', 'items.product', 'warehouse'])->findOrFail($id);
            if (request()->ajax()) {
                return response()->json($sale);
            }
            return view('admin.sales.show', compact('sale'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Sale not found.'], 404);
            }
            abort(404);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->can('sale.edit')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You do not have permission to edit sales.'], 403);
            }
            abort(403);
        }

        // findOrFail is handled separately from the rest of the query so a
        // "not found" (404) is never confused with a genuine server error (500).
        try {
            $sale = Sale::with('items')->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Sale not found.'], 404);
            }
            abort(404);
        }

        if ($sale->payment_status === 'paid') {
            return response()->json(['success' => false, 'error' => 'Paid sales cannot be edited.'], 422);
        }

        try {
            $customers = Customer::where('is_active', 1)->get();
            $products = Product::where('is_active', 1)->get();
            $currencies = Currency::where('is_active', 1)->get();
            $warehouses = Warehouse::where('is_active', 1)->get();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'sale' => $sale,
                    'customers' => $customers,
                    'products' => $products,
                    'currencies' => $currencies,
                    'warehouses' => $warehouses,
                ]);
            }

            return view('admin.sales.edit', compact('sale', 'customers', 'products', 'currencies', 'warehouses'));
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Sale edit load DB error', [
                'sale_id' => $id,
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
            ]);
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
            }
            abort(500);
        } catch (\Exception $e) {
            Log::error('Sale edit load failed', [
                'sale_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            abort(500);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('sale.edit')) {
            return response()->json(['error' => 'You do not have permission to edit sales.'], 403);
        }

        try {
            $sale = Sale::findOrFail($id);
            if ($sale->payment_status === 'paid') {
                return response()->json(['error' => 'Paid sales cannot be edited.'], 422);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'currency_id' => 'required|exists:currencies,id',
                'sale_date' => 'required|date',
                'due_date' => 'nullable|date|after:sale_date',
                'payment_method' => 'required|in:cash,card,mobile,credit,bank_transfer,cheque',
                'discount_type' => 'nullable|in:percentage,fixed',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            // Restore old stock (reverse previous deductions)
            foreach ($sale->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->quantity += $oldItem->quantity;
                    $product->save();
                }
            }

            // Delete old items
            $sale->items()->delete();

            // Update sale details
            $sale->update([
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'currency_id' => $request->currency_id,
                'sale_date' => $request->sale_date,
                'due_date' => $request->due_date,
                'discount_type' => $request->discount_type ?? 'percentage',
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'internal_notes' => $request->internal_notes,
            ]);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found: {$itemData['product_id']}");
                }
                if ($product->quantity < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $total = $itemData['quantity'] * $itemData['unit_price'];
                $discountAmount = $itemData['discount'] ?? 0;
                $discountedTotal = $total - $discountAmount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $discountAmount,
                    'tax_rate' => $product->tax_rate ?? 0,
                    'total' => $discountedTotal,
                ]);

                // Deduct stock again
                $oldQty = $product->quantity;
                $product->quantity -= $itemData['quantity'];
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'sale',
                    'quantity_change' => -$itemData['quantity'],
                    'previous_quantity' => $oldQty,
                    'new_quantity' => $product->quantity,
                    'reference' => $sale->invoice_number,
                    'notes' => "Sale updated - {$sale->invoice_number}",
                ]);

                $subtotal += $discountedTotal;
            }

            $discountValue = ($request->discount_type === 'fixed') ? ($request->discount ?? 0) : ($subtotal * ($request->discount / 100));
            $taxAmount = $subtotal * ($request->tax / 100);
            $total = $subtotal - $discountValue + $taxAmount + ($request->shipping_cost ?? 0);

            $sale->update([
                'subtotal' => $subtotal,
                'discount' => $discountValue,
                'tax' => $taxAmount,
                'total' => $total,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale updated successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update sale: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('sale.delete')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You do not have permission to delete sales.'], 403);
            }
            abort(403);
        }

        try {
            $sale = Sale::findOrFail($id);
            if (!in_array($sale->payment_status, ['pending', 'cancelled'])) {
                return response()->json(['error' => 'Only pending or cancelled sales can be deleted.'], 422);
            }

            // Restore stock
            foreach ($sale->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity += $item->quantity;
                    $product->save();
                }
            }

            $sale->items()->delete();
            $sale->delete();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Sale deleted successfully!']);
            }
            return redirect()->route('admin.sales.index')->with('success', 'Sale deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Failed to delete sale.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete sale.');
        }
    }

    /**
     * Register a payment against a sale.
     *
     * Hardened version:
     *  - Wrapped in a DB transaction so a failure never leaves the sale half-updated.
     *  - Casts amount_paid/total to float defensively (in case columns are null or
     *    stored as strings from the DB driver), preventing "Unsupported operand
     *    types" TypeErrors on some PHP/DB combinations.
     *  - Catches QueryException separately so a missing/renamed column (e.g.
     *    change_amount) surfaces a clear "Database error" message with the SQL,
     *    instead of a generic 500.
     *  - Always logs the real exception server-side via Log::error, and returns
     *    the message in the JSON payload so the frontend can display it instead
     *    of a hardcoded "Failed to process payment." string.
     */
      public function pay(Request $request, $id)
    {
        if (!auth()->user()->can('sale.pay')) {
            return response()->json(['error' => 'You do not have permission to process payments.'], 403);
        }

        try {
            $sale = Sale::findOrFail($id);

            if ($sale->payment_status === 'paid') {
                return response()->json(['error' => 'Sale is already paid.'], 422);
            }
            if ($sale->payment_status === 'cancelled') {
                return response()->json(['error' => 'Cancelled sales cannot receive payments.'], 422);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'nullable|string',
                'reference' => 'nullable|string|max:100',
                'notes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $amount = (float) $request->amount;
            $total = (float) $sale->total;
            $currentPaid = (float) ($sale->amount_paid ?? 0);
            $balance = $total - $currentPaid;

            if ($amount > $balance + 0.0001) {
                return response()->json(['error' => 'Amount exceeds outstanding balance of ' . number_format($balance, 2) . '.'], 422);
            }

            DB::beginTransaction();

            // Get or create invoice for this sale
            $invoice = \App\Models\Invoice::where('sale_id', $sale->id)->first();
            
            if (!$invoice) {
                // Create invoice if it doesn't exist (fallback)
                $invoice = \App\Models\Invoice::create([
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

            $newPaid = $currentPaid + $amount;
            $sale->amount_paid = $newPaid;

            if ($newPaid >= $total) {
                $sale->payment_status = 'paid';
            } else {
                $sale->payment_status = 'partial';
            }

            $sale->save();

            // Update invoice
            $invoice->amount_paid = $newPaid;
            $invoice->balance = $total - $newPaid;
            if ($newPaid >= $total) {
                $invoice->payment_status = 'paid';
            } elseif ($newPaid > 0 && $newPaid < $total) {
                $invoice->payment_status = 'partial';
            } else {
                $invoice->payment_status = 'pending';
            }
            $invoice->save();

            // Create payment record
            SalesPayment::create([
                'invoice_id' => $invoice->id,  // Use the invoice ID, not sale ID
                'customer_id' => $sale->customer_id,
                'currency_id' => $sale->currency_id,
                'exchange_rate' => $sale->exchange_rate ?? 1,
                'amount' => $amount,
                'payment_date' => now(),
                'method' => $request->payment_method ?? $sale->payment_method,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!',
                'payment_status' => $sale->payment_status,
                'balance' => round($total - $sale->amount_paid, 2),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale payment failed', [
                'sale_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to process payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($id)
    {
        if (!auth()->user()->can('sale.cancel')) {
            return response()->json(['error' => 'You do not have permission to cancel sales.'], 403);
        }

        try {
            $sale = Sale::findOrFail($id);
            if ($sale->payment_status === 'paid') {
                return response()->json(['error' => 'Paid sales cannot be cancelled.'], 422);
            }

            // Restore stock
            foreach ($sale->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity += $item->quantity;
                    $product->save();
                }
            }

            $sale->payment_status = 'cancelled';
            $sale->save();

            return response()->json(['success' => true, 'message' => 'Sale cancelled successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to cancel sale.'], 500);
        }
    }

    public function invoice($id)
    {
        if (!auth()->user()->can('sale.view')) {
            abort(403);
        }
        $sale = Sale::with(['customer', 'user', 'currency', 'items.product'])->findOrFail($id);
        return view('admin.sales.invoice', compact('sale'));
    }

    public function print($id)
    {
        if (!auth()->user()->can('sale.print')) {
            abort(403);
        }
        $sale = Sale::with(['customer', 'user', 'currency', 'items.product'])->findOrFail($id);
        return view('admin.sales.print', compact('sale'));
    }

    public function pdf($id)
    {
        if (!auth()->user()->can('sale.pdf')) {
            abort(403);
        }
        $sale = Sale::with(['customer', 'user', 'currency', 'items.product'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.sales.pdf', compact('sale'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('invoice-' . $sale->invoice_number . '.pdf');
    }

    public function export()
    {
        if (!auth()->user()->can('sale.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('sale.delete')) {
            return response()->json(['error' => 'You do not have permission to delete sales.'], 403);
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:sales,id',
        ]);

        try {
            $count = Sale::whereIn('id', $request->ids)
                ->whereIn('payment_status', ['pending', 'cancelled'])
                ->count();

            if ($count !== count($request->ids)) {
                return response()->json(['error' => 'Some sales cannot be deleted (only pending or cancelled).'], 422);
            }

            // Restore stock for all
            $sales = Sale::whereIn('id', $request->ids)->get();
            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->quantity += $item->quantity;
                        $product->save();
                    }
                }
                $sale->items()->delete();
            }

            Sale::whereIn('id', $request->ids)->delete();
            return response()->json(['success' => true, 'message' => "$count sales deleted successfully!"]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete sales.'], 500);
        }
    }

    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $last = Sale::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
    /**
     * Get product details by ID (for AJAX)
     */
    public function getProductDetails($id)
    {
        if (!auth()->user()->can('sale.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $product = Product::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'selling_price' => $product->selling_price ?? 0,
            'purchase_price' => $product->purchase_price ?? 0,
            'cost_price' => $product->cost_price ?? 0,
            'quantity' => $product->quantity,
            'unit' => $product->unit ?? 'piece',
            'tax_rate' => $product->tax_rate ?? 0,
        ]);
    }
}