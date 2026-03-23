<template>
  <div class="relative" ref="wrapper">
    <!-- Input -->
    <div class="relative">
      <input
        ref="input"
        v-model="query"
        type="text"
        :placeholder="placeholder"
        class="form-input pr-8"
        :class="{ 'border-primary-500 ring-1 ring-primary-500': isOpen }"
        @focus="open"
        @input="onInput"
        @keydown.down.prevent="highlightNext"
        @keydown.up.prevent="highlightPrev"
        @keydown.enter.prevent="selectHighlighted"
        @keydown.escape="close"
        autocomplete="off"
      >
      <button
        v-if="selectedItem"
        class="absolute right-2 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
        @click.stop="clear"
        type="button"
      >
        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
      </button>
      <svg v-else class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-secondary-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
    </div>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <ul
        v-if="isOpen && filteredOptions.length > 0"
        class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-200 max-h-60 overflow-y-auto py-1"
      >
        <li
          v-for="(option, idx) in filteredOptions"
          :key="option[valueKey]"
          class="px-3 py-2 text-sm cursor-pointer transition-colors"
          :class="{
            'bg-primary-50 text-primary-700': idx === highlightedIndex,
            'text-secondary-700 hover:bg-gray-50': idx !== highlightedIndex,
          }"
          @mouseenter="highlightedIndex = idx"
          @click="select(option)"
        >
          <slot name="option" :option="option">
            <span>{{ option[labelKey] }}</span>
            <span v-if="option[subtitleKey]" class="block text-xs text-secondary-400">{{ option[subtitleKey] }}</span>
          </slot>
        </li>
      </ul>
    </Transition>

    <ul v-if="isOpen && filteredOptions.length === 0 && query.length > 0"
        class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-200 py-3">
      <li class="px-3 text-sm text-secondary-400 text-center">No results found</li>
    </ul>

    <!-- Hidden input for form submission -->
    <input v-if="name" type="hidden" :name="name" :value="modelValue">
  </div>
</template>

<script>
export default {
  name: 'SearchableSelect',
  props: {
    modelValue:   { type: [String, Number], default: null },
    options:      { type: Array, default: () => [] },
    labelKey:     { type: String, default: 'label' },
    valueKey:     { type: String, default: 'value' },
    subtitleKey:  { type: String, default: 'subtitle' },
    placeholder:  { type: String, default: 'Select...' },
    name:         { type: String, default: null },
  },
  emits: ['update:modelValue', 'change'],
  data() {
    return {
      query: '',
      isOpen: false,
      highlightedIndex: 0,
    };
  },
  computed: {
    selectedItem() {
      return this.options.find(o => o[this.valueKey] === this.modelValue) || null;
    },
    filteredOptions() {
      if (!this.query) return this.options;
      const q = this.query.toLowerCase();
      return this.options.filter(o => {
        const label = String(o[this.labelKey] || '').toLowerCase();
        const subtitle = String(o[this.subtitleKey] || '').toLowerCase();
        return label.includes(q) || subtitle.includes(q);
      });
    },
  },
  watch: {
    modelValue: {
      immediate: true,
      handler() {
        if (this.selectedItem) {
          this.query = this.selectedItem[this.labelKey];
        }
      },
    },
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    open() {
      this.isOpen = true;
      this.highlightedIndex = 0;
      if (this.selectedItem) {
        this.query = '';
      }
    },
    close() {
      this.isOpen = false;
      if (this.selectedItem) {
        this.query = this.selectedItem[this.labelKey];
      } else {
        this.query = '';
      }
    },
    onInput() {
      this.isOpen = true;
      this.highlightedIndex = 0;
    },
    select(option) {
      this.$emit('update:modelValue', option[this.valueKey]);
      this.$emit('change', option);
      this.query = option[this.labelKey];
      this.isOpen = false;
    },
    clear() {
      this.$emit('update:modelValue', null);
      this.$emit('change', null);
      this.query = '';
      this.$refs.input.focus();
    },
    highlightNext() {
      if (this.highlightedIndex < this.filteredOptions.length - 1) {
        this.highlightedIndex++;
      }
    },
    highlightPrev() {
      if (this.highlightedIndex > 0) {
        this.highlightedIndex--;
      }
    },
    selectHighlighted() {
      if (this.filteredOptions[this.highlightedIndex]) {
        this.select(this.filteredOptions[this.highlightedIndex]);
      }
    },
    handleClickOutside(e) {
      if (this.$refs.wrapper && !this.$refs.wrapper.contains(e.target)) {
        this.close();
      }
    },
  },
};
</script>
