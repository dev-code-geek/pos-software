<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of available products.
     */
    public function index(Request $request)
    {
        $query = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->with(['category', 'supplier']);

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'price_asc':
                    $query->orderBy('purchase_price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('purchase_price', 'desc');
                    break;
                case 'quantity':
                    $query->orderBy('quantity', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(20);
        $categories = Category::all();

        return view('user.products.index', compact('products', 'categories'));
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->with(['category', 'supplier'])
            ->findOrFail($id);

        $categories = Category::all();

        return view('user.products.show', compact('product', 'categories'));
    }

    /**
     * Search products via AJAX.
     */
    public function search(Request $request)
    {
        $search = $request->search;

        $products = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'quantity', 'type']);

        return response()->json($products);
    }

    /**
     * Get products by category.
     */
    public function byCategory($categoryId)
    {
        $products = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->where('category_id', $categoryId)
            ->get(['id', 'name', 'sku', 'quantity', 'type']);

        return response()->json($products);
    }
}