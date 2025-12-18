<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  customer: {
    type: Object,
    required: true,
  },
  accounts: {
    type: Array,
    required: true,
  },
  transactions: {
    type: Array,
    required: true,
  },
});
</script>

<template>
  <AppLayout>
    <main class="space-y-6">
      <!-- Customer header -->
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">
            Customer #{{ customer.id }} – {{ customer.name }}
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Status:
            <span class="inline-flex items-center gap-1 font-mono">
              <span
                class="w-1.5 h-1.5 rounded-full"
                :class="{
                  'bg-emerald-400': customer.status === 'active',
                  'bg-amber-400': customer.status === 'blocked',
                  'bg-rose-400': customer.status === 'closed',
                }"
              />
              {{ customer.status }}
            </span>
          </p>
        </div>
      </header>

      <!-- Accounts summary -->
      <section class="space-y-3">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-slate-900">Accounts</h2>
          <p class="text-xs text-slate-500">
            Click a row or "View account" to manage or close it.
          </p>
        </div>

        <p
          v-if="accounts.length === 0"
          class="text-sm text-slate-600 bg-white border border-dashed border-slate-200 rounded-lg p-4"
        >
          This customer has no accounts yet. Use "Open Account" to create one.
        </p>

        <div v-else class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
              <tr>
                <th class="px-2 py-1 text-left">ID</th>
                <th class="px-2 py-1 text-left">Type</th>
                <th class="px-2 py-1 text-left">Currency</th>
                <th class="px-2 py-1 text-left">Balance</th>
                <th class="px-2 py-1 text-left">Status</th>
                <th class="px-2 py-1 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="account in accounts"
                :key="account.id"
                class="border-t border-slate-200 hover:bg-slate-50 cursor-pointer"
                @click="$inertia.visit(`/accounts/${account.id}`)"
              >
                <td class="px-2 py-1 font-mono text-slate-700">#{{ account.id }}</td>
                <td class="px-2 py-1 font-mono text-slate-700">{{ account.type }}</td>
                <td class="px-2 py-1 font-mono text-slate-700">{{ account.currency }}</td>
                <td class="px-2 py-1 font-mono text-slate-700">
                  {{ (account.balance / 100).toFixed(2) }} {{ account.currency }}
                </td>
                <td class="px-2 py-1 font-mono text-slate-700">{{ account.status }}</td>
                <td class="px-2 py-1 text-right">
                  <Link
                    :href="`/accounts/${account.id}`"
                    class="inline-flex items-center px-2 py-1 text-xs rounded-md border border-slate-300 text-slate-700 bg-white hover:bg-slate-100"
                    @click.stop
                  >
                    View account
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Transactions summary across all accounts -->
      <section class="space-y-3">
        <h2 class="text-lg font-semibold text-slate-900">Transactions</h2>

        <p
          v-if="transactions.length === 0"
          class="text-sm text-slate-600 bg-white border border-dashed border-slate-200 rounded-lg p-4"
        >
          No transactions yet for this customer. Make a deposit, withdrawal, or transfer to see them here.
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
                  {{ tx.source_account_id ?? '—' }}
                </td>
                <td class="px-2 py-1 font-mono text-slate-500">
                  {{ tx.target_account_id ?? '—' }}
                </td>
                <td
                  class="px-2 py-1 font-mono"
                  :class="tx.status === 'success' ? 'text-emerald-600' : 'text-rose-600'"
                >
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
