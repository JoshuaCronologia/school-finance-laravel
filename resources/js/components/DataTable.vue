<template>
  <div class="card">
    <!-- Toolbar -->
    <div class="card-header">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full">
        <!-- Search -->
        <div v-if="searchable" class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2 w-full sm:w-72">
          <svg class="w-4 h-4 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
          <input
            v-model="search"
            type="text"
            :placeholder="searchPlaceholder"
            class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-400 focus:outline-none w-full"
          >
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <slot name="actions" />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              :class="[col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left', col.sortable !== false ? 'cursor-pointer select-none hover:text-secondary-700' : '']"
              @click="col.sortable !== false && toggleSort(col.key)"
            >
              <span class="inline-flex items-center gap-1">
                {{ col.label }}
                <template v-if="col.sortable !== false && sortKey === col.key">
                  <svg v-if="sortOrder === 'asc'" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>
                  <svg v-else class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </template>
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="paginatedRows.length === 0">
            <td :colspan="columns.length" class="text-center py-8 text-secondary-400">
              {{ emptyText }}
            </td>
          </tr>
          <tr v-for="(row, idx) in paginatedRows" :key="row.id || idx">
            <td v-for="col in columns" :key="col.key" :class="col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : ''">
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                {{ row[col.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="card-footer">
      <div class="flex items-center justify-between text-sm text-secondary-500">
        <span>
          Showing {{ startRow }} to {{ endRow }} of {{ filteredRows.length }} entries
        </span>
        <div class="flex items-center gap-1">
          <button
            class="btn-icon"
            :disabled="currentPage <= 1"
            @click="currentPage--"
          >
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
          </button>
          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page === '...'"
              class="px-2 py-1 text-secondary-400 cursor-default"
              disabled
            >...</button>
            <button
              v-else
              class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
              :class="page === currentPage ? 'bg-primary-600 text-white' : 'text-secondary-600 hover:bg-gray-100'"
              @click="currentPage = page"
            >{{ page }}</button>
          </template>
          <button
            class="btn-icon"
            :disabled="currentPage >= totalPages"
            @click="currentPage++"
          >
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DataTable',
  props: {
    columns:           { type: Array, required: true },
    rows:              { type: Array, default: () => [] },
    searchable:        { type: Boolean, default: true },
    searchPlaceholder: { type: String, default: 'Search...' },
    perPage:           { type: Number, default: 15 },
    emptyText:         { type: String, default: 'No records found.' },
  },
  data() {
    return {
      search: '',
      sortKey: null,
      sortOrder: 'asc',
      currentPage: 1,
    };
  },
  computed: {
    filteredRows() {
      let data = [...this.rows];

      // Search
      if (this.search) {
        const q = this.search.toLowerCase();
        data = data.filter(row =>
          this.columns.some(col => {
            const val = row[col.key];
            return val != null && String(val).toLowerCase().includes(q);
          })
        );
      }

      // Sort
      if (this.sortKey) {
        data.sort((a, b) => {
          let aVal = a[this.sortKey];
          let bVal = b[this.sortKey];
          if (aVal == null) aVal = '';
          if (bVal == null) bVal = '';
          if (typeof aVal === 'number' && typeof bVal === 'number') {
            return this.sortOrder === 'asc' ? aVal - bVal : bVal - aVal;
          }
          const cmp = String(aVal).localeCompare(String(bVal), 'en', { numeric: true });
          return this.sortOrder === 'asc' ? cmp : -cmp;
        });
      }
      return data;
    },
    totalPages() {
      return Math.ceil(this.filteredRows.length / this.perPage);
    },
    startRow() {
      return (this.currentPage - 1) * this.perPage + 1;
    },
    endRow() {
      return Math.min(this.currentPage * this.perPage, this.filteredRows.length);
    },
    paginatedRows() {
      const start = (this.currentPage - 1) * this.perPage;
      return this.filteredRows.slice(start, start + this.perPage);
    },
    visiblePages() {
      const total = this.totalPages;
      const current = this.currentPage;
      if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
      const pages = [];
      pages.push(1);
      if (current > 3) pages.push('...');
      for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
        pages.push(i);
      }
      if (current < total - 2) pages.push('...');
      pages.push(total);
      return pages;
    },
  },
  watch: {
    search() {
      this.currentPage = 1;
    },
    rows() {
      this.currentPage = 1;
    },
  },
  methods: {
    toggleSort(key) {
      if (this.sortKey === key) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortKey = key;
        this.sortOrder = 'asc';
      }
    },
  },
};
</script>
