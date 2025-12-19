import { ref } from 'vue';

const toasts = ref([]);
const MAX_TOASTS = 5;
let toastId = 0;
const timers = new Map();

export function useToast() {
  const showToast = (message, type = 'success', duration = 5000) => {
    // Limit max toasts
    if (toasts.value.length >= MAX_TOASTS) {
      // Remove oldest toast
      const oldest = toasts.value.shift();
      if (oldest && timers.has(oldest.id)) {
        clearTimeout(timers.get(oldest.id));
        timers.delete(oldest.id);
      }
    }

    const id = toastId++;
    const toast = {
      id,
      message,
      type, // 'success', 'error', 'info', 'warning'
      duration,
      remaining: duration,
      paused: false,
      startTime: Date.now(),
    };

    toasts.value.push(toast);

    // Auto remove after duration
    const timer = setTimeout(() => {
      removeToast(id);
    }, duration);
    timers.set(id, timer);

    return id;
  };

  const removeToast = (id) => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
    if (timers.has(id)) {
      clearTimeout(timers.get(id));
      timers.delete(id);
    }
  };

  const pauseToast = (id) => {
    const toast = toasts.value.find((t) => t.id === id);
    if (toast && !toast.paused) {
      toast.paused = true;
      const elapsed = Date.now() - toast.startTime;
      toast.remaining = toast.remaining - elapsed;
      
      if (timers.has(id)) {
        clearTimeout(timers.get(id));
      }
    }
  };

  const resumeToast = (id) => {
    const toast = toasts.value.find((t) => t.id === id);
    if (toast && toast.paused) {
      toast.paused = false;
      toast.startTime = Date.now();
      
      const timer = setTimeout(() => {
        removeToast(id);
      }, toast.remaining);
      timers.set(id, timer);
    }
  };

  const success = (message, duration) => showToast(message, 'success', duration);
  const error = (message, duration) => showToast(message, 'error', duration || 7000);
  const info = (message, duration) => showToast(message, 'info', duration);
  const warning = (message, duration) => showToast(message, 'warning', duration);

  return {
    toasts,
    showToast,
    removeToast,
    pauseToast,
    resumeToast,
    success,
    error,
    info,
    warning,
  };
}

