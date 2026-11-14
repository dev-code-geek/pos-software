<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of stock adjustments.
     * Requires 'stock_adjustment.view' permission.
     */
    public function index()
    {
        if (!auth()->user()->can('stock_adjustment.view')) {
            abort(403, 'You do not have permission to view stock adjustments.');
        }

        $adjustments = StockAdjustment::with(['warehouse', 'user'])->latest()->paginate(15);

        $stats = [
            'total' => StockAdjustment::count(),
            'increase' => StockAdjustment::where('type', 'increase')->count(),
            'decrease' => StockAdjustment::where('type', 'decrease')->count(),
            'transfer' => StockAdjustment::where('type', 'transfer')->count(),
            'draft' => StockAdjustment::where('status', 'draft')->count(),
            'approved' => StockAdjustment::where('status', 'approved')->count(),
            'cancelled' => StockAdjustment::where('status', 'cancelled')->count(),
        ];

        $warehouses = Warehouse::where('is_active', 1)->get();
        $products = Product::where('is_active', 1)->get();

        return view('admin.stock-adjustments.index', compact('adjustments', 'stats', 'warehouses', 'products'));
    }

    /**
     * Store a newly created stock adjustment.
     * Requires 'stock_adjustment.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('stock_adjustment.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create stock adjustments.'
            ], 403);
        }

        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required|exists:warehouses,id',
                'type' => 'required|in:increase,decrease,transfer',
                'adjustment_date' => 'required|date',
                'reason' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the adjustment
            $adjustment = StockAdjustment::create([
                'adjustment_number' => $this->generateAdjustmentNumber(),
                'warehouse_id' => $request->warehouse_id,
                'user_id' => auth()->id(),
                'adjustment_date' => $request->adjustment_date,
                'type' => $request->type,
                'reason' => $request->reason,
                'status' => 'draft',
                'notes' => $request->notes,
            ]);

            // Add items
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found with ID: {$itemData['product_id']}");
                }

                $quantity = (float) $itemData['quantity'];
                $adjustmentQty = ($request->type == 'decrease') ? -$quantity : $quantity;
                $newQuantity = $product->quantity + $adjustmentQty;

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_before' => $product->quantity,
                    'quantity_adjustment' => $adjustmentQty,
                    'quantity_after' => $newQuantity,
                    'reason' => $itemData['reason'] ?? null,
                ]);
            }

            Log::info('Stock adjustment created', [
                'adjustment_id' => $adjustment->id,
                'user_id' => auth()->id(),
                'type' => $request->type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment created successfully!',
                'adjustment' => $adjustment
            ]);

        } catch (\Exception $e) {
            Log::error('Stock adjustment creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create stock adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified stock adjustment.
     * Requires 'stock_adjustment.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('stock_adjustment.view')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view stock adjustments.'
                ], 403);
            }
            abort(403);
        }

        try {
            $adjustment = StockAdjustment::with(['warehouse', 'user', 'items.product'])->findOrFail($id);

            if (request()->ajax()) {
                return response()->json($adjustment);
            }

            return view('admin.stock-adjustments.show', compact('adjustment'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Stock adjustment not found.'
                ], 404);
            }
            abort(404);
        } catch (\Exception $e) {
            Log::error('Show stock adjustment failed: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load stock adjustment details.'
                ], 500);
            }
            abort(500);
        }
    }

    /**
     * Show the form for editing the specified stock adjustment.
     * Returns JSON for modal.
     * Requires 'stock_adjustment.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('stock_adjustment.edit')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit stock adjustments.'
                ], 403);
            }
            abort(403);
        }

        try {
            $adjustment = StockAdjustment::with('items')->findOrFail($id);

            if ($adjustment->status !== 'draft') {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Only draft adjustments can be edited.'
                    ], 422);
                }
                return redirect()->route('admin.stock-adjustments.index')
                    ->with('error', 'Only draft adjustments can be edited.');
            }

            $warehouses = Warehouse::where('is_active', 1)->get();
            $products = Product::where('is_active', 1)->get();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'adjustment' => $adjustment,
                    'warehouses' => $warehouses,
                    'products' => $products,
                ]);
            }

            return view('admin.stock-adjustments.edit', compact('adjustment', 'warehouses', 'products'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Stock adjustment not found.'
                ], 404);
            }
            abort(404);
        } catch (\Exception $e) {
            Log::error('Edit stock adjustment failed: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to load stock adjustment data.'
                ], 500);
            }
            abort(500);
        }
    }

    /**
     * Update the specified stock adjustment.
     * Requires 'stock_adjustment.edit' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('stock_adjustment.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit stock adjustments.'
            ], 403);
        }

        try {
            $adjustment = StockAdjustment::findOrFail($id);

            if ($adjustment->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft adjustments can be edited.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required|exists:warehouses,id',
                'type' => 'required|in:increase,decrease,transfer',
                'adjustment_date' => 'required|date',
                'reason' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update adjustment
            $adjustment->update([
                'warehouse_id' => $request->warehouse_id,
                'type' => $request->type,
                'adjustment_date' => $request->adjustment_date,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            // Delete old items
            $adjustment->items()->delete();

            // Add new items
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found with ID: {$itemData['product_id']}");
                }

                $quantity = (float) $itemData['quantity'];
                $adjustmentQty = ($request->type == 'decrease') ? -$quantity : $quantity;
                $newQuantity = $product->quantity + $adjustmentQty;

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_before' => $product->quantity,
                    'quantity_adjustment' => $adjustmentQty,
                    'quantity_after' => $newQuantity,
                    'reason' => $itemData['reason'] ?? null,
                ]);
            }

            Log::info('Stock adjustment updated', [
                'adjustment_id' => $adjustment->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment updated successfully!',
                'adjustment' => $adjustment
            ]);

        } catch (\Exception $e) {
            Log::error('Stock adjustment update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update stock adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified stock adjustment.
     * Requires 'stock_adjustment.delete' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('stock_adjustment.delete')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete stock adjustments.'
                ], 403);
            }
            abort(403);
        }

        try {
            $adjustment = StockAdjustment::findOrFail($id);

            if (!in_array($adjustment->status, ['draft', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft or cancelled adjustments can be deleted.'
                ], 422);
            }

            $adjustment->items()->delete();
            $adjustment->delete();

            Log::info('Stock adjustment deleted', [
                'adjustment_id' => $id,
                'user_id' => auth()->id()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock adjustment deleted successfully!'
                ]);
            }

            return redirect()->route('admin.stock-adjustments.index')
                ->with('success', 'Stock adjustment deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Stock adjustment deletion failed: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete stock adjustment.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete stock adjustment.');
        }
    }

    /**
     * Approve a stock adjustment (applies stock changes).
     * Requires 'stock_adjustment.approve' permission.
     */
    public function approve($id)
    {
        if (!auth()->user()->can('stock_adjustment.approve')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to approve stock adjustments.'
            ], 403);
        }

        try {
            $adjustment = StockAdjustment::findOrFail($id);

            if ($adjustment->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft adjustments can be approved.'
                ], 422);
            }

            // Apply stock changes
            foreach ($adjustment->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $oldQty = $product->quantity;
                    $product->quantity += $item->quantity_adjustment;
                    $product->save();

                    // Log stock movement
                    \App\Models\StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'adjustment',
                        'quantity_change' => $item->quantity_adjustment,
                        'previous_quantity' => $oldQty,
                        'new_quantity' => $product->quantity,
                        'reference' => $adjustment->adjustment_number,
                        'notes' => "Stock adjustment approved: {$adjustment->reason}",
                    ]);
                }
            }

            $adjustment->update(['status' => 'approved']);

            Log::info('Stock adjustment approved', [
                'adjustment_id' => $adjustment->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment approved successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Stock adjustment approval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve stock adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a stock adjustment.
     * Requires 'stock_adjustment.cancel' permission.
     */
    public function cancel($id)
    {
        if (!auth()->user()->can('stock_adjustment.cancel')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to cancel stock adjustments.'
            ], 403);
        }

        try {
            $adjustment = StockAdjustment::findOrFail($id);

            if ($adjustment->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft adjustments can be cancelled.'
                ], 422);
            }

            $adjustment->update(['status' => 'cancelled']);

            Log::info('Stock adjustment cancelled', [
                'adjustment_id' => $adjustment->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment cancelled successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Stock adjustment cancellation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel stock adjustment.'
            ], 500);
        }
    }

    /**
     * Export stock adjustments.
     * Requires 'stock_adjustment.export' permission.
     */
    public function export()
    {
        if (!auth()->user()->can('stock_adjustment.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    /**
     * Generate unique adjustment number.
     */
    private function generateAdjustmentNumber()
    {
        $prefix = 'SA';
        $date = now()->format('Ymd');
        $last = StockAdjustment::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}