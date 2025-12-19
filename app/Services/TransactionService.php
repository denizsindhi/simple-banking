<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Repositories\AccountRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * TransactionService is the ONLY place where account balances may change.
 *
 * It enforces:
 * - No negative balances
 * - Blocked customers/accounts cannot perform operations
 * - Closed accounts are read-only
 * - Transfers are atomic (all-or-nothing)
 * - Rejected transactions are stored with clear reasons
 * - Transaction history is immutable (append-only log)
 */
class TransactionService
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly TransactionRepository $transactions,
    ) {
    }

    public function deposit(int $targetAccountId, int $amount): Transaction
    {
        $account = $this->requireAccount($targetAccountId);

        if ($amount <= 0) {
            return $this->reject(
                TransactionType::DEPOSIT,
                $amount,
                null,
                $account->id,
                'Amount must be positive.',
            );
        }

        if (! $this->canMutate($account)) {
            return $this->reject(
                TransactionType::DEPOSIT,
                $amount,
                null,
                $account->id,
                'Account is not active.',
            );
        }

        // Apply mutation through domain methods.
        $account->increaseBalance($amount);
        $this->accounts->save($account);

        return $this->transactions->log(
            TransactionType::DEPOSIT,
            $amount,
            null,
            $account->id,
            TransactionStatus::SUCCESS,
        );
    }

    public function withdraw(int $sourceAccountId, int $amount): Transaction
    {
        $account = $this->requireAccount($sourceAccountId);

        if ($amount <= 0) {
            return $this->reject(
                TransactionType::WITHDRAWAL,
                $amount,
                $account->id,
                null,
                'Amount must be positive.',
            );
        }

        if (! $this->canMutate($account)) {
            return $this->reject(
                TransactionType::WITHDRAWAL,
                $amount,
                $account->id,
                null,
                'Account is not active.',
            );
        }

        // No negative balances.
        if ($account->balance < $amount) {
            return $this->reject(
                TransactionType::WITHDRAWAL,
                $amount,
                $account->id,
                null,
                'Insufficient funds.',
            );
        }

        $account->decreaseBalance($amount);
        $this->accounts->save($account);

        return $this->transactions->log(
            TransactionType::WITHDRAWAL,
            $amount,
            $account->id,
            null,
            TransactionStatus::SUCCESS,
        );
    }

    public function transfer(int $sourceAccountId, int $targetAccountId, int $amount): Transaction
    {
        if ($sourceAccountId === $targetAccountId) {
            return $this->reject(
                TransactionType::TRANSFER,
                $amount,
                $sourceAccountId,
                $targetAccountId,
                'Source and target accounts must be different.',
            );
        }

        $source = $this->requireAccount($sourceAccountId);
        $target = $this->requireAccount($targetAccountId);

        if ($amount <= 0) {
            return $this->reject(
                TransactionType::TRANSFER,
                $amount,
                $source->id,
                $target->id,
                'Amount must be positive.',
            );
        }

        if (! $this->canMutate($source) || ! $this->canMutate($target)) {
            return $this->reject(
                TransactionType::TRANSFER,
                $amount,
                $source->id,
                $target->id,
                'Both accounts must be active.',
            );
        }

        // No negative balance on source.
        if ($source->balance < $amount) {
            return $this->reject(
                TransactionType::TRANSFER,
                $amount,
                $source->id,
                $target->id,
                'Insufficient funds on source account.',
            );
        }

        // Atomicity: wrap in database transaction to ensure all-or-nothing.
        return DB::transaction(function () use ($source, $target, $amount) {
            // Reload accounts within transaction to get latest balance
            $source = $this->accounts->find($source->id);
            $target = $this->accounts->find($target->id);

            // Double-check balance after reload (prevent race conditions)
            if ($source->balance < $amount) {
                return $this->reject(
                    TransactionType::TRANSFER,
                    $amount,
                    $source->id,
                    $target->id,
                    'Insufficient funds on source account.',
                );
            }

            // Apply both mutations
            $source->decreaseBalance($amount);
            $target->increaseBalance($amount);

            $this->accounts->save($source);
            $this->accounts->save($target);

            return $this->transactions->log(
                TransactionType::TRANSFER,
                $amount,
                $source->id,
                $target->id,
                TransactionStatus::SUCCESS,
            );
        });
    }

    /**
     * @return array<int,Transaction>
     */
    public function historyForAccount(int $accountId): array
    {
        return $this->transactions->forAccount($accountId);
    }

    private function canMutate(Account $account): bool
    {
        // Blocked customers/accounts cannot perform operations.
        if ($account->status === AccountStatus::BLOCKED) {
            return false;
        }

        // Closed accounts are read-only.
        if ($account->status === AccountStatus::CLOSED) {
            return false;
        }

        return true;
    }

    private function requireAccount(int $id): Account
    {
        $account = $this->accounts->find($id);

        if (! $account) {
            throw new RuntimeException("Account {$id} not found.");
        }

        return $account;
    }

    private function reject(
        TransactionType $type,
        int $amount,
        ?int $sourceAccountId,
        ?int $targetAccountId,
        string $reason,
    ): Transaction {
        // Rejected transactions must be stored with clear reason.
        return $this->transactions->log(
            $type,
            $amount,
            $sourceAccountId,
            $targetAccountId,
            TransactionStatus::REJECTED,
            $reason,
        );
    }
}

