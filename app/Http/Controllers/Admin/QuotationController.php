<?php

namespace App\Http\Controllers\Admin;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('quotation.view')) {
            abort(403, 'You do not have permission to view quotations.');
        }

        $quotations = Quotation::with(['customer', 'user', 'currency'])->latest()->paginate(15);

        $stats = [
            'total' => Quotation::count(),
            'draft' => Quotation::where('status', 'draft')->count(),
            'sent' => Quotation::where('status', 'sent')->count(),
            'accepted' => Quotation::where('status', 'accepted')->count(),
            'rejected' => Quotation::where('status', 'rejected')->count(),
            'expired' => Quotation::where('status', 'expired')->count(),
            'converted' => Quotation::where('status', 'converted')->count(),
        ];

        $customers = Customer::where('is_active', 1)->get();
        $products = Product::where('is_active', 1)->get();
        $currencies = Currency::where('is_active', 1)->get();
        $warehouses = Warehouse::where('is_active', 1)->get();

        return view('admin.quotations.index', compact('quotations', 'stats', 'customers', 'products', 'currencies', 'warehouses'));
    }

    // create() omitted – handled by modal

    public function store(Request $request)
    {
        if (!auth()->user()->can('quotation.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create quotations.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'currency_id' => 'required|exists:currencies,id',
                'quotation_date' => 'required|date',
                'valid_until' => 'nullable|date|after:quotation_date',
                'discount_type' => 'required|in:percentage,fixed',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $quotation = Quotation::create([
                'quotation_number' => $this->generateQuotationNumber(),
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'warehouse_id' => $request->warehouse_id,
                'currency_id' => $request->currency_id,
                'exchange_rate' => 1, // can be dynamic based on currency
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'discount_type' => $request->discount_type,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'status' => 'draft',
                'notes' => $request->notes,
                'internal_notes' => $request->internal_notes,
                'subtotal' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found with ID: {$itemData['product_id']}");
                }

                // Price is authoritative from the product record — the client-submitted
                // unit_price is display-only and never trusted for the stored amount.
                $unitPrice = $product->selling_price ?? 0;

                $total = $itemData['quantity'] * $unitPrice;
                $discountAmount = isset($itemData['discount']) ? $itemData['discount'] : 0;
                $discountedTotal = $total - $discountAmount;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'discount' => $discountAmount,
                    'tax_rate' => $product->tax_rate ?? 0,
                    'total' => $discountedTotal,
                ]);

                $subtotal += $discountedTotal;
            }

            // Calculate final totals
            $discountValue = ($request->discount_type === 'percentage') ? ($subtotal * ($request->discount / 100)) : ($request->discount ?? 0);
            $taxAmount = $subtotal * ($request->tax / 100);
            $total = $subtotal - $discountValue + $taxAmount;

            $quotation->update([
                'subtotal' => $subtotal,
                'discount' => $discountValue,
                'tax' => $taxAmount,
                'total' => $total,
            ]);

            Log::info('Quotation created', [
                'quotation_id' => $quotation->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation created successfully!',
                'quotation' => $quotation
            ]);

        } catch (\Exception $e) {
            Log::error('Quotation creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to create quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('quotation.view')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view quotations.'
                ], 403);
            }
            abort(403);
        }

        try {
            $quotation = Quotation::with(['customer', 'user', 'currency', 'warehouse', 'items.product'])->findOrFail($id);
            if (request()->ajax()) {
                return response()->json($quotation);
            }
            return view('admin.quotations.show', compact('quotation'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Quotation not found.'
                ], 404);
            }
            abort(404);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->can('quotation.edit')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit quotations.'
                ], 403);
            }
            abort(403);
        }

        try {
            $quotation = Quotation::with('items')->findOrFail($id);
            if ($quotation->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft quotations can be edited.'
                ], 422);
            }

            $customers = Customer::where('is_active', 1)->get();
            $products = Product::where('is_active', 1)->get();
            $currencies = Currency::where('is_active', 1)->get();
            $warehouses = Warehouse::where('is_active', 1)->get();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'quotation' => $quotation,
                    'customers' => $customers,
                    'products' => $products,
                    'currencies' => $currencies,
                    'warehouses' => $warehouses,
                ]);
            }

            return view('admin.quotations.edit', compact('quotation', 'customers', 'products', 'currencies', 'warehouses'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Quotation not found.'
                ], 404);
            }
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('quotation.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit quotations.'
            ], 403);
        }

        try {
            $quotation = Quotation::findOrFail($id);
            if ($quotation->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft quotations can be edited.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'currency_id' => 'required|exists:currencies,id',
                'quotation_date' => 'required|date',
                'valid_until' => 'nullable|date|after:quotation_date',
                'discount_type' => 'required|in:percentage,fixed',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $quotation->update([
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'currency_id' => $request->currency_id,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'discount_type' => $request->discount_type,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'notes' => $request->notes,
                'internal_notes' => $request->internal_notes,
            ]);

            // Delete old items
            $quotation->items()->delete();

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found with ID: {$itemData['product_id']}");
                }

                // Price is authoritative from the product record, same as store().
                $unitPrice = $product->selling_price ?? 0;

                $total = $itemData['quantity'] * $unitPrice;
                $discountAmount = isset($itemData['discount']) ? $itemData['discount'] : 0;
                $discountedTotal = $total - $discountAmount;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'discount' => $discountAmount,
                    'tax_rate' => $product->tax_rate ?? 0,
                    'total' => $discountedTotal,
                ]);

                $subtotal += $discountedTotal;
            }

            $discountValue = ($request->discount_type === 'percentage') ? ($subtotal * ($request->discount / 100)) : ($request->discount ?? 0);
            $taxAmount = $subtotal * ($request->tax / 100);
            $total = $subtotal - $discountValue + $taxAmount;

            $quotation->update([
                'subtotal' => $subtotal,
                'discount' => $discountValue,
                'tax' => $taxAmount,
                'total' => $total,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Quotation update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('quotation.delete')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete quotations.'
                ], 403);
            }
            abort(403);
        }

        try {
            $quotation = Quotation::findOrFail($id);
            if (!in_array($quotation->status, ['draft', 'rejected', 'expired'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft, rejected, or expired quotations can be deleted.'
                ], 422);
            }

            $quotation->items()->delete();
            $quotation->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quotation deleted successfully!'
                ]);
            }
            return redirect()->route('admin.quotations.index')->with('success', 'Quotation deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete quotation.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete quotation.');
        }
    }

    public function send($id)
    {
        if (!auth()->user()->can('quotation.send')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to send quotations.'
            ], 403);
        }

        try {
            $quotation = Quotation::findOrFail($id);
            if ($quotation->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft quotations can be sent.'
                ], 422);
            }

            $quotation->update(['status' => 'sent']);

            // Send email logic here (optional)

            return response()->json([
                'success' => true,
                'message' => 'Quotation sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to send quotation.'
            ], 500);
        }
    }

    public function accept($id)
    {
        if (!auth()->user()->can('quotation.accept')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to accept quotations.'
            ], 403);
        }

        try {
            $quotation = Quotation::findOrFail($id);
            if ($quotation->status !== 'sent') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only sent quotations can be accepted.'
                ], 422);
            }

            $quotation->update(['status' => 'accepted']);

            return response()->json([
                'success' => true,
                'message' => 'Quotation accepted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to accept quotation.'
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->can('quotation.reject')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to reject quotations.'
            ], 403);
        }

        try {
            $quotation = Quotation::findOrFail($id);
            if ($quotation->status !== 'sent') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only sent quotations can be rejected.'
                ], 422);
            }

            $quotation->update([
                'status' => 'rejected',
                'notes' => $request->reason ? ($quotation->notes ? $quotation->notes . "\n" : '') . "Rejected: " . $request->reason : $quotation->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation rejected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject quotation.'
            ], 500);
        }
    }

    /**
     * Convert an accepted quotation into a sale.
     *
     * This is the point where inventory is actually committed: each product's
     * stock is locked, verified, and decremented inside a single DB transaction,
     * with a StockMovement audit row written per line. If any line doesn't have
     * enough stock, the whole conversion is rolled back and nothing is created.
     */
    public function convertToSale(Request $request, $id)
    {
        if (!auth()->user()->can('quotation.convert_to_sale')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to convert quotations to sales.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            // Sales require a payment_method (NOT NULL in the sales table), so the
            // quotation form must supply one at conversion time — it has no field
            // for this itself. Defaults to 'credit' if the client sends nothing.
            'payment_method' => 'nullable|in:cash,card,mobile,credit,bank_transfer,cheque',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $paymentMethod = $request->input('payment_method', 'credit');

        try {
            $quotation = Quotation::with('items')->findOrFail($id);
            if ($quotation->status !== 'accepted') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only accepted quotations can be converted to sales.'
                ], 422);
            }

            if ($quotation->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This quotation has no items to convert.'
                ], 422);
            }

            $sale = DB::transaction(function () use ($quotation, $paymentMethod) {
                // Lock every affected product row up front and verify stock
                // BEFORE creating anything, so a shortfall rolls back cleanly.
                $lockedProducts = [];
                foreach ($quotation->items as $item) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                    if (!$product) {
                        throw new \Exception("Product '{$item->product_name}' (SKU: {$item->product_sku}) no longer exists.");
                    }
                    if ($product->quantity < $item->quantity) {
                        throw new \Exception("Insufficient stock for '{$product->name}': {$product->quantity} available, {$item->quantity} requested.");
                    }

                    $lockedProducts[$item->id] = $product;
                }

                $sale = \App\Models\Sale::create([
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'customer_id' => $quotation->customer_id,
                    'user_id' => auth()->id(),
                    'warehouse_id' => $quotation->warehouse_id,
                    'currency_id' => $quotation->currency_id,
                    'exchange_rate' => $quotation->exchange_rate,
                    'sale_date' => now(),
                    'due_date' => $quotation->valid_until,
                    'subtotal' => $quotation->subtotal,
                    'discount_type' => $quotation->discount_type,
                    'discount' => $quotation->discount,
                    'tax' => $quotation->tax,
                    'shipping_cost' => 0,
                    'total' => $quotation->total,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'pending',
                    'amount_paid' => 0,
                    'change_amount' => 0,
                    'quotation_id' => $quotation->id,
                    'notes' => 'Converted from quotation #' . $quotation->quotation_number,
                    'internal_notes' => $quotation->internal_notes,
                ]);

                foreach ($quotation->items as $item) {
                    \App\Models\SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'product_sku' => $item->product_sku,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'tax_rate' => $item->tax_rate,
                        'tax_amount' => $item->tax_amount ?? 0,
                        'total' => $item->total,
                    ]);

                    // Deduct stock and log the movement.
                    $product = $lockedProducts[$item->id];
                    $previousQuantity = $product->quantity;
                    $newQuantity = $previousQuantity - $item->quantity;

                    $product->update(['quantity' => $newQuantity]);

                    StockMovement::create([
                        'product_id'        => $product->id,
                        'user_id'           => auth()->id(),
                        'type'              => 'sale',
                        'quantity_change'   => -$item->quantity,
                        'previous_quantity' => $previousQuantity,
                        'new_quantity'      => $newQuantity,
                        'reference'         => $sale->invoice_number,
                        'notes'             => 'Sold via quotation #' . $quotation->quotation_number,
                    ]);

                    if ($newQuantity <= $product->min_stock_level) {
                        $this->checkLowStock($product);
                    }
                }

                $quotation->update([
                    'status' => 'converted',
                    'converted_to_sale_id' => $sale->id,
                ]);

                return $sale;
            });

            Log::info('Quotation converted to sale', [
                'quotation_id' => $quotation->id,
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation converted to sale successfully!',
                'sale_id' => $sale->id,
                'invoice_url' => route('admin.sales.invoice', $sale->id),
            ]);
        } catch (\Exception $e) {
            Log::error('Quotation conversion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage() ?: 'Failed to convert quotation.'
            ], 422);
        }
    }

    public function print($id)
    {
        if (!auth()->user()->can('quotation.print')) {
            abort(403);
        }

        $quotation = Quotation::with(['customer', 'user', 'currency', 'items.product'])->findOrFail($id);
        return view('admin.quotations.print', compact('quotation'));
    }

      public function pdf($id)
    {
        if (!auth()->user()->can('quotation.pdf')) {
            abort(403, 'You do not have permission to download quotation PDF.');
        }

        $quotation = Quotation::with(['customer', 'currency', 'items.product'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.quotations.pdf', compact('quotation'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('quotation-' . $quotation->quotation_number . '.pdf');
    }

    public function export()
    {
        if (!auth()->user()->can('quotation.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    private function generateQuotationNumber()
    {
        $prefix = 'QUO';
        $date = now()->format('Ymd');
        $last = Quotation::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $last = \App\Models\Sale::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mirrors ProductController::checkLowStock() so quotation-driven stock
     * deductions raise the same low-stock notifications as manual adjustments.
     */
    private function checkLowStock(Product $product)
    {
        try {
            $exists = Notification::where('type', 'low_stock')
                ->where('message', 'LIKE', "%{$product->name}%")
                ->where('is_read', false)
                ->exists();

            if (!$exists) {
                $adminUsers = \App\Models\User::role(['admin', 'super_admin'])->get();
                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'low_stock',
                        'message' => "Low stock alert: {$product->name} (SKU: {$product->sku}) has only {$product->quantity} units remaining. Minimum stock level is {$product->min_stock_level}.",
                        'link' => route('admin.products.show', $product->id),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('checkLowStock failed', [
                'product_id' => $product->id ?? null,
                'message' => $e->getMessage(),
            ]);
        }
    }
}