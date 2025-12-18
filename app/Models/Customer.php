<?php

namespace App\Models;

use App\Enums\CustomerStatus;

class Customer
{
    public int $id;
    public string $name;
    public CustomerStatus $status;

    public function __construct(int $id, string $name, CustomerStatus $status)
    {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
    }

    public function isActive(): bool
    {
        return $this->status === CustomerStatus::ACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this->status === CustomerStatus::BLOCKED;
    }

    public function isClosed(): bool
    {
        return $this->status === CustomerStatus::CLOSED;
    }
}


