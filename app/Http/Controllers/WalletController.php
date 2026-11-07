<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Display authenticated user's wallet balance and details.
     */
    public function index(Request $request): JsonResponse
    {
        $wallet = $this->walletService->getWallet($request->user());

        return response()->json([
            'message' => 'Wallet details retrieved successfully.',
            'wallet' => $wallet,
        ], 200);
    }

    /**
     * Show wallet details.
     */
    public function show(Request $request): JsonResponse
    {
        return $this->index($request);
    }
}
