<?php

namespace App\Services\Api;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;



class PaymentGatewayService
{
    /**
     * Create a new class instance.
     */
    protected string $baseUrl;

    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('services.korapay.base_url'), '/');
        $this->secretKey = config('services.korapay.secret_key');
    }



    /**
     * Build a pre-configured HTTP client for Korapay.
     * All child services use this instead of Http:: directly.
     */
    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->secretKey)
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->retry(2, 500);
    }

    public function initializePayment(array $request,string $txRef): array
    {
        $payload = [
            'amount' => $request['amount'],
            'currency' => 'NGN',
            'reference' => $txRef,
            'customer' => [
                'email' => $request['customer']['email'],
                'name' => $request['customer']['name'],
                'phone' => $request['customer']['phone'],
            ],
            'metadata' => $request['metadata'] ?? [],
            'callback_url' => $request['callback_url'] ?? null,
            'redirect_url' => $request['redirect_url'] ?? null,
        ];

        return $this->http()
            ->post('/api/v1/charges/initialize', $payload)
            ->throw()
            ->json();
    }


}
