<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="visible" class="modal-overlay">
        <div class="absolute inset-0 bg-black/50" @click="cancel"></div>

        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="visible" class="modal-content max-w-md relative">
            <div class="p-6 text-center">
              <!-- Icon -->
              <div class="mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-4" :class="iconBgClass">
                <svg class="w-6 h-6" :class="iconTextClass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath" />
                </svg>
              </div>

              <!-- Title & Message -->
              <h3 class="text-lg font-semibold text-secondary-900 mb-2">{{ title }}</h3>
              <p class="text-sm text-secondary-500 mb-6">{{ message }}</p>

              <!-- Buttons -->
              <div class="flex items-center justify-center gap-3">
                <button @click="cancel" class="btn-secondary">
                  {{ cancelText }}
                </button>
                <button @click="confirm" :class="confirmButtonClass">
                  {{ confirmText }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
export default {
  name: 'ConfirmDialog',
  props: {
    modelValue:  { type: Boolean, default: false },
    title:       { type: String, default: 'Confirm Action' },
    message:     { type: String, default: 'Are you sure you want to proceed? This action cannot be undone.' },
    type:        { type: String, default: 'danger' },  // danger, warning, info
    confirmText: { type: String, default: 'Confirm' },
    cancelText:  { type: String, default: 'Cancel' },
  },
  emits: ['update:modelValue', 'confirm', 'cancel'],
  computed: {
    visible() {
      return this.modelValue;
    },
    iconBgClass() {
      const map = {
        danger:  'bg-danger-50',
        warning: 'bg-warning-50',
        info:    'bg-primary-50',
      };
      return map[this.type] || map.danger;
    },
    iconTextClass() {
      const map = {
        danger:  'text-danger-500',
        warning: 'text-warning-600',
        info:    'text-primary-600',
      };
      return map[this.type] || map.danger;
    },
    iconPath() {
      const map = {
        danger:  'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        warning: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        info:    'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
      };
      return map[this.type] || map.danger;
    },
    confirmButtonClass() {
      const map = {
        danger:  'btn-danger',
        warning: 'btn-primary',
        info:    'btn-primary',
      };
      return map[this.type] || 'btn-danger';
    },
  },
  watch: {
    visible(val) {
      document.body.style.overflow = val ? 'hidden' : '';
    },
  },
  methods: {
    confirm() {
      this.$emit('confirm');
      this.$emit('update:modelValue', false);
    },
    cancel() {
      this.$emit('cancel');
      this.$emit('update:modelValue', false);
    },
  },
};
</script>
