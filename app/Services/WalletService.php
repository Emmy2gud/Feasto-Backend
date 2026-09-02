<?php

namespace App\Services;

class WalletService
{
    public function create(array $data): array
    {
        return $data;
    }

    public function update(int $id, array $data): array
    {
        return [
            'id' => $id,
            ...$data,
        ];
    }
}
