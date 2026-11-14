<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('variant.view')) {
            abort(403, 'You do not have permission to view product variants.');
        }

        $variants = ProductVariant::with('product')->paginate(15);
        $stats = [
            'total' => ProductVariant::count(),
            'active' => ProductVariant::where('is_active', 1)->count(),
            'inactive' => ProductVariant::where('is_active', 0)->count(),
            'low_stock' => ProductVariant::where('quantity', '<=', 5)->where('quantity', '>', 0)->count(),
        ];

        $products = Product::where('is_active', 1)->get(); // for dropdowns in modals

        return view('admin.variants.index', compact('variants', 'stats', 'products'));
    }

    // No create() method – handled by modal in index

    public function store(Request $request)
    {
        if (!auth()->user()->can('variant.create')) {
            return response()->json(['error' => 'You do not have permission to create product variants.'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|string|max:100|unique:product_variants',
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'attributes' => 'nullable|array',
            'barcode' => 'nullable|string|max:255|unique:product_variants',
            'image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            ProductVariant::create($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Product variant created successfully!']);
            }
            return redirect()->route('admin.variants.index')->with('success', 'Product variant created successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to create product variant: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to create product variant.');
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('variant.view')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You do not have permission to view product variants.'], 403);
            }
            abort(403);
        }

        try {
            $variant = ProductVariant::with('product')->findOrFail($id);
            if (request()->ajax()) {
                return response()->json($variant);
            }
            return view('admin.variants.show', compact('variant'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Product variant not found.'], 404);
            }
            abort(404);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->can('variant.edit')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You do not have permission to edit product variants.'], 403);
            }
            abort(403);
        }

        try {
            $variant = ProductVariant::findOrFail($id);
            if (request()->ajax()) {
                // Return the variant data for the modal, plus product list if needed
                $products = Product::where('is_active', 1)->get();
                return response()->json([
                    'variant' => $variant,
                    'products' => $products
                ]);
            }
            // If not AJAX, we can still render a view (but we want modals)
            return redirect()->route('admin.variants.index');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Product variant not found.'], 404);
            }
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('variant.edit')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You do not have permission to edit product variants.'], 403);
            }
            abort(403);
        }

        try {
            $variant = ProductVariant::findOrFail($id);

            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'sku' => 'required|string|max:100|unique:product_variants,sku,' . $id,
                'name' => 'required|string|max:255',
                'selling_price' => 'required|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'attributes' => 'nullable|array',
                'barcode' => 'nullable|string|max:255|unique:product_variants,barcode,' . $id,
                'image' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $variant->update($validated);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Product variant updated successfully!']);
            }
            return redirect()->route('admin.variants.index')->with('success', 'Product variant updated successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to update product variant: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to update product variant.');
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('variant.delete')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You do not have permission to delete product variants.'], 403);
            }
            abort(403);
        }

        try {
            $variant = ProductVariant::findOrFail($id);
            $variant->delete();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Product variant deleted successfully!']);
            }
            return redirect()->route('admin.variants.index')->with('success', 'Product variant deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Failed to delete product variant: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete product variant.');
        }
    }

    public function toggleStatus($id)
    {
        if (!auth()->user()->can('variant.toggle_status')) {
            return response()->json(['error' => 'You do not have permission to toggle variant status.'], 403);
        }

        try {
            $variant = ProductVariant::findOrFail($id);
            $variant->is_active = !$variant->is_active;
            $variant->save();
            return response()->json([
                'success' => true,
                'message' => 'Variant status toggled successfully!',
                'is_active' => $variant->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to toggle variant status: ' . $e->getMessage()], 500);
        }
    }

    public function byProduct($productId)
    {
        if (!auth()->user()->can('variant.view')) {
            return response()->json(['error' => 'You do not have permission to view product variants.'], 403);
        }

        $variants = ProductVariant::where('product_id', $productId)
            ->where('is_active', 1)
            ->get(['id', 'name', 'sku', 'selling_price', 'quantity', 'attributes']);
        
        return response()->json($variants);
    }

    public function export()
    {
        if (!auth()->user()->can('variant.export')) {
            abort(403);
        }
        return back()->with('info', 'Export feature coming soon!');
    }
}