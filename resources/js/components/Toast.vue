<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[60] space-y-2 w-80">
      <TransitionGroup
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-x-8"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-8"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="alertClass(toast.type)"
          class="alert shadow-lg cursor-pointer"
          @click="dismiss(toast.id)"
        >
          <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(toast.type)" />
          </svg>
          <div class="flex-1 text-sm">{{ toast.message }}</div>
          <button class="flex-shrink-0 -mr-1 -mt-1 p-1 rounded hover:bg-black/5">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script>
let toastId = 0;

export default {
  name: 'Toast',
  data() {
    return {
      toasts: [],
    };
  },
  mounted() {
    window.addEventListener('toast', this.handleToast);
  },
  beforeUnmount() {
    window.removeEventListener('toast', this.handleToast);
  },
  methods: {
    handleToast(e) {
      this.add(e.detail);
    },
    add({ type = 'success', message = '', duration = 5000 }) {
      const id = ++toastId;
      this.toasts.push({ id, type, message });
      if (duration > 0) {
        setTimeout(() => this.dismiss(id), duration);
      }
    },
    dismiss(id) {
      this.toasts = this.toasts.filter(t => t.id !== id);
    },
    alertClass(type) {
      const map = {
        success: 'alert-success',
        warning: 'alert-warning',
        danger:  'alert-danger',
        error:   'alert-danger',
        info:    'alert-info',
      };
      return map[type] || 'alert-info';
    },
    iconPath(type) {
      const map = {
        success: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        warning: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        danger:  'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        error:   'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        info:    'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
      };
      return map[type] || map.info;
    },
  },
};
</script>
