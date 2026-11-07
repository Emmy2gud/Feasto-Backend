<?php

namespace App\Http\Controllers;

use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct(
        protected PayoutService $payoutService
    ) {}

    /**
     * List user's payout requests.
     */
    public function index(Request $request): JsonResponse
    {
        $payouts = $this->payoutService->getUserPayouts($request->user());

        return response()->json([
            'message' => 'Payout history retrieved successfully.',
            'payouts' => $payouts,
        ], 200);
    }

    /**
     * Request a withdrawal/payout from wallet.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:20'],
            'account_name' => ['nullable', 'string', 'max:150'],
        ]);

        $reason = null;
        if (! empty($validated['bank_name']) && ! empty($validated['account_number'])) {
            $reason = "{$validated['bank_name']} - {$validated['account_number']} ({$validated['account_name']})";
        }

        $payout = $this->payoutService->requestPayout(
            $request->user(),
            (float) $validated['amount'],
            'korapay',
            $reason
        );

        return response()->json([
            'message' => 'Payout request submitted successfully.',
            'payout' => $payout,
        ], 201);
    }
}
