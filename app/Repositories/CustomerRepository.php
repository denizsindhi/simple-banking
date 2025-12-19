<?php

namespace App\Repositories;

use App\Enums\CustomerStatus;
use App\Models\Customer;

class CustomerRepository
{
    /**
     * @return array<int,Customer>
     */
    public function all(): array
    {
        return Customer::all()->all();
    }

    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }

    public function create(string $name): Customer
    {
        return Customer::create([
            'name' => $name,
            'status' => CustomerStatus::ACTIVE,
        ]);
    }

    public function save(Customer $customer): void
    {
        $customer->save();
    }
}


