<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('category.view')) {
            abort(403, 'You do not have permission to view categories.');
        }

        $query = Category::withCount('products');

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Sort by product count
        if ($request->has('sort')) {
            if ($request->sort === 'products_asc') {
                $query->orderBy('products_count', 'asc');
            } elseif ($request->sort === 'products_desc') {
                $query->orderBy('products_count', 'desc');
            } else {
                $query->orderBy('name', $request->sort ?? 'asc');
            }
        } else {
            $query->latest();
        }

        $perPage = $request->per_page ?? 15;
        $categories = $query->paginate($perPage);

        // Stats
        $stats = [
            'total' => Category::count(),
            'with_products' => Category::has('products')->count(),
            'without_products' => Category::doesntHave('products')->count(),
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        if (!auth()->user()->can('category.create')) {
            abort(403, 'You do not have permission to create categories.');
        }

        return view('admin.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('category.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create categories.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:categories',
                'description' => 'nullable|string|max:500',
            ]);

            $category = Category::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category created successfully!',
                    'category' => $category
                ]);
            }

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        try {
            if (!auth()->user()->can('category.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view categories.'
                ], 403);
            }

            $category = Category::withCount('products')
                ->with(['products' => function($query) {
                    $query->select('id', 'name', 'sku', 'type', 'status', 'quantity')
                          ->latest()
                          ->limit(10);
                }])
                ->findOrFail($id);
            
            // Get product counts by type
            $productTypeCounts = Product::where('category_id', $id)
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();

            $hardwareCount = $productTypeCounts['hardware'] ?? 0;
            $softwareCount = $productTypeCounts['software'] ?? 0;

            return response()->json([
                'success' => true,
                'category' => $category,
                'product_stats' => [
                    'hardware' => $hardwareCount,
                    'software' => $softwareCount,
                    'total' => $category->products_count,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load category details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        try {
            if (!auth()->user()->can('category.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit categories.'
                ], 403);
            }

            $category = Category::findOrFail($id);

            return response()->json([
                'success' => true,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'slug' => $category->slug,
                    'is_active' => $category->is_active,
                    'order' => $category->order,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching category for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load category data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('category.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit categories.'
                ], 403);
            }

            $category = Category::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:categories,name,' . $id,
                'description' => 'nullable|string|max:500',
                'is_active' => 'nullable|boolean',
            ]);

            $category->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category updated successfully!',
                    'category' => $category
                ]);
            }

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        try {
            if (!auth()->user()->can('category.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete categories.'
                ], 403);
            }

            $category = Category::findOrFail($id);

            $productCount = $category->products()->count();
            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with ' . $productCount . ' associated product(s). Reassign products first.'
                ], 422);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDelete(Request $request)
    {
        try {
            if (!auth()->user()->can('category.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete categories.'
                ], 403);
            }

            $request->validate([
                'category_ids' => 'required|array|min:1',
                'category_ids.*' => 'exists:categories,id',
            ]);

            $categories = Category::whereIn('id', $request->category_ids)
                ->withCount('products')
                ->get();

            $categoriesWithProducts = $categories->filter(function($category) {
                return $category->products_count > 0;
            });

            if ($categoriesWithProducts->isNotEmpty()) {
                $names = $categoriesWithProducts->pluck('name')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete categories with associated products: {$names}"
                ], 422);
            }

            $deletedCount = Category::whereIn('id', $request->category_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} category(s) deleted successfully!"
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export categories to CSV (downloadable).
     */
    public function export()
    {
        try {
            if (!auth()->user()->can('category.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export categories.'
                ], 403);
            }

            $categories = Category::withCount('products')->get();

            $filename = 'categories_export_' . date('Y-m-d_His') . '.csv';

            return response()->streamDownload(function () use ($categories) {
                $handle = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($handle, ['ID', 'Name', 'Slug', 'Description', 'Product Count', 'Is Active', 'Order', 'Created At', 'Updated At']);
                
                // Add data rows
                foreach ($categories as $category) {
                    fputcsv($handle, [
                        $category->id,
                        $category->name,
                        $category->slug ?? '',
                        $category->description ?? '',
                        $category->products_count,
                        $category->is_active ? 'Yes' : 'No',
                        $category->order ?? 0,
                        $category->created_at->format('Y-m-d H:i:s'),
                        $category->updated_at->format('Y-m-d H:i:s'),
                    ]);
                }
                
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import categories from CSV (stub).
     */
    public function import(Request $request)
    {
        try {
            if (!auth()->user()->can('category.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to import categories.'
                ], 403);
            }

            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
            ]);

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
            \Log::error('Error importing categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus($id)
    {
        try {
            if (!auth()->user()->can('category.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to toggle category status.'
                ], 403);
            }

            $category = Category::findOrFail($id);
            $category->is_active = !$category->is_active;
            $category->save();

            return response()->json([
                'success' => true,
                'message' => 'Category status updated.',
                'is_active' => $category->is_active
            ]);

        } catch (\Exception $e) {
            \Log::error('Error toggling category status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle category status.'
            ], 500);
        }
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request)
    {
        try {
            if (!auth()->user()->can('category.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to reorder categories.'
                ], 403);
            }

            $request->validate([
                'order' => 'required|array',
                'order.*' => 'exists:categories,id',
            ]);

            foreach ($request->order as $index => $id) {
                Category::where('id', $id)->update(['order' => $index]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categories reordered successfully!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error reordering categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for dashboard widgets.
     */
    public function widget()
    {
        try {
            if (!auth()->user()->can('category.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view categories.'
                ], 403);
            }

            $topCategories = Category::withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit(5)
                ->get();

            $recentCategories = Category::latest()
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'top_categories' => $topCategories,
                'recent_categories' => $recentCategories,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching category widget data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category data.'
            ], 500);
        }
    }

    /**
     * Get categories with product counts (for dropdowns, etc.).
     */
    public function dropdown(Request $request)
    {
        try {
            if (!auth()->user()->can('category.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view categories.'
                ], 403);
            }

            $query = Category::where('is_active', 1)->orderBy('order')->orderBy('name');

            if ($request->has('search') && $request->search) {
                $query->where('name', 'LIKE', "%{$request->search}%");
            }

            $categories = $query->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching category dropdown: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories.'
            ], 500);
        }
    }

    /**
     * Get category by slug.
     */
    public function bySlug($slug)
    {
        try {
            if (!auth()->user()->can('category.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view categories.'
                ], 403);
            }

            $category = Category::where('slug', $slug)
                ->withCount('products')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'category' => $category
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching category by slug: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }
    }
}