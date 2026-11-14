<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements.
     * Requires 'stock.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('stock.view')) {
            abort(403, 'You do not have permission to view stock movements.');
        }

        $query = StockMovement::with(['product', 'user']);

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by movement direction
        if ($request->has('direction')) {
            if ($request->direction === 'incoming') {
                $query->where('quantity_change', '>', 0);
            } elseif ($request->direction === 'outgoing') {
                $query->where('quantity_change', '<', 0);
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function($p) use ($search) {
                      $p->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $movements = $query->latest('created_at')->paginate(15);

        // Stats
        $stats = [
            'total' => StockMovement::count(),
            'incoming' => StockMovement::where('quantity_change', '>', 0)->sum('quantity_change'),
            'outgoing' => StockMovement::where('quantity_change', '<', 0)->sum('quantity_change'),
            'net' => StockMovement::sum('quantity_change'),
            'by_type' => StockMovement::select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray(),
            'today' => StockMovement::whereDate('created_at', today())->count(),
            'this_week' => StockMovement::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => StockMovement::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $products = Product::all();
        $users = User::all();
        $types = ['purchase', 'adjustment', 'return', 'damage'];

        return view('admin.stock-movements.index', compact('movements', 'products', 'users', 'stats', 'types'));
    }

    /**
     * Show the form for creating a new stock movement.
     * Requires 'stock.create' permission.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('stock.create')) {
            abort(403, 'You do not have permission to create stock movements.');
        }

        $products = Product::all();
        $types = ['purchase', 'adjustment', 'return', 'damage'];
        
        // Pre-select product if provided
        $selectedProduct = null;
        if ($request->has('product_id')) {
            $selectedProduct = Product::find($request->product_id);
        }

        return view('admin.stock-movements.create', compact('products', 'types', 'selectedProduct'));
    }

    /**
     * Store a newly created stock movement.
     * Requires 'stock.create' permission.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('stock.create')) {
            return response()->json(['error' => 'You do not have permission to create stock movements.'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:purchase,adjustment,return,damage',
            'quantity_change' => 'required|integer|not_in:0',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Check if product is software and adjust quantity limits
        if ($product->isSoftware()) {
            // For software, check available licenses
            if ($validated['quantity_change'] < 0 && abs($validated['quantity_change']) > $product->available_licenses) {
                return response()->json([
                    'error' => 'Not enough available licenses. Available: ' . $product->available_licenses
                ], 422);
            }
        } else {
            // For hardware, check stock levels for outgoing movements
            if ($validated['quantity_change'] < 0 && abs($validated['quantity_change']) > $product->quantity) {
                return response()->json([
                    'error' => 'Insufficient stock. Current quantity: ' . $product->quantity
                ], 422);
            }
        }

        $validated['user_id'] = auth()->id();
        $validated['previous_quantity'] = $product->quantity;
        $validated['new_quantity'] = $product->quantity + $validated['quantity_change'];

        if ($validated['new_quantity'] < 0) {
            return response()->json(['error' => 'Insufficient stock.'], 422);
        }

        $movement = StockMovement::create($validated);

        // Update product quantity
        if ($product->isSoftware()) {
            $product->available_licenses += $validated['quantity_change'];
            $product->quantity = $validated['new_quantity'];
        } else {
            $product->quantity = $validated['new_quantity'];
        }

        // Update product status
        if ($product->quantity == 0) {
            $product->status = 'assigned';
        } elseif ($product->quantity > 0) {
            $product->status = 'available';
        }
        $product->save();

        // Check low stock and create notification
        if ($product->quantity <= $product->min_stock_level && $product->quantity > 0) {
            $this->checkLowStock($product);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock movement recorded successfully!',
                'movement' => $movement
            ]);
        }

        return redirect()->route('admin.stock-movements.index')
            ->with('success', 'Stock movement recorded successfully!');
    }

    /**
     * Display the specified stock movement.
     * Requires 'stock.view' permission.
     */
    public function show($id)
    {
        if (!auth()->user()->can('stock.view')) {
            return response()->json(['error' => 'You do not have permission to view stock movements.'], 403);
        }

        $movement = StockMovement::with(['product', 'user'])->findOrFail($id);
        return response()->json($movement);
    }

    /**
     * Show the form for editing the specified stock movement.
     * Requires 'stock.edit' permission.
     */
    public function edit($id)
    {
        if (!auth()->user()->can('stock.create')) {
            abort(403, 'You do not have permission to edit stock movements.');
        }

        $movement = StockMovement::findOrFail($id);
        $products = Product::all();
        $types = ['purchase', 'adjustment', 'return', 'damage'];

        return view('admin.stock-movements.edit', compact('movement', 'products', 'types'));
    }

    /**
     * Update the specified stock movement.
     * Requires 'stock.create' permission.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('stock.create')) {
            return response()->json(['error' => 'You do not have permission to edit stock movements.'], 403);
        }

        $movement = StockMovement::findOrFail($id);

        // Only allow updating notes and reference
        $validated = $request->validate([
            'type' => 'required|in:purchase,adjustment,return,damage',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $movement->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock movement updated successfully!',
                'movement' => $movement
            ]);
        }

        return redirect()->route('admin.stock-movements.index')
            ->with('success', 'Stock movement updated successfully!');
    }

    /**
     * Remove the specified stock movement.
     * Requires 'stock.create' permission.
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('stock.create')) {
            return response()->json(['error' => 'You do not have permission to delete stock movements.'], 403);
        }

        $movement = StockMovement::findOrFail($id);

        // Reverse the stock change
        $product = $movement->product;
        $oldQuantity = $product->quantity;
        
        if ($product->isSoftware()) {
            $product->available_licenses -= $movement->quantity_change;
        }
        $product->quantity -= $movement->quantity_change;
        
        if ($product->quantity < 0) {
            return response()->json([
                'error' => 'Cannot delete movement as it would result in negative stock.'
            ], 422);
        }

        // Update product status
        if ($product->quantity == 0) {
            $product->status = 'assigned';
        } elseif ($product->quantity > 0) {
            $product->status = 'available';
        }
        $product->save();

        $movement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stock movement deleted successfully!'
        ]);
    }

    /**
     * Bulk delete stock movements.
     * Requires 'stock.create' permission.
     */
    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('stock.create')) {
            return response()->json(['error' => 'You do not have permission to delete stock movements.'], 403);
        }

        $request->validate([
            'movement_ids' => 'required|array|min:1',
            'movement_ids.*' => 'exists:stock_movements,id',
        ]);

        $movements = StockMovement::whereIn('id', $request->movement_ids)->get();
        $errors = [];
        $deleted = 0;

        foreach ($movements as $movement) {
            // Reverse the stock change
            $product = $movement->product;
            $product->quantity -= $movement->quantity_change;
            
            if ($product->quantity < 0) {
                $errors[] = "Cannot delete movement for product: {$product->name} (would result in negative stock)";
                continue;
            }

            if ($product->isSoftware()) {
                $product->available_licenses -= $movement->quantity_change;
            }

            // Update product status
            if ($product->quantity == 0) {
                $product->status = 'assigned';
            } elseif ($product->quantity > 0) {
                $product->status = 'available';
            }
            $product->save();

            $movement->delete();
            $deleted++;
        }

        $message = "{$deleted} movement(s) deleted successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'errors' => $errors,
        ]);
    }

    /**
     * Get stock movement statistics.
     * Requires 'stock.view' permission.
     */
    public function statistics(Request $request)
    {
        if (!auth()->user()->can('stock.view')) {
            return response()->json(['error' => 'You do not have permission to view statistics.'], 403);
        }

        $period = $request->period ?? 'all';

        $stats = [
            'total' => StockMovement::count(),
            'incoming' => StockMovement::where('quantity_change', '>', 0)->sum('quantity_change'),
            'outgoing' => StockMovement::where('quantity_change', '<', 0)->sum('quantity_change'),
            'net' => StockMovement::sum('quantity_change'),
            'by_type' => StockMovement::select('type', DB::raw('count(*) as total'), DB::raw('sum(quantity_change) as quantity'))
                ->groupBy('type')
                ->get(),
            'today' => StockMovement::whereDate('created_at', today())->count(),
            'today_incoming' => StockMovement::whereDate('created_at', today())->where('quantity_change', '>', 0)->sum('quantity_change'),
            'today_outgoing' => StockMovement::whereDate('created_at', today())->where('quantity_change', '<', 0)->sum('quantity_change'),
            'this_week' => StockMovement::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => StockMovement::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'top_products' => StockMovement::with('product')
                ->select('product_id', DB::raw('count(*) as total'), DB::raw('sum(quantity_change) as quantity'))
                ->groupBy('product_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
            'by_user' => StockMovement::with('user')
                ->select('user_id', DB::raw('count(*) as total'))
                ->groupBy('user_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        // Monthly trends (last 12 months)
        $monthlyTrends = StockMovement::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as incoming'),
                DB::raw('SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as outgoing'),
                DB::raw('SUM(quantity_change) as net')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'stats' => $stats,
            'monthly_trends' => $monthlyTrends,
        ]);
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        $recentMovements = StockMovement::with(['product', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_movements' => StockMovement::count(),
            'total_incoming' => StockMovement::where('quantity_change', '>', 0)->sum('quantity_change'),
            'total_outgoing' => StockMovement::where('quantity_change', '<', 0)->sum('quantity_change'),
            'net_change' => StockMovement::sum('quantity_change'),
            'today' => StockMovement::whereDate('created_at', today())->count(),
        ];

        return response()->json([
            'recent_movements' => $recentMovements,
            'stats' => $stats,
        ]);
    }

    /**
     * Get stock movements for a specific product.
     * Requires 'stock.view' permission.
     */
    public function byProduct($productId)
    {
        if (!auth()->user()->can('stock.view')) {
            return response()->json(['error' => 'You do not have permission to view stock movements.'], 403);
        }

        $movements = StockMovement::with('user')
            ->where('product_id', $productId)
            ->latest()
            ->get();

        $product = Product::find($productId);

        $summary = [
            'total' => $movements->count(),
            'incoming' => $movements->where('quantity_change', '>', 0)->sum('quantity_change'),
            'outgoing' => $movements->where('quantity_change', '<', 0)->sum('quantity_change'),
            'net' => $movements->sum('quantity_change'),
            'by_type' => $movements->groupBy('type')->map(function($group) {
                return $group->sum('quantity_change');
            }),
        ];

        return response()->json([
            'product' => $product->name ?? 'Unknown',
            'summary' => $summary,
            'movements' => $movements,
        ]);
    }

    /**
     * Get stock movements for a specific type.
     * Requires 'stock.view' permission.
     */
    public function byType($type)
    {
        if (!auth()->user()->can('stock.view')) {
            return response()->json(['error' => 'You do not have permission to view stock movements.'], 403);
        }

        $validTypes = ['purchase', 'adjustment', 'return', 'damage'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid movement type.'], 422);
        }

        $movements = StockMovement::with(['product', 'user'])
            ->where('type', $type)
            ->latest()
            ->get();

        return response()->json([
            'type' => $type,
            'count' => $movements->count(),
            'total_quantity' => $movements->sum('quantity_change'),
            'movements' => $movements,
        ]);
    }

    /**
     * Export stock movements to CSV/Excel.
     * Requires 'stock.view' permission.
     */
    public function export(Request $request)
    {
        if (!auth()->user()->can('stock.view')) {
            return response()->json(['error' => 'You do not have permission to export stock movements.'], 403);
        }

        $movements = StockMovement::with(['product', 'user'])
            ->latest()
            ->get();

        // Prepare export data
        $exportData = [];
        $exportData[] = [
            'ID', 'Product', 'User', 'Type', 'Quantity Change',
            'Previous Quantity', 'New Quantity', 'Reference', 'Notes', 'Created At'
        ];

        foreach ($movements as $movement) {
            $exportData[] = [
                $movement->id,
                $movement->product->name ?? 'N/A',
                $movement->user->name ?? 'N/A',
                ucfirst($movement->type),
                $movement->quantity_change,
                $movement->previous_quantity,
                $movement->new_quantity,
                $movement->reference ?? 'N/A',
                $movement->notes ?? 'N/A',
                $movement->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'data' => $exportData,
            'count' => $movements->count(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get stock movement summary for a product.
     * Requires 'stock.view' permission.
     */
    public function summary(Request $request)
    {
        if (!auth()->user()->can('stock.view')) {
            return response()->json(['error' => 'You do not have permission to view stock summary.'], 403);
        }

        $productId = $request->product_id;

        if ($productId) {
            // Summary for specific product
            $movements = StockMovement::where('product_id', $productId)->get();
            $product = Product::find($productId);

            $summary = [
                'product' => $product->name ?? 'Unknown',
                'sku' => $product->sku ?? 'N/A',
                'current_quantity' => $product->quantity ?? 0,
                'min_stock_level' => $product->min_stock_level ?? 0,
                'total_incoming' => $movements->where('quantity_change', '>', 0)->sum('quantity_change'),
                'total_outgoing' => $movements->where('quantity_change', '<', 0)->sum('quantity_change'),
                'net_change' => $movements->sum('quantity_change'),
                'last_movement' => $movements->isNotEmpty() ? $movements->last()->created_at : null,
                'by_type' => $movements->groupBy('type')->map(function($group) {
                    return $group->sum('quantity_change');
                }),
            ];
        } else {
            // Summary for all products
            $products = Product::all();
            $summary = [
                'total_products' => $products->count(),
                'total_quantity' => $products->sum('quantity'),
                'total_value' => $products->sum('purchase_price'),
                'products_low_stock' => $products->filter(function($p) {
                    return $p->quantity <= $p->min_stock_level && $p->quantity > 0;
                })->count(),
                'products_out_of_stock' => $products->filter(function($p) {
                    return $p->quantity <= 0;
                })->count(),
            ];
        }

        return response()->json($summary);
    }

    /**
     * Check low stock and create notification.
     * Helper method.
     */
    private function checkLowStock(Product $product)
    {
        // Check if notification already exists for this product
        $exists = Notification::where('type', 'low_stock')
            ->where('message', 'LIKE', "%{$product->name}%")
            ->where('is_read', false)
            ->exists();

        if (!$exists) {
            // Notify admin users
            $adminUsers = User::role(['admin', 'super_admin'])->get();
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'low_stock',
                    'message' => "Low stock alert: {$product->name} (SKU: {$product->sku}) has only {$product->quantity} units remaining. Minimum stock level is {$product->min_stock_level}.",
                    'link' => route('admin.products.show', $product->id),
                ]);
            }
        }
    }
}