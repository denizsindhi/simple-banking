<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useToast } from '../composables/useToast';

const { toasts, removeToast, pauseToast, resumeToast } = useToast();
const progress = ref(new Map());
let progressInterval = null;

const getIcon = (type) => {
  const icons = {
    success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
  };
  return icons[type] || icons.info;
};

const getProgress = (toast) => {
  if (!progress.value.has(toast.id)) {
    progress.value.set(toast.id, 100);
  }
  return progress.value.get(toast.id);
};

const updateProgress = () => {
  toasts.value.forEach((toast) => {
    if (toast.paused) {
      const percent = (toast.remaining / toast.duration) * 100;
      progress.value.set(toast.id, percent);
    } else {
      const elapsed = Date.now() - toast.startTime;
      const remaining = Math.max(0, toast.remaining - elapsed);
      const percent = (remaining / toast.duration) * 100;
      progress.value.set(toast.id, percent);
    }
  });
};

onMounted(() => {
  progressInterval = setInterval(updateProgress, 50);
});

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval);
  }
});
</script>

<template>
  <div
    class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none max-w-md w-full sm:w-auto"
    aria-live="polite"
    aria-atomic="true"
  >
    <transition-group
      name="toast"
      tag="div"
      class="flex flex-col gap-2"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto"
        @mouseenter="pauseToast(toast.id)"
        @mouseleave="resumeToast(toast.id)"
      >
        <div
          :class="{
            'bg-emerald-50 border-emerald-300 text-emerald-800': toast.type === 'success',
            'bg-rose-50 border-rose-300 text-rose-800': toast.type === 'error',
            'bg-blue-50 border-blue-300 text-blue-800': toast.type === 'info',
            'bg-amber-50 border-amber-300 text-amber-800': toast.type === 'warning',
          }"
          class="relative flex items-start justify-between gap-3 rounded-lg border-2 px-4 py-3 text-sm shadow-xl backdrop-blur-sm min-w-[280px] sm:min-w-[320px] overflow-hidden group"
        >
          <!-- Progress bar -->
          <div
            :class="{
              'bg-emerald-400': toast.type === 'success',
              'bg-rose-400': toast.type === 'error',
              'bg-blue-400': toast.type === 'info',
              'bg-amber-400': toast.type === 'warning',
            }"
            class="absolute bottom-0 left-0 h-1 transition-all duration-100 ease-linear"
            :style="{ width: `${getProgress(toast)}%` }"
          ></div>

          <!-- Icon -->
          <div
            :class="{
              'text-emerald-600': toast.type === 'success',
              'text-rose-600': toast.type === 'error',
              'text-blue-600': toast.type === 'info',
              'text-amber-600': toast.type === 'warning',
            }"
            class="shrink-0 mt-0.5"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(toast.type)" />
            </svg>
          </div>

          <!-- Message -->
          <p class="font-semibold flex-1 leading-relaxed">{{ toast.message }}</p>

          <!-- Close button -->
          <button
            @click="removeToast(toast.id)"
            :class="{
              'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100': toast.type === 'success',
              'text-rose-600 hover:text-rose-800 hover:bg-rose-100': toast.type === 'error',
              'text-blue-600 hover:text-blue-800 hover:bg-blue-100': toast.type === 'info',
              'text-amber-600 hover:text-amber-800 hover:bg-amber-100': toast.type === 'warning',
            }"
            class="shrink-0 rounded p-1 transition-colors opacity-70 hover:opacity-100"
            aria-label="Dismiss"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.7, 0, 0.84, 0);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(120%) scale(0.95);
}

.toast-enter-to {
  opacity: 1;
  transform: translateX(0) scale(1);
}

.toast-leave-from {
  opacity: 1;
  transform: translateX(0) scale(1);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(120%) scale(0.95);
}

.toast-move {
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>

