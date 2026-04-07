import { createApp } from 'vue';
import axios from 'axios';

import '../css/app.css';

/* ----------------------------------------------------------------
   Axios defaults
   ---------------------------------------------------------------- */
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

var token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.axios = axios;

/* ----------------------------------------------------------------
   Vue components (explicit imports for Laravel Mix)
   ---------------------------------------------------------------- */
import BarChart from './components/BarChart.vue';
import ConfirmDialog from './components/ConfirmDialog.vue';
import DataTable from './components/DataTable.vue';
import DoughnutChart from './components/DoughnutChart.vue';
import LineChart from './components/LineChart.vue';
import Modal from './components/Modal.vue';
import SearchableSelect from './components/SearchableSelect.vue';
import StatCard from './components/StatCard.vue';
import Toast from './components/Toast.vue';

/* ----------------------------------------------------------------
   Vue 3 application
   ---------------------------------------------------------------- */
var app = createApp({});

app.config.compilerOptions.isCustomElement = function () { return false; };
app.config.warnHandler = function (msg) {
    if (msg.indexOf('<template> cannot be child of') !== -1 ||
        msg.indexOf('was accessed during render but is not defined') !== -1) {
        return;
    }
    console.warn('[Vue warn]: ' + msg);
};

// Register components
app.component('BarChart', BarChart);
app.component('ConfirmDialog', ConfirmDialog);
app.component('DataTable', DataTable);
app.component('DoughnutChart', DoughnutChart);
app.component('LineChart', LineChart);
app.component('Modal', Modal);
app.component('SearchableSelect', SearchableSelect);
app.component('StatCard', StatCard);
app.component('Toast', Toast);

/* ----------------------------------------------------------------
   Global properties
   ---------------------------------------------------------------- */
app.config.globalProperties.$axios = axios;

app.config.globalProperties.$currency = function (value) {
    if (value === null || value === undefined) return '₱0.00';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(value);
};

app.config.globalProperties.$formatDate = function (dateStr, options) {
    if (!dateStr) return '';
    var defaults = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateStr).toLocaleDateString('en-PH', Object.assign({}, defaults, options || {}));
};

/* ----------------------------------------------------------------
   Mount
   ---------------------------------------------------------------- */
function mountVue() {
    var mountTarget = document.querySelector('[data-vue-root], #vue-app');
    if (mountTarget && !mountTarget.__vue_app__) {
        app.mount(mountTarget);
    }
}

mountVue();
document.addEventListener('turbo:load', mountVue);
