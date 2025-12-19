<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Services\AccountService;
use App\Services\TransactionService;
use RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customers,
        private readonly AccountService $accounts,
        private readonly TransactionService $transactions,
    ) {
    }

    public function index(Request $request): Response
    {
        // Get flash messages from session and pass them directly
        $flash = [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
        ];
        
        return Inertia::render('Customers/Index', [
            'customers' => $this->customers->list(),
            'flash' => $flash,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->customers->create($data['name']);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function block(int $id): RedirectResponse
    {
        try {
            $this->customers->block($id);

            return redirect()->route('customers.index')->with('success', 'Customer blocked and associated accounts blocked.');
        } catch (RuntimeException $e) {
            return redirect()->route('customers.index')->with('error', $e->getMessage());
        }
    }

    public function unblock(int $id): RedirectResponse
    {
        try {
            $this->customers->unblock($id);

            return redirect()->route('customers.index')->with('success', 'Customer unblocked and associated accounts unblocked.');
        } catch (RuntimeException $e) {
            return redirect()->route('customers.index')->with('error', $e->getMessage());
        }
    }

    public function close(int $id): RedirectResponse
    {
        try {
            $this->customers->close($id);

            return redirect()->route('customers.index')->with('success', 'Customer closed successfully.');
        } catch (RuntimeException $e) {
            return redirect()->route('customers.index')->with('error', $e->getMessage());
        }
    }

    public function show(int $id): Response
    {
        $customer = $this->customers->get($id);
        $accounts = $this->accounts->forCustomer($id);

        // Aggregate all transactions across this customer's accounts.
        $transactions = [];
        foreach ($accounts as $account) {
            foreach ($this->transactions->historyForAccount($account->id) as $tx) {
                $transactions[] = $tx;
            }
        }

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'accounts' => $accounts,
            'transactions' => $transactions,
        ]);
    }
}

