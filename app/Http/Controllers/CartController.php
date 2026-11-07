<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the authenticated user's cart.
     */
    public function showCart(Request $request): JsonResponse
    {
        $vendorId = $request->query('vendor_id');
        $cart = $this->cartService->getCart($request->user(), $vendorId ? (int) $vendorId : null);

        return response()->json($cart, 200);
    }

    /**
     * Add a product to the cart.
     */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'exists:product_addons,id'],
        ]);

        $result = $this->cartService->addItem(
            $request->user(),
            (int) $validated['product_id'],
            (int) ($validated['quantity'] ?? 1),
            $validated['addon_ids'] ?? []
        );

        return response()->json($result, 200);
    }

    /**
     * Add item via route parameter (for backward compatibility).
     */
    public function addToCart(Request $request, int $productId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'exists:product_addons,id'],
        ]);

        $result = $this->cartService->addItem(
            $request->user(),
            $productId,
            (int) ($validated['quantity'] ?? 1),
            $validated['addon_ids'] ?? []
        );

        return response()->json($result, 200);
    }

    /**
     * Update item quantity in the cart.
     */
    public function updateQuantity(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $result = $this->cartService->updateQuantity(
            $request->user(),
            $id,
            (int) $validated['quantity']
        );

        return response()->json($result, 200);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(Request $request, int $id): JsonResponse
    {
        $result = $this->cartService->removeItem($request->user(), $id);

        return response()->json($result, 200);
    }

    /**
     * Clear all cart items.
     */
    public function clearCart(Request $request): JsonResponse
    {
        $vendorId = $request->query('vendor_id');
        $result = $this->cartService->clearCart($request->user(), $vendorId ? (int) $vendorId : null);

        return response()->json($result, 200);
    }
}
