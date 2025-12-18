<?php

namespace App\Repositories;

use App\Enums\CustomerStatus;
use App\Models\Customer;

class CustomerRepository
{
    /** @var array<int,Customer> */
    private static array $customers = [];
    private static int $nextId = 1;
    private static bool $booted = false;

    /**
     * Load state from the session the first time this repository is used
     * during a request, so data survives across HTTP requests without a DB.
     */
    private function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $data = session()->get('bank.customers', [
            'items' => [],
            'next_id' => 1,
        ]);

        self::$customers = $data['items'] ?? [];
        self::$nextId = $data['next_id'] ?? 1;
        self::$booted = true;
    }

    private function persist(): void
    {
        session()->put('bank.customers', [
            'items' => self::$customers,
            'next_id' => self::$nextId,
        ]);
    }

    public function all(): array
    {
        $this->boot();

        return array_values(self::$customers);
    }

    public function find(int $id): ?Customer
    {
        $this->boot();

        return self::$customers[$id] ?? null;
    }

    public function create(string $name): Customer
    {
        $this->boot();

        $id = self::$nextId++;
        $customer = new Customer($id, $name, CustomerStatus::ACTIVE);
        self::$customers[$id] = $customer;

        $this->persist();

        return $customer;
    }

    public function save(Customer $customer): void
    {
        $this->boot();

        self::$customers[$customer->id] = $customer;
        $this->persist();
    }
}


