<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;

class OrderItemController extends Controller
{
    /**
     * Show single order item details with addons.
     */
    public function show(int $id): JsonResponse
    {
        $item = OrderItem::with(['addons', 'order'])->findOrFail($id);

        return response()->json([
            'message' => 'Order item details retrieved.',
            'item' => $item,
        ], 200);
    }
}
