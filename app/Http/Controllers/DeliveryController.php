<?php

namespace App\Http\Controllers;

use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        protected DeliveryService $deliveryService
    ) {}

    /**
     * Helper to retrieve and authorize current authenticated rider.
     */
    protected function getAuthenticatedRider(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->rider) {
            abort(response()->json(['message' => 'Rider profile not found for this account.'], 403));
        }

        return $user->rider;
    }

    /**
     * Get available orders waiting for rider pickup.
     */
    public function available(Request $request): JsonResponse
    {
        $rider = $this->getAuthenticatedRider($request);
        $deliveries = $this->deliveryService->getAvailableDeliveries(
            $rider->latitude ? (float) $rider->latitude : null,
            $rider->longitude ? (float) $rider->longitude : null
        );

        return response()->json([
            'message' => 'Available deliveries retrieved.',
            'deliveries' => $deliveries,
        ], 200);
    }

    /**
     * Get rider's current active delivery.
     */
    public function active(Request $request): JsonResponse
    {
        $rider = $this->getAuthenticatedRider($request);
        $delivery = $this->deliveryService->getActiveDelivery($rider);

        return response()->json([
            'message' => 'Active delivery retrieved.',
            'delivery' => $delivery,
        ], 200);
    }

    /**
     * Accept a delivery order.
     */
    public function acceptDelivery(Request $request): JsonResponse
    {
        $rider = $this->getAuthenticatedRider($request);

        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = $this->deliveryService->acceptDelivery($rider, (int) $validated['order_id']);

        return response()->json([
            'message' => 'Delivery accepted successfully.',
            'data' => $order,
        ], 200);
    }

    /**
     * Rider picks up food from restaurant.
     */
    public function pickup(Request $request, int $orderId): JsonResponse
    {
        $rider = $this->getAuthenticatedRider($request);
        $order = $this->deliveryService->pickupDelivery($rider, $orderId);

        return response()->json([
            'message' => 'Order marked as picked up.',
            'data' => $order,
        ], 200);
    }

    /**
     * Rider delivers order to customer.
     */
    public function complete(Request $request, int $orderId): JsonResponse
    {
        $rider = $this->getAuthenticatedRider($request);
        $order = $this->deliveryService->completeDelivery($rider, $orderId);

        return response()->json([
            'message' => 'Delivery completed successfully.',
            'data' => $order,
        ], 200);
    }

    /**
     * Get rider past delivery history.
     */
    public function history(Request $request): JsonResponse
    {
        $rider = $this->getAuthenticatedRider($request);
        $history = $this->deliveryService->getDeliveryHistory($rider);

        return response()->json([
            'message' => 'Delivery history retrieved.',
            'data' => $history,
        ], 200);
    }

    /**
     * Get rider earnings and stats dashboard.
     */
    public function earnings(Request $request): JsonResponse
    {
        $rider = $this->getAuthenticatedRider($request);
        $summary = $this->deliveryService->getEarningsSummary($rider);

        return response()->json([
            'message' => 'Earnings summary retrieved.',
            'data' => $summary,
        ], 200);
    }

    /**
     * Decline / reject a delivery offer.
     */
    public function rejectDelivery(Request $request): JsonResponse
    {
        $this->getAuthenticatedRider($request);

        return response()->json([
            'message' => 'Delivery offer rejected.',
        ], 200);
    }
}
