<?php

namespace App\Repositories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use DateTimeImmutable;

/**
 * Append-only transaction log using Eloquent.
 *
 * Transactions are immutable: we only ever append, never update or delete.
 */
class TransactionRepository
{
    /**
     * @return array<int,Transaction>
     */
    public function all(): array
    {
        // Return in ascending id (creation) order for deterministic behavior.
        return Transaction::orderBy('id')->get()->all();
    }

    /**
     * @return array<int,Transaction>
     */
    public function forAccount(int $accountId): array
    {
        return Transaction::where('source_account_id', $accountId)
            ->orWhere('target_account_id', $accountId)
            ->orderBy('id')
            ->get()
            ->all();
    }

    public function log(
        TransactionType $type,
        int $amount,
        ?int $sourceAccountId,
        ?int $targetAccountId,
        TransactionStatus $status,
        ?string $rejectionReason = null,
        ?DateTimeImmutable $timestamp = null,
    ): Transaction {
        // Immutable history: append only.
        return Transaction::create([
            'type' => $type,
            'amount' => $amount,
            'timestamp' => $timestamp ?? new DateTimeImmutable(),
            'source_account_id' => $sourceAccountId,
            'target_account_id' => $targetAccountId,
            'status' => $status,
            'rejection_reason' => $rejectionReason,
        ]);
    }
}

