<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use Throwable;

class PurchaseOrderController extends Controller
{
    /**
     * Generate a unique PO number with a database lock to prevent race conditions.
     * Format: PO-YYYYMMDD-XXXX where XXXX increments per day.
     */
    private function generateUniquePoNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = 'PO-' . $today . '-';

        return DB::transaction(function () use ($prefix) {
            $lastPo = PurchaseOrder::where('po_number', 'LIKE', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('po_number', 'desc')
                ->first();

            if ($lastPo) {
                $lastNumber = (int) substr($lastPo->po_number, -4);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            return $prefix . $newNumber;
        });
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('purchase_order.view')) {
            abort(403, 'You do not have permission to view purchase orders.');
        }

        $query = PurchaseOrder::with(['supplier', 'user', 'currency']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'LIKE', "%{$search}%"))
                    ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        $purchaseOrders = $query->latest()->paginate(15);

        $stats = [
            'total' => PurchaseOrder::count(),
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'ordered' => PurchaseOrder::where('status', 'ordered')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'invoiced' => PurchaseOrder::where('status', 'invoiced')->count(),
            'paid' => PurchaseOrder::where('status', 'paid')->count(),
            'cancelled' => PurchaseOrder::where('status', 'cancelled')->count(),
            'total_amount' => PurchaseOrder::sum('total_amount'),
            'this_month' => PurchaseOrder::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'this_week' => PurchaseOrder::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();
        $statuses = ['draft', 'ordered', 'received', 'invoiced', 'paid', 'cancelled'];

        $poNumber = $this->generateUniquePoNumber();

        return view('admin.purchase-orders.index', compact(
            'purchaseOrders', 'suppliers', 'products', 'stats', 'statuses', 'currencies', 'poNumber'
        ));
    }

    public function create()
    {
        if (!auth()->user()->can('purchase_order.create')) {
            abort(403, 'You do not have permission to create purchase orders.');
        }

        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $currencies = Currency::where('is_active', 1)->orderBy('code')->get();

        $poNumber = $this->generateUniquePoNumber();

        return view('admin.purchase-orders.create', compact('suppliers', 'products', 'currencies', 'poNumber'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('purchase_order.create')) {
            return response()->json(['error' => 'You do not have permission to create purchase orders.'], 403);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'currency_id' => 'required|exists:currencies,id',
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'supplier_invoice_number' => 'nullable|string|max:50',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated) {
            $validated['user_id'] = auth()->id();
            $validated['status'] = 'draft';
            $validated['total_amount'] = 0;
            $validated['exchange_rate'] = 1;
            $validated['paid_amount'] = 0;
            $validated['payment_status'] = 'pending';

            $purchaseOrder = PurchaseOrder::create($validated);

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $item['purchase_order_id'] = $purchaseOrder->id;
                $item['total_price'] = $item['quantity_ordered'] * $item['unit_price'];
                $totalAmount += $item['total_price'];
                PurchaseOrderItem::create($item);
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);

            return $purchaseOrder;
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase Order created successfully!',
                'purchase_order' => $purchaseOrder->load('currency')
            ]);
        }

        return redirect()->route('admin.purchase-orders.index')->with('success', 'PO created!');
    }

    public function show($id)
    {
        if (!auth()->user()->can('purchase_order.view')) {
            return response()->json(['error' => 'You do not have permission to view purchase orders.'], 403);
        }

        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'user',
            'currency',
            'items.product',
            'purchasePayments.currency',
            'purchasePayments.createdBy'
        ])->findOrFail($id);

        $stockMovements = StockMovement::where('reference', 'LIKE', "%{$purchaseOrder->po_number}%")
            ->with('user')
            ->get();

        return response()->json([
            'purchase_order' => $purchaseOrder,
            'stock_movements' => $stockMovements,
        ]);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'You do not have permission to edit purchase orders.'], 403);
        }

        $purchaseOrder = PurchaseOrder::with(['items.product', 'currency'])->findOrFail($id);

        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return response()->json(['error' => 'Cannot edit a ' . $purchaseOrder->status . ' PO.'], 422);
        }

        return response()->json([
            'id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'currency_id' => $purchaseOrder->currency_id,
            'po_number' => $purchaseOrder->po_number,
            'order_date' => optional($purchaseOrder->order_date)->format('Y-m-d'),
            'expected_delivery_date' => optional($purchaseOrder->expected_delivery_date)->format('Y-m-d'),
            'supplier_invoice_number' => $purchaseOrder->supplier_invoice_number,
            'invoice_date' => optional($purchaseOrder->invoice_date)->format('Y-m-d'),
            'due_date' => optional($purchaseOrder->due_date)->format('Y-m-d'),
            'paid_amount' => $purchaseOrder->paid_amount,
            'payment_status' => $purchaseOrder->payment_status,
            'notes' => $purchaseOrder->notes,
            'items' => $purchaseOrder->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'N/A',
                'quantity_ordered' => $item->quantity_ordered,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]),
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'You do not have permission to edit purchase orders.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return response()->json(['error' => 'Cannot edit a ' . $purchaseOrder->status . ' PO.'], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'currency_id' => 'required|exists:currencies,id',
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number,' . $id,
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'supplier_invoice_number' => 'nullable|string|max:50',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:pending,partial,paid',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'PO updated!',
                'purchase_order' => $purchaseOrder->fresh('currency')
            ]);
        }

        return redirect()->route('admin.purchase-orders.index')->with('success', 'PO updated!');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('purchase_order.delete')) {
            return response()->json(['error' => 'You do not have permission to delete purchase orders.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (in_array($purchaseOrder->status, ['received', 'ordered'])) {
            return response()->json(['error' => 'Cannot delete a ' . $purchaseOrder->status . ' PO.'], 422);
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return response()->json(['success' => true, 'message' => 'PO deleted!']);
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('purchase_order.delete')) {
            return response()->json(['error' => 'You do not have permission to delete purchase orders.'], 403);
        }

        $request->validate([
            'purchase_order_ids' => 'required|array|min:1',
            'purchase_order_ids.*' => 'exists:purchase_orders,id',
        ]);

        $invalidOrders = PurchaseOrder::whereIn('id', $request->purchase_order_ids)
            ->whereIn('status', ['received', 'ordered'])
            ->pluck('po_number')
            ->toArray();

        if (!empty($invalidOrders)) {
            return response()->json([
                'error' => "Cannot delete orders with status 'received' or 'ordered': " . implode(', ', $invalidOrders)
            ], 422);
        }

        PurchaseOrderItem::whereIn('purchase_order_id', $request->purchase_order_ids)->delete();
        $deleted = PurchaseOrder::whereIn('id', $request->purchase_order_ids)->delete();

        return response()->json(['success' => true, 'message' => "{$deleted} PO(s) deleted!"]);
    }

    public function submit($id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'You do not have permission to submit purchase orders.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return response()->json(['error' => 'Cannot submit a ' . $purchaseOrder->status . ' PO.'], 422);
        }

        $purchaseOrder->update(['status' => 'ordered']);

        $staff = User::role('procurement_officer')->get();
        foreach ($staff as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'assignment',
                'message' => "Purchase Order {$purchaseOrder->po_number} has been submitted.",
                'link' => route('admin.purchase-orders.show', $purchaseOrder->id),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'PO submitted!']);
    }

    public function cancel(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'You do not have permission to cancel purchase orders.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status === 'received') {
            return response()->json(['error' => 'Cannot cancel a received PO.'], 422);
        }
        if ($purchaseOrder->status === 'cancelled') {
            return response()->json(['error' => 'PO already cancelled.'], 422);
        }

        $request->validate(['reason' => 'nullable|string|max:255']);

        $purchaseOrder->update([
            'status' => 'cancelled',
            'notes' => ($purchaseOrder->notes ? $purchaseOrder->notes . "\n" : '') .
                       'Cancelled: ' . ($request->reason ?? 'No reason') . ' on ' . now(),
        ]);

        return response()->json(['success' => true, 'message' => 'PO cancelled.']);
    }

    public function receive(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_order.receive')) {
            return response()->json(['error' => 'You do not have permission to receive purchase orders.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status === 'received') {
            return response()->json(['error' => 'PO already received.'], 422);
        }
        if ($purchaseOrder->status === 'cancelled') {
            return response()->json(['error' => 'Cannot receive a cancelled PO.'], 422);
        }

        $validated = $request->validate([
            'actual_delivery_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
        ]);

        $allReceived = true;
        $totalReceived = 0;

        foreach ($validated['items'] as $itemData) {
            $item = PurchaseOrderItem::findOrFail($itemData['id']);
            $qty = $itemData['quantity_received'];

            if ($qty > $item->quantity_ordered) {
                return response()->json([
                    'error' => "Quantity received for product {$item->product->name} exceeds ordered."
                ], 422);
            }

            $item->update(['quantity_received' => $qty]);

            if ($qty > 0) {
                $product = $item->product;
                $oldQty = $product->quantity;
                $product->quantity += $qty;
                if ($product->quantity > 0 && $product->status === 'assigned') {
                    $product->status = 'available';
                }
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'purchase',
                    'quantity_change' => $qty,
                    'previous_quantity' => $oldQty,
                    'new_quantity' => $product->quantity,
                    'reference' => $purchaseOrder->po_number,
                    'notes' => 'Received from PO: ' . $purchaseOrder->po_number,
                ]);

                $totalReceived += $qty;
            }

            if ($qty < $item->quantity_ordered) {
                $allReceived = false;
            }
        }

        $status = $allReceived ? 'received' : 'ordered';
        $purchaseOrder->update([
            'actual_delivery_date' => $validated['actual_delivery_date'],
            'status' => $status,
        ]);

        Notification::create([
            'user_id' => $purchaseOrder->user_id,
            'type' => 'assignment',
            'message' => "Purchase Order {$purchaseOrder->po_number} has been received.",
            'link' => route('admin.purchase-orders.show', $purchaseOrder->id),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PO received!',
            'status' => $status,
            'total_received' => $totalReceived,
        ]);
    }

    public function updateItems(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return response()->json(['error' => 'Cannot edit items for a ' . $purchaseOrder->status . ' PO.'], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $existingIds = $purchaseOrder->items()->pluck('id')->toArray();
        $updatedIds = [];
        $total = 0;

        foreach ($validated['items'] as $data) {
            if (isset($data['id']) && in_array($data['id'], $existingIds)) {
                $item = PurchaseOrderItem::find($data['id']);
                $item->update([
                    'product_id' => $data['product_id'],
                    'quantity_ordered' => $data['quantity_ordered'],
                    'unit_price' => $data['unit_price'],
                    'total_price' => $data['quantity_ordered'] * $data['unit_price'],
                ]);
                $updatedIds[] = $data['id'];
            } else {
                $new = PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $data['product_id'],
                    'quantity_ordered' => $data['quantity_ordered'],
                    'unit_price' => $data['unit_price'],
                    'total_price' => $data['quantity_ordered'] * $data['unit_price'],
                ]);
                $updatedIds[] = $new->id;
            }
        }

        $toDelete = array_diff($existingIds, $updatedIds);
        if (!empty($toDelete)) {
            PurchaseOrderItem::whereIn('id', $toDelete)->delete();
        }

        $newTotal = $purchaseOrder->items()->sum('total_price');
        $purchaseOrder->update(['total_amount' => $newTotal]);

        return response()->json(['success' => true, 'message' => 'Items updated!', 'total_amount' => $newTotal]);
    }

    public function export(Request $request)
    {
        if (!auth()->user()->can('purchase_order.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $orders = PurchaseOrder::with(['supplier', 'user', 'currency'])->latest()->get();
        $exportData = [
            ['ID', 'PO Number', 'Supplier', 'Order Date', 'Expected', 'Actual', 'Status', 'Currency', 'Total Amount', 'Created By', 'Created At']
        ];

        foreach ($orders as $po) {
            $exportData[] = [
                $po->id,
                $po->po_number,
                $po->supplier->name ?? 'N/A',
                $po->order_date->format('Y-m-d'),
                $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : 'N/A',
                $po->actual_delivery_date ? $po->actual_delivery_date->format('Y-m-d') : 'N/A',
                ucfirst($po->status),
                $po->currency->code ?? 'N/A',
                number_format($po->total_amount ?? 0, 2),
                $po->user->name ?? 'N/A',
                $po->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json(['data' => $exportData, 'count' => $orders->count()]);
    }

    public function pdf($id)
    {
        if (!auth()->user()->can('purchase_order.view')) {
            abort(403, 'You do not have permission to download purchase orders.');
        }

        $purchaseOrder = PurchaseOrder::with(['supplier', 'user', 'currency', 'items.product'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('admin.purchase-orders.pdf', compact('purchaseOrder'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('LPO-' . $purchaseOrder->po_number . '.pdf');
    }

    public function streamPdf($id)
    {
        if (!auth()->user()->can('purchase_order.view')) {
            abort(403, 'You do not have permission to view purchase orders.');
        }

        $purchaseOrder = PurchaseOrder::with(['supplier', 'user', 'currency', 'items.product'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('admin.purchase-orders.pdf', compact('purchaseOrder'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('LPO-' . $purchaseOrder->po_number . '.pdf');
    }

    public function bulkPdf(Request $request)
    {
        if (!auth()->user()->can('purchase_order.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $request->validate([
            'purchase_order_ids' => 'required|array|min:1',
            'purchase_order_ids.*' => 'exists:purchase_orders,id',
        ]);

        $orders = PurchaseOrder::with(['supplier', 'user', 'currency', 'items.product'])
            ->whereIn('id', $request->purchase_order_ids)
            ->get();

        $pdf = Pdf::loadView('admin.purchase-orders.bulk-pdf', compact('orders'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('LPO-Bulk-' . date('Y-m-d') . '.pdf');
    }

    public function emailPdf(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $purchaseOrder = PurchaseOrder::with(['supplier', 'user', 'currency', 'items.product'])
            ->findOrFail($id);

        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string',
        ]);

        try {
            $pdf = Pdf::loadView('admin.purchase-orders.pdf', compact('purchaseOrder'));
            $pdf->setPaper('A4', 'portrait');

            \Mail::send('admin.purchase-orders.email', [
                'purchaseOrder' => $purchaseOrder,
                'messageText' => $request->message ?? 'Please find attached LPO.',
            ], function ($message) use ($request, $purchaseOrder, $pdf) {
                $message->to($request->email)
                        ->subject('LPO: ' . $purchaseOrder->po_number)
                        ->attachData($pdf->output(), 'LPO-' . $purchaseOrder->po_number . '.pdf', [
                            'mime' => 'application/pdf',
                        ]);
            });

            return response()->json(['success' => true, 'message' => 'LPO sent to ' . $request->email]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    public function statistics()
    {
        if (!auth()->user()->can('purchase_order.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return response()->json([
            'total' => PurchaseOrder::count(),
            'by_status' => PurchaseOrder::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            'total_amount' => PurchaseOrder::sum('total_amount'),
            'average_amount' => PurchaseOrder::avg('total_amount'),
            'by_supplier' => PurchaseOrder::with('supplier')
                ->select('supplier_id', DB::raw('count(*) as total'), DB::raw('sum(total_amount) as amount'))
                ->groupBy('supplier_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }

    public function widget()
    {
        return response()->json([
            'recent_orders' => PurchaseOrder::with(['supplier', 'currency'])->latest()->limit(5)->get(),
            'pending_orders' => PurchaseOrder::where('status', 'ordered')->count(),
            'received_orders' => PurchaseOrder::where('status', 'received')->count(),
            'total_orders' => PurchaseOrder::count(),
            'total_amount' => PurchaseOrder::sum('total_amount'),
        ]);
    }

    public function bySupplier($supplierId)
    {
        if (!auth()->user()->can('purchase_order.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $orders = PurchaseOrder::where('supplier_id', $supplierId)->latest()->get();
        return response()->json([
            'supplier' => Supplier::find($supplierId)->name ?? 'Unknown',
            'count' => $orders->count(),
            'total_amount' => $orders->sum('total_amount'),
            'orders' => $orders,
        ]);
    }

    public function items($id)
    {
        if (!auth()->user()->can('purchase_order.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $items = PurchaseOrderItem::with('product')->where('purchase_order_id', $id)->get();
        return response()->json(['count' => $items->count(), 'items' => $items]);
    }

    public function supplierProducts($supplierId)
    {
        if (!auth()->user()->can('purchase_order.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $products = Product::where('supplier_id', $supplierId)
            ->orWhereNull('supplier_id')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'purchase_price']);

        return response()->json($products);
    }

    /**
     * Get product details by ID (for AJAX)
     */
    public function getProductDetails($id)
    {
        if (!auth()->user()->can('purchase_order.create')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $product = Product::findOrFail($id);

        return response()->json([
            'success' => true,
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'purchase_price' => $product->purchase_price ?? 0,
            'selling_price' => $product->selling_price ?? 0,
            'cost_price' => $product->cost_price ?? 0,
            'quantity' => $product->quantity,
            'unit' => $product->unit ?? 'piece',
        ]);
    }

    /**
     * Record a payment against a purchase order
     */
    public function recordPayment(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        // Allow payment only if PO is received or invoiced or paid status
        if (!in_array($purchaseOrder->status, ['received', 'invoiced', 'paid'])) {
            return response()->json(['error' => 'PO must be received or invoiced to record payment.'], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,bank_transfer,cheque,mobile',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0',
        ]);

        $remaining = $purchaseOrder->total_amount - $purchaseOrder->paid_amount;
        if ($validated['amount'] > $remaining) {
            return response()->json(['error' => 'Amount exceeds remaining balance of ' . number_format($remaining, 2) . '.'], 422);
        }

        DB::transaction(function () use ($validated, $purchaseOrder) {
            // Create payment record
            $payment = PurchasePayment::create([
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'amount' => $validated['amount'],
                'currency_id' => $validated['currency_id'],
                'exchange_rate' => $validated['exchange_rate'] ?? 1,
                'payment_date' => $validated['payment_date'],
                'method' => $validated['method'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            // Update PO payment status
            $newPaid = $purchaseOrder->paid_amount + $validated['amount'];
            $purchaseOrder->paid_amount = $newPaid;

            if ($newPaid >= $purchaseOrder->total_amount) {
                $purchaseOrder->payment_status = 'paid';
                // If fully paid and status is received or invoiced, update to paid
                if (in_array($purchaseOrder->status, ['received', 'invoiced'])) {
                    $purchaseOrder->status = 'paid';
                }
            } else {
                $purchaseOrder->payment_status = 'partial';
            }

            $purchaseOrder->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully!',
            'purchase_order' => $purchaseOrder->fresh(['purchasePayments', 'currency'])
        ]);
    }

    /**
     * Get payment details for the "Record Payment" modal.
     *
     * When app.debug is true, the JSON error response includes the exception
     * class, message, file, and line so the browser console / network tab
     * shows exactly what failed instead of a generic message. Full trace
     * always goes to storage/logs/laravel.log regardless of debug mode.
     */
    public function getPaymentDetails($id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        try {
            $purchaseOrder = PurchaseOrder::with(['currency', 'supplier'])->find($id);

            if (!$purchaseOrder) {
                return response()->json([
                    'success' => false,
                    'error' => "Purchase Order #{$id} not found.",
                ], 404);
            }

            // Safely get currency symbol
            $currencySymbol = '$';
            if ($purchaseOrder->currency) {
                $currencySymbol = $purchaseOrder->currency->symbol ?? '$';
            }

            // Calculate amounts safely
            $totalAmount = floatval($purchaseOrder->total_amount ?? 0);
            $paidAmount = floatval($purchaseOrder->paid_amount ?? 0);
            $remaining = $totalAmount - $paidAmount;

            return response()->json([
                'success' => true,
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'remaining' => $remaining,
                'supplier_id' => $purchaseOrder->supplier_id,
                'currency_id' => $purchaseOrder->currency_id ?? null,
                'currency_symbol' => $currencySymbol,
                'status' => $purchaseOrder->status,
            ]);

        } catch (Throwable $e) {
            // Always log the full trace, regardless of environment.
            \Log::error('getPaymentDetails failed', [
                'purchase_order_id' => $id,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $payload = [
                'success' => false,
                'error' => 'Failed to load PO details.',
            ];

            // Only expose internals when debug mode is on (local/staging),
            // never in production, to avoid leaking stack traces to end users.
            if (config('app.debug')) {
                $payload['error'] = 'Failed to load PO details: ' . $e->getMessage();
                $payload['debug'] = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect(explode("\n", $e->getTraceAsString()))->take(10)->values(),
                ];
            }

            return response()->json($payload, 500);
        }
    }

    /**
     * Mark a purchase order as invoiced (using the PO as the supplier invoice)
     */
    public function markInvoiced(Request $request, $id)
    {
        if (!auth()->user()->can('purchase_order.edit')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (!in_array($purchaseOrder->status, ['received', 'ordered'])) {
            return response()->json(['error' => 'PO must be received or ordered to invoice.'], 422);
        }

        $validated = $request->validate([
            'supplier_invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
        ]);

        $purchaseOrder->update([
            'supplier_invoice_number' => $validated['supplier_invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'status' => 'invoiced',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PO marked as Invoiced.',
            'purchase_order' => $purchaseOrder->fresh()
        ]);
    }
}