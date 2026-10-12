<?php

namespace App\DTOs;

class VendorVerificationDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $nin,
        public readonly string $bvn,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $dateOfBirth,
    ) {}
}
