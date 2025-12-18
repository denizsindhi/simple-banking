<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// usePage() returns a reactive object; props is reactive in-place
const page = usePage();
const flash = computed(() => (page.props && page.props.flash) ? page.props.flash : {});

const navItems = [
  { label: 'Customers', href: '/customers' },
  { label: 'Open Account', href: '/accounts/create' },
  { label: 'Deposit', href: '/transactions/deposit' },
  { label: 'Withdraw', href: '/transactions/withdraw' },
  { label: 'Transfer', href: '/transactions/transfer' },
];

function isActive(href) {
  return page.url.startsWith(href);
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 flex text-slate-900">
    <!-- Sidebar -->
    <aside
      class="w-64 bg-slate-900 text-slate-50 flex flex-col shadow-xl"
    >
      <div class="px-6 py-5 border-b border-slate-800 flex items-center gap-3">
        <div
          class="h-9 w-9 rounded-xl bg-gradient-to-br from-emerald-400 to-sky-400 flex items-center justify-center text-slate-900 font-bold shadow-lg"
        >
          SB
        </div>
        <div>
          <div class="text-sm font-semibold tracking-wide">
            Simple Bank
          </div>
          <div class="text-xs text-slate-400">
            In-memory playground
          </div>
        </div>
      </div>

      <nav class="flex-1 p-3 space-y-1 text-sm">
        <Link
          v-for="item in navItems"
          :key="item.href"
          :href="item.href"
          class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors"
          :class="isActive(item.href)
            ? 'bg-slate-800 text-emerald-300'
            : 'text-slate-200 hover:bg-slate-800/70 hover:text-white'"
        >
          <span class="w-1.5 h-1.5 rounded-full"
                :class="isActive(item.href) ? 'bg-emerald-300' : 'bg-slate-500'"/>
          <span>{{ item.label }}</span>
        </Link>
      </nav>

      <footer class="px-4 py-3 border-t border-slate-800 text-[11px] text-slate-400">
        State resets when server restarts. No database.
      </footer>
    </aside>

    <!-- Main content -->
    <main class="flex-1 flex flex-col">
      <header class="h-14 border-b border-slate-200 bg-white/80 backdrop-blur flex items-center px-6">
        <h1 class="text-sm font-semibold text-slate-700">
          Banking Sandbox
        </h1>
      </header>

      <section class="flex-1 p-6 overflow-y-auto">
        <div class="max-w-5xl mx-auto space-y-4">
          <!-- Flash messages -->
          <div v-if="flash.success || flash.error" class="space-y-2">
            <div
              v-if="flash.success"
              class="flex items-start gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800 shadow-sm"
            >
              <span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
              <p class="font-medium">{{ flash.success }}</p>
            </div>
            <div
              v-if="flash.error"
              class="flex items-start gap-2 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800 shadow-sm"
            >
              <span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-rose-500"></span>
              <p class="font-medium">{{ flash.error }}</p>
            </div>
          </div>

          <slot />
        </div>
      </section>
    </main>
  </div>
</template>

