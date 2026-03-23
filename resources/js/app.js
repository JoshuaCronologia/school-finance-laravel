import { createApp, h } from 'vue';
import axios from 'axios';

import '../css/app.css';

/* ----------------------------------------------------------------
   Axios defaults
   ---------------------------------------------------------------- */
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.axios = axios;

/* ----------------------------------------------------------------
   Vue 3 application
   ---------------------------------------------------------------- */
const app = createApp({});

/* ----------------------------------------------------------------
   Auto-register Vue components from ./components directory
   Requires: import.meta.glob (Vite)
   Convention:
     components/StatCard.vue      -> <StatCard />
     components/ui/Badge.vue      -> <UiBadge />
   ---------------------------------------------------------------- */
const componentFiles = import.meta.glob('./components/**/*.vue', { eager: true });

Object.entries(componentFiles).forEach(([path, module]) => {
    // ./components/StatCard.vue  ->  StatCard
    // ./components/ui/Badge.vue  ->  UiBadge
    const name = path
        .replace('./components/', '')
        .replace(/\.vue$/, '')
        .split('/')
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join('');

    app.component(name, module.default || module);
});

/* ----------------------------------------------------------------
   Global properties
   ---------------------------------------------------------------- */
app.config.globalProperties.$axios = axios;

/**
 * Format a number as Philippine Peso currency.
 */
app.config.globalProperties.$currency = (value) => {
    if (value === null || value === undefined) return '₱0.00';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(value);
};

/**
 * Format a date string.
 */
app.config.globalProperties.$formatDate = (dateStr, options = {}) => {
    if (!dateStr) return '';
    const defaults = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateStr).toLocaleDateString('en-PH', { ...defaults, ...options });
};

/* ----------------------------------------------------------------
   Mount
   ---------------------------------------------------------------- */
app.mount('#app');
