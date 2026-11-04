<?php

namespace App\Services\Orders;

use App\Models\Order;

class AcceptOrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function acceptOrder($orderId): Order
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'status' => 'accepted',
        ]);

        return $order;
    }
}
