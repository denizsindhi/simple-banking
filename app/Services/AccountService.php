<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Account;
use App\Repositories\AccountRepository;
use App\Repositories\CustomerRepository;
use RuntimeException;

/**
 * AccountService encapsulates account lifecycle rules.
 */
class AccountService
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly CustomerRepository $customers,
    ) {
    }

    /**
     * List all accounts (used by transaction forms).
     *
     * @return array<int,Account>
     */
    public function list(): array
    {
        return $this->accounts->all();
    }

    /**
     * List accounts for a specific customer.
     *
     * @return array<int,Account>
     */
    public function forCustomer(int $customerId): array
    {
        return $this->accounts->forCustomer($customerId);
    }

    public function open(
        int $customerId,
        AccountType $type,
        string $currency = 'EUR',
    ): Account {
        $customer = $this->customers->find($customerId);

        if (! $customer) {
            throw new RuntimeException("Customer {$customerId} not found.");
        }

        if (! $customer->isActive()) {
            throw new RuntimeException('Accounts can only be opened for active customers.');
        }

        return $this->accounts->create(
            $customer->id,
            $type,
            $currency,
            0,
            AccountStatus::ACTIVE,
        );
    }

    public function block(int $accountId): Account
    {
        $account = $this->requireAccount($accountId);

        if ($account->isClosed()) {
            throw new RuntimeException('Closed accounts cannot be blocked.');
        }

        $account->status = AccountStatus::BLOCKED;
        $this->accounts->save($account);

        return $account;
    }

    public function unblock(int $accountId): Account
    {
        $account = $this->requireAccount($accountId);

        if ($account->isClosed()) {
            throw new RuntimeException('Closed accounts cannot be unblocked.');
        }

        if ($account->isActive()) {
            throw new RuntimeException('Account is already active.');
        }

        // Check if the customer is blocked - if so, cannot unblock individual accounts
        $customer = $this->customers->find($account->customer_id);
        if ($customer && $customer->isBlocked()) {
            throw new RuntimeException('Cannot unblock account: customer is blocked. Unblock the customer first.');
        }

        $account->status = AccountStatus::ACTIVE;
        $this->accounts->save($account);

        return $account;
    }

    public function close(int $accountId): Account
    {
        $account = $this->requireAccount($accountId);

        // Business rule: Account can be closed only if balance == 0
        if ($account->balance !== 0) {
            throw new RuntimeException('Account can only be closed when balance is zero.');
        }

        $account->status = AccountStatus::CLOSED;
        $this->accounts->save($account);

        return $account;
    }

    public function show(int $accountId): Account
    {
        return $this->requireAccount($accountId);
    }

    private function requireAccount(int $id): Account
    {
        $account = $this->accounts->find($id);

        if (! $account) {
            throw new RuntimeException("Account {$id} not found.");
        }

        return $account;
    }
}

