<script setup>
import { computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  accounts: {
    type: Array,
    required: true,
  },
  customers: {
    type: Array,
    required: true,
  },
});

const form = reactive({
  account_id: props.accounts[0]?.id ?? '',
  amount: '',
});

function customerName(account) {
  const customer = props.customers.find(c => c.id === account.customer_id);
  return customer ? customer.name : `Customer ${account.customer_id}`;
}

const selectedAccount = computed(() =>
  props.accounts.find(a => a.id === Number(form.account_id)) ?? null,
);

function submit() {
  const cents = Math.round(Number(form.amount) * 100);
  router.post('/transactions/deposit', {
    account_id: Number(form.account_id),
    amount: cents,
  });
}
</script>

<template>
  <AppLayout>
    <main class="max-w-md space-y-4">
      <h1 class="text-xl font-semibold text-slate-900">Deposit</h1>

      <section
        v-if="accounts.length === 0"
        class="text-sm text-slate-600 bg-white border border-dashed border-slate-200 rounded-lg p-4"
      >
        No accounts yet. Please create an account first, then make a deposit.
      </section>

      <form
        v-else
        @submit.prevent="submit"
        class="space-y-3 bg-white border border-slate-200 rounded-lg p-4 shadow-sm"
      >
        <div class="space-y-1">
          <label class="text-sm font-medium text-slate-700">Target Account</label>
          <select
            v-model="form.account_id"
            class="w-full border border-slate-300 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            required
          >
            <option
              v-for="account in accounts"
              :key="account.id"
              :value="account.id"
            >
              #{{ account.id }} – {{ customerName(account) }} ({{ account.type }})
            </option>
          </select>
          <p v-if="selectedAccount" class="text-xs text-slate-500 mt-1">
            Current balance:
            <span class="font-mono">
              {{ (selectedAccount.balance / 100).toFixed(2) }} {{ selectedAccount.currency }}
            </span>
          </p>
        </div>

        <div class="space-y-1">
          <label class="text-sm font-medium text-slate-700">Amount (EUR)</label>
          <input
            v-model="form.amount"
            type="number"
            step="0.01"
            min="0.01"
            class="w-full border border-slate-300 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            required
          />
        </div>

        <button
          type="submit"
          class="px-3 py-1.5 rounded-md bg-emerald-600 text-white text-sm shadow-sm hover:bg-emerald-700"
        >
          Deposit
        </button>
      </form>
    </main>
  </AppLayout>
</template>
