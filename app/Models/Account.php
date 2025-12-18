<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;

class Account
{
    public int $id;
    public int $customer_id;
    public AccountType $type;
    public string $currency;
    public int $balance;
    public AccountStatus $status;

    public function __construct(
        int $id,
        int $customerId,
        AccountType $type,
        string $currency,
        int $balance,
        AccountStatus $status
    ) {
        $this->id = $id;
        $this->customer_id = $customerId;
        $this->type = $type;
        $this->currency = $currency;
        $this->balance = $balance;
        $this->status = $status;
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::ACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this->status === AccountStatus::BLOCKED;
    }

    public function isClosed(): bool
    {
        return $this->status === AccountStatus::CLOSED;
    }

    // NOTE: These must only be used by TransactionService to enforce invariants.
    public function increaseBalance(int $amount): void
    {
        $this->balance += $amount;
    }

    public function decreaseBalance(int $amount): void
    {
        $this->balance -= $amount;
    }
}


