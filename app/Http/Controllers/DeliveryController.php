<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{


public function acceptDelivery(Request $request)
{
    $validatedData = $request->validate([
        'order_id' => ['required', 'integer', 'exists:orders,id'],
    ]);

    $user = $request->user();

    if (! $user || $user->role !== 'rider') {
        return response()->json([
            'message' => 'Unauthorized',
        ], 403);
    }

    $rider = $user->rider;

    if (! $rider) {
        return response()->json([
            'message' => 'Rider profile not found.',
        ], 404);
    }

    if (! $rider->is_online || ! $rider->is_available) {
        return response()->json([
            'message' => 'You are not available for deliveries.',
        ], 422);
    }

    $accepted = DB::transaction(function () use ($validatedData, $rider) {

        $order = Order::lockForUpdate()
            ->findOrFail($validatedData['order_id']);
        if ($order->status !== 'ready') {
            return false;
        }

        $order->update([
            'status' => 'accepted',
            'rider_id' => $rider->id,
            'accepted_at' => now(),
        ]);

        // Rider cannot receive another new delivery.
        $rider->update([
            'is_available' => false,
        ]);

        return true;
    });

    if (! $accepted) {
        return response()->json([
            'message' => 'This delivery has already been accepted by another rider.',
        ], 409);
    }

    return response()->json([
        'message' => 'Delivery accepted successfully.',
    ], 200);
}


    public function rejectdelivery(Request $request)
    {
    $validatedData = $request->validate([
        'order_id' => ['required', 'integer', 'exists:orders,id'],
    ]);

    $user = $request->user();

    if (! $user || $user->role !== 'rider') {
        return response()->json([
            'message' => 'Unauthorized',
        ], 403);
    }

    $rider = $user->rider;

    if (! $rider) {
        return response()->json([
            'message' => 'Rider profile not found.',
        ], 404);
    }

    if (! $rider->is_online || ! $rider->is_available) {
        return response()->json([
            'message' => 'You are not available for deliveries.',
        ], 422);
    }
    return response()->json([
        'message' => 'Delivery offer rejected.',
    ]);
    }
}
