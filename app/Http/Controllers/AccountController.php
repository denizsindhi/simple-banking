<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Services\AccountService;
use App\Services\CustomerService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountService $accounts,
        private readonly CustomerService $customers,
        private readonly TransactionService $transactions,
    ) {
    }

    public function create(): Response
    {
        return Inertia::render('Accounts/Create', [
            // Provide existing customers so the UI can show a dropdown instead of raw IDs.
            'customers' => $this->customers->list(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'type' => ['required', 'in:personal,savings,business'],
        ]);

        $type = match ($data['type']) {
            'personal' => AccountType::PERSONAL,
            'savings' => AccountType::SAVINGS,
            'business' => AccountType::BUSINESS,
        };

        try {
            $account = $this->accounts->open(
                (int) $data['customer_id'],
                $type,
            );

            return redirect()
                ->route('accounts.show', $account->id)
                ->with('success', 'Account opened successfully.');
        } catch (RuntimeException $e) {
            return redirect()
                ->route('accounts.create')
                ->with('error', $e->getMessage());
        }
    }

    public function block(int $id): RedirectResponse
    {
        try {
            $this->accounts->block($id);

            return redirect()->route('accounts.show', $id)->with('success', 'Account blocked successfully.');
        } catch (RuntimeException $e) {
            return redirect()->route('accounts.show', $id)->with('error', $e->getMessage());
        }
    }

    public function unblock(int $id): RedirectResponse
    {
        try {
            $this->accounts->unblock($id);

            return redirect()->route('accounts.show', $id)->with('success', 'Account unblocked successfully.');
        } catch (RuntimeException $e) {
            return redirect()->route('accounts.show', $id)->with('error', $e->getMessage());
        }
    }

    public function close(int $id): RedirectResponse
    {
        try {
            $this->accounts->close($id);

            return redirect()->route('accounts.show', $id)->with('success', 'Account closed successfully.');
        } catch (RuntimeException $e) {
            return redirect()->route('accounts.show', $id)->with('error', $e->getMessage());
        }
    }

    public function show(int $id, Request $request): Response
    {
        $account = $this->accounts->show($id);
        $history = $this->transactions->historyForAccount($id);
        
        // Get flash messages from session and pass them directly
        $flash = [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
        ];

        return Inertia::render('Accounts/Show', [
            'account' => $account,
            'transactions' => $history,
            'flash' => $flash,
        ]);
    }
}

