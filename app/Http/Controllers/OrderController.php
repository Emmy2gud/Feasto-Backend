<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\Orders\AcceptOrderService;
use App\Services\Orders\CancelOrderService;
use App\Services\Orders\DeliveredOrderService;
use App\Services\Orders\PickedUpOrderService;
use App\Services\Orders\PrepareOrderService;
use App\Services\Orders\ReadyOrderService;
use App\Services\OrderService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class OrderController extends Controller
{
    public function __construct(
        protected DeliveredOrderService $deliveredOrderService,
        protected CancelOrderService $cancelOrderService,
        protected AcceptOrderService $acceptOrderService,
        protected PrepareOrderService $prepareOrderService,
        protected ReadyOrderService $readyOrderService,
        protected PickedUpOrderService $pickedUpOrderService,
        protected OrderService $orderService
    ) {}

    public function index() {}

    public function show($id) {}

    public function showUserOrders(Request $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = $request->user();
        // json decode the redis cache and return it if it exists, else fetch from db and store in redis cache
        $orderlist = Redis::get('user_orders_'.$user->id)->json_decode();
        if ($orderlist == '') {
            $orders = $user->orders()->with('items.addons')->paginate(10);
            Redis::set('user_orders_'.$user->id, json_encode($orders));
        }

        return response()->json(['message' => 'User orders retrieved successfully', 'data' => $orders], 200);
    }

    public function showVendorOrders(Request $request)
    {
        $user = $request->user();
        if (! $request->user() && $user->role !== 'vendor') {
            return response()->json(['message' => 'Unauthorized '], 401);
        }

        $orders = $user->vendorOrders()->with('items.addons')->paginate(10);

        return response()->json(['message' => 'Vendor orders retrieved successfully', 'data' => $orders], 200);
    }

    public function showAllOrders(Request $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $orders = Order::with('items.addons')->paginate(10);

        return response()->json(['message' => 'All orders retrieved successfully', 'data' => $orders], 200);
    }

    public function placeOrder(StoreOrderRequest $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validated();

        $response = $this->orderService->createorder($validatedData);

        return response()->json(['message' => 'Order placed successfully', 'data' => $response], 201);

    }

    public function update(Request $request, $id) {}

    // controllers for actions like accept, prepare, ready, picked_up, delivered, cancelled
    public function acceptOrder(Request $request, $id)
    {
        $user = $request->user();
        if (! $request->user() && $user->role !== 'vendor') {
            return response()->json(['message' => 'Unauthorized '], 401);
        }

        $response = $this->acceptOrderService->acceptOrder($id);

        return response()->json(['message' => 'Order accepted successfully', 'data' => $response], 200);

    }

    public function cancelOrder(Request $request, $id)
    {
        $user = $request->user();
        if (! $request->user() && $user->role !== 'vendor') {
            return response()->json(['message' => 'Unauthorized '], 401);
        }

        $response = $this->cancelOrderService->cancelOrder($id);

        return response()->json(['message' => 'Order accepted successfully', 'data' => $response], 200);
    }

    public function prepareOrder(Request $request, $id)
    {
        $user = $request->user();
        if (! $request->user() && $user->role !== 'vendor') {
            return response()->json(['message' => 'Unauthorized '], 401);
        }
        $response = $this->prepareOrderService->prepareOrder($id);

        return response()->json(['message' => 'Preparing Order. ', 'data' => $response], 200);
    }

    public function readyOrder(Request $request, $id)
    {
        $user = $request->user();
        if (! $request->user() && $user->role !== 'vendor') {
            return response()->json(['message' => 'Unauthorized '], 401);
        }

        $this->readyOrderService->readyOrder($id);

        return response()->json(['message' => 'Order is ready for pickup. '], 200);
    }

    // public function pickedUpOrder(Request $request, $id)
    // {
    //     $user = $request->user();
    //     if (! $request->user() && $user->role !== 'rider') {
    //         return response()->json(['message' => 'Unauthorized '], 401);
    //     }
    //     $response = $this->pickedUpOrderService->pickedUpOrder($id);

    //     return response()->json(['message' => 'Order has been picked up. ', 'data' => $response], 200);
    // }

    public function deliveredOrder(Request $request, $id)
    {
        $user = $request->user();
        if (! $request->user() && $user->role !== 'rider') {
            return response()->json(['message' => 'Unauthorized '], 401);
        }
        $response = $this->deliveredOrderService->deliveredOrder($id);

        return response()->json(['message' => 'Order has been delivered. ', 'data' => $response], 200);
    }



    public function destroy($id) {}
}
