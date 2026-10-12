<?php

namespace App\Services;

use App\DTOs\VendorVerificationDTO;
use App\Models\Vendor;
use App\Models\VendorVerification;
use App\Services\Api\Korapay\KorapayClient;
use Illuminate\Support\Facades\DB;

class VendorVerificationService
{
    public function __construct(
        protected KorapayClient $koraPay
    ) {}

    public function verify(VendorVerificationDTO $dto,int $vendorId): Vendor {

        return DB::transaction(function () use ($dto, $vendorId) {

            $validation = [
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'date_of_birth' => $dto->dateOfBirth,
            ];

            $bvnResponse = $this->koraPay->verifyBvn(
                $dto->bvn,
                $validation
            );

            $ninResponse = $this->koraPay->verifyNin(
                $dto->nin,
                $validation
            );

            $bvnVerified = $bvnResponse['status'] ?? false;
            $ninVerified = $ninResponse['status'] ?? false;

            $status = ($bvnVerified && $ninVerified)
                ? 'verified'
                : 'failed';

            return Vendor::updateOrCreate(
                ['vendor_id' => $vendorId],
                [
                    'nin' => $dto->nin,
                    'bvn' => $dto->bvn,
                    'bvn_status' => $bvnVerified ? 'verified' : 'failed',
                    'nin_status' => $ninVerified ? 'verified' : 'failed',
                    'status' => $status,
                    'bvn_reference' => $bvnResponse['data']['reference'] ?? null,
                    'nin_reference' => $ninResponse['data']['reference'] ?? null,
                ]
            );
        });
    }
}
