<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use App\Notifications\VendorApprovedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('businessHours')->latest()->get();

        return response()->json(['vendors' => $vendors], 200);
    }

    public function show($id)
    {
        $vendor = Vendor::with('businessHours')->findOrFail($id);

        return response()->json(['vendor' => $vendor], 200);
    }

    public function store(StoreVendorRequest $request)
    {

        $user = auth()->user();

        // Block if the user already has a vendor
        if ($user->vendor()->exists()) {
            return response()->json([
                'message' => 'You already have a vendor. Use the update endpoint instead.',
            ], 409); // 409 Conflict
        }

        $validatedData = $request->validated();

        $vendorData = [
            'business_name' => $validatedData['business_name'],
            // ... (no need for user_id here — the relationship sets it)
            'business_description' => $validatedData['business_description'] ?? null,
            'business_phone' => $validatedData['business_phone'] ?? null,
            'business_email' => $validatedData['business_email'] ?? null,
            'business_address' => $validatedData['business_address'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'state' => $validatedData['state'] ?? null,
            'country' => $validatedData['country'] ?? null,
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
            'is_accepting_orders' => $validatedData['is_accepting_orders'] ?? true,
            'status' => $validatedData['status'] ?? 'active',
            'verification_status' => $validatedData['verification_status'] ?? 'pending',
        ];

        if ($request->hasFile('profile_picture')) {
            $vendorData['profile_picture'] = $request->file('profile_picture')
                ->store('vendors/profile-pictures', 'public');
        }

        if ($request->hasFile('business_document')) {
            $vendorData['business_document'] = $request->file('business_document')
                ->store('vendors/documents', 'public');
        }

        $vendor = DB::transaction(function () use ($user, $vendorData, $validatedData) {
            $user->update(['role' => 'vendor']);

            $vendor = $user->vendor()->create($vendorData);
            $vendor->wallet()->create([
                'balance' => 0.00,
                'currency' => 'NGN',
                'status' => 'active',
            ]);

            // Initial business hours — create once
            $vendor->businessHours()->create([
                'day_of_week' => $validatedData['day_of_week'] ?? 'monday',
                'open_time' => $validatedData['open_time']
                                  ?? $validatedData['opening_time']
                                  ?? '09:00',
                'close_time' => $validatedData['close_time']
                                  ?? $validatedData['closing_time']
                                  ?? '17:00',
                'is_closed' => $validatedData['is_closed'] ?? false,
            ]);

            return $vendor->fresh(['businessHours']);
        });

        return response()->json([
            'message' => 'Vendor created successfully',
            'vendor' => $vendor,
        ], 201);
    }

    public function update(UpdateVendorRequest $request, $id)
    {
        $vendor = Vendor::with('businessHours')->findOrFail($id);
        $user = auth()->user();

        if ($user->id !== $vendor->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validatedData = $request->validated();

        $vendorData = [
            'business_name' => $validatedData['business_name'] ?? $vendor->business_name,
            'business_description' => $validatedData['business_description'] ?? $vendor->business_description,
            'business_phone' => $validatedData['business_phone'] ?? $vendor->business_phone,
            'business_email' => $validatedData['business_email'] ?? $vendor->business_email,
            'business_address' => $validatedData['business_address'] ?? $vendor->business_address,
            'city' => $validatedData['city'] ?? $vendor->city,
            'state' => $validatedData['state'] ?? $vendor->state,
            'country' => $validatedData['country'] ?? $vendor->country,
            'latitude' => $validatedData['latitude'] ?? $vendor->latitude,
            'longitude' => $validatedData['longitude'] ?? $vendor->longitude,
            'is_accepting_orders' => $validatedData['is_accepting_orders'] ?? $vendor->is_accepting_orders,
            'status' => $validatedData['status'] ?? $vendor->status,
            'verification_status' => $validatedData['verification_status'] ?? $vendor->verification_status,
        ];

        if ($request->hasFile('profile_picture')) {
            if ($vendor->profile_picture && Storage::disk('public')->exists($vendor->profile_picture)) {
                Storage::disk('public')->delete($vendor->profile_picture);
            }

            $vendorData['profile_picture'] = $request->file('profile_picture')->store('vendors/profile-pictures', 'public');
        }

        if ($request->hasFile('business_document')) {
            if ($vendor->business_document && Storage::disk('public')->exists($vendor->business_document)) {
                Storage::disk('public')->delete($vendor->business_document);
            }

            $vendorData['business_document'] = $request->file('business_document')->store('vendors/documents', 'public');
        }

        $vendor->update($vendorData);

        if (isset($validatedData['day_of_week']) || isset($validatedData['open_time']) || isset($validatedData['close_time']) || isset($validatedData['opening_time']) || isset($validatedData['closing_time']) || array_key_exists('is_closed', $validatedData)) {
            $vendor->businessHours()->updateOrCreate(
                ['vendor_id' => $vendor->id, 'day_of_week' => $validatedData['day_of_week'] ?? $vendor->businessHours()->value('day_of_week') ?? 'monday'],
                [
                    'open_time' => $validatedData['open_time'] ?? $validatedData['opening_time'] ?? $vendor->businessHours()->value('open_time') ?? '09:00',
                    'close_time' => $validatedData['close_time'] ?? $validatedData['closing_time'] ?? $vendor->businessHours()->value('close_time') ?? '17:00',
                    'is_closed' => $validatedData['is_closed'] ?? $vendor->businessHours()->value('is_closed') ?? false,
                ]
            );
        }

        $vendor->refresh();

        if (($validatedData['verification_status'] ?? null) === 'approved' && $vendor->wasChanged('verification_status')) {
            Notification::send($vendor->user, new VendorApprovedNotification($vendor));
        }

        return response()->json([
            'message' => 'Vendor updated successfully',
            'vendor' => $vendor->fresh(['businessHours']),
        ], 200);
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $user = auth()->user();

        if ($user->id !== $vendor->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($vendor->profile_picture && Storage::disk('public')->exists($vendor->profile_picture)) {
            Storage::disk('public')->delete($vendor->profile_picture);
        }

        if ($vendor->business_document && Storage::disk('public')->exists($vendor->business_document)) {
            Storage::disk('public')->delete($vendor->business_document);
        }

        $vendor->businessHours()->delete();
        $vendor->delete();

        return response()->json(['message' => 'Vendor deleted successfully'], 200);
    }
}
