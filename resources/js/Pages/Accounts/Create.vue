<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  customers: {
    type: Array,
    required: true,
  },
});

const form = reactive({
  customer_id: props.customers[0]?.id ?? '',
  type: 'personal',
});

function submit() {
  router.post('/accounts', {
    customer_id: Number(form.customer_id),
    type: form.type,
  });
}
</script>

<template>
  <AppLayout>
    <main class="max-w-md space-y-4">
      <h1 class="text-xl font-semibold text-slate-900">Open Account</h1>

      <section
        v-if="customers.length === 0"
        class="text-sm text-slate-600 bg-white border border-dashed border-slate-200 rounded-lg p-4"
      >
        No customers yet. Please create a customer first from the Customers page,
        then open an account for them here.
      </section>

      <form
        v-else
        @submit.prevent="submit"
        class="space-y-3 bg-white border border-slate-200 rounded-lg p-4 shadow-sm"
      >
        <div class="space-y-1">
          <label class="text-sm font-medium text-slate-700">Customer</label>
          <select
            v-model="form.customer_id"
            class="w-full border border-slate-300 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            required
          >
            <option
              v-for="customer in customers"
              :key="customer.id"
              :value="customer.id"
            >
              #{{ customer.id }} – {{ customer.name }}
            </option>
          </select>
        </div>

        <div class="space-y-1">
          <label class="text-sm font-medium text-slate-700">Account Type</label>
          <select
            v-model="form.type"
            class="w-full border border-slate-300 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
          >
            <option value="personal">Personal</option>
            <option value="savings">Savings</option>
            <option value="business">Business</option>
          </select>
        </div>

        <button
          type="submit"
          class="px-3 py-1.5 rounded-md bg-emerald-600 text-white text-sm shadow-sm hover:bg-emerald-700"
        >
          Create
        </button>
      </form>
    </main>
  </AppLayout>
</template>
