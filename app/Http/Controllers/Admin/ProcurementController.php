<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Models\AssetRequest;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\RequestWorkflow;
use App\Models\Notification;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
    /**
     * Display a listing of procurements.
     * Requires 'procurement.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('procurement.view')) {
            abort(403, 'You do not have permission to view procurements.');
        }

        $query = Procurement::with(['assetRequest', 'purchaseOrder', 'supplier', 'procurementOfficer', 'receivedBy']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('delivery_status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by supplier
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('assetRequest', function($a) use ($search) {
                    $a->where('title', 'LIKE', "%{$search}%");
                })->orWhereHas('supplier', function($s) use ($search) {
                    $s->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('purchaseOrder', function($p) use ($search) {
                    $p->where('po_number', 'LIKE', "%{$search}%");
                });
            });
        }

        $procurements = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'total' => Procurement::count(),
            'pending' => Procurement::where('delivery_status', 'pending')->count(),
            'ordered' => Procurement::where('delivery_status', 'ordered')->count(),
            'shipped' => Procurement::where('delivery_status', 'shipped')->count(),
            'delivered' => Procurement::where('delivery_status', 'delivered')->count(),
            'partial_delivery' => Procurement::where('delivery_status', 'partial_delivery')->count(),
            'returned' => Procurement::where('delivery_status', 'returned_to_supplier')->count(),
            'pending_payment' => Procurement::where('payment_status', 'pending')->count(),
            'paid' => Procurement::where('payment_status', 'paid')->count(),
            'total_cost' => Procurement::sum('total_cost'),
        ];

        $suppliers = Supplier::all();
        $statuses = ['pending', 'ordered', 'shipped', 'delivered', 'partial_delivery', 'returned_to_supplier'];
        $paymentStatuses = ['pending', 'paid', 'partial', 'cancelled'];          
        $assetRequests = AssetRequest::whereIn('status', ['procurement_reviewing', 'ready_for_purchase', 'sent_to_procurement'])->get();
        $purchaseOrders = PurchaseOrder::where('status', 'ordered')->get();


        return view('admin.procurements.index', compact('procurements', 'stats', 'suppliers', 'statuses', 'paymentStatuses','assetRequests','purchaseOrders'));
    }

    /**
     * Show the form for creating a new procurement.
     * Requires 'procurement.create' permission.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('procurement.create')) {
            abort(403, 'You do not have permission to create procurements.');
        }

        $assetRequestId = $request->asset_request_id;
        $assetRequest = null;

        if ($assetRequestId) {
            $assetRequest = AssetRequest::with(['user', 'category'])->find($assetRequestId);
        }

        $suppliers = Supplier::all();
        $procurementOfficers = User::role(['admin', 'procurement_officer'])->get();
        $purchaseOrders = PurchaseOrder::where('status', 'ordered')->get();

        return view('admin.procurements.create', compact('assetRequest', 'suppliers', 'procurementOfficers', 'purchaseOrders'));
    }

    /**
     * Store a newly created procurement.
     * Requires 'procurement.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('procurement.create')) {
            return response()->json(['error' => 'You do not have permission to create procurements.'], 403);
        }

        $validated = $request->validate([
            'asset_request_id' => 'required|exists:asset_requests,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery' => 'nullable|date|after:order_date',
            'delivery_notes' => 'nullable|string',
            'total_cost' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,paid,partial,cancelled',
        ]);

        $validated['procurement_officer_id'] = auth()->id();
        $validated['delivery_status'] = 'pending';

        $procurement = Procurement::create($validated);

        // Update asset request status
        $assetRequest = AssetRequest::find($validated['asset_request_id']);
        $oldStatus = $assetRequest->status;
        $assetRequest->update(['status' => 'procurement_reviewing']);

        // Create workflow entry
        RequestWorkflow::create([
            'asset_request_id' => $assetRequest->id,
            'actor_id' => auth()->id(),
            'from_department' => 'procurement',
            'to_department' => 'procurement',
            'action' => 'procurement_created',
            'status_before' => $oldStatus,
            'status_after' => 'procurement_reviewing',
            'comments' => 'Procurement record created',
        ]);

        // Notify the user
        Notification::create([
            'user_id' => $assetRequest->user_id,
            'type' => 'assignment',
            'message' => "Your request #{$assetRequest->id} is now in procurement",
            'link' => route('admin.procurements.show', $procurement->id),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Procurement created successfully!',
                'procurement' => $procurement
            ]);
        }

        return redirect()->route('admin.procurements.index')
            ->with('success', 'Procurement created successfully!');
    }

    /**
     * Display the specified procurement.
     * Requires 'procurement.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('procurement.view')) {
            return response()->json(['error' => 'You do not have permission to view procurements.'], 403);
        }

        $procurement = Procurement::with([
            'assetRequest' => function($q) {
                $q->with(['user', 'category']);
            },
            'purchaseOrder' => function($q) {
                $q->with(['supplier', 'items.product']);
            },
            'supplier',
            'procurementOfficer',
            'receivedBy'
        ])->findOrFail($id);

        // Get workflow history
        $workflows = RequestWorkflow::where('asset_request_id', $procurement->asset_request_id)
            ->with('actor')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'procurement' => $procurement,
            'workflows' => $workflows,
        ]);
    }

    /**
     * Show the form for editing the specified procurement.
     * Requires 'procurement.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('procurement.edit')) {
            abort(403, 'You do not have permission to edit procurements.');
        }

        $procurement = Procurement::findOrFail($id);
        $suppliers = Supplier::all();
        $procurementOfficers = User::role(['admin', 'procurement_officer'])->get();
        $purchaseOrders = PurchaseOrder::where('status', 'ordered')->get();

        return view('admin.procurements.edit', compact('procurement', 'suppliers', 'procurementOfficers', 'purchaseOrders'));
    }

    /**
     * Update the specified procurement.
     * Requires 'procurement.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('procurement.edit')) {
            return response()->json(['error' => 'You do not have permission to edit procurements.'], 403);
        }

        $procurement = Procurement::findOrFail($id);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery' => 'nullable|date|after:order_date',
            'delivery_notes' => 'nullable|string',
            'total_cost' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,paid,partial,cancelled',
        ]);

        $procurement->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Procurement updated successfully!',
                'procurement' => $procurement
            ]);
        }

        return redirect()->route('admin.procurements.index')
            ->with('success', 'Procurement updated successfully!');
    }

    /**
     * Remove the specified procurement.
     * Requires 'procurement.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('procurement.delete')) {
            return response()->json(['error' => 'You do not have permission to delete procurements.'], 403);
        }

        $procurement = Procurement::findOrFail($id);
        
        // Check if procurement is delivered or in progress
        if (in_array($procurement->delivery_status, ['delivered', 'shipped', 'ordered'])) {
            return response()->json([
                'error' => 'Cannot delete procurement that is in progress or delivered.'
            ], 422);
        }

        $procurement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Procurement deleted successfully!'
        ]);
    }

    /**
     * Bulk delete procurements.
     * Requires 'procurement.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('procurement.delete')) {
            return response()->json(['error' => 'You do not have permission to delete procurements.'], 403);
        }

        $request->validate([
            'procurement_ids' => 'required|array|min:1',
            'procurement_ids.*' => 'exists:procurements,id',
        ]);

        $deletedCount = Procurement::whereIn('id', $request->procurement_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} procurement(s) deleted successfully!"
        ]);
    }

    /**
     * Update delivery status.
     * Requires 'procurement.edit' permission.
     */
    public function updateDeliveryStatus(Request $request, $id)
    {
        if (!auth()->user()->can('procurement.edit')) {
            return response()->json(['error' => 'You do not have permission to update delivery status.'], 403);
        }

        $request->validate([
            'delivery_status' => 'required|in:pending,ordered,shipped,delivered,partial_delivery,returned_to_supplier',
            'actual_delivery' => 'nullable|date',
            'delivery_notes' => 'nullable|string',
        ]);

        $procurement = Procurement::findOrFail($id);
        $oldStatus = $procurement->delivery_status;

        $procurement->update([
            'delivery_status' => $request->delivery_status,
            'actual_delivery' => $request->actual_delivery ?? $procurement->actual_delivery,
            'delivery_notes' => $request->delivery_notes ?? $procurement->delivery_notes,
        ]);

        // If delivered, update asset request status
        if ($request->delivery_status === 'delivered') {
            $assetRequest = AssetRequest::find($procurement->asset_request_id);
            if ($assetRequest) {
                $oldRequestStatus = $assetRequest->status;
                $assetRequest->update(['status' => 'received']);

                RequestWorkflow::create([
                    'asset_request_id' => $assetRequest->id,
                    'actor_id' => auth()->id(),
                    'from_department' => 'procurement',
                    'to_department' => 'ict',
                    'action' => 'delivered',
                    'status_before' => $oldRequestStatus,
                    'status_after' => 'received',
                    'comments' => 'Items received and ready for inspection',
                ]);

                // Notify ICT staff
                $ictStaff = User::role('ict_officer')->get();
                foreach ($ictStaff as $staff) {
                    Notification::create([
                        'user_id' => $staff->id,
                        'type' => 'assignment',
                        'message' => "Items for request #{$assetRequest->id} are ready for inspection",
                        'link' => route('admin.asset-requests.show', $assetRequest->id),
                    ]);
                }
            }
        }

        // If returned to supplier, update status
        if ($request->delivery_status === 'returned_to_supplier') {
            $assetRequest = AssetRequest::find($procurement->asset_request_id);
            if ($assetRequest) {
                $assetRequest->update(['status' => 'procurement_reviewing']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Delivery status updated to " . str_replace('_', ' ', $request->delivery_status) . "!",
            'procurement' => $procurement
        ]);
    }

    /**
     * Receive items and mark as delivered.
     * Requires 'procurement.receive' permission.
     */
    public function receive(Request $request, $id)
    {
        if (!auth()->user()->can('procurement.receive')) {
            return response()->json(['error' => 'You do not have permission to receive items.'], 403);
        }

        $request->validate([
            'received_date' => 'required|date',
            'delivery_notes' => 'nullable|string',
        ]);

        $procurement = Procurement::findOrFail($id);

        if ($procurement->delivery_status === 'delivered') {
            return response()->json(['error' => 'Items already received.'], 422);
        }

        $procurement->update([
            'delivery_status' => 'delivered',
            'actual_delivery' => $request->received_date,
            'received_by' => auth()->id(),
            'received_date' => $request->received_date,
            'delivery_notes' => $request->delivery_notes ?? $procurement->delivery_notes,
        ]);

        // Update asset request
        $assetRequest = AssetRequest::find($procurement->asset_request_id);
        if ($assetRequest) {
            $oldStatus = $assetRequest->status;
            $assetRequest->update(['status' => 'received']);

            RequestWorkflow::create([
                'asset_request_id' => $assetRequest->id,
                'actor_id' => auth()->id(),
                'from_department' => 'procurement',
                'to_department' => 'ict',
                'action' => 'received',
                'status_before' => $oldStatus,
                'status_after' => 'received',
                'comments' => 'Items received and ready for inspection',
            ]);

            // Notify ICT staff
            $ictStaff = User::role('ict_officer')->get();
            foreach ($ictStaff as $staff) {
                Notification::create([
                    'user_id' => $staff->id,
                    'type' => 'assignment',
                    'message' => "Items for request #{$assetRequest->id} are ready for inspection",
                    'link' => route('admin.asset-requests.show', $assetRequest->id),
                ]);
            }

            // Notify the user
            Notification::create([
                'user_id' => $assetRequest->user_id,
                'type' => 'assignment',
                'message' => "Items for your request #{$assetRequest->id} have been received and are ready for inspection",
                'link' => route('user.asset-requests.show', $assetRequest->id),
            ]);
        }

        // Update product stock if purchase order exists
        if ($procurement->purchase_order_id) {
            $this->updateStockFromProcurement($procurement);
        }

        return response()->json([
            'success' => true,
            'message' => 'Items received successfully!',
            'procurement' => $procurement
        ]);
    }

    /**
     * Update payment status.
     * Requires 'procurement.edit' permission.
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        if (!auth()->user()->can('procurement.edit')) {
            return response()->json(['error' => 'You do not have permission to update payment status.'], 403);
        }

        $request->validate([
            'payment_status' => 'required|in:pending,paid,partial,cancelled',
            'payment_notes' => 'nullable|string',
        ]);

        $procurement = Procurement::findOrFail($id);
        $oldStatus = $procurement->payment_status;

        $procurement->update([
            'payment_status' => $request->payment_status,
            'delivery_notes' => $request->payment_notes ? 
                ($procurement->delivery_notes ? $procurement->delivery_notes . "\n" : '') . 
                "Payment update: " . $request->payment_notes : 
                $procurement->delivery_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Payment status updated to " . str_replace('_', ' ', $request->payment_status) . "!",
            'procurement' => $procurement
        ]);
    }

    /**
     * Export procurements to CSV/Excel.
     * Requires 'procurement.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('procurement.view')) {
            return response()->json(['error' => 'You do not have permission to export procurements.'], 403);
        }

        $procurements = Procurement::with(['assetRequest', 'supplier', 'procurementOfficer'])
            ->latest()
            ->get();

        // Prepare export data
        $exportData = [];
        $exportData[] = [
            'ID', 'Request', 'Supplier', 'Procurement Officer', 'Order Date',
            'Expected Delivery', 'Actual Delivery', 'Delivery Status',
            'Payment Status', 'Total Cost', 'Created At'
        ];

        foreach ($procurements as $procurement) {
            $exportData[] = [
                $procurement->id,
                $procurement->assetRequest->title ?? 'N/A',
                $procurement->supplier->name ?? 'N/A',
                $procurement->procurementOfficer->name ?? 'N/A',
                $procurement->order_date->format('Y-m-d'),
                $procurement->expected_delivery ? $procurement->expected_delivery->format('Y-m-d') : 'N/A',
                $procurement->actual_delivery ? $procurement->actual_delivery->format('Y-m-d') : 'N/A',
                ucfirst(str_replace('_', ' ', $procurement->delivery_status)),
                ucfirst($procurement->payment_status),
                $procurement->total_cost ?? '0.00',
                $procurement->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $procurements->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get procurement statistics.
     * Requires 'procurement.view' permission.
     */
    public function statistics(Request $request)
    {
        if (!auth()->user()->can('procurement.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $period = $request->period ?? 'all';

        $stats = [
            'total' => Procurement::count(),
            'by_delivery_status' => Procurement::select('delivery_status', DB::raw('count(*) as total'))
                ->groupBy('delivery_status')
                ->pluck('total', 'delivery_status')
                ->toArray(),
            'by_payment_status' => Procurement::select('payment_status', DB::raw('count(*) as total'))
                ->groupBy('payment_status')
                ->pluck('total', 'payment_status')
                ->toArray(),
            'total_cost' => Procurement::sum('total_cost'),
            'average_cost' => Procurement::avg('total_cost'),
            'delivered_count' => Procurement::where('delivery_status', 'delivered')->count(),
            'pending_count' => Procurement::where('delivery_status', 'pending')->count(),
            'returned_count' => Procurement::where('delivery_status', 'returned_to_supplier')->count(),
            'on_time_delivery' => Procurement::where('delivery_status', 'delivered')
                ->whereNotNull('expected_delivery')
                ->whereColumn('actual_delivery', '<=', 'expected_delivery')
                ->count(),
            'top_suppliers' => Procurement::with('supplier')
                ->select('supplier_id', DB::raw('count(*) as total'), DB::raw('sum(total_cost) as cost'))
                ->groupBy('supplier_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        $recentProcurements = Procurement::with(['assetRequest', 'supplier'])
            ->latest()
            ->limit(5)
            ->get();

        $pendingDeliveries = Procurement::where('delivery_status', 'pending')
            ->count();

        $pendingPayments = Procurement::where('payment_status', 'pending')
            ->count();

        return response()->json([
            'recent_procurements' => $recentProcurements,
            'pending_deliveries' => $pendingDeliveries,
            'pending_payments' => $pendingPayments,
            'total_procurements' => Procurement::count(),
            'total_cost' => Procurement::sum('total_cost'),
        ]);
    }

    /**
     * Get procurements by supplier.
     * Requires 'procurement.view' permission.
     */
    public function bySupplier($supplierId)
    {
        if (!auth()->user()->can('procurement.view')) {
            return response()->json(['error' => 'You do not have permission to view procurements.'], 403);
        }

        $procurements = Procurement::with(['assetRequest'])
            ->where('supplier_id', $supplierId)
            ->latest()
            ->get();

        $supplier = Supplier::find($supplierId);

        return response()->json([
            'supplier' => $supplier->name ?? 'Unknown',
            'count' => $procurements->count(),
            'procurements' => $procurements,
        ]);
    }

    /**
     * Get procurement report.
     * Requires 'procurement.view' permission.
     */
    public function report(Request $request)
    {
        if (!auth()->user()->can('procurement.view')) {
            return response()->json(['error' => 'You do not have permission to view reports.'], 403);
        }

        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now();

        $report = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total' => Procurement::whereBetween('order_date', [$startDate, $endDate])->count(),
                'delivered' => Procurement::whereBetween('order_date', [$startDate, $endDate])
                    ->where('delivery_status', 'delivered')
                    ->count(),
                'pending' => Procurement::whereBetween('order_date', [$startDate, $endDate])
                    ->where('delivery_status', 'pending')
                    ->count(),
                'total_cost' => Procurement::whereBetween('order_date', [$startDate, $endDate])->sum('total_cost'),
                'paid' => Procurement::whereBetween('order_date', [$startDate, $endDate])
                    ->where('payment_status', 'paid')
                    ->count(),
                'paid_amount' => Procurement::whereBetween('order_date', [$startDate, $endDate])
                    ->where('payment_status', 'paid')
                    ->sum('total_cost'),
            ],
            'by_supplier' => Procurement::whereBetween('order_date', [$startDate, $endDate])
                ->with('supplier')
                ->select('supplier_id', DB::raw('count(*) as total'), DB::raw('sum(total_cost) as cost'))
                ->groupBy('supplier_id')
                ->orderBy('total', 'desc')
                ->get(),
            'monthly_trends' => Procurement::whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'), DB::raw('count(*) as total'))
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get(),
        ];

        return response()->json($report);
    }

    /**
     * Update stock from procurement.
     * Helper method.
     */
    private function updateStockFromProcurement(Procurement $procurement)
    {
        $purchaseOrder = PurchaseOrder::find($procurement->purchase_order_id);
        
        if (!$purchaseOrder) {
            return;
        }

        foreach ($purchaseOrder->items as $item) {
            $product = Product::find($item->product_id);
            
            if ($product) {
                $oldQuantity = $product->quantity;
                
                if ($product->isSoftware()) {
                    $product->available_licenses += $item->quantity_received;
                    $product->quantity += $item->quantity_received;
                } else {
                    $product->quantity += $item->quantity_received;
                }

                // Update product status if currently out of stock
                if ($product->status === 'assigned' && $product->quantity > 0) {
                    $product->status = 'available';
                }

                $product->save();

                // Record stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'purchase',
                    'quantity_change' => $item->quantity_received,
                    'previous_quantity' => $oldQuantity,
                    'new_quantity' => $product->quantity,
                    'reference' => 'PO-' . $purchaseOrder->po_number,
                    'notes' => 'Received from procurement #' . $procurement->id,
                ]);
            }
        }
    }
}