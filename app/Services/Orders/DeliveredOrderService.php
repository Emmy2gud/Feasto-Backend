<?php

namespace App\Services\Orders;

use App\Models\Order;

class DeliveredOrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function deliveredOrder($orderId): Order
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'status' => 'delivered',
        ]);

        return $order;
    }
}
