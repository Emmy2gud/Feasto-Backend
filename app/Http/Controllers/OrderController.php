<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\Api\PaymentGatewayService;
use DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
        public function __construct(
        protected PaymentGatewayService $paymentGatewayService
    ) {}
    public function index()
    {}
    public function show($id)
    {}
    public function placeOrder(StoreOrderRequest $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validated();
    //    check if idempotency key exists in the database and if it does return a message saying that the order has already been placed
        $existingOrder = Order::where('idempotency_key', $validatedData['idempotency_key'])->first();
        if ($existingOrder) {
            return response()->json(['message' => 'Order has already been placed'], 400);
        }


        //craete the order,order_item and order_item_addons in the database but status would be pending until the payment is successful
        $Order = DB::transaction(function () use ($validatedData) {
            $Order = Order::create([
                'order_number'       => $validatedData['order_number'],
                'customer_id'        => $validatedData['customer_id'],
                'vendor_id'          => $validatedData['vendor_id'],
                'rider_id'           => $validatedData['rider_id'] ?? null,
                'subtotal'           => $validatedData['subtotal'],
                'delivery_fee'       => $validatedData['delivery_fee'],
                'service_fee'        => $validatedData['service_fee'],
                'discount'           => $validatedData['discount'] ?? 0,
                'total'              => $validatedData['total'],
                'payment_method'     => $validatedData['payment_method'] ?? null,
                'delivery_status'    => $validatedData['delivery_status'] ?? null,
                'delivery_address'   => $validatedData['delivery_address'] ?? null,
                'delivery_latitude'  => $validatedData['delivery_latitude'] ?? null,
                'delivery_longitude' => $validatedData['delivery_longitude'] ?? null,
                'status'             => 'pending', // set the status to pending until the payment is successful
                'payment_status'     => 'pending', // set the payment status to pending until the payment is successful
                'idempotency_key'    => $validatedData['idempotency_key'],
                'notes'              => $validatedData['notes'] ?? null,
            ]);
            $Order_items = $Order->items()->createMany([
                [
                    'dish_id'  => $validatedData['dish_id'],
                    'quantity' => $validatedData['quantity'],
                    'price'    => $validatedData['price'],
                    'name'     => $validatedData['name'],
                ],
            ]);
            $Order_item_addons = $Order_items->first()->addons()->createMany([
                [
                    'addon_id' => $validatedData['addon_id'],
                    'name'     => $validatedData['addon_name'],
                    'price'    => $validatedData['addon_price'],
                ],
            ]);

            return response()->json(['message' => 'Order placed successfully', 'order' => $Order], 201);

        });
        //create reference for the order to be used for payment verification
        $Ref = 'ORD-' . strtoupper(uniqid());
        //submit the order to the payment gateway and get the response
        $response = $this->paymentGatewayService->initializePayment($Order, $Ref);
        //if the payment method is card encrypt the card details and send to kora pay endpoints
        //if the payment method is normal payment method hit the enpoints

        // return response()->json(['message' => 'Order placed successfully', 'order' => $order], 201);

    }
    public function update(Request $request, $id)
    {}
    public function destroy($id)
    {}
}
