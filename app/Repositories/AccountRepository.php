<?php

namespace App\Repositories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Account;

/**
 * Repository for accounts using Eloquent.
 */
class AccountRepository
{
    /**
     * @return array<int,Account>
     */
    public function all(): array
    {
        return Account::all()->all();
    }

    public function find(int $id): ?Account
    {
        return Account::find($id);
    }

    public function create(
        int $customerId,
        AccountType $type,
        string $currency,
        int $initialBalance = 0,
        AccountStatus $status = AccountStatus::ACTIVE,
    ): Account {
        return Account::create([
            'customer_id' => $customerId,
            'type' => $type,
            'currency' => $currency,
            'balance' => $initialBalance,
            'status' => $status,
        ]);
    }

    public function save(Account $account): void
    {
        $account->save();
    }

    /**
     * Return all accounts for a given customer.
     *
     * @return array<int,Account>
     */
    public function forCustomer(int $customerId): array
    {
        return Account::where('customer_id', $customerId)->get()->all();
    }
}

