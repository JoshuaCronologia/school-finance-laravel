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

// Alpine.js manages its own <template x-for> / <template x-if> elements.
// Tell Vue's runtime compiler to treat them as custom elements so it
// does not flag them as invalid HTML nesting (e.g. <template> in <tbody>).
app.config.compilerOptions.isCustomElement = (tag) => {
    return false;
};
app.config.warnHandler = (msg, instance, trace) => {
    // Suppress Vue warnings about Alpine-managed <template> and x-data properties
    if (msg.includes('<template> cannot be child of') ||
        msg.includes('was accessed during render but is not defined')) {
        return;
    }
    console.warn(`[Vue warn]: ${msg}${trace}`);
};


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
// Vue was previously mounted on the entire #app container, which also holds
// Alpine-driven pages. Mounting Vue there caused Vue to re-render the DOM and
// Alpine to lose its scope (_x_dataStack became null), triggering many Alpine
// expression errors. To avoid stepping on Alpine, only mount Vue when an
// explicit root is present.
const mountTarget = document.querySelector('[data-vue-root], #vue-app');
if (mountTarget) {
    app.mount(mountTarget);
}
