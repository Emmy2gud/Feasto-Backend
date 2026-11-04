<?php

namespace App\Services\Orders;

use App\Models\Order;

class CancelOrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function cancelOrder($orderId): Order
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'status' => 'cancelled',
        ]);

        return $order;
    }
}
