<template>
  <div class="stat-card">
    <div class="flex items-start justify-between">
      <div class="min-w-0 flex-1">
        <p class="stat-card-label">{{ label }}</p>
        <p class="stat-card-value">
          <span v-if="prefix">{{ prefix }}</span>{{ displayValue }}<span v-if="suffix">{{ suffix }}</span>
        </p>
        <p v-if="subtitle" class="stat-card-trend mt-1 text-secondary-500">{{ subtitle }}</p>
        <p v-if="trend !== null" :class="['stat-card-trend', trend >= 0 ? 'stat-card-trend--up' : 'stat-card-trend--down']">
          <svg v-if="trend >= 0" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
          <svg v-else class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" /></svg>
          {{ Math.abs(trend) }}%
          <span class="text-secondary-400 ml-1">vs last period</span>
        </p>
      </div>
      <div v-if="$slots.icon" class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" :class="iconColorClass">
        <slot name="icon" />
      </div>
    </div>
    <slot />
  </div>
</template>

<script>
export default {
  name: 'StatCard',
  props: {
    label:    { type: String, required: true },
    value:    { type: [Number, String], required: true },
    prefix:   { type: String, default: '' },
    suffix:   { type: String, default: '' },
    subtitle: { type: String, default: null },
    trend:    { type: Number, default: null },
    color:    { type: String, default: 'blue' },
    animate:  { type: Boolean, default: true },
    decimals: { type: Number, default: 0 },
    currency: { type: Boolean, default: false },
  },
  data() {
    return {
      animatedValue: 0,
    };
  },
  computed: {
    iconColorClass() {
      const map = {
        blue:   'bg-primary-50 text-primary-600',
        green:  'bg-success-50 text-success-600',
        red:    'bg-danger-50 text-danger-500',
        yellow: 'bg-warning-50 text-warning-600',
        purple: 'bg-purple-50 text-purple-600',
        indigo: 'bg-indigo-50 text-indigo-600',
        gray:   'bg-secondary-100 text-secondary-600',
      };
      return map[this.color] || map.blue;
    },
    numericValue() {
      return typeof this.value === 'number' ? this.value : parseFloat(this.value) || 0;
    },
    displayValue() {
      if (typeof this.value === 'string' && isNaN(this.value)) {
        return this.value;
      }
      const val = this.animate ? this.animatedValue : this.numericValue;
      if (this.currency) {
        return new Intl.NumberFormat('en-PH', {
          style: 'currency',
          currency: 'PHP',
          minimumFractionDigits: 2,
        }).format(val);
      }
      return this.decimals > 0
        ? val.toFixed(this.decimals)
        : new Intl.NumberFormat('en-PH').format(Math.round(val));
    },
  },
  watch: {
    value() {
      this.countUp();
    },
  },
  mounted() {
    if (this.animate && typeof this.value === 'number') {
      this.countUp();
    } else {
      this.animatedValue = this.numericValue;
    }
  },
  methods: {
    countUp() {
      const target = this.numericValue;
      const duration = 800;
      const start = this.animatedValue;
      const diff = target - start;
      const startTime = performance.now();

      const step = (now) => {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // ease-out cubic
        const eased = 1 - Math.pow(1 - progress, 3);
        this.animatedValue = start + diff * eased;
        if (progress < 1) {
          requestAnimationFrame(step);
        } else {
          this.animatedValue = target;
        }
      };
      requestAnimationFrame(step);
    },
  },
};
</script>
