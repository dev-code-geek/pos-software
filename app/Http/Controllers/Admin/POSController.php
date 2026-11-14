<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index(Request $request)
    {
        $products = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->get();

        $categories = \App\Models\Category::all();
        $customers = Customer::where('is_active', true)->get();

        $cart = session()->get('cart', []);
        $subtotal = $this->cartSubtotal();
        $itemCount = $this->cartCount();

        return view('admin.pos.index', compact(
            'products', 'categories', 'customers',
            'cart', 'subtotal', 'itemCount'
        ));
    }

    /**
     * Add product to cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock.'
            ], 422);
        }

        $quantity = $request->quantity ?? 1;
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $quantity,
                'unit_price' => $product->selling_price,
                'total' => $product->selling_price * $quantity,
            ];
        }

        // Recalculate total
        $cart[$product->id]['total'] = $cart[$product->id]['unit_price'] * $cart[$product->id]['quantity'];
        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart.',
            'cart' => $cart,
            'subtotal' => $this->cartSubtotal(),
            'count' => $this->cartCount(),
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->product_id])) {
            if ($request->quantity <= 0) {
                unset($cart[$request->product_id]);
            } else {
                $cart[$request->product_id]['quantity'] = $request->quantity;
                $cart[$request->product_id]['total'] = $cart[$request->product_id]['unit_price'] * $request->quantity;
            }
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'subtotal' => $this->cartSubtotal(),
            'count' => $this->cartCount(),
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cart = session()->get('cart', []);
        unset($cart[$request->product_id]);
        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'subtotal' => $this->cartSubtotal(),
            'count' => $this->cartCount(),
        ]);
    }

    /**
     * Clear the cart.
     */
    public function clearCart()
    {
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }

    /**
     * Search products for POS.
     */
    public function searchProducts(Request $request)
    {
        $search = $request->search;

        $products = Product::where('status', 'available')
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('barcode', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    /**
     * Process checkout.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|in:cash,card,mobile,credit',
            'amount_paid' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty.'
            ], 422);
        }

        // Check stock availability
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->quantity < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for: {$item['name']}"
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $subtotal = $this->cartSubtotal();
            $discount = $request->discount ?? 0;
            $tax = 0; // You can adjust tax logic later
            $total = $subtotal - $discount + $tax;

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Create sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'amount_paid' => $request->amount_paid,
                'change_amount' => $request->amount_paid - $total,
                'notes' => $request->notes,
            ]);

            // Create sale items and deduct stock
            foreach ($cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => 0,
                    'total' => $item['total'],
                ]);

                // Deduct stock
                $product = Product::find($item['product_id']);
                $oldQuantity = $product->quantity;
                $product->quantity -= $item['quantity'];
                $product->save();

                // Record stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'sale',
                    'quantity_change' => -$item['quantity'],
                    'previous_quantity' => $oldQuantity,
                    'new_quantity' => $product->quantity,
                    'reference' => $invoiceNumber,
                    'notes' => "Sale via POS - {$invoiceNumber}",
                ]);
            }

            // Clear cart
            session()->forget('cart');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!',
                'invoice_number' => $invoiceNumber,
                'sale_id' => $sale->id,
                'redirect' => route('admin.sales.invoice', $sale->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product by barcode.
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::where('barcode', $request->barcode)
            ->orWhere('sku', $request->barcode)
            ->where('status', 'available')
            ->where('quantity', '>', 0)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or out of stock.'
            ], 404);
        }

        return response()->json($product);
    }

    // ==========================================
    // PRIVATE HELPERS
    // ==========================================

    /**
     * Calculate cart subtotal.
     */
    private function cartSubtotal()
    {
        $cart = session()->get('cart', []);
        return array_sum(array_column($cart, 'total'));
    }

    /**
     * Calculate total items in cart.
     */
    private function cartCount()
    {
        $cart = session()->get('cart', []);
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Generate invoice number.
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastSale = Sale::whereDate('created_at', today())->latest()->first();

        if ($lastSale) {
            $lastNumber = intval(substr($lastSale->invoice_number, -4));
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return $prefix . '-' . $date . '-' . $sequence;
    }
}