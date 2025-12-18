<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const form = reactive({
  name: '',
});

function submit() {
  router.post('/customers', form);
}
</script>

<template>
  <AppLayout>
    <main class="max-w-md space-y-4">
      <h1 class="text-xl font-semibold text-slate-900">Create Customer</h1>

      <form @submit.prevent="submit" class="space-y-3 bg-white border border-slate-200 rounded-lg p-4 shadow-sm">
        <div class="space-y-1">
          <label class="text-sm font-medium text-slate-700">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full border border-slate-300 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            required
          />
        </div>

        <button
          type="submit"
          class="px-3 py-1.5 rounded-md bg-emerald-600 text-white text-sm shadow-sm hover:bg-emerald-700"
        >
          Save
        </button>
      </form>
    </main>
  </AppLayout>
</template>
