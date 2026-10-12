<?php

namespace App\Services\Api\Korapay;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class KorapayClient
{
    /**
     * The base URL for all Korapay calls.
     * Comes from config, so it's changeable in one place.
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

 public function verifyBvn(string $bvn,?array $validation = null ): array {
        $payload = [
            'id' => $bvn,
            'verification_consent' => true,
        ];

        if ($validation) {
            $payload['validation'] = $validation;
        }

        return $this->http()
            ->post('/api/v1/identities/ng/bvn', $payload)
            ->throw()
            ->json();
    }

    public function verifyNin(string $nin,?array $validation = null ): array {
        $payload = [
            'id' => $nin,
            'verification_consent' => true,
        ];

        if ($validation) {
            $payload['validation'] = $validation;
        }

        return $this->http()
            ->post('/api/v1/identities/ng/nin', $payload)
            ->throw()
            ->json();
       }

    /**
     * Optional: central place to log any failed Korapay call.
     */
    protected function logFailure(string $endpoint, Response $response): void
    {
        Log::warning('Korapay API failure', [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
            'body'     => $response->body(),
        ]);
    }
}
