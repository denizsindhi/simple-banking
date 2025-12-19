<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'currency',
        'balance',
        'status',
    ];

    protected $casts = [
        'type' => AccountType::class,
        'status' => AccountStatus::class,
        'balance' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sourceTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'source_account_id');
    }

    public function targetTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'target_account_id');
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


