<template>
  <div class="relative" :style="{ height: height }">
    <Line v-if="loaded" :data="chartData" :options="chartOptions" />
  </div>
</template>

<script>
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

export default {
  name: 'LineChart',
  components: { Line },
  props: {
    labels:   { type: Array, required: true },
    datasets: { type: Array, required: true },
    title:    { type: String, default: '' },
    height:   { type: String, default: '300px' },
    currency: { type: Boolean, default: false },
    fill:     { type: Boolean, default: false },
  },
  data() {
    return { loaded: false };
  },
  computed: {
    chartData() {
      const defaultColors = [
        { line: '#2563eb', bg: 'rgba(37, 99, 235, 0.1)' },
        { line: '#16a34a', bg: 'rgba(22, 163, 74, 0.1)' },
        { line: '#eab308', bg: 'rgba(234, 179, 8, 0.1)' },
        { line: '#ef4444', bg: 'rgba(239, 68, 68, 0.1)' },
      ];

      return {
        labels: this.labels,
        datasets: this.datasets.map((ds, i) => ({
          label: ds.label || `Dataset ${i + 1}`,
          data: ds.data,
          borderColor: ds.borderColor || defaultColors[i % defaultColors.length].line,
          backgroundColor: ds.backgroundColor || defaultColors[i % defaultColors.length].bg,
          borderWidth: ds.borderWidth ?? 2,
          pointRadius: ds.pointRadius ?? 3,
          pointHoverRadius: ds.pointHoverRadius ?? 5,
          tension: ds.tension ?? 0.3,
          fill: ds.fill ?? this.fill,
          ...ds,
        })),
      };
    },
    chartOptions() {
      const self = this;
      return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
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
            grid: { display: false },
            ticks: { font: { size: 11 }, color: '#64748b' },
          },
          y: {
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
