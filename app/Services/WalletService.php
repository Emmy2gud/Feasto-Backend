<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Get or create wallet for user.
     */
    public function getWallet(User $user): Wallet
    {
        return $user->wallet()->firstOrCreate([], [
            'balance' => 0.00,
            'currency' => 'NGN',
            'status' => 'active',
        ]);
    }

    /**
     * Get paginated transaction ledger entries for user's wallet.
     */
    public function getLedger(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $wallet = $this->getWallet($user);

        return $wallet->ledger()->latest()->paginate($perPage);
    }

    /**
     * Credit funds to a wallet.
     */
    public function credit(Wallet $wallet, float $amount, string $type, string $description, ?string $refType = null, ?int $refId = null): Wallet
    {
        return DB::transaction(function () use ($wallet, $amount, $type, $description, $refType, $refId) {
            $wallet->refresh();
            $before = (float) $wallet->balance;
            $after = $before + $amount;

            $wallet->update(['balance' => $after]);

            $wallet->ledger()->create([
                'transaction_type' => $type,
                'direction' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $description,
                'status' => 'successful',
            ]);

            return $wallet->fresh();
        });
    }

    /**
     * Debit funds from a wallet.
     */
    public function debit(Wallet $wallet, float $amount, string $type, string $description, ?string $refType = null, ?int $refId = null): Wallet
    {
        return DB::transaction(function () use ($wallet, $amount, $type, $description, $refType, $refId) {
            $wallet->refresh();
            $before = (float) $wallet->balance;

            if ($before < $amount) {
                throw ValidationException::withMessages([
                    'balance' => 'Insufficient wallet balance.',
                ]);
            }

            $after = $before - $amount;
            $wallet->update(['balance' => $after]);

            $wallet->ledger()->create([
                'transaction_type' => $type,
                'direction' => 'debit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $description,
                'status' => 'successful',
            ]);

            return $wallet->fresh();
        });
    }
}
