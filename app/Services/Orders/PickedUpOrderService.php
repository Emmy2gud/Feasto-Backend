<?php

namespace App\Services\Orders;

use App\Models\Order;

class PickedUpOrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function pickedUpOrder($orderId): Order
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'status' => 'picked_up',
        ]);

        return $order;
    }
}
