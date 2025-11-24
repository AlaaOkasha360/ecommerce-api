<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\HttpResponses;
use App\Models\Cart;
use App\Models\Cart_item;
use App\Models\Product;
use Auth;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use HttpResponses;

    /**
     * Get user's cart
     */
    public function index()
    {
        $user = Auth::user();
        $cart = Cart::with(['cartItems.product'])->firstOrCreate(['user_id' => $user->id]);

        return $this->success([
            'cart_id' => $cart->id,
            'items' => $cart->cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_addition,
                    'subtotal' => $item->quantity * $item->price_at_addition
                ];
            }),
            'total' => $cart->cartItems->sum(function ($item) {
                return $item->quantity * $item->price_at_addition;
            })
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(AddToCartRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $product = Product::findOrFail($validated['product_id']);

        $cartItem = Cart_item::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $validated['quantity'];
            $cartItem->save();
        } else {
            Cart_item::create([
                'cart_id' => $cart->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'price_at_addition' => $product->price
            ]);
        }

        return $this->success([], 'Item added to cart successfully');
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(UpdateCartItemRequest $request, Cart_item $item)
    {
        $user = Auth::user();

        if ($item->cart->user->id !== $user->id) {
            return $this->error([], 'Unauthorized', 403);
        }

        $validated = $request->validated();
        $item->quantity = $validated['quantity'];
        $item->save();

        return $this->success([], 'Cart item updated successfully');
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Cart_item $item)
    {
        $user = Auth::user();

        if ($item->cart->user->id !== $user->id) {
            return $this->error([], 'Unauthorized', 403);
        }

        $item->delete();
        return $this->success([], 'Item removed from cart');
    }

    /**
     * Clear cart
     */
    public function clearCart()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $cart->cartItems()->delete();
        }

        return $this->success([], 'Cart cleared successfully');
    }

    /**
     * Get cart total
     */
    public function getTotal()
    {
        $user = Auth::user();
        $cart = Cart::with('cartItems')->where('user_id', $user->id)->first();

        if (!$cart) {
            return $this->success(['total' => 0]);
        }

        $total = $cart->cartItems->sum(function ($item) {
            return $item->quantity * $item->price_at_addition;
        });

        return $this->success(['total' => $total]);
    }
}
