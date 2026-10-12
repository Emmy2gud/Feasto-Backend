<?php

namespace App\Services\Api;

class VerifyIdentityService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
       $this->baseUrl   = rtrim(config('services.korapay.base_url'), '/');
        $this->secretKey = config('services.korapay.secret_key');
    }
}
