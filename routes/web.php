<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('customers.index'));

// Customers
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::post('/customers/{id}/block', [CustomerController::class, 'block'])->name('customers.block');
Route::post('/customers/{id}/close', [CustomerController::class, 'close'])->name('customers.close');
Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');

// Accounts
Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
Route::post('/accounts/{id}/block', [AccountController::class, 'block'])->name('accounts.block');
Route::post('/accounts/{id}/close', [AccountController::class, 'close'])->name('accounts.close');
Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');

// Transactions
Route::get('/transactions/deposit', [TransactionController::class, 'depositForm'])->name('transactions.deposit.form');
Route::get('/transactions/withdraw', [TransactionController::class, 'withdrawForm'])->name('transactions.withdraw.form');
Route::get('/transactions/transfer', [TransactionController::class, 'transferForm'])->name('transactions.transfer.form');

Route::post('/transactions/deposit', [TransactionController::class, 'deposit'])->name('transactions.deposit');
Route::post('/transactions/withdraw', [TransactionController::class, 'withdraw'])->name('transactions.withdraw');
Route::post('/transactions/transfer', [TransactionController::class, 'transfer'])->name('transactions.transfer');

