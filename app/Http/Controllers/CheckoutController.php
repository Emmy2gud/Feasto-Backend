<?php
namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Vendor;
use App\Services\DistanceCalculatorService;
use Illuminate\Http\Request;

class CheckoutController
{
    public function summary(Request $request, Vendor $vendor)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cartItems = CartItem::where('user_id', $request->user()->id)
            ->where('vendor_id', $vendor->id)
            ->with(['product', 'addons']) // eager load so no extra queries per item ,gets the product and addons for each cart item
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 404);
        }

        $subtotal = $cartItems->sum(function ($item) {
            $addonsTotal = $item->addons->sum('price');
            return ($item->product->price + $addonsTotal) * $item->quantity;
        });

        $distanceKm  = null;
        $deliveryFee = null;
        if ($request->has(['latitude', 'longitude'])) {
            $distanceKm = DistanceCalculatorService::kmBetween(
                $vendor->latitude, $vendor->longitude,
                $request->latitude, $request->longitude
            );
            $deliveryFee = round(
                config('delivery.base_fee') + ($distanceKm * config('delivery.rate_per_km')), 2
            );
        }

        return response()->json([
            'cart_items'   => $cartItems,
            'subtotal'     => round($subtotal, 2),
            'distance_km'  => $distanceKm,
            'delivery_fee' => $deliveryFee,
        ]);
    }


}
