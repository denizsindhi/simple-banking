<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  customers: {
    type: Array,
    required: true,
  },
});

// Read CSRF token safely (avoid touching document during SSR)
const csrfToken =
  typeof window !== 'undefined'
    ? document.querySelector('meta[name="csrf-token"]')?.content ?? ''
    : '';
</script>

<template>
  <AppLayout>
    <main class="space-y-6">
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Customers</h1>
          <p class="text-xs text-slate-500 mt-1">
            Manage customer lifecycle: create, block, close.
          </p>
        </div>
        <Link
          href="/customers/create"
          class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-sm shadow-sm hover:bg-emerald-700"
        >
          New Customer
        </Link>
      </header>

      <section
        v-if="customers.length === 0"
        class="text-sm text-slate-600 bg-white border border-dashed border-slate-200 rounded-lg p-4"
      >
        No customers yet. Create one to get started.
      </section>

      <section v-else class="grid gap-3">
        <article
          v-for="customer in customers"
          :key="customer.id"
          class="bg-white border border-slate-200 rounded-lg px-4 py-3 flex items-center justify-between shadow-sm"
        >
          <div>
            <div class="font-medium text-slate-900">
              <Link :href="`/customers/${customer.id}`" class="hover:underline">
                #{{ customer.id }} – {{ customer.name }}
              </Link>
            </div>
            <div class="text-xs text-slate-500 mt-1">
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
            </div>
          </div>
          <div class="flex gap-2">
            <!-- Block: blocked customers cannot perform operations -->
            <form
              method="post"
              :action="`/customers/${customer.id}/block`"
            >
              <input type="hidden" name="_token" :value="csrfToken">
              <button
                type="submit"
                class="px-2 py-1 text-xs rounded-md border border-amber-400 text-amber-700 bg-amber-50 hover:bg-amber-100 disabled:opacity-40"
                :disabled="customer.status !== 'active'"
              >
                Block
              </button>
            </form>

            <!-- Close: customer can be closed only if all accounts are closed, enforced in service -->
            <form
              method="post"
              :action="`/customers/${customer.id}/close`"
            >
              <input type="hidden" name="_token" :value="csrfToken">
              <button
                type="submit"
                class="px-2 py-1 text-xs rounded-md border border-rose-400 text-rose-600 bg-rose-50 hover:bg-rose-100 disabled:opacity-40"
                :disabled="customer.status === 'closed'"
              >
                Close
              </button>
            </form>
          </div>
        </article>
      </section>
    </main>
  </AppLayout>
</template>
