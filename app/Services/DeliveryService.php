<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Rider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryService
{
    /**
     * Get orders that are ready or awaiting a delivery rider.
     */
    public function getAvailableDeliveries(?float $lat = null, ?float $lon = null): Collection
    {
        return Order::with(['vendor', 'items.addons'])
            ->whereIn('status', ['ready', 'accepted'])
            ->whereNull('rider_id')
            ->latest()
            ->limit(20)
            ->get();
    }

    /**
     * Get the rider's current active delivery in progress.
     */
    public function getActiveDelivery(Rider $rider): ?Order
    {
        return Order::with(['vendor', 'customer', 'items.addons'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['accepted', 'preparing', 'ready', 'picked_up'])
            ->first();
    }

    /**
     * Rider accepts an order delivery.
     */
    public function acceptDelivery(Rider $rider, int $orderId): Order
    {
        if (! $rider->is_online) {
            throw ValidationException::withMessages([
                'rider' => 'You must go online first before accepting deliveries.',
            ]);
        }

        return DB::transaction(function () use ($rider, $orderId) {
            $order = Order::lockForUpdate()->findOrFail($orderId);

            if ($order->rider_id && $order->rider_id !== $rider->id) {
                throw ValidationException::withMessages([
                    'order' => 'This delivery has already been accepted by another rider.',
                ]);
            }

            if (! in_array($order->status, ['accepted', 'preparing', 'ready'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'This order is not available for delivery.',
                ]);
            }

            $order->update([
                'rider_id' => $rider->id,
                'rider_assigned_at' => now(),
            ]);

            $rider->update(['is_available' => false]);

            return $order->fresh(['vendor', 'customer', 'items.addons']);
        });
    }

    /**
     * Rider picks up the order from the vendor.
     */
    public function pickupDelivery(Rider $rider, int $orderId): Order
    {
        $order = Order::where('id', $orderId)
            ->where('rider_id', $rider->id)
            ->firstOrFail();

        $order->update([
            'status' => 'picked_up',
        ]);

        return $order->fresh(['vendor', 'customer', 'items.addons']);
    }

    /**
     * Rider marks the delivery as completed/delivered.
     * Credits the rider's wallet with the delivery fee.
     */
    public function completeDelivery(Rider $rider, int $orderId): Order
    {
        return DB::transaction(function () use ($rider, $orderId) {
            $order = Order::lockForUpdate()
                ->where('id', $orderId)
                ->where('rider_id', $rider->id)
                ->firstOrFail();

            $order->update([
                'status' => 'delivered',
                'completed_at' => now(),
            ]);

            // Rider becomes available again
            $rider->update(['is_available' => true]);

            // Credit delivery earnings to rider's wallet
            $earnings = $order->delivery_fee > 0 ? (float) $order->delivery_fee : 500.00;
            $wallet = $rider->user->wallet()->firstOrCreate([], [
                'balance' => 0.00,
                'currency' => 'NGN',
                'status' => 'active',
            ]);

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore + $earnings;

            $wallet->update(['balance' => $balanceAfter]);

            $wallet->ledger()->create([
                'transaction_type' => 'delivery_earning',
                'direction' => 'credit',
                'amount' => $earnings,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'description' => 'Earnings for delivery of order #' . $order->order_number,
                'status' => 'successful',
            ]);

            return $order->fresh(['vendor', 'customer', 'items.addons']);
        });
    }

    /**
     * Get past delivery history for the rider.
     */
    public function getDeliveryHistory(Rider $rider, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['vendor', 'customer', 'items'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['delivered', 'cancelled'])
            ->latest('completed_at')
            ->paginate($perPage);
    }

    /**
     * Get earnings and stats summary for the rider dashboard.
     */
    public function getEarningsSummary(Rider $rider): array
    {
        $wallet = $rider->user->wallet;
        $totalDeliveries = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->count();

        $todayDeliveries = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('completed_at', today())
            ->count();

        $todayEarnings = Order::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereDate('completed_at', today())
            ->sum('delivery_fee');

        return [
            'wallet_balance' => (float) ($wallet?->balance ?? 0.00),
            'currency' => $wallet?->currency ?? 'NGN',
            'total_completed_deliveries' => $totalDeliveries,
            'today_completed_deliveries' => $todayDeliveries,
            'today_earnings' => (float) $todayEarnings,
            'is_online' => (bool) $rider->is_online,
            'is_available' => (bool) $rider->is_available,
        ];
    }
}

