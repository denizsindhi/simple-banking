<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  account: {
    type: Object,
    required: true,
  },
  transactions: {
    type: Array,
    required: true,
  },
});

// Safe CSRF token for POST forms on this page
const csrfToken =
  typeof window !== 'undefined'
    ? document.querySelector('meta[name=\"csrf-token\"]')?.content ?? ''
    : '';
</script>

<template>
  <AppLayout>
    <main class="space-y-6">
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">
            Account #{{ account.id }}
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Customer #{{ account.customer_id }} �
            <span class="font-mono">{{ account.type }}</span>
            <span class="font-mono">({{ account.currency }})</span>
          </p>
        </div>
        <div class="text-right bg-white border border-slate-200 rounded-lg px-4 py-2 shadow-sm">
          <div class="text-xs text-slate-500">Balance</div>
          <div class="text-xl font-mono text-slate-900">
            {{ (account.balance / 100).toFixed(2) }} {{ account.currency }}
          </div>
          <div class="text-xs text-slate-500">
            Status:
            <span class="font-mono">{{ account.status }}</span>
          </div>
        </div>
      </header>

      <section class="flex gap-2">
        <!-- Blocked accounts cannot perform operations (enforced in TransactionService) -->
        <form
          method="post"
          :action="`/accounts/${account.id}/block`"
        >
          <input type="hidden" name="_token" :value="csrfToken">
          <button
            type="submit"
            class="px-3 py-1.5 text-xs rounded-md border border-amber-400 text-amber-700 bg-amber-50 hover:bg-amber-100 disabled:opacity-40"
            :disabled="account.status !== 'active'"
          >
            Block account
          </button>
        </form>

        <!-- Account can be closed only if balance == 0, enforced in AccountService -->
        <form
          method="post"
          :action="`/accounts/${account.id}/close`"
        >
          <input type="hidden" name="_token" :value="csrfToken">
          <button
            type="submit"
            class="px-3 py-1.5 text-xs rounded-md border border-rose-400 text-rose-600 bg-rose-50 hover:bg-rose-100"
          >
            Close account
          </button>
        </form>
      </section>

      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Transactions</h2>

        <p v-if="transactions.length === 0" class="text-sm text-slate-600 bg-white border border-dashed border-slate-200 rounded-lg p-4">
          No transactions yet. Use deposit, withdraw or transfer to create some.
        </p>

        <div v-else class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
              <tr>
                <th class="px-2 py-1 text-left">ID</th>
                <th class="px-2 py-1 text-left">Type</th>
                <th class="px-2 py-1 text-left">Amount</th>
                <th class="px-2 py-1 text-left">Source</th>
                <th class="px-2 py-1 text-left">Target</th>
                <th class="px-2 py-1 text-left">Status</th>
                <th class="px-2 py-1 text-left">Reason</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="tx in transactions"
                :key="tx.id"
                class="border-t border-slate-200"
              >
                <td class="px-2 py-1 font-mono text-slate-700">{{ tx.id }}</td>
                <td class="px-2 py-1 font-mono text-slate-700">{{ tx.type }}</td>
                <td class="px-2 py-1 font-mono text-slate-700">
                  {{ (tx.amount / 100).toFixed(2) }} EUR
                </td>
                <td class="px-2 py-1 font-mono text-slate-500">
                  {{ tx.source_account_id ?? '' }}
                </td>
                <td class="px-2 py-1 font-mono text-slate-500">
                  {{ tx.target_account_id ?? '' }}
                </td>
                <td class="px-2 py-1 font-mono" :class="tx.status === 'success' ? 'text-emerald-600' : 'text-rose-600'">
                  {{ tx.status }}
                </td>
                <td class="px-2 py-1 text-xs text-rose-600">
                  {{ tx.rejection_reason ?? '' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </AppLayout>
</template>
