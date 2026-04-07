<template>
  <div class="relative" :style="{ height: height }">
    <Doughnut v-if="loaded && hasData" :data="chartData" :options="chartOptions" />
  </div>
</template>

<script>
import { Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

export default {
  name: 'DoughnutChart',
  components: { Doughnut },
  props: {
    labels:   { type: Array, default: function () { return []; } },
    data:     { type: Array, default: function () { return []; } },
    title:    { type: String, default: '' },
    height:   { type: String, default: '280px' },
    colors:   { type: Array, default: null },
    currency: { type: Boolean, default: false },
    cutout:   { type: String, default: '65%' },
  },
  data() {
    return { loaded: false };
  },
  computed: {
    hasData() {
      return this.data && this.data.length > 0;
    },
    chartData() {
      if (!this.hasData) return { labels: [], datasets: [] };
      const defaultColors = [
        '#2563eb', '#16a34a', '#eab308', '#ef4444',
        '#a855f7', '#06b6d4', '#f97316', '#ec4899',
      ];
      return {
        labels: this.labels,
        datasets: [{
          data: this.data,
          backgroundColor: this.colors || defaultColors.slice(0, this.data.length),
          borderWidth: 2,
          borderColor: '#ffffff',
          hoverOffset: 6,
        }],
      };
    },
    chartOptions() {
      const self = this;
      return {
        responsive: true,
        maintainAspectRatio: false,
        cutout: this.cutout,
        plugins: {
          title: {
            display: !!this.title,
            text: this.title,
            font: { size: 14, weight: '600' },
            color: '#334155',
          },
          legend: {
            position: 'bottom',
            labels: { usePointStyle: true, padding: 14, font: { size: 11 } },
          },
          tooltip: {
            backgroundColor: '#0f172a',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label(ctx) {
                let val = ctx.parsed;
                if (self.currency) {
                  val = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);
                }
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                return `${ctx.label}: ${val} (${pct}%)`;
              },
            },
          },
        },
      };
    },
  },
  mounted() {
    this.loaded = true;
  },
};
</script>
