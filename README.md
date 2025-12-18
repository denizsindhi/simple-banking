## Simple Banking System (Laravel + Vue 3 + Inertia)

This project is a **simple in-memory banking system** built on top of Laravel 12, Vue 3, Inertia.js, and Vite. It focuses on **correct business rules**, a clean service/repository/domain layer, and deterministic behaviour without a database.

All data (customers, accounts, transactions) lives in **static arrays persisted in the session**, so it resets when you restart PHP or clear your browser session.

---

### Domain overview

- **Customer**
  - `id`, `name`, `status: active | blocked | closed`
  - A customer may own **multiple accounts**.

- **Account**
  - `id`, `customer_id`, `type: personal | savings | business`
  - `currency: EUR`, `balance` (stored in cents), `status: active | blocked | closed`

- **Transaction**
  - `id`, `type: deposit | withdrawal | transfer`
  - `amount` (in cents), `timestamp`
  - `source_account_id` (nullable), `target_account_id` (nullable)
  - `status: success | rejected`, `rejection_reason` (nullable)

All balance-changing operations go through `TransactionService`. **You never change `Account::balance` directly** outside of that service.

---

### Business rules

These rules are enforced in the domain layer (services + repositories):

- A **customer may own multiple accounts**.
- A **blocked customer cannot perform operations** (their accounts are blocked when the customer is blocked).
- A **customer can only be closed if all accounts are closed**.
- **Balance must never be negative** (withdraw/transfer reject when insufficient funds).
- **Blocked accounts** cannot perform operations.
- **Closed accounts** are read-only.
- An **account can only be closed if the balance is zero**.
- **Account balance must never be modified directly**.
- **All balance changes go through `TransactionService`** (deposit, withdraw, transfer).
- **Transaction history is immutable** (append-only in `TransactionRepository`).

---

### Core backend structure

- **Enums** (`app/Enums`)
  - `CustomerStatus`, `AccountStatus`, `AccountType`, `TransactionStatus`, `TransactionType`.

- **Models** (`app/Models`)
  - Plain PHP models: `Customer`, `Account`, `Transaction`.

- **Repositories** (`app/Repositories`)
  - `CustomerRepository`, `AccountRepository`, `TransactionRepository`.
  - Use **static arrays + Laravel session** to persist state across requests.

- **Services** (`app/Services`)
  - `CustomerService` – create/block/close customers and enforce customer rules.
  - `AccountService` – open/block/close accounts, fetch accounts.
  - `TransactionService` – the **only place** where balances change.

- **Controllers** (`app/Http/Controllers`)
  - `CustomerController`, `AccountController`, `TransactionController`.
  - Thin controllers that:
    - Delegate all business logic to services.
    - Convert domain exceptions into user-friendly **flash messages**.

- **Frontend**
  - Vue 3 + Inertia.js, Vite bundler.
  - Layout: `resources/js/Layouts/AppLayout.vue` – sidebar navigation, flash messages.
  - Pages:
    - `Customers/Index`, `Customers/Create`, `Customers/Show`.
    - `Accounts/Create`, `Accounts/Show`.
    - `Transactions/Deposit`, `Transactions/Withdraw`, `Transactions/Transfer`.

---

### Running the app

#### 1. Install dependencies

```bash
composer install
npm install
cp .env.example .env  # if not already present
php artisan key:generate
```

> Note: migrations are present but the current implementation uses **in-memory + session** repositories, not the database.

#### 2. Start dev environment (recommended)

The easiest way to run everything is via Composer:

```bash
composer dev
```

This will start:

- `php artisan serve` (Laravel app at `http://127.0.0.1:8000`)
- `php artisan queue:listen` and `php artisan pail` (for logging / background)
- `npm run dev:vite` (Vite dev server for assets)

#### 3. Start via npm only (what you asked for)

You can now simply run:

```bash
npm run dev
```

This will:

- Start `php artisan serve`.
- Start the Vite dev server (`npm run dev:vite`).
- On macOS, automatically open `http://127.0.0.1:8000` in your default browser.

> If you prefer to control PHP and Vite separately, you can still run:
>
> ```bash
> php artisan serve
> npm run dev:vite
> ```

---

### UI overview

The UI is a simple **single-page app** with a sidebar and multiple screens:

- **Sidebar navigation** (in `AppLayout.vue`):
  - `Customers` – list, block, close, and navigate to a customer detail page.
  - `Open Account` – open an account for a selected customer.
  - `Deposit` – deposit into a selected account.
  - `Withdraw` – withdraw from a selected account.
  - `Transfer` – transfer between accounts.

- **Flash messages** (top of every page):
  - Green banner for **success**, e.g. “Account closed successfully.”
  - Red banner for **rule violations**, e.g. “Account can only be closed when balance is zero.”

- **Customer detail page** (`Customers/Show.vue`):
  - Shows **customer status**.
  - Lists all the customer’s accounts with balances, types, and statuses.
  - Rows and a "View account" button navigate to the account detail.
  - Shows an aggregated **transaction history** across all the customer’s accounts.

- **Account detail page** (`Accounts/Show.vue`):
  - Shows **current balance** and status.
  - Buttons to **Block account** and **Close account** (enforcing rules).
  - Shows immutable **transaction history** for that account.

- **Transaction pages**:
  - **Deposit/Withdraw**:
    - Dropdown of accounts: `#id – CustomerName (type)`.
    - Live **current balance** display for the selected account.
  - **Transfer**:
    - Source & target account dropdowns (with customer names and types).
    - Live balance display for both source and target.

---

### Development notes

- **State & persistence**
  - Repositories store state in static arrays and mirror it into the Laravel **session**.
  - Restarting the PHP server or clearing the browser session will reset data.

- **Error handling**
  - Services throw `RuntimeException` for rule violations.
  - Controllers catch these and redirect back with `session()->with('error', ...)`.
  - Flash errors are displayed in the layout.

- **Extending the system**
  - To add new operations (e.g. interest accrual), implement them **inside `TransactionService`**, so all balance rules stay centralized.
  - To change persistence, you can swap repository implementations (e.g. from session to database) without touching services or controllers.

---

### Useful scripts

- **Install & build**

```bash
composer setup   # installs dependencies, sets up .env, migrates, npm install + build
```

- **Full dev stack via Composer** (PHP + queues + logs + Vite):

```bash
composer dev
```

- **Full dev stack via npm** (PHP + Vite + auto-open browser):

```bash
npm run dev
```

- **Vite only**:

```bash
npm run dev:vite
```

---

### License

This project is based on the Laravel framework and is licensed under the [MIT License](https://opensource.org/licenses/MIT). 
