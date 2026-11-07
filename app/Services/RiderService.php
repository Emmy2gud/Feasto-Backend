<?php
namespace App\Services;

use App\Models\Rider;
use App\Models\User;
use App\Services\Api\Korapay\KorapayClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RiderService
{

    public function __construct(
        protected KorapayClient $koraPay
    ) {}

    public function create(User $user, array $data, ?\App\DTOs\RiderVerificationDTO $dto = null): Rider
    {
        // Step 1: Verify NIN if provided
        if ($dto && ! empty($dto->nin)) {
            $validation = [
                'first_name'    => $dto->firstName,
                'last_name'     => $dto->lastName,
                'date_of_birth' => $dto->dateOfBirth,
            ];

            $ninResponse = $this->koraPay->verifyNin(
                $dto->nin,
                $validation
            );

            if (($ninResponse['status'] ?? null) !== 'verified') {
                throw ValidationException::withMessages([
                    'nin' => 'Your identity could not be verified.',
                ]);
            }

            $data['nin_status']      = 'verified';
            $data['nin_verified_at'] = now();
        }

        return DB::transaction(function () use ($user, $data) {
            // Recheck inside the transaction for safety.
            if ($user->rider()->exists()) {
                throw ValidationException::withMessages([
                    'rider' => 'You already have a rider profile.',
                ]);
            }

            $data['approval_status']     = $data['approval_status'] ?? 'pending';
            $data['is_online']           = (bool) ($data['is_online'] ?? false);
            $data['is_available']        = (bool) ($data['is_available'] ?? false);

            $user->update([
                'role' => 'rider',
            ]);

            $rider = $user->rider()->create($data);

            $user->wallet()->firstOrCreate([], [
                'balance'  => 0.00,
                'currency' => 'NGN',
                'status'   => 'active',
            ]);

            return $rider->fresh();
        });
    }

    public function buildPayload(array $validatedData, ?string $documentPath = null): array
    {
        $payload = [
            'vehicle_type'        => $validatedData['vehicle_type'] ?? null,
            'plate_number'        => $validatedData['plate_number'] ?? null,
            'rejection_reason'    => $validatedData['rejection_reason'] ?? null,
            'phone_number'        => $validatedData['phone_number'] ?? null,
            'email'               => $validatedData['email'] ?? null,
            'emergency_name'      => $validatedData['emergency_name'] ?? null,
            'emergency_phone'     => $validatedData['emergency_phone'] ?? null,
            'is_online'           => (bool) ($validatedData['is_online'] ?? false),
            'latitude'            => $validatedData['latitude'] ?? null,
            'longitude'           => $validatedData['longitude'] ?? null,
        ];

        if ($documentPath) {
            $payload['documents'] = $documentPath;
        }

        return $payload;
    }
}
