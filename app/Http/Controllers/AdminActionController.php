<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Rider;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\VendorApprovedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AdminActionController extends Controller
{
    /**
     * Get platform overview metrics for admin.
     */
    public function dashboardStats(Request $request): JsonResponse
    {
        return response()->json([
            'total_users' => User::count(),
            'total_vendors' => Vendor::count(),
            'total_riders' => Rider::count(),
            'total_orders' => Order::count(),
            'pending_vendor_approvals' => Vendor::where('verification_status', 'pending')->count(),
            'pending_rider_approvals' => Rider::where('approval_status', 'pending')->count(),
        ], 200);
    }

    /**
     * Approve a vendor profile.
     */
    public function approveVendor(Request $request, int $id): JsonResponse
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'verification_status' => 'approved',
            'status' => 'active',
        ]);

        Notification::send($vendor->user, new VendorApprovedNotification($vendor));

        return response()->json([
            'message' => 'Vendor approved successfully.',
            'vendor' => $vendor,
        ], 200);
    }

    /**
     * Approve a rider profile.
     */
    public function approveRider(Request $request, int $id): JsonResponse
    {
        $rider = Rider::findOrFail($id);
        $rider->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'is_available' => true,
        ]);

        return response()->json([
            'message' => 'Rider approved successfully.',
            'rider' => $rider,
        ], 200);
    }

    /**
     * Reject a rider profile.
     */
    public function rejectRider(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $rider = Rider::findOrFail($id);
        $rider->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Rider application rejected.',
            'rider' => $rider,
        ], 200);
    }
}
