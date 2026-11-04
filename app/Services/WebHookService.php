<?php

namespace App\Services;

use App\Services\WebhookSubclass\ChargeService;
use App\Services\WebhookSubclass\RefundService;
use Illuminate\Http\Request;

class WebHookService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected ChargeService $chargeService,
        protected RefundService $refundService
    ) {
        //
    }

    public function handleKorapayWebhook(Request $request)
    {
        $signature = $request->header('x-korapay-signature');
        $secret = env('KORA_SECRET_KEY');
        $hash = hash_hmac('sha256', $request->getContent(), $secret);

        if ($signature !== $hash) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? null;
        $status = $payload['data']['status'] ?? null;
        $txRef = $payload['data']['reference'] ?? null;
        $curency = $payload['data']['currency'] ?? null;
        $data = $payload['data'] ?? [];

        switch ($event) {
            case 'charge.success':
                $this->chargeService->handleChargeSuccess($data, $status, $txRef);
                break;
            case 'charge.failed':
                $this->chargeService->handleChargeFailed($data, $status, $txRef);
                break;
            case 'refund.success':
                $this->refundService->handleChargeRefunded($data, $status, $txRef);
                break;
            case 'refund.failed':
                $this->refundService->handleChargeRefundFailed($data, $status, $txRef);
                break;

            default:
                return response()->json(['message' => 'Unhandled event type.'], 400);
        }

        return response()->json(['message' => 'Webhook processed successfully.'], 200);
    }
}
