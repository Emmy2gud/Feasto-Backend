<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class PrepareOrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function prepareOrder(int $orderId): Order
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'accepted') {
            throw ValidationException::withMessages([
                'status' => 'Only accepted orders can be marked as preparing.',
            ]);
        }

        $order->update([
            'status' => 'preparing',
        ]);

        return $order;
    }
}
