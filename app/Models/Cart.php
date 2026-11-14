<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class Cart
{
    public static function get()
    {
        return Session::get('cart', []);
    }

    public static function add($productId, $quantity = 1)
    {
        $cart = self::get();
        $product = Product::findOrFail($productId);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $quantity,
                'unit_price' => $product->selling_price,
                'total' => $product->selling_price * $quantity,
            ];
        }

        $cart[$productId]['total'] = $cart[$productId]['unit_price'] * $cart[$productId]['quantity'];
        Session::put('cart', $cart);
        return self::get();
    }

    public static function update($productId, $quantity)
    {
        $cart = self::get();

        if (isset($cart[$productId])) {
            if ($quantity <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $quantity;
                $cart[$productId]['total'] = $cart[$productId]['unit_price'] * $quantity;
            }
        }

        Session::put('cart', $cart);
        return self::get();
    }

    public static function remove($productId)
    {
        $cart = self::get();
        unset($cart[$productId]);
        Session::put('cart', $cart);
        return self::get();
    }

    public static function clear()
    {
        Session::forget('cart');
        return [];
    }

    public static function count()
    {
        $cart = self::get();
        return array_sum(array_column($cart, 'quantity'));
    }

    public static function subtotal()
    {
        $cart = self::get();
        return array_sum(array_column($cart, 'total'));
    }

    public static function isEmpty()
    {
        return empty(self::get());
    }

    public static function getProductIds()
    {
        return array_keys(self::get());
    }
}