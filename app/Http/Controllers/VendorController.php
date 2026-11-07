<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    public function __construct(
        protected VendorService $vendorService
    ) {}

    /**
     * Display a listing of vendors.
     */
    public function index(): JsonResponse
    {
        $vendors = $this->vendorService->getAll();

        return response()->json(['vendors' => $vendors], 200);
    }

    /**
     * Display the specified vendor.
     */
    public function show(int $id): JsonResponse
    {
        $vendor = $this->vendorService->getById($id);

        return response()->json(['vendor' => $vendor], 200);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(StoreVendorRequest $request): JsonResponse
    {
        $vendor = $this->vendorService->createVendor(
            $request->user(),
            $request->validated(),
            $request->file('profile_picture'),
            $request->file('business_document')
        );

        return response()->json([
            'message' => 'Vendor created successfully',
            'vendor' => $vendor,
        ], 201);
    }

    /**
     * Update the specified vendor.
     */
    public function update(UpdateVendorRequest $request, int $id): JsonResponse
    {
        $vendor = Vendor::findOrFail($id);
        $user = $request->user();

        if ($user->id !== $vendor->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $updatedVendor = $this->vendorService->updateVendor(
            $vendor,
            $request->validated(),
            $request->file('profile_picture'),
            $request->file('business_document')
        );

        return response()->json([
            'message' => 'Vendor updated successfully',
            'vendor' => $updatedVendor,
        ], 200);
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(int $id): JsonResponse
    {
        $vendor = Vendor::findOrFail($id);
        $user = auth()->user();

        if ($user->id !== $vendor->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->vendorService->deleteVendor($vendor);

        return response()->json(['message' => 'Vendor deleted successfully'], 200);
    }
}
