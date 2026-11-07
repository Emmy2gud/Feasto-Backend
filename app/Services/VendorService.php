<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Notifications\VendorApprovedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VendorService
{
    /**
     * Retrieve all vendors with business hours.
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator|Collection
    {
        return Vendor::with('businessHours')
            ->where('status', 'active')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get vendor by ID.
     */
    public function getById(int $id): Vendor
    {
        return Vendor::with(['businessHours', 'products.addons'])->findOrFail($id);
    }

    /**
     * Create a new vendor with wallet and initial business hours.
     */
    public function createVendor(User $user, array $data, ?UploadedFile $profilePic = null, ?UploadedFile $businessDoc = null): Vendor
    {
        if ($user->vendor()->exists()) {
            throw ValidationException::withMessages([
                'vendor' => 'You already have a vendor profile. Use the update endpoint instead.',
            ]);
        }

        $vendorData = [
            'business_name' => $data['business_name'],
            'business_description' => $data['business_description'] ?? null,
            'business_phone' => $data['business_phone'] ?? null,
            'business_email' => $data['business_email'] ?? null,
            'business_address' => $data['business_address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_accepting_orders' => $data['is_accepting_orders'] ?? true,
            'status' => $data['status'] ?? 'active',
            'verification_status' => $data['verification_status'] ?? 'pending',
        ];

        if ($profilePic) {
            $vendorData['profile_picture'] = $profilePic->store('vendors/profile-pictures', 'public');
        }

        if ($businessDoc) {
            $vendorData['business_document'] = $businessDoc->store('vendors/documents', 'public');
        }

        return DB::transaction(function () use ($user, $vendorData, $data) {
            $user->update(['role' => 'vendor']);

            $vendor = $user->vendor()->create($vendorData);

            $user->wallet()->firstOrCreate([], [
                'balance' => 0.00,
                'currency' => 'NGN',
                'status' => 'active',
            ]);

            $vendor->businessHours()->create([
                'day_of_week' => $data['day_of_week'] ?? 'monday',
                'open_time' => $data['open_time'] ?? $data['opening_time'] ?? '09:00',
                'close_time' => $data['close_time'] ?? $data['closing_time'] ?? '17:00',
                'is_closed' => $data['is_closed'] ?? false,
            ]);

            return $vendor->fresh(['businessHours']);
        });
    }

    /**
     * Update an existing vendor.
     */
    public function updateVendor(Vendor $vendor, array $data, ?UploadedFile $profilePic = null, ?UploadedFile $businessDoc = null): Vendor
    {
        $vendorData = [
            'business_name' => $data['business_name'] ?? $vendor->business_name,
            'business_description' => $data['business_description'] ?? $vendor->business_description,
            'business_phone' => $data['business_phone'] ?? $vendor->business_phone,
            'business_email' => $data['business_email'] ?? $vendor->business_email,
            'business_address' => $data['business_address'] ?? $vendor->business_address,
            'city' => $data['city'] ?? $vendor->city,
            'state' => $data['state'] ?? $vendor->state,
            'country' => $data['country'] ?? $vendor->country,
            'latitude' => $data['latitude'] ?? $vendor->latitude,
            'longitude' => $data['longitude'] ?? $vendor->longitude,
            'is_accepting_orders' => $data['is_accepting_orders'] ?? $vendor->is_accepting_orders,
            'status' => $data['status'] ?? $vendor->status,
            'verification_status' => $data['verification_status'] ?? $vendor->verification_status,
        ];

        if ($profilePic) {
            if ($vendor->profile_picture && Storage::disk('public')->exists($vendor->profile_picture)) {
                Storage::disk('public')->delete($vendor->profile_picture);
            }
            $vendorData['profile_picture'] = $profilePic->store('vendors/profile-pictures', 'public');
        }

        if ($businessDoc) {
            if ($vendor->business_document && Storage::disk('public')->exists($vendor->business_document)) {
                Storage::disk('public')->delete($vendor->business_document);
            }
            $vendorData['business_document'] = $businessDoc->store('vendors/documents', 'public');
        }

        $vendor->update($vendorData);

        // Update or create business hours if provided
        if (isset($data['day_of_week']) || isset($data['open_time']) || isset($data['close_time']) || isset($data['opening_time']) || isset($data['closing_time']) || array_key_exists('is_closed', $data)) {
            $day = $data['day_of_week'] ?? $vendor->businessHours()->value('day_of_week') ?? 'monday';
            $vendor->businessHours()->updateOrCreate(
                ['vendor_id' => $vendor->id, 'day_of_week' => $day],
                [
                    'open_time' => $data['open_time'] ?? $data['opening_time'] ?? $vendor->businessHours()->value('open_time') ?? '09:00',
                    'close_time' => $data['close_time'] ?? $data['closing_time'] ?? $vendor->businessHours()->value('close_time') ?? '17:00',
                    'is_closed' => $data['is_closed'] ?? $vendor->businessHours()->value('is_closed') ?? false,
                ]
            );
        }

        $vendor->refresh();

        if (($data['verification_status'] ?? null) === 'approved' && $vendor->wasChanged('verification_status')) {
            Notification::send($vendor->user, new VendorApprovedNotification($vendor));
        }

        return $vendor->fresh(['businessHours']);
    }

    /**
     * Delete a vendor and its associated resources.
     */
    public function deleteVendor(Vendor $vendor): bool
    {
        if ($vendor->profile_picture && Storage::disk('public')->exists($vendor->profile_picture)) {
            Storage::disk('public')->delete($vendor->profile_picture);
        }

        if ($vendor->business_document && Storage::disk('public')->exists($vendor->business_document)) {
            Storage::disk('public')->delete($vendor->business_document);
        }

        $vendor->businessHours()->delete();
        return (bool) $vendor->delete();
    }
}
