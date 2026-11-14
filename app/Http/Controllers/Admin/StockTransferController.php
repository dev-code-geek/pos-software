<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StockTransferController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('stock_transfer.view')) {
            abort(403, 'You do not have permission to view stock transfers.');
        }

        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user'])->latest()->paginate(15);

        $stats = [
            'total' => StockTransfer::count(),
            'draft' => StockTransfer::where('status', 'draft')->count(),
            'pending' => StockTransfer::where('status', 'pending')->count(),
            'in_transit' => StockTransfer::where('status', 'in_transit')->count(),
            'completed' => StockTransfer::where('status', 'completed')->count(),
            'cancelled' => StockTransfer::where('status', 'cancelled')->count(),
        ];

        $warehouses = Warehouse::where('is_active', 1)->get();
        $products = Product::where('is_active', 1)->get();

        return view('admin.stock-transfers.index', compact('transfers', 'stats', 'warehouses', 'products'));
    }

    // create() omitted – handled by modal

    public function store(Request $request)
    {
        if (!auth()->user()->can('stock_transfer.create')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create stock transfers.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
                'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
                'transfer_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check stock availability in source warehouse
            // For simplicity, we check global product quantity (you may implement warehouse-specific stock)
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found with ID: {$itemData['product_id']}");
                }
                if ($product->quantity < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name} (available: {$product->quantity})");
                }
            }

            $transfer = StockTransfer::create([
                'transfer_number' => $this->generateTransferNumber(),
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'user_id' => auth()->id(),
                'transfer_date' => $request->transfer_date,
                'status' => 'draft',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }

            Log::info('Stock transfer created', [
                'transfer_id' => $transfer->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer created successfully!',
                'transfer' => $transfer
            ]);
        } catch (\Exception $e) {
            Log::error('Stock transfer creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to create stock transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('stock_transfer.view')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view stock transfers.'
                ], 403);
            }
            abort(403);
        }

        try {
            $transfer = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user', 'items.product'])->findOrFail($id);
            if (request()->ajax()) {
                return response()->json($transfer);
            }
            return view('admin.stock-transfers.show', compact('transfer'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Stock transfer not found.'
                ], 404);
            }
            abort(404);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->can('stock_transfer.edit')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit stock transfers.'
                ], 403);
            }
            abort(403);
        }

        try {
            $transfer = StockTransfer::with('items')->findOrFail($id);
            if ($transfer->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft transfers can be edited.'
                ], 422);
            }

            $warehouses = Warehouse::where('is_active', 1)->get();
            $products = Product::where('is_active', 1)->get();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'transfer' => $transfer,
                    'warehouses' => $warehouses,
                    'products' => $products,
                ]);
            }

            return view('admin.stock-transfers.edit', compact('transfer', 'warehouses', 'products'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Stock transfer not found.'
                ], 404);
            }
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('stock_transfer.edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit stock transfers.'
            ], 403);
        }

        try {
            $transfer = StockTransfer::findOrFail($id);
            if ($transfer->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft transfers can be edited.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
                'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
                'transfer_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check stock availability
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found with ID: {$itemData['product_id']}");
                }
                if ($product->quantity < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name} (available: {$product->quantity})");
                }
            }

            $transfer->update([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'transfer_date' => $request->transfer_date,
                'notes' => $request->notes,
            ]);

            $transfer->items()->delete();

            foreach ($request->items as $itemData) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Stock transfer update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update stock transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('stock_transfer.delete')) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete stock transfers.'
                ], 403);
            }
            abort(403);
        }

        try {
            $transfer = StockTransfer::findOrFail($id);
            if (!in_array($transfer->status, ['draft', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft or cancelled transfers can be deleted.'
                ], 422);
            }

            $transfer->items()->delete();
            $transfer->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock transfer deleted successfully!'
                ]);
            }
            return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete stock transfer.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete stock transfer.');
        }
    }

    public function approve($id)
    {
        if (!auth()->user()->can('stock_transfer.approve')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to approve stock transfers.'
            ], 403);
        }

        try {
            $transfer = StockTransfer::findOrFail($id);
            if ($transfer->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft transfers can be approved.'
                ], 422);
            }

            $transfer->update(['status' => 'pending']);

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer approved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve stock transfer.'
            ], 500);
        }
    }

        public function complete($id)
    {
        if (!auth()->user()->can('stock_transfer.complete')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to complete stock transfers.'
            ], 403);
        }

        try {
            $transfer = StockTransfer::with('items.product')->findOrFail($id);

            if (!in_array($transfer->status, ['pending', 'in_transit'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending or in-transit transfers can be completed.'
                ], 422);
            }

            // Apply stock changes
            foreach ($transfer->items as $item) {
                $product = $item->product;
                if (!$product) {
                    throw new \Exception("Product not found for transfer item ID: {$item->id}");
                }

                // Check if there is enough stock to deduct
                if ($product->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name} (available: {$product->quantity}, required: {$item->quantity})");
                }

                // --- Deduct from source warehouse ---
                $oldQty = $product->quantity;
                $product->quantity -= $item->quantity;
                $product->save();

                // Record outgoing movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'transfer_out',
                    'quantity_change' => -$item->quantity,
                    'previous_quantity' => $oldQty,
                    'new_quantity' => $product->quantity,
                    'reference' => $transfer->transfer_number,
                    'notes' => "Transfer out to warehouse #{$transfer->to_warehouse_id}",
                ]);

                // --- Add to destination warehouse ---
                // For simplicity, we add to the global quantity.
                // In a real system, you would update warehouse-specific stock.
                $oldQty2 = $product->quantity;
                $product->quantity += $item->quantity;
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'transfer_in',
                    'quantity_change' => $item->quantity,
                    'previous_quantity' => $oldQty2,
                    'new_quantity' => $product->quantity,
                    'reference' => $transfer->transfer_number,
                    'notes' => "Transfer in from warehouse #{$transfer->from_warehouse_id}",
                ]);
            }

            $transfer->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer completed successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Stock transfer completion failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to complete stock transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id)
    {
        if (!auth()->user()->can('stock_transfer.cancel')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to cancel stock transfers.'
            ], 403);
        }

        try {
            $transfer = StockTransfer::findOrFail($id);
            if (!in_array($transfer->status, ['draft', 'pending', 'in_transit'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Transfer cannot be cancelled.'
                ], 422);
            }

            $transfer->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer cancelled successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel stock transfer.'
            ], 500);
        }
    }

    public function export()
    {
        if (!auth()->user()->can('stock_transfer.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }

    private function generateTransferNumber()
    {
        $prefix = 'ST';
        $date = now()->format('Ymd');
        $last = StockTransfer::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}