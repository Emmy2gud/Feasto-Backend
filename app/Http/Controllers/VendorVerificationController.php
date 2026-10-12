<?php

namespace App\Http\Controllers;

use App\DTOs\VendorVerificationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\VendorVerificationRequest;
use App\Models\Vendor;
use App\Notifications\VendorApprovedNotification;
use App\Services\VendorVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class VendorVerificationController extends Controller
{
 public function __construct( protected VendorVerificationService $verificationService) {}

    public function verify(VendorVerificationRequest $request)
    {
         $validatedData   = $request->validated();
        $user = auth()->user();
        if(! $user->vendor) {
            return response()->json([
                'message' => 'You do not have a vendor associated with your account.',
            ], 404);
        }

        $dto = new VendorVerificationDTO(
            nin: $validatedData->nin,
            bvn: $validatedData->bvn,
            firstName: $validatedData->first_name,
            lastName: $validatedData->last_name,
            dateOfBirth: $validatedData->date_of_birth,
        );

        $verification = $this->verificationService->verify(
            $dto,
            $request->user()->vendor->id
        );

        return response()->json([
            'message' => 'Vendor verification completed.',
            'data' => $verification,
        ]);
    }
}
