<?php
namespace App\Services\Orders;

use App\Events\DeliveryOfferBroadcast;
use App\Models\Order;
use App\Models\Rider;
use App\Services\DistanceCalculatorService;

class ReadyOrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function readyOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'status' => 'ready',
        ]);
        // step1.Get all riders from the model
        $riders = Rider::query()
            ->where('approval_status', 'approved')
            ->where('is_online', true)
            ->where('is_available', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('location_updated_at', '>=', now()->subSeconds(120))
            ->get();

        //step2. filter the riders based on distance from the vendor
        $vendorLatitude  = $order->vendor->latitude;
        $vendorLongitude = $order->vendor->longitude;
        $nearbyRiders    = $riders->filter(function ($rider) use ($vendorLatitude, $vendorLongitude) {
            $distance = DistanceCalculatorService::kmBetween(
                $vendorLatitude,
                $vendorLongitude,
                $rider->latitude,
                $rider->longitude
            );

            return $distance <= 15;
        });

        if (!$nearbyRiders->isEmpty()) {
            foreach ($nearbyRiders as $rider) {
                event(new DeliveryOfferBroadcast(
                    $rider->id,
                    $order->id
                ));
            }
        } else {
            //step3. Broadcast the order to the closest riders
            $closedbyRider = $riders->sortBy(function ($rider) use ($vendorLatitude, $vendorLongitude) {
                return DistanceCalculatorService::kmBetween(
                    $vendorLatitude,
                    $vendorLongitude,
                    $rider->latitude,
                    $rider->longitude
                );
            })->first();
            foreach ($closedbyRider as $rider) {
                // Broadcast the order to the rider
                broadcast(new DeliveryOfferBroadcast($rider->id, $order->id));
            }
        }

    }
}
