<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     * Requires 'supplier.view' permission.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('supplier.view')) {
            abort(403, 'You do not have permission to view suppliers.');
        }

        $query = Supplier::withCount(['products', 'purchaseOrders']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        // Filter by has products
        if ($request->has('has_products')) {
            if ($request->has_products === 'yes') {
                $query->has('products');
            } elseif ($request->has_products === 'no') {
                $query->doesntHave('products');
            }
        }

        // Filter by has purchase orders
        if ($request->has('has_purchase_orders')) {
            if ($request->has_purchase_orders === 'yes') {
                $query->has('purchaseOrders');
            } elseif ($request->has_purchase_orders === 'no') {
                $query->doesntHave('purchaseOrders');
            }
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sort
        if ($request->has('sort')) {
            $sortField = $request->sort;
            $sortDirection = $request->direction ?? 'asc';
            
            if (in_array($sortField, ['name', 'products_count', 'purchase_orders_count', 'created_at'])) {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->per_page ?? 15;
        $suppliers = $query->paginate($perPage);

        // Stats
        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
            'with_products' => Supplier::has('products')->count(),
            'without_products' => Supplier::doesntHave('products')->count(),
            'with_purchase_orders' => Supplier::has('purchaseOrders')->count(),
            'active_suppliers' => Supplier::has('products')->orHas('purchaseOrders')->count(),
        ];

        // Get top suppliers by product count
        $topSuppliers = Supplier::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.suppliers.index', compact('suppliers', 'stats', 'topSuppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     * Requires 'supplier.create' permission.
     */
    public function create()
    {
        if (!auth()->user()->can('supplier.create')) {
            abort(403, 'You do not have permission to create suppliers.');
        }

        return view('admin.suppliers.create');
    }

    /**
     * Store a newly created supplier.
     * Requires 'supplier.create' permission.
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('supplier.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create suppliers.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'website' => 'nullable|url|max:255',
                'tax_id' => 'nullable|string|max:100',
                'registration_number' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['created_by'] = auth()->id(); // Explicitly set
            $validated['updated_by'] = auth()->id(); // Explicitly set
            
            $supplier = Supplier::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier created successfully!',
                    'supplier' => $supplier
                ]);
            }

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified supplier.
     * Requires 'supplier.edit' permission.
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('supplier.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit suppliers.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'website' => 'nullable|url|max:255',
                'tax_id' => 'nullable|string|max:100',
                'registration_number' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['is_active'] = $validated['is_active'] ?? false;
            $validated['updated_by'] = auth()->id(); // Explicitly set
            
            $supplier->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier updated successfully!',
                    'supplier' => $supplier
                ]);
            }

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified supplier.
     * Requires 'supplier.view' permission.
     */
    public function show($id)
    {
        try {
            if (!auth()->user()->can('supplier.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view suppliers.'
                ], 403);
            }

            $supplier = Supplier::withCount(['products', 'purchaseOrders', 'procurements'])
                ->with(['products' => function($query) {
                    $query->select('id', 'name', 'sku', 'type', 'status', 'quantity', 'purchase_price')
                          ->latest()
                          ->limit(10);
                }, 'purchaseOrders' => function($query) {
                    $query->select('id', 'supplier_id', 'po_number', 'total_amount', 'status', 'order_date')
                          ->latest()
                          ->limit(5);
                }])
                ->findOrFail($id);

            // Get product type distribution
            $productTypeCounts = Product::where('supplier_id', $id)
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();

            // Get purchase order statistics
            $poStats = [
                'total' => PurchaseOrder::where('supplier_id', $id)->count(),
                'total_amount' => PurchaseOrder::where('supplier_id', $id)->sum('total_amount'),
                'received' => PurchaseOrder::where('supplier_id', $id)->where('status', 'received')->count(),
                'ordered' => PurchaseOrder::where('supplier_id', $id)->where('status', 'ordered')->count(),
                'cancelled' => PurchaseOrder::where('supplier_id', $id)->where('status', 'cancelled')->count(),
            ];

            return response()->json([
                'success' => true,
                'supplier' => $supplier,
                'product_type_counts' => $productTypeCounts,
                'purchase_order_stats' => $poStats,
                'total_products' => $supplier->products_count,
                'total_purchase_orders' => $supplier->purchase_orders_count,
                'total_procurements' => $supplier->procurements_count,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load supplier details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified supplier.
     * RETURNS JSON FOR AJAX
     */
    public function edit($id)
    {
        try {
            if (!auth()->user()->can('supplier.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit suppliers.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);

            // Format the supplier data for the edit form
            $supplierData = [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'website' => $supplier->website,
                'tax_id' => $supplier->tax_id,
                'registration_number' => $supplier->registration_number,
                'notes' => $supplier->notes,
                'is_active' => $supplier->is_active,
                'created_at' => $supplier->created_at,
                'updated_at' => $supplier->updated_at,
            ];

            return response()->json([
                'success' => true,
                'supplier' => $supplierData
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load supplier data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified supplier.
     * Requires 'supplier.delete' permission.
     */
    public function destroy($id)
    {
        try {
            if (!auth()->user()->can('supplier.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete suppliers.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);

            // Check if supplier has associated products
            $productCount = $supplier->products()->count();
            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete supplier with {$productCount} associated product(s)."
                ], 422);
            }

            // Check if supplier has purchase orders
            $poCount = $supplier->purchaseOrders()->count();
            if ($poCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete supplier with {$poCount} associated purchase order(s)."
                ], 422);
            }

            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete suppliers.
     * Requires 'supplier.delete' permission.
     */
    public function bulkDelete(Request $request)
    {
        try {
            if (!auth()->user()->can('supplier.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete suppliers.'
                ], 403);
            }

            $request->validate([
                'supplier_ids' => 'required|array|min:1',
                'supplier_ids.*' => 'exists:suppliers,id',
            ]);

            // Check if any supplier has associated products or purchase orders
            $suppliers = Supplier::whereIn('id', $request->supplier_ids)
                ->withCount(['products', 'purchaseOrders'])
                ->get();

            $invalidSuppliers = $suppliers->filter(function($supplier) {
                return $supplier->products_count > 0 || $supplier->purchase_orders_count > 0;
            });

            if ($invalidSuppliers->isNotEmpty()) {
                $names = $invalidSuppliers->pluck('name')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete suppliers with associated products or purchase orders: {$names}"
                ], 422);
            }

            $deletedCount = Supplier::whereIn('id', $request->supplier_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} supplier(s) deleted successfully!"
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error bulk deleting suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete suppliers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle supplier active status.
     * Requires 'supplier.edit' permission.
     */
    public function toggleStatus($id)
    {
        try {
            if (!auth()->user()->can('supplier.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update supplier status.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);
            $supplier->update(['is_active' => !$supplier->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier status updated successfully!',
                'is_active' => $supplier->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling supplier status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier status.'
            ], 500);
        }
    }

    /**
     * Get suppliers dropdown list for select inputs.
     * Requires 'supplier.view' permission.
     */
    public function dropdown(Request $request)
    {
        try {
            if (!auth()->user()->can('supplier.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view suppliers.'
                ], 403);
            }

            $query = Supplier::select('id', 'name');

            if ($request->has('search') && $request->search) {
                $query->where('name', 'LIKE', "%{$request->search}%");
            }

            if ($request->has('active_only') && $request->active_only) {
                $query->where('is_active', true);
            }

            $suppliers = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'suppliers' => $suppliers
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier dropdown: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers.'
            ], 500);
        }
    }

    /**
     * Get supplier statistics.
     * Requires 'supplier.view' permission.
     */
    public function statistics()
    {
        try {
            if (!auth()->user()->can('supplier.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view statistics.'
                ], 403);
            }

            $stats = [
                'total' => Supplier::count(),
                'active' => Supplier::where('is_active', true)->count(),
                'inactive' => Supplier::where('is_active', false)->count(),
                'with_products' => Supplier::has('products')->count(),
                'without_products' => Supplier::doesntHave('products')->count(),
                'with_purchase_orders' => Supplier::has('purchaseOrders')->count(),
                'without_purchase_orders' => Supplier::doesntHave('purchaseOrders')->count(),
                'top_suppliers' => Supplier::withCount(['products', 'purchaseOrders'])
                    ->orderBy('products_count', 'desc')
                    ->limit(5)
                    ->get(),
                'by_product_count' => Supplier::select(
                    DB::raw('CASE 
                        WHEN products_count = 0 THEN "0 products"
                        WHEN products_count <= 5 THEN "1-5 products"
                        WHEN products_count <= 20 THEN "6-20 products"
                        ELSE "20+ products"
                    END as category'),
                    DB::raw('count(*) as total')
                )
                ->withCount('products')
                ->groupBy('category')
                ->get(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Export suppliers to CSV/Excel.
     * Requires 'supplier.view' permission.
     */
    public function export(Request $request)
    {
        try {
            if (!auth()->user()->can('supplier.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export suppliers.'
                ], 403);
            }

            $suppliers = Supplier::withCount(['products', 'purchaseOrders'])
                ->latest()
                ->get();

            // Prepare export data
            $exportData = [];
            $exportData[] = [
                'ID', 'Name', 'Contact Person', 'Email', 'Phone', 'Address',
                'Website', 'Tax ID', 'Registration Number', 'Products Count',
                'Purchase Orders Count', 'Status', 'Created At'
            ];

            foreach ($suppliers as $supplier) {
                $exportData[] = [
                    $supplier->id,
                    $supplier->name,
                    $supplier->contact_person ?? 'N/A',
                    $supplier->email ?? 'N/A',
                    $supplier->phone ?? 'N/A',
                    $supplier->address ?? 'N/A',
                    $supplier->website ?? 'N/A',
                    $supplier->tax_id ?? 'N/A',
                    $supplier->registration_number ?? 'N/A',
                    $supplier->products_count,
                    $supplier->purchase_orders_count,
                    $supplier->is_active ? 'Active' : 'Inactive',
                    $supplier->created_at->format('Y-m-d H:i:s'),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'count' => $suppliers->count(),
                'exported_at' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export suppliers.'
            ], 500);
        }
    }

    /**
     * Import suppliers from CSV/Excel.
     * Requires 'supplier.create' permission.
     */
    public function import(Request $request)
    {
        try {
            if (!auth()->user()->can('supplier.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to import suppliers.'
                ], 403);
            }

            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
            ]);

            // Store the file temporarily
            $path = $request->file('file')->store('temp', 'public');

            return response()->json([
                'success' => true,
                'message' => 'Import feature coming soon!',
                'file' => $path,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error importing suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import suppliers.'
            ], 500);
        }
    }

    /**
     * Get supplier products.
     * Requires 'supplier.view' permission.
     */
    public function products($id)
    {
        try {
            if (!auth()->user()->can('supplier.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view supplier products.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);
            $products = Product::where('supplier_id', $id)
                ->select('id', 'name', 'sku', 'type', 'status', 'quantity', 'purchase_price')
                ->get();

            return response()->json([
                'success' => true,
                'supplier' => $supplier->name,
                'total_products' => $products->count(),
                'products' => $products,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch supplier products.'
            ], 500);
        }
    }

    /**
     * Get supplier purchase orders.
     * Requires 'supplier.view' permission.
     */
    public function purchaseOrders($id)
    {
        try {
            if (!auth()->user()->can('supplier.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view supplier purchase orders.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);
            $purchaseOrders = PurchaseOrder::where('supplier_id', $id)
                ->select('id', 'po_number', 'total_amount', 'status', 'order_date', 'expected_delivery_date', 'actual_delivery_date')
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'supplier' => $supplier->name,
                'total_purchase_orders' => $purchaseOrders->count(),
                'total_amount' => $purchaseOrders->sum('total_amount'),
                'purchase_orders' => $purchaseOrders,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier purchase orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchase orders.'
            ], 500);
        }
    }

    /**
     * Get supplier procurements.
     * Requires 'supplier.view' permission.
     */
    public function procurements($id)
    {
        try {
            if (!auth()->user()->can('supplier.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view supplier procurements.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);
            $procurements = Procurement::where('supplier_id', $id)
                ->with(['assetRequest', 'procurementOfficer'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'supplier' => $supplier->name,
                'total_procurements' => $procurements->count(),
                'total_cost' => $procurements->sum('total_cost'),
                'procurements' => $procurements,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier procurements: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch procurements.'
            ], 500);
        }
    }

    /**
     * Get dashboard widget data.
     */
    public function widget()
    {
        try {
            $recentSuppliers = Supplier::latest()
                ->limit(5)
                ->get();

            $topSuppliers = Supplier::withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'recent_suppliers' => $recentSuppliers,
                'top_suppliers' => $topSuppliers,
                'total_suppliers' => Supplier::count(),
                'active_suppliers' => Supplier::where('is_active', true)->count(),
                'total_products' => Product::count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching widget data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch widget data.'
            ], 500);
        }
    }

    /**
     * Check if a supplier name exists.
     */
    public function checkName(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'except_id' => 'nullable|exists:suppliers,id',
            ]);

            $exists = Supplier::where('name', $request->name)
                ->when($request->except_id, function($query) use ($request) {
                    return $query->where('id', '!=', $request->except_id);
                })
                ->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists,
                'message' => $exists ? 'Supplier name already exists' : 'Supplier name is available',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error checking supplier name: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check supplier name.'
            ], 500);
        }
    }

    /**
     * Get supplier summary for dashboard.
     */
    public function summary()
    {
        try {
            $summary = [
                'total_suppliers' => Supplier::count(),
                'active_suppliers' => Supplier::where('is_active', true)->count(),
                'suppliers_with_products' => Supplier::has('products')->count(),
                'suppliers_without_products' => Supplier::doesntHave('products')->count(),
                'total_products' => Product::count(),
                'total_purchase_orders' => PurchaseOrder::count(),
                'total_procurement_cost' => Procurement::sum('total_cost'),
                'top_supplier' => Supplier::withCount('products')
                    ->orderBy('products_count', 'desc')
                    ->first(),
            ];

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch summary.'
            ], 500);
        }
    }

    /**
     * Get suppliers with their product count.
     * Requires 'supplier.view' permission.
     */
    public function withProducts()
    {
        try {
            if (!auth()->user()->can('supplier.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view suppliers.'
                ], 403);
            }

            $suppliers = Supplier::withCount('products')
                ->having('products_count', '>', 0)
                ->orderBy('products_count', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'total' => $suppliers->count(),
                'suppliers' => $suppliers,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching suppliers with products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers.'
            ], 500);
        }
    }
}