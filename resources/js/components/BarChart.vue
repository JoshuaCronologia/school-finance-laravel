<template>
  <div class="relative" :style="{ height: height }">
    <Bar v-if="loaded" :data="chartData" :options="chartOptions" />
  </div>
</template>

<script>
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

export default {
  name: 'BarChart',
  components: { Bar },
  props: {
    labels:   { type: Array, required: true },
    datasets: { type: Array, required: true },
    title:    { type: String, default: '' },
    height:   { type: String, default: '300px' },
    stacked:  { type: Boolean, default: false },
    currency: { type: Boolean, default: false },
  },
  data() {
    return { loaded: false };
  },
  computed: {
    chartData() {
      const defaultColors = [
        { bg: 'rgba(37, 99, 235, 0.8)',  border: '#2563eb' },
        { bg: 'rgba(22, 163, 74, 0.8)',  border: '#16a34a' },
        { bg: 'rgba(234, 179, 8, 0.8)',  border: '#eab308' },
        { bg: 'rgba(239, 68, 68, 0.8)',  border: '#ef4444' },
        { bg: 'rgba(168, 85, 247, 0.8)', border: '#a855f7' },
      ];

      return {
        labels: this.labels,
        datasets: this.datasets.map((ds, i) => ({
          label: ds.label || `Dataset ${i + 1}`,
          data: ds.data,
          backgroundColor: ds.backgroundColor || defaultColors[i % defaultColors.length].bg,
          borderColor: ds.borderColor || defaultColors[i % defaultColors.length].border,
          borderWidth: ds.borderWidth ?? 1,
          borderRadius: ds.borderRadius ?? 4,
          ...ds,
        })),
      };
    },
    chartOptions() {
      const self = this;
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          title: {
            display: !!this.title,
            text: this.title,
            font: { size: 14, weight: '600' },
            color: '#334155',
          },
          legend: {
            position: 'bottom',
            labels: { usePointStyle: true, padding: 16, font: { size: 12 } },
          },
          tooltip: {
            backgroundColor: '#0f172a',
            titleFont: { size: 12 },
            bodyFont: { size: 12 },
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label(ctx) {
                let val = ctx.parsed.y;
                if (self.currency) {
                  val = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);
                }
                return `${ctx.dataset.label}: ${val}`;
              },
            },
          },
        },
        scales: {
          x: {
            stacked: this.stacked,
            grid: { display: false },
            ticks: { font: { size: 11 }, color: '#64748b' },
          },
          y: {
            stacked: this.stacked,
            grid: { color: '#f1f5f9' },
            ticks: {
              font: { size: 11 },
              color: '#64748b',
              callback(value) {
                if (self.currency) {
                  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', notation: 'compact' }).format(value);
                }
                return value;
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
