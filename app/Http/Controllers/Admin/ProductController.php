<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\Warehouse;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'You do not have permission to view products.');
        }

        $query = Product::with(['category', 'supplier']);

        if ($request->filled('type')) {
            $query->where('type', 'LIKE', '%' . $request->type . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('barcode', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15);

        $stats = [
            'total'          => Product::count(),
            'available'      => Product::where('status', 'available')->sum('quantity'),
            'assigned'       => Product::where('status', 'assigned')->sum('quantity'),
            'under_repair'   => Product::where('status', 'under_repair')->count(),
            'retired'        => Product::where('status', 'retired')->count(),
            'expired'        => Product::where('status', 'expired')->count(),
            'low_stock'      => Product::whereColumn('quantity', '<=', 'min_stock_level')
                ->where('quantity', '>', 0)
                ->count(),
            'out_of_stock'   => Product::where('quantity', '<=', 0)->count(),
            'total_value'    => Product::sum('purchase_price'),
        ];

        $categories = Category::all();
        $suppliers  = Supplier::all();
        $statuses   = ['available', 'assigned', 'under_repair', 'retired', 'expired'];

        return view('admin.products.index', compact('products', 'categories', 'suppliers', 'stats', 'statuses'));
    }

    public function create()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'You do not have permission to create products.');
        }
        $categories = Category::all();
        $suppliers  = Supplier::all();
        return view('admin.products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            return response()->json(['success' => false, 'error' => 'You do not have permission to create products.'], 403);
        }

        try {
            $validated = $request->validate([
                'category_id'      => 'required|exists:categories,id',
                'supplier_id'      => 'nullable|exists:suppliers,id',
                'name'             => 'required|string|max:255',
                'sku'              => 'required|string|max:100|unique:products',
                'barcode'          => 'nullable|string|max:255|unique:products',
                'description'      => 'nullable|string',
                'type'             => 'nullable|string|max:255',
                'purchase_date'    => 'nullable|date',
                'warranty_end_date'=> 'nullable|date',
                'purchase_price'   => 'nullable|numeric|min:0',
                'selling_price'    => 'nullable|numeric|min:0',
                'wholesale_price'  => 'nullable|numeric|min:0',
                'cost_price'       => 'nullable|numeric|min:0',
                'current_value'    => 'nullable|numeric|min:0',
                'status'           => 'required|in:available,assigned,under_repair,retired,expired',
                'quantity'         => 'required|integer|min:0',
                'min_stock_level'  => 'required|integer|min:0',
                'reorder_level'    => 'nullable|integer|min:0',
                'reorder_quantity' => 'nullable|integer|min:0',
                'unit'             => 'nullable|string|max:50',
                'weight'           => 'nullable|numeric|min:0',
                'dimensions'       => 'nullable|string|max:100',
                'is_active'        => 'nullable|boolean',
                'is_taxable'       => 'nullable|boolean',
                'tax_rate'         => 'nullable|numeric|min:0|max:100',
            ]);

            $validated['is_active']  = $request->has('is_active') ? 1 : 0;
            $validated['is_taxable'] = $request->has('is_taxable') ? 1 : 0;

            $product = DB::transaction(function () use ($validated) {
                $product = Product::create($validated);

                if ($product->quantity > 0) {
                    StockMovement::create([
                        'product_id'        => $product->id,
                        'user_id'           => auth()->id(),
                        'type'              => 'purchase',
                        'quantity_change'   => $product->quantity,
                        'previous_quantity' => 0,
                        'new_quantity'      => $product->quantity,
                        'reference'         => 'INITIAL',
                        'notes'             => 'Initial product creation',
                    ]);
                }

                return $product;
            });

            Log::info('Product created', ['product_id' => $product->id, 'user_id' => auth()->id()]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Product created successfully!', 'product' => $product]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
        } catch (ValidationException $e) {
            Log::warning('Product creation validation failed', ['errors' => $e->errors(), 'user_id' => auth()->id()]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Product creation failed unexpectedly', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('product.view')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to view products.'], 403);
        }

        // Deep diagnostic: catch non-numeric IDs before they ever hit the DB.
        // If this fires, it proves a route-matching problem is sending a
        // string like "bulk-upload" or "export" into this method.
        if (!is_numeric($id)) {
            Log::warning('ProductController@show received a non-numeric id — this indicates a route ordering problem, not a missing product.', [
                'id_received' => $id,
                'url'         => request()->fullUrl(),
                'route_name'  => optional(request()->route())->getName(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid product identifier.',
                'debug'   => config('app.debug')
                    ? "Received non-numeric id '{$id}'. This usually means a route like /products/{$id} is matching the wildcard {id} route instead of a dedicated route. Check route order in your routes file."
                    : null,
            ], 400);
        }

        try {
            $product = Product::with(['category', 'supplier'])->findOrFail($id);

            $stockMovements = StockMovement::where('product_id', $id)
                ->with('user')
                ->latest()
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'product' => $product,
                'stock_movements' => $stockMovements,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::info('Product not found', ['id_requested' => $id, 'url' => request()->fullUrl()]);
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        } catch (\Throwable $e) {
            Log::error('Failed to load product details', [
                'id_requested' => $id,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load product details.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->can('product.edit')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to edit products.'], 403);
        }

        if (!is_numeric($id)) {
            Log::warning('ProductController@edit received a non-numeric id — route ordering problem.', [
                'id_received' => $id,
                'url' => request()->fullUrl(),
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid product identifier.'], 400);
        }

        try {
            $product = Product::with(['category', 'supplier'])->findOrFail($id);
            return response()->json(['success' => true, 'product' => $product]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        } catch (\Throwable $e) {
            Log::error('Failed to load product for editing', [
                'id_requested' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load product data.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('product.edit')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to edit products.'], 403);
        }

        if (!is_numeric($id)) {
            Log::warning('ProductController@update received a non-numeric id — route ordering problem.', ['id_received' => $id]);
            return response()->json(['success' => false, 'message' => 'Invalid product identifier.'], 400);
        }

        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'category_id'      => 'required|exists:categories,id',
                'supplier_id'      => 'nullable|exists:suppliers,id',
                'name'             => 'required|string|max:255',
                'sku'              => 'required|string|max:100|unique:products,sku,' . $id,
                'barcode'          => 'nullable|string|max:255|unique:products,barcode,' . $id,
                'description'      => 'nullable|string',
                'type'             => 'nullable|string|max:255',
                'purchase_date'    => 'nullable|date',
                'warranty_end_date'=> 'nullable|date',
                'purchase_price'   => 'nullable|numeric|min:0',
                'selling_price'    => 'nullable|numeric|min:0',
                'wholesale_price'  => 'nullable|numeric|min:0',
                'cost_price'       => 'nullable|numeric|min:0',
                'current_value'    => 'nullable|numeric|min:0',
                'status'           => 'required|in:available,assigned,under_repair,retired,expired',
                'quantity'         => 'required|integer|min:0',
                'min_stock_level'  => 'required|integer|min:0',
                'reorder_level'    => 'nullable|integer|min:0',
                'reorder_quantity' => 'nullable|integer|min:0',
                'unit'             => 'nullable|string|max:50',
                'weight'           => 'nullable|numeric|min:0',
                'dimensions'       => 'nullable|string|max:100',
                'is_active'        => 'nullable|boolean',
                'is_taxable'       => 'nullable|boolean',
                'tax_rate'         => 'nullable|numeric|min:0|max:100',
            ]);

            $validated['is_active']  = $request->has('is_active') ? 1 : 0;
            $validated['is_taxable'] = $request->has('is_taxable') ? 1 : 0;

            $oldQuantity = $product->quantity;

            DB::transaction(function () use ($product, $validated, $oldQuantity) {
                $product->update($validated);

                if ($oldQuantity != $product->quantity) {
                    $quantityChange = $product->quantity - $oldQuantity;
                    StockMovement::create([
                        'product_id'        => $product->id,
                        'user_id'           => auth()->id(),
                        'type'              => 'adjustment',
                        'quantity_change'   => $quantityChange,
                        'previous_quantity' => $oldQuantity,
                        'new_quantity'      => $product->quantity,
                        'reference'         => 'UPDATE',
                        'notes'             => 'Quantity updated from ' . $oldQuantity . ' to ' . $product->quantity,
                    ]);
                }
            });

            if ($product->quantity <= $product->min_stock_level && $product->quantity > 0) {
                $this->checkLowStock($product);
            }

            Log::info('Product updated', ['product_id' => $product->id, 'user_id' => auth()->id()]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Product updated successfully!', 'product' => $product]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to update product', [
                'id_requested' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('product.delete')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete products.'], 403);
        }

        if (!is_numeric($id)) {
            Log::warning('ProductController@destroy received a non-numeric id — route ordering problem.', ['id_received' => $id]);
            return response()->json(['success' => false, 'message' => 'Invalid product identifier.'], 400);
        }

        try {
            $product = Product::findOrFail($id);
            $product->delete();
            Log::info('Product deleted', ['product_id' => $id, 'user_id' => auth()->id()]);
            return response()->json(['success' => true, 'message' => 'Product deleted successfully!']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        } catch (\Throwable $e) {
            Log::error('Failed to delete product', [
                'id_requested' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('product.delete')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete products.'], 403);
        }

        try {
            $request->validate([
                'product_ids' => 'required|array|min:1',
                'product_ids.*' => 'exists:products,id',
            ]);

            $count = Product::whereIn('id', $request->product_ids)->delete();
            Log::info('Bulk delete performed', ['count' => $count, 'user_id' => auth()->id()]);

            return response()->json(['success' => true, 'message' => "{$count} product(s) deleted successfully!"]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('Bulk delete failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete products.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function lowStock()
    {
        if (!auth()->user()->can('stock.view')) {
            abort(403, 'You do not have permission to view stock levels.');
        }

        $lowStockProducts = Product::with(['category', 'supplier'])
            ->whereColumn('quantity', '<=', 'min_stock_level')
            ->where('quantity', '>', 0)
            ->orderBy('quantity', 'asc')
            ->paginate(20);

        $outOfStockProducts = Product::where('quantity', '<=', 0)
            ->orderBy('name')
            ->get();

        return view('admin.products.low-stock', compact('lowStockProducts', 'outOfStockProducts'));
    }

    public function export()
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'You do not have permission to export products.');
        }

        try {
            $products = Product::with(['category', 'supplier'])->get();

            $exportData = [];
            $exportData[] = [
                'ID', 'Name', 'SKU', 'Barcode', 'Type', 'Category', 'Supplier',
                'Quantity', 'Status', 'Purchase Price', 'Selling Price',
                'Wholesale Price', 'Cost Price', 'Current Value',
                'Purchase Date', 'Warranty End Date', 'Min Stock Level',
                'Reorder Level', 'Reorder Quantity', 'Unit', 'Weight', 'Dimensions',
                'Taxable', 'Tax Rate', 'Active', 'Created At'
            ];

            foreach ($products as $product) {
                $exportData[] = [
                    $product->id,
                    $product->name,
                    $product->sku,
                    $product->barcode ?? 'N/A',
                    $product->type ?? 'N/A',
                    $product->category->name ?? 'N/A',
                    $product->supplier->name ?? 'N/A',
                    $product->quantity,
                    ucfirst(str_replace('_', ' ', $product->status)),
                    $product->purchase_price ?? '0.00',
                    $product->selling_price ?? '0.00',
                    $product->wholesale_price ?? '0.00',
                    $product->cost_price ?? '0.00',
                    $product->current_value ?? '0.00',
                    $product->purchase_date ? $product->purchase_date->format('Y-m-d') : 'N/A',
                    $product->warranty_end_date ? $product->warranty_end_date->format('Y-m-d') : 'N/A',
                    $product->min_stock_level,
                    $product->reorder_level ?? 0,
                    $product->reorder_quantity ?? 0,
                    $product->unit ?? 'piece',
                    $product->weight ?? 'N/A',
                    $product->dimensions ?? 'N/A',
                    $product->is_taxable ? 'Yes' : 'No',
                    $product->tax_rate ?? '0.00',
                    $product->is_active ? 'Yes' : 'No',
                    $product->created_at->format('Y-m-d H:i:s'),
                ];
            }

            return response()->json([
                'data' => $exportData,
                'count' => $products->count(),
                'exported_at' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Product export failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export products.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // ============================================
    // BULK UPLOAD METHODS
    // ============================================

    public function bulkUpload()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'You do not have permission to create products.');
        }

        $categories = Category::where('is_active', 1)->get();
        $suppliers = Supplier::where('is_active', 1)->get();

        return view('admin.products.bulk-upload', compact('categories', 'suppliers'));
    }

    public function processBulkUpload(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
                'category_id' => 'nullable|exists:categories,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Bulk upload validation failed', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }

        try {
            $file = $request->file('file');

            if (!$file || !$file->isValid()) {
                Log::error('Bulk upload received an invalid file object', [
                    'file_present' => (bool) $file,
                    'error_code' => $file ? $file->getError() : null,
                ]);
                return response()->json(['success' => false, 'message' => 'Uploaded file is invalid or corrupted.'], 422);
            }

            $extension = strtolower($file->getClientOriginalExtension());

            $data = [];
            if ($extension === 'csv') {
                $handle = fopen($file->getPathname(), 'r');
                if (!$handle) {
                    Log::error('Could not open uploaded CSV for reading', ['path' => $file->getPathname()]);
                    return response()->json(['success' => false, 'message' => 'Could not read the uploaded file.'], 422);
                }

                $headers = fgetcsv($handle);
                if (!$headers) {
                    fclose($handle);
                    Log::error('CSV file has no header row', ['filename' => $file->getClientOriginalName()]);
                    return response()->json(['success' => false, 'message' => 'The CSV file appears to be empty.'], 422);
                }

                $headers = array_map(function ($header) {
                    return trim(str_replace("\xEF\xBB\xBF", '', (string) $header));
                }, $headers);

                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) === count($headers)) {
                        $rowData = [];
                        foreach ($headers as $index => $header) {
                            $rowData[$header] = $row[$index] ?? '';
                        }
                        $data[] = $rowData;
                    }
                }
                fclose($handle);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                    Log::error('PhpSpreadsheet package is not installed but an Excel file was uploaded.');
                    return response()->json([
                        'success' => false,
                        'message' => 'Excel file support is not installed on this server. Please upload a CSV file instead, or run "composer require phpoffice/phpspreadsheet".',
                    ], 500);
                }

                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                if (count($rows) > 1) {
                    $headers = array_map('trim', array_shift($rows));
                    foreach ($rows as $row) {
                        if (count($row) === count($headers)) {
                            $rowData = [];
                            foreach ($headers as $index => $header) {
                                $rowData[$header] = $row[$index] ?? '';
                            }
                            $data[] = $rowData;
                        }
                    }
                }
            } else {
                Log::warning('Bulk upload received unsupported file extension', ['extension' => $extension]);
                return response()->json(['success' => false, 'message' => 'Unsupported file type.'], 422);
            }

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found in the file or file format is invalid.'
                ], 422);
            }

            $results = $this->processBulkData($data, $request);

            Log::info('Bulk upload completed', [
                'created' => $results['created'],
                'skipped' => $results['skipped'],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$results['created']} product(s) created successfully. {$results['skipped']} skipped.",
                'results' => $results
            ]);
        } catch (\Throwable $e) {
            Log::error('Bulk upload failed unexpectedly', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process file: ' . $e->getMessage(),
                'debug'   => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    private function processBulkData(array $data, Request $request)
    {
        $created = 0;
        $skipped = 0;
        $errors = [];

        $defaultCategoryId = $request->category_id;
        $defaultSupplierId = $request->supplier_id;

        foreach ($data as $index => $row) {
            try {
                $name = $this->getFieldValue($row, ['name', 'Name', 'product_name', 'Product Name']);
                $sku = $this->getFieldValue($row, ['sku', 'SKU', 'product_sku', 'Product SKU']);

                if (empty($name) || empty($sku)) {
                    $errors[] = "Row " . ($index + 2) . ": Name and SKU are required.";
                    $skipped++;
                    continue;
                }

                if (Product::where('sku', $sku)->exists()) {
                    $errors[] = "Row " . ($index + 2) . ": SKU '{$sku}' already exists.";
                    $skipped++;
                    continue;
                }

                $categoryId = $defaultCategoryId;
                $categoryName = $this->getFieldValue($row, ['category', 'Category', 'category_name', 'Category Name']);
                if (!empty($categoryName)) {
                    $category = Category::where('name', 'LIKE', $categoryName)->first();
                    if ($category) {
                        $categoryId = $category->id;
                    } else {
                        $errors[] = "Row " . ($index + 2) . ": Category '{$categoryName}' not found.";
                    }
                }

                if (empty($categoryId)) {
                    $errors[] = "Row " . ($index + 2) . ": No category resolved (row has none and no default was set). Row skipped.";
                    $skipped++;
                    continue;
                }

                $supplierId = $defaultSupplierId;
                $supplierName = $this->getFieldValue($row, ['supplier', 'Supplier', 'supplier_name', 'Supplier Name']);
                if (!empty($supplierName)) {
                    $supplier = Supplier::where('name', 'LIKE', $supplierName)->first();
                    if ($supplier) {
                        $supplierId = $supplier->id;
                    } else {
                        $errors[] = "Row " . ($index + 2) . ": Supplier '{$supplierName}' not found.";
                    }
                }

                $quantity = (int) $this->getFieldValue($row, ['quantity', 'Quantity', 'qty', 'Qty']);
                if ($quantity < 0) $quantity = 0;

                $productData = [
                    'name' => $name,
                    'sku' => $sku,
                    'barcode' => $this->getFieldValue($row, ['barcode', 'Barcode']) ?: null,
                    'description' => $this->getFieldValue($row, ['description', 'Description', 'desc', 'Desc']) ?: null,
                    'type' => $this->getFieldValue($row, ['type', 'Type', 'product_type', 'Product Type']) ?: null,
                    'category_id' => $categoryId,
                    'supplier_id' => $supplierId ?: null,
                    'quantity' => $quantity,
                    'purchase_price' => (float) ($this->getFieldValue($row, ['purchase_price', 'Purchase_Price', 'Purchase Price', 'cost', 'Cost']) ?: 0),
                    'selling_price' => (float) ($this->getFieldValue($row, ['selling_price', 'Selling_Price', 'Selling Price', 'price', 'Price']) ?: 0),
                    'wholesale_price' => (float) ($this->getFieldValue($row, ['wholesale_price', 'Wholesale_Price', 'Wholesale Price']) ?: 0),
                    'cost_price' => (float) ($this->getFieldValue($row, ['cost_price', 'Cost_Price', 'Cost Price']) ?: 0),
                    'current_value' => (float) ($this->getFieldValue($row, ['current_value', 'Current_Value', 'Current Value']) ?: 0),
                    'status' => 'available',
                    'min_stock_level' => (int) ($this->getFieldValue($row, ['min_stock', 'Min_Stock', 'Min Stock', 'min_stock_level', 'Min Stock Level']) ?: 0),
                    'reorder_level' => (int) ($this->getFieldValue($row, ['reorder_level', 'Reorder_Level', 'Reorder Level']) ?: 0),
                    'reorder_quantity' => (int) ($this->getFieldValue($row, ['reorder_quantity', 'Reorder_Quantity', 'Reorder Quantity']) ?: 0),
                    'unit' => $this->getFieldValue($row, ['unit', 'Unit']) ?: 'piece',
                    'weight' => (float) ($this->getFieldValue($row, ['weight', 'Weight']) ?: 0),
                    'dimensions' => $this->getFieldValue($row, ['dimensions', 'Dimensions']) ?: null,
                    'is_active' => 1,
                    'is_taxable' => 1,
                    'tax_rate' => (float) ($this->getFieldValue($row, ['tax_rate', 'Tax_Rate', 'Tax Rate']) ?: 0),
                ];

                $purchaseDate = $this->getFieldValue($row, ['purchase_date', 'Purchase_Date', 'Purchase Date']);
                if (!empty($purchaseDate)) {
                    $ts = strtotime($purchaseDate);
                    if ($ts !== false) {
                        $productData['purchase_date'] = date('Y-m-d', $ts);
                    } else {
                        $errors[] = "Row " . ($index + 2) . ": Could not parse purchase_date '{$purchaseDate}', left blank.";
                    }
                }

                $warrantyEnd = $this->getFieldValue($row, ['warranty_end', 'Warranty_End', 'Warranty End', 'warranty_end_date']);
                if (!empty($warrantyEnd)) {
                    $ts = strtotime($warrantyEnd);
                    if ($ts !== false) {
                        $productData['warranty_end_date'] = date('Y-m-d', $ts);
                    } else {
                        $errors[] = "Row " . ($index + 2) . ": Could not parse warranty_end '{$warrantyEnd}', left blank.";
                    }
                }

                DB::transaction(function () use ($productData, &$product) {
                    $product = Product::create($productData);

                    if ($product->quantity > 0) {
                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'type' => 'purchase',
                            'quantity_change' => $product->quantity,
                            'previous_quantity' => 0,
                            'new_quantity' => $product->quantity,
                            'reference' => 'BULK_UPLOAD',
                            'notes' => 'Product created via bulk upload',
                        ]);
                    }
                });

                $created++;
            } catch (\Throwable $e) {
                Log::error('Bulk upload row failed', [
                    'row_index' => $index + 2,
                    'row_data'  => $row,
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ]);
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        if ($created > 0) {
            try {
                $lowStockProducts = Product::whereColumn('quantity', '<=', 'min_stock_level')
                    ->where('quantity', '>', 0)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->get();
                foreach ($lowStockProducts as $product) {
                    $this->checkLowStock($product);
                }
            } catch (\Throwable $e) {
                // Notifications are non-critical to the upload result; log and continue.
                Log::warning('Low stock notification pass failed after bulk upload', ['message' => $e->getMessage()]);
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function getFieldValue($row, $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return trim($row[$key]);
            }
        }
        return '';
    }

    public function downloadTemplate()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403);
        }

        $headers = [
            'name', 'sku', 'barcode', 'description', 'type',
            'category', 'supplier', 'quantity', 'purchase_price',
            'selling_price', 'wholesale_price', 'cost_price',
            'current_value', 'min_stock', 'reorder_level',
            'reorder_quantity', 'unit', 'weight', 'dimensions',
            'tax_rate', 'purchase_date', 'warranty_end'
        ];

        $filename = 'products_bulk_template_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            fputcsv($handle, [
                'Dell XPS 15 Laptop', 'LAP-DELL-XPS15', '1234567890123',
                'High performance laptop with 16GB RAM and 512GB SSD', 'hardware',
                'Electronics', 'Tech Suppliers Ltd', '10', '1500.00', '2200.00',
                '2000.00', '1400.00', '14000.00', '5', '3',
                '5', 'piece', '2.5', '15x10x2', '18.00',
                '2024-01-15', '2025-01-15'
            ]);

            fputcsv($handle, [
                'Office Chair', 'FUR-CHAIR-001', '9876543210987',
                'Ergonomic office chair with lumbar support', 'furniture',
                'Furniture', 'Furniture World', '25', '250.00', '450.00',
                '400.00', '220.00', '5500.00', '10', '5',
                '10', 'piece', '15.5', '60x60x120', '0.00',
                '2024-02-01', ''
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

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

    public function adjustQuantity(Request $request, $id)
    {
        if (!auth()->user()->can('stock.create')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to adjust stock.'], 403);
        }

        if (!is_numeric($id)) {
            Log::warning('ProductController@adjustQuantity received a non-numeric id — route ordering problem.', ['id_received' => $id]);
            return response()->json(['success' => false, 'message' => 'Invalid product identifier.'], 400);
        }

        try {
            $request->validate([
                'adjustment' => 'required|integer|min:1',
                'type' => 'required|in:add,subtract',
                'reason' => 'nullable|string',
            ]);

            $product = Product::findOrFail($id);
            $oldQuantity = $product->quantity;

            if ($request->type === 'add') {
                $newQuantity = $oldQuantity + $request->adjustment;
            } else {
                $newQuantity = $oldQuantity - $request->adjustment;
                if ($newQuantity < 0) {
                    return response()->json(['success' => false, 'message' => 'Quantity cannot be negative.'], 422);
                }
            }

            DB::transaction(function () use ($product, $newQuantity, $oldQuantity, $request) {
                $product->update(['quantity' => $newQuantity]);

                StockMovement::create([
                    'product_id'        => $product->id,
                    'user_id'           => auth()->id(),
                    'type'              => 'adjustment',
                    'quantity_change'   => $newQuantity - $oldQuantity,
                    'previous_quantity' => $oldQuantity,
                    'new_quantity'      => $newQuantity,
                    'reference'         => 'ADJUST',
                    'notes'             => $request->reason ?? 'Manual stock adjustment',
                ]);
            });

            if ($product->quantity <= $product->min_stock_level && $product->quantity > 0) {
                $this->checkLowStock($product);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully!',
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'product' => $product,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('Stock adjustment failed', [
                'id_requested' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stock.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function statistics()
    {
        if (!auth()->user()->can('product.view')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to view statistics.'], 403);
        }

        try {
            $stats = [
                'total' => Product::count(),
                'by_status' => Product::select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray(),
                'by_category' => Category::withCount('products')
                    ->orderBy('products_count', 'desc')
                    ->limit(5)
                    ->get(),
                'total_value' => Product::sum('purchase_price'),
                'total_quantity' => Product::sum('quantity'),
                'low_stock_count' => Product::whereColumn('quantity', '<=', 'min_stock_level')
                    ->where('quantity', '>', 0)
                    ->count(),
                'out_of_stock_count' => Product::where('quantity', '<=', 0)->count(),
                'expired_warranties' => Product::where('warranty_end_date', '<', now())
                    ->whereNotNull('warranty_end_date')
                    ->count(),
                'expiring_soon' => Product::whereBetween('warranty_end_date', [now(), now()->addDays(30)])
                    ->whereNotNull('warranty_end_date')
                    ->count(),
            ];

            return response()->json(['success' => true, 'stats' => $stats]);
        } catch (\Throwable $e) {
            Log::error('Failed to compute statistics', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function dropdown(Request $request)
    {
        if (!auth()->user()->can('product.view')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to view products.'], 403);
        }

        try {
            $query = Product::select('id', 'name', 'sku', 'type', 'selling_price');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $products = $query->orderBy('name')->limit(50)->get();

            return response()->json(['success' => true, 'products' => $products]);
        } catch (\Throwable $e) {
            Log::error('Dropdown query failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function ensureStockRecord(Product $product, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouse = Warehouse::where('is_active', 1)->first();
            if (!$warehouse) {
                Log::warning('No active warehouse found; skipping stock record creation.', ['product_id' => $product->id]);
                return null;
            }
            $warehouseId = $warehouse->id;
        }

        return Stock::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
            ['quantity' => $product->quantity ?? 0]
        );
    }
}