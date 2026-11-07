<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductAddons;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Get user's cart items, optionally filtered by vendor.
     */
    public function getCart(User $user, ?int $vendorId = null): array
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $query = $cart->items()->with(['product', 'vendor', 'addons.addon']);

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        $items = $query->get();

        $subtotal = $items->sum(function ($item) {
            $addonsTotal = $item->addons->sum('price');
            $unitPrice = $item->product ? ($item->product->discount_price ?? $item->product->price) : 0;
            return ($unitPrice + $addonsTotal) * $item->quantity;
        });

        return [
            'cart_id' => $cart->id,
            'items' => $items,
            'total_items' => $items->sum('quantity'),
            'subtotal' => round($subtotal, 2),
        ];
    }

    /**
     * Add an item to the user's cart.
     */
    public function addItem(User $user, int $productId, int $quantity = 1, array $addonIds = []): array
    {
        $product = Product::with('addons')->findOrFail($productId);

        if (! $product->is_available || $product->status !== 'active') {
            throw ValidationException::withMessages([
                'product' => 'This product is currently unavailable.',
            ]);
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        return DB::transaction(function () use ($cart, $product, $quantity, $addonIds) {
            $sortedAddons = collect($addonIds)->filter()->sort()->values();

            // Look for an existing cart item with the same product and identical addons
            $existingItem = $cart->items()
                ->where('product_id', $product->id)
                ->with('addons')
                ->get()
                ->first(function ($item) use ($sortedAddons) {
                    $itemAddonIds = $item->addons->pluck('addon_id')->filter()->sort()->values();
                    return $itemAddonIds->toArray() === $sortedAddons->toArray();
                });

            if ($existingItem) {
                $existingItem->increment('quantity', $quantity);
                return [
                    'message' => 'Cart item quantity updated.',
                    'cart_item' => $existingItem->fresh(['product', 'addons']),
                ];
            }

            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'quantity' => $quantity,
            ]);

            foreach ($sortedAddons as $addonId) {
                $addon = ProductAddons::find($addonId);
                if ($addon) {
                    $cartItem->addons()->create([
                        'addon_id' => $addon->id,
                        'price' => $addon->price,
                    ]);
                }
            }

            return [
                'message' => 'Item added to cart successfully.',
                'cart_item' => $cartItem->fresh(['product', 'addons']),
            ];
        });
    }

    /**
     * Update cart item quantity.
     */
    public function updateQuantity(User $user, int $cartItemId, int $quantity): array
    {
        $cart = Cart::where('user_id', $user->id)->first();

        if (! $cart) {
            throw ValidationException::withMessages(['cart' => 'Cart not found.']);
        }

        $cartItem = $cart->items()->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $cartItem->addons()->delete();
            $cartItem->delete();
            return ['message' => 'Item removed from cart.'];
        }

        $cartItem->update(['quantity' => $quantity]);

        return [
            'message' => 'Cart item quantity updated.',
            'cart_item' => $cartItem->fresh(['product', 'addons']),
        ];
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(User $user, int $cartItemId): array
    {
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $cartItem = $cart->items()->find($cartItemId);
            if ($cartItem) {
                $cartItem->addons()->delete();
                $cartItem->delete();
            }
        }

        return ['message' => 'Item removed from cart successfully.'];
    }

    /**
     * Clear all items or vendor items in cart.
     */
    public function clearCart(User $user, ?int $vendorId = null): array
    {
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $query = $cart->items();
            if ($vendorId) {
                $query->where('vendor_id', $vendorId);
            }
            $items = $query->get();
            foreach ($items as $item) {
                $item->addons()->delete();
                $item->delete();
            }
        }

        return ['message' => 'Cart cleared successfully.'];
    }
}

