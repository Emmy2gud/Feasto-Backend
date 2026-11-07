<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletLedgerController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Display wallet transaction history.
     */
    public function index(Request $request): JsonResponse
    {
        $ledger = $this->walletService->getLedger($request->user());

        return response()->json([
            'message' => 'Wallet transactions retrieved successfully.',
            'ledger' => $ledger,
        ], 200);
    }
}
