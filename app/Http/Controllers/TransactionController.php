<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use App\Services\CustomerService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class TransactionController extends Controller
{
    public function __construct(
        private readonly AccountService $accounts,
        private readonly CustomerService $customers,
        private readonly TransactionService $transactions,
    ) {
    }

    public function depositForm(): Response
    {
        return Inertia::render('Transactions/Deposit', [
            'accounts' => $this->accounts->list(),
            'customers' => $this->customers->list(),
        ]);
    }

    public function withdrawForm(): Response
    {
        return Inertia::render('Transactions/Withdraw', [
            'accounts' => $this->accounts->list(),
            'customers' => $this->customers->list(),
        ]);
    }

    public function transferForm(): Response
    {
        return Inertia::render('Transactions/Transfer', [
            'accounts' => $this->accounts->list(),
            'customers' => $this->customers->list(),
        ]);
    }

    public function deposit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $tx = $this->transactions->deposit(
                (int) $data['account_id'],
                (int) $data['amount'],
            );

            return redirect()
                ->route('accounts.show', $tx->target_account_id)
                ->with('success', 'Deposit completed successfully.');
        } catch (RuntimeException $e) {
            return redirect()
                ->route('transactions.deposit.form')
                ->with('error', $e->getMessage());
        }
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $tx = $this->transactions->withdraw(
                (int) $data['account_id'],
                (int) $data['amount'],
            );

            return redirect()
                ->route('accounts.show', $tx->source_account_id)
                ->with('success', 'Withdrawal completed successfully.');
        } catch (RuntimeException $e) {
            return redirect()
                ->route('transactions.withdraw.form')
                ->with('error', $e->getMessage());
        }
    }

    public function transfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_account_id' => ['required', 'integer'],
            'target_account_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $tx = $this->transactions->transfer(
                (int) $data['source_account_id'],
                (int) $data['target_account_id'],
                (int) $data['amount'],
            );

            return redirect()
                ->route('accounts.show', $tx->source_account_id)
                ->with('success', 'Transfer completed successfully.');
        } catch (RuntimeException $e) {
            return redirect()
                ->route('transactions.transfer.form')
                ->with('error', $e->getMessage());
        }
    }
}
