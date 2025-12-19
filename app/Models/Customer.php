<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => CustomerStatus::class,
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
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


