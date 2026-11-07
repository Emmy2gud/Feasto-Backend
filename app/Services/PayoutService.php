<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayoutService
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Get paginated payout history for a user.
     */
    public function getUserPayouts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Payout::where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Request a withdrawal/payout from user wallet.
     */
    public function requestPayout(User $user, float $amount, string $provider = 'korapay', ?string $reason = null): Payout
    {
        $wallet = $this->walletService->getWallet($user);

        if ((float) $wallet->balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Requested payout amount exceeds current wallet balance.',
            ]);
        }

        if ($amount < 1000) {
            throw ValidationException::withMessages([
                'amount' => 'Minimum payout request amount is ₦1,000.',
            ]);
        }

        return DB::transaction(function () use ($user, $wallet, $amount, $provider, $reason) {
            $ref = 'POUT-' . strtoupper(uniqid());

            // Debit wallet
            $this->walletService->debit(
                $wallet,
                $amount,
                'payout_request',
                'Withdrawal request ' . $ref,
                'payout',
                null
            );

            return Payout::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'currency' => $wallet->currency,
                'trigger_type' => 'manual',
                'reference' => $ref,
                'provider' => $provider,
                'status' => 'pending',
                'failure_reason' => $reason,
                'scheduled_at' => now(),
            ]);
        });
    }
}
