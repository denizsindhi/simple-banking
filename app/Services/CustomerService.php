<?php

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Repositories\AccountRepository;
use App\Repositories\CustomerRepository;
use RuntimeException;

/**
 * CustomerService encapsulates all customer-related business rules.
 *
 * Controllers must call this instead of mutating models directly.
 */
class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly AccountRepository $accounts,
    ) {
    }

    public function list(): array
    {
        return $this->customers->all();
    }

    public function get(int $id): Customer
    {
        return $this->requireCustomer($id);
    }

    public function create(string $name): Customer
    {
        return $this->customers->create($name);
    }

    public function block(int $customerId): Customer
    {
        $customer = $this->requireCustomer($customerId);

        if ($customer->isClosed()) {
            throw new RuntimeException('Closed customers cannot be blocked.');
        }

        // Rule: a blocked customer cannot perform operations.
        // To enforce this, we also block all of their non-closed accounts so
        // TransactionService will reject any future operations.
        $accounts = $this->accounts->forCustomer($customer->id);
        foreach ($accounts as $account) {
            if (! $account->isClosed()) {
                $account->status = \App\Enums\AccountStatus::BLOCKED;
                $this->accounts->save($account);
            }
        }

        $customer->status = CustomerStatus::BLOCKED;
        $this->customers->save($customer);

        return $customer;
    }

    public function unblock(int $customerId): Customer
    {
        $customer = $this->requireCustomer($customerId);

        if ($customer->isClosed()) {
            throw new RuntimeException('Closed customers cannot be unblocked.');
        }

        if ($customer->isActive()) {
            throw new RuntimeException('Customer is already active.');
        }

        // Unblock the customer
        $customer->status = CustomerStatus::ACTIVE;
        $this->customers->save($customer);

        // Unblock all blocked accounts (but not closed accounts)
        $accounts = $this->accounts->forCustomer($customer->id);
        foreach ($accounts as $account) {
            if ($account->isBlocked()) {
                $account->status = \App\Enums\AccountStatus::ACTIVE;
                $this->accounts->save($account);
            }
        }

        return $customer;
    }

    public function close(int $customerId): Customer
    {
        $customer = $this->requireCustomer($customerId);

        // Business rule: Customer can be closed only if all accounts are closed.
        $accounts = $this->accounts->forCustomer($customer->id);
        foreach ($accounts as $account) {
            if (! $account->isClosed()) {
                throw new RuntimeException('Customer can only be closed when all accounts are closed.');
            }
        }

        $customer->status = CustomerStatus::CLOSED;
        $this->customers->save($customer);

        return $customer;
    }

    private function requireCustomer(int $id): Customer
    {
        $customer = $this->customers->find($id);

        if (! $customer) {
            throw new RuntimeException("Customer {$id} not found.");
        }

        return $customer;
    }
}

