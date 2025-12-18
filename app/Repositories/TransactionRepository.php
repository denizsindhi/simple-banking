<?php

namespace App\Repositories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use DateTimeImmutable;

/**
 * In-memory, append-only transaction log.
 *
 * Transactions are immutable: we only ever append, never update or delete.
 */
class TransactionRepository
{
    /** @var array<int,Transaction> */
    private static array $transactions = [];
    private static int $nextId = 1;
    private static bool $booted = false;

    private function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $data = session()->get('bank.transactions', [
            'items' => [],
            'next_id' => 1,
        ]);

        self::$transactions = $data['items'] ?? [];
        self::$nextId = $data['next_id'] ?? 1;
        self::$booted = true;
    }

    private function persist(): void
    {
        session()->put('bank.transactions', [
            'items' => self::$transactions,
            'next_id' => self::$nextId,
        ]);
    }

    /**
     * @return array<int,Transaction>
     */
    public function all(): array
    {
        $this->boot();

        // Return in ascending id (creation) order for deterministic behavior.
        return array_values(self::$transactions);
    }

    /**
     * @return array<int,Transaction>
     */
    public function forAccount(int $accountId): array
    {
        $this->boot();

        return array_values(array_filter(
            self::$transactions,
            fn (Transaction $tx) =>
                $tx->source_account_id === $accountId
                || $tx->target_account_id === $accountId,
        ));
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
        $this->boot();

        $id = self::$nextId++;
        $transaction = new Transaction(
            $id,
            $type,
            $amount,
            $timestamp ?? new DateTimeImmutable(),
            $sourceAccountId,
            $targetAccountId,
            $status,
            $rejectionReason,
        );

        // Immutable history: append only.
        self::$transactions[$id] = $transaction;

        $this->persist();

        return $transaction;
    }
}

