<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('stock.view')) {
            abort(403, 'You do not have permission to view stock.');
        }

        $query = Stock::with(['product', 'warehouse']);

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->has('min_quantity') && $request->min_quantity !== '') {
            $query->where('quantity', '>=', $request->min_quantity);
        }
        if ($request->has('max_quantity') && $request->max_quantity !== '') {
            $query->where('quantity', '<=', $request->max_quantity);
        }
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        $stock = $query->paginate(20);

        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', 1)->get();

        $stats = [
            'total_products' => Stock::distinct('product_id')->count('product_id'),
            'total_quantity' => Stock::sum('quantity'),
            'low_stock' => Stock::whereHas('product', function($q) {
                $q->whereColumn('quantity', '<=', 'min_stock_level');
            })->count(),
        ];

        return view('admin.stock.index', compact('stock', 'products', 'warehouses', 'stats'));
    }

    // AJAX – get stock details for a product
    public function show($id)
    {
        if (!auth()->user()->can('stock.view')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }
        $stock = Stock::with(['product', 'warehouse'])->findOrFail($id);
        return response()->json($stock);
    }

    // Export stock levels to CSV
    public function export(Request $request)
    {
        if (!auth()->user()->can('stock.export')) {
            abort(403);
        }
        $stock = Stock::with(['product', 'warehouse'])->get();
        $filename = 'stock_levels_' . date('Y-m-d_His') . '.csv';
        return response()->streamDownload(function () use ($stock) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Product', 'SKU', 'Warehouse', 'Quantity', 'Min Stock']);
            foreach ($stock as $s) {
                fputcsv($handle, [
                    $s->product->name ?? 'N/A',
                    $s->product->sku ?? 'N/A',
                    $s->warehouse->name ?? 'N/A',
                    $s->quantity,
                    $s->product->min_stock_level ?? 0,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
    
    /**
     * TEMPORARY: Populate stock table from existing products.
     * Call once via route, then remove this method and route.
     */
    public function populateStock()
    {
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Only super admin can run this.');
        }

        $defaultWarehouse = Warehouse::where('is_active', 1)->first();

        if (!$defaultWarehouse) {
            // Create a default warehouse if none exists
            $defaultWarehouse = Warehouse::create([
                'name' => 'Main Warehouse',
                'code' => 'MAIN',
                'address' => 'Default address',
                'is_active' => 1,
            ]);
        }

        $count = 0;
        Product::chunk(100, function ($products) use ($defaultWarehouse, &$count) {
            foreach ($products as $product) {
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $defaultWarehouse->id,
                    ],
                    [
                        'quantity' => $product->quantity ?? 0,
                    ]
                );
                if ($stock->wasRecentlyCreated) {
                    $count++;
                }
            }
        });

        return response()->json([
            'message' => "Populated {$count} stock records for warehouse: {$defaultWarehouse->name}",
        ]);
    }
}