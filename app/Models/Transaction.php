<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use DateTimeImmutable;

class Transaction
{
    public int $id;
    public TransactionType $type;
    public int $amount;
    public DateTimeImmutable $timestamp;
    public ?int $source_account_id;
    public ?int $target_account_id;
    public TransactionStatus $status;
    public ?string $rejection_reason;

    public function __construct(
        int $id,
        TransactionType $type,
        int $amount,
        DateTimeImmutable $timestamp,
        ?int $sourceAccountId,
        ?int $targetAccountId,
        TransactionStatus $status,
        ?string $rejectionReason
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->amount = $amount;
        $this->timestamp = $timestamp;
        $this->source_account_id = $sourceAccountId;
        $this->target_account_id = $targetAccountId;
        $this->status = $status;
        $this->rejection_reason = $rejectionReason;
    }
}


