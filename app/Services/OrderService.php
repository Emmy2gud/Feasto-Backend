<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Services\PaymentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Get paginated orders for a customer.
     */
    public function getUserOrders(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Order::with(['items.addons', 'vendor', 'rider'])
            ->where('customer_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get paginated orders for a vendor.
     */
    public function getVendorOrders(Vendor $vendor, int $perPage = 10): LengthAwarePaginator
    {
        return Order::with(['items.addons', 'customer', 'rider'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all orders (for admin/monitoring).
     */
    public function getAllOrders(int $perPage = 10): LengthAwarePaginator
    {
        return Order::with(['items.addons', 'customer', 'vendor', 'rider'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get single order by ID with relationships.
     */
    public function getById(int $id): Order
    {
        return Order::with(['items.addons', 'customer', 'vendor', 'rider'])->findOrFail($id);
    }

    /**
     * Create a new order with items, addons, and initialize payment.
     */
    public function createOrder(User $customer, array $data): array
    {
        // Check idempotency key if supplied
        if (! empty($data['idempotency_key'])) {
            $existing = Order::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return [
                    'message' => 'Order already exists with this idempotency key.',
                    'order' => $existing->load('items.addons'),
                ];
            }
        }

        $orderNumber = $data['order_number'] ?? ('ORD-' . strtoupper(uniqid()));

        $order = DB::transaction(function () use ($customer, $data, $orderNumber) {
            $order = Order::create([
                'order_number' => $orderNumber,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'customer_id' => $customer->id,
                'vendor_id' => $data['vendor_id'],
                'subtotal' => $data['subtotal'],
                'delivery_fee' => $data['delivery_fee'] ?? 0,
                'service_fee' => $data['service_fee'] ?? 0,
                'tip' => $data['tip'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'total' => $data['total'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'card',
                'delivery_address' => $data['delivery_address'] ?? 'Customer Address',
                'delivery_latitude' => $data['delivery_latitude'] ?? 0.0,
                'delivery_longitude' => $data['delivery_longitude'] ?? 0.0,
                'distance_km' => $data['distance_km'] ?? null,
                'dropoff_preference' => $data['dropoff_preference'] ?? null,
                'recipient_phone' => $data['recipient_phone'] ?? $customer->phone_number,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            // Save order items if passed as an array
            $items = $data['items'] ?? [];
            if (empty($items) && isset($data['dish_id'])) {
                $items = [[
                    'dish_id' => $data['dish_id'],
                    'name' => $data['name'] ?? 'Dish Item',
                    'price' => $data['price'] ?? $data['subtotal'],
                    'quantity' => $data['quantity'] ?? 1,
                    'addons' => isset($data['addon_name']) ? [[
                        'addon_id' => $data['addon_id'] ?? null,
                        'name' => $data['addon_name'],
                        'price' => $data['addon_price'] ?? 0,
                    ]] : [],
                ]];
            }

            foreach ($items as $itemData) {
                $item = $order->items()->create([
                    'dish_id' => $itemData['dish_id'] ?? null,
                    'name' => $itemData['name'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'] ?? 1,
                    'special_instructions' => $itemData['special_instructions'] ?? null,
                ]);

                if (! empty($itemData['addons'])) {
                    foreach ($itemData['addons'] as $addon) {
                        $item->addons()->create([
                            'addon_id' => $addon['addon_id'] ?? null,
                            'name' => $addon['name'],
                            'price' => $addon['price'] ?? 0,
                        ]);
                    }
                }
            }

            return $order->fresh(['items.addons', 'vendor']);
        });

        // Initialize payment
        $paymentData = $this->paymentService->initiatePayment($order, $data['payment_method'] ?? 'card');

        return [
            'message' => 'Order placed successfully.',
            'order' => $order,
            'payment' => $paymentData,
        ];
    }

    /**
     * Vendor transitions order status.
     */
    public function updateVendorOrderStatus(int $orderId, Vendor $vendor, string $newStatus): Order
    {
        $order = Order::where('id', $orderId)
            ->where('vendor_id', $vendor->id)
            ->firstOrFail();

        $allowedStatuses = ['accepted', 'preparing', 'ready', 'cancelled'];
        if (! in_array($newStatus, $allowedStatuses, true)) {
            throw ValidationException::withMessages(['status' => 'Invalid status transition.']);
        }

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'accepted') {
            $updateData['accepted_at'] = now();
        } elseif ($newStatus === 'cancelled') {
            $updateData['cancelled_at'] = now();
        }

        $order->update($updateData);

        return $order->fresh(['items.addons', 'customer']);
    }
}
