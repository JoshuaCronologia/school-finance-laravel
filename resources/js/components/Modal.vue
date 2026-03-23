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
      <div v-if="modelValue" class="modal-overlay" @keydown.escape="close">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="closeOnBackdrop && close()"></div>

        <!-- Panel -->
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          enter-to-class="opacity-100 translate-y-0 sm:scale-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 translate-y-0 sm:scale-100"
          leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
          <div v-if="modelValue" class="modal-content relative" :class="maxWidthClass">
            <!-- Header -->
            <div v-if="title" class="modal-header">
              <h3 class="text-lg font-semibold text-secondary-900">{{ title }}</h3>
              <button @click="close" class="btn-icon">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
              <slot />
            </div>

            <!-- Footer -->
            <div v-if="$slots.footer" class="modal-footer">
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
export default {
  name: 'Modal',
  props: {
    modelValue:      { type: Boolean, default: false },
    title:           { type: String, default: '' },
    maxWidth:        { type: String, default: '2xl' },
    closeOnBackdrop: { type: Boolean, default: true },
  },
  emits: ['update:modelValue'],
  computed: {
    maxWidthClass() {
      const map = {
        sm: 'max-w-sm', md: 'max-w-md', lg: 'max-w-lg',
        xl: 'max-w-xl', '2xl': 'max-w-2xl', '3xl': 'max-w-3xl',
        '4xl': 'max-w-4xl', '5xl': 'max-w-5xl',
      };
      return map[this.maxWidth] || 'max-w-2xl';
    },
  },
  watch: {
    modelValue(val) {
      document.body.style.overflow = val ? 'hidden' : '';
    },
  },
  methods: {
    close() {
      this.$emit('update:modelValue', false);
    },
  },
};
</script>
