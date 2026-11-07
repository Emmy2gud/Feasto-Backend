<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display a listing of orders for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->vendor) {
            $orders = $this->orderService->getVendorOrders($user->vendor);
        } else {
            $orders = $this->orderService->getUserOrders($user);
        }

        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'orders' => $orders,
        ], 200);
    }

    /**
     * Display single order details.
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getById($id);

        return response()->json([
            'message' => 'Order retrieved successfully.',
            'order' => $order,
        ], 200);
    }

    /**
     * Get orders placed by current user.
     */
    public function showUserOrders(Request $request): JsonResponse
    {
        $orders = $this->orderService->getUserOrders($request->user());

        return response()->json([
            'message' => 'User orders retrieved successfully.',
            'data' => $orders,
        ], 200);
    }

    /**
     * Get orders for the authenticated vendor.
     */
    public function showVendorOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->vendor) {
            return response()->json(['message' => 'Vendor profile not found.'], 403);
        }

        $orders = $this->orderService->getVendorOrders($user->vendor);

        return response()->json([
            'message' => 'Vendor orders retrieved successfully.',
            'data' => $orders,
        ], 200);
    }

    /**
     * Get all platform orders (admin).
     */
    public function showAllOrders(Request $request): JsonResponse
    {
        $orders = $this->orderService->getAllOrders();

        return response()->json([
            'message' => 'All orders retrieved successfully.',
            'data' => $orders,
        ], 200);
    }

    /**
     * Customer places an order.
     */
    public function placeOrder(StoreOrderRequest $request): JsonResponse
    {
        $result = $this->orderService->createOrder(
            $request->user(),
            $request->validated()
        );

        return response()->json($result, 201);
    }

    /**
     * Vendor accepts an incoming order.
     */
    public function acceptOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->vendor) {
            return response()->json(['message' => 'Unauthorized. Must be a vendor.'], 403);
        }

        $order = $this->orderService->updateVendorOrderStatus($id, $user->vendor, 'accepted');

        return response()->json([
            'message' => 'Order accepted successfully.',
            'data' => $order,
        ], 200);
    }

    /**
     * Vendor starts preparing an order.
     */
    public function prepareOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->vendor) {
            return response()->json(['message' => 'Unauthorized. Must be a vendor.'], 403);
        }

        $order = $this->orderService->updateVendorOrderStatus($id, $user->vendor, 'preparing');

        return response()->json([
            'message' => 'Order is now being prepared.',
            'data' => $order,
        ], 200);
    }

    /**
     * Vendor marks order as ready for pickup.
     */
    public function readyOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->vendor) {
            return response()->json(['message' => 'Unauthorized. Must be a vendor.'], 403);
        }

        $order = $this->orderService->updateVendorOrderStatus($id, $user->vendor, 'ready');

        return response()->json([
            'message' => 'Order is ready for pickup.',
            'data' => $order,
        ], 200);
    }

    /**
     * Vendor or customer cancels order.
     */
    public function cancelOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user->vendor) {
            $order = $this->orderService->updateVendorOrderStatus($id, $user->vendor, 'cancelled');
        } else {
            $order = Order::where('id', $id)->where('customer_id', $user->id)->firstOrFail();
            $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => $order,
        ], 200);
    }
}
