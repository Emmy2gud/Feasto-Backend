<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartItemAddons;
use App\Models\Product;
use App\Models\ProductAddons;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
public function addToCart(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'addon_ids'  => 'array',
        'addon_ids.*' => 'exists:addons,id',
        'vendor_id' => 'required|exists:vendors,id',
    ]);

    $product = Product::findOrFail($validated['product_id'])->where('vendor_id', $validated['vendor_id'])->firstOrFail();
    $chosenAddonIds = collect($validated['addon_ids'] ?? [])->sort()->values();

    $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

    $existingItem = $cart->items()
        ->where('product_id', $product->id)
        ->get()
        ->first(function ($item) use ($chosenAddonIds) {
            $itemAddonIds = $item->addons->pluck('addon_id')->sort()->values();
            return $itemAddonIds->toArray() === $chosenAddonIds->toArray();
            //  return $itemAddonIds->equalTo($chosenAddonIds);
        });

    if ($existingItem) {
        $existingItem->increment('quantity');
        return response()->json(['message' => 'Quantity updated']);
    }

    // no match found — create a fresh cart item
    $cartItem = $cart->items()->create([
        'product_id' => $product->id,
        'name'       => $product->name,
        'price'      => $product->price,
        'quantity'   => 1,
    ]);

    foreach ($chosenAddonIds as $addonId) {
        $addon = ProductAddons::findOrFail($addonId);
        $cartItem->addons()->create([
            'addon_id' => $addon->id,
            'name'     => $addon->name,
            'price'    => $addon->price,
        ]);
    }

    return response()->json(['message' => 'Product added to cart successfully.']);
}
    public function showCart()
    {
        if (Auth::check()) {
            //get all cart items for the authenticated user for a particular vendor  because each vendor has a cart item for their products
            $cart            = Cart::where('user_id', Auth::id())->first();
            $vendorId        = request()->query('vendor_id');
            $vendorCartItems = $cart->items()->where('vendor_id',  $vendorId  )->get();

            return response()->json($vendorCartItems);
        }

        $cart = session()->get('cart', []);
        return response()->json($cart);
    }

    public function removeFromCart($productId)
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cartItem = $cart->items()->where('product_id', $productId)->first();
                if ($cartItem) {
                    CartItem::where('id', $cartItem->id)->delete();
                }
            }
        }

        return response()->json(['message' => 'Product removed from cart successfully.']);
    }




    public function clearCart()
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cart->items()->delete();
            }
        }

        return response()->json(['message' => 'Cart cleared successfully.']);
    }
}
