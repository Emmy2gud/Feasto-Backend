<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\CartItem;
use App\Models\Vendor;
use App\Services\DistanceCalculatorService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected ?OrderService $orderService = null
    ) {}

    /**
     * Get checkout summary for a vendor including distance and delivery fee calculation.
     */
    public function summary(Request $request, Vendor $vendor): JsonResponse
    {
        $user = $request->user();

        $cartItems = CartItem::where('user_id', $user->id)
            ->where('vendor_id', $vendor->id)
            ->with(['product', 'addons'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty for this vendor.'], 404);
        }

        $subtotal = $cartItems->sum(function ($item) {
            $addonsTotal = $item->addons->sum('price');
            $price = $item->product ? ($item->product->discount_price ?? $item->product->price) : 0;
            return ($price + $addonsTotal) * $item->quantity;
        });

        $distanceKm = null;
        $deliveryFee = (float) config('delivery.base_fee', 500);

        if ($request->has(['latitude', 'longitude']) && $vendor->latitude && $vendor->longitude) {
            $distanceKm = DistanceCalculatorService::kmBetween(
                (float) $vendor->latitude,
                (float) $vendor->longitude,
                (float) $request->latitude,
                (float) $request->longitude
            );

            $ratePerKm = (float) config('delivery.rate_per_km', 100);
            $deliveryFee = round($deliveryFee + ($distanceKm * $ratePerKm), 2);
        }

        $serviceFee = round($subtotal * 0.05, 2); // 5% service fee
        $total = round($subtotal + $deliveryFee + $serviceFee, 2);

        return response()->json([
            'vendor' => $vendor->only(['id', 'business_name', 'business_address']),
            'cart_items' => $cartItems,
            'subtotal' => round($subtotal, 2),
            'distance_km' => $distanceKm ? round($distanceKm, 2) : null,
            'delivery_fee' => $deliveryFee,
            'service_fee' => $serviceFee,
            'total' => $total,
        ], 200);
    }

    /**
     * Place order from checkout.
     */
    public function placeOrder(StoreOrderRequest $request): JsonResponse
    {
        if (! $this->orderService) {
            $this->orderService = app(OrderService::class);
        }

        $result = $this->orderService->createOrder(
            $request->user(),
            $request->validated()
        );

        return response()->json($result, 201);
    }
}
