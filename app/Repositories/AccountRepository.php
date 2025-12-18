<?php

namespace App\Repositories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Account;

/**
 * In-memory repository for accounts.
 *
 * NOTE: This intentionally does NOT use Eloquent or the database.
 * All data is stored in static arrays for deterministic, in-memory behavior.
 */
class AccountRepository
{
    /** @var array<int,Account> */
    private static array $accounts = [];
    private static int $nextId = 1;
    private static bool $booted = false;

    private function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $data = session()->get('bank.accounts', [
            'items' => [],
            'next_id' => 1,
        ]);

        self::$accounts = $data['items'] ?? [];
        self::$nextId = $data['next_id'] ?? 1;
        self::$booted = true;
    }

    private function persist(): void
    {
        session()->put('bank.accounts', [
            'items' => self::$accounts,
            'next_id' => self::$nextId,
        ]);
    }

    /**
     * @return array<int,Account>
     */
    public function all(): array
    {
        $this->boot();

        return array_values(self::$accounts);
    }

    public function find(int $id): ?Account
    {
        $this->boot();

        return self::$accounts[$id] ?? null;
    }

    public function create(
        int $customerId,
        AccountType $type,
        string $currency,
        int $initialBalance = 0,
        AccountStatus $status = AccountStatus::ACTIVE,
    ): Account {
        $this->boot();

        $id = self::$nextId++;
        $account = new Account(
            $id,
            $customerId,
            $type,
            $currency,
            $initialBalance,
            $status,
        );

        self::$accounts[$id] = $account;

        $this->persist();

        return $account;
    }

    public function save(Account $account): void
    {
        $this->boot();

        self::$accounts[$account->id] = $account;
        $this->persist();
    }

    /**
     * Return all accounts for a given customer.
     *
     * @return array<int,Account>
     */
    public function forCustomer(int $customerId): array
    {
        $this->boot();

        return array_values(array_filter(
            self::$accounts,
            fn (Account $account) => $account->customer_id === $customerId,
        ));
    }
}

