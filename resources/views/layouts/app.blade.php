<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'School Finance ERP') }} &mdash; @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Turbo Drive – instant page transitions without full reload -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8/dist/turbo.es2017-esm.min.js"></script>

    <!-- Alpine.js – plugins must load BEFORE the core -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Re-initialize Alpine after Turbo page transitions -->
    <script>
        document.addEventListener('turbo:render', () => {
            if (window.Alpine) {
                // Destroy then re-init Alpine on the new DOM
                document.querySelectorAll('[x-data]').forEach(el => {
                    if (!el._x_dataStack) window.Alpine.initTree(el);
                });
            }
        });
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased" x-data="{ sidebarOpen: false }">

    {{-- ================================================================
         SIDEBAR
         ================================================================ --}}
    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/50 lg:hidden"
         style="display: none;"></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="sidebar bg-white border-r border-gray-200 transform transition-transform duration-200 ease-in-out lg:translate-x-0 scrollbar-thin">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-200">
            <div class="flex items-center justify-center w-9 h-9 bg-primary-600 rounded-lg">
                {{-- Graduation cap icon --}}
                <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15v-3.75m0 0 5.25 3 5.25-3" />
                </svg>
            </div>
            <div>
                <span class="text-base font-bold text-gray-900 tracking-tight">ORANGEAPPS</span>
                <span class="block text-[10px] font-medium text-gray-500 uppercase tracking-widest">School Finance ERP</span>
            </div>
        </div>

        {{-- Navigation --}}
        @php
            $currentRoute = $currentRoute ?? request()->path();
            $currentRoute = '/' . ltrim($currentRoute, '/');
        @endphp

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            {{-- OVERVIEW --}}
            <p class="sidebar-section-title mt-0">Overview</p>
            <a href="/" class="sidebar-link {{ $currentRoute === '/' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                <span>Finance Dashboard</span>
            </a>
            <a href="/accounting/dashboard" class="sidebar-link {{ $currentRoute === '/accounting/dashboard' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" /></svg>
                <span>Accounting Home</span>
            </a>

            {{-- BUDGET MANAGEMENT --}}
            <p class="sidebar-section-title mt-4">Budget Management</p>
            <a href="/budget/dashboard" class="sidebar-link {{ $currentRoute === '/budget/dashboard' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                <span>Budget Dashboard</span>
            </a>
            <a href="/budget/planning" class="sidebar-link {{ $currentRoute === '/budget/planning' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                <span>Budget Planning</span>
            </a>
            <a href="/budget/allocation" class="sidebar-link {{ $currentRoute === '/budget/allocation' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" /></svg>
                <span>Budget Allocation</span>
            </a>

            {{-- Budget Analysis (collapsible)
            <div x-data="{ open: {{ str_starts_with($currentRoute, '/reports/budget-vs-actual') || str_starts_with($currentRoute, '/reports/monthly-variance') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full justify-between">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>
                        <span>Budget Analysis</span>
                    </span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-white/10 pl-3">
                    <a href="/reports/budget-vs-actual" class="sidebar-link text-xs {{ $currentRoute === '/reports/budget-vs-actual' ? 'sidebar-link--active' : '' }}">Budget vs Actual</a>
                    <a href="/reports/monthly-variance" class="sidebar-link text-xs {{ $currentRoute === '/reports/monthly-variance' ? 'sidebar-link--active' : '' }}">Monthly Variance</a>
                </div>
            </div> --}}

            {{-- ACCOUNTS PAYABLE --}}
            <p class="sidebar-section-title mt-4">Accounts Payable</p>

            {{-- Bills & Disbursements (collapsible) --}}
            <div x-data="{ open: {{ str_starts_with($currentRoute, '/ap/bills') || str_starts_with($currentRoute, '/ap/disbursements') || str_starts_with($currentRoute, '/ap/approval-queue') || str_starts_with($currentRoute, '/ap/payment-processing') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full justify-between">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        <span>Bills & Disbursements</span>
                    </span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-white/10 pl-3">
                    <a href="/ap/bills" class="sidebar-link text-xs {{ $currentRoute === '/ap/bills' ? 'sidebar-link--active' : '' }}">Supplier Bills</a>
                    <a href="/ap/disbursements" class="sidebar-link text-xs {{ $currentRoute === '/ap/disbursements' ? 'sidebar-link--active' : '' }}">Disbursement Requests</a>
                    <a href="/ap/disbursements/create" class="sidebar-link text-xs {{ $currentRoute === '/ap/disbursements/create' ? 'sidebar-link--active' : '' }}">Create Request</a>
                    <a href="/ap/approval-queue" class="sidebar-link text-xs {{ $currentRoute === '/ap/approval-queue' ? 'sidebar-link--active' : '' }}">Approval Queue</a>
                    <a href="/ap/payment-processing" class="sidebar-link text-xs {{ $currentRoute === '/ap/payment-processing' ? 'sidebar-link--active' : '' }}">Payment Processing</a>
                    <a href="/tax/check-writer" class="sidebar-link text-xs {{ $currentRoute === '/tax/check-writer' ? 'sidebar-link--active' : '' }}">Check Writer</a>
                </div>
            </div>

            <a href="/vendors" class="sidebar-link {{ $currentRoute === '/vendors' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                <span>Vendors / Payees</span>
            </a>

            {{-- AP Payments (collapsible) --}}
            <div x-data="{ open: {{ str_starts_with($currentRoute, '/ap/supplier-payments') || str_starts_with($currentRoute, '/ap/aging') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full justify-between">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        <span>AP Payments</span>
                    </span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-white/10 pl-3">
                    <a href="/ap/supplier-payments" class="sidebar-link text-xs {{ $currentRoute === '/ap/supplier-payments' ? 'sidebar-link--active' : '' }}">Supplier Payments</a>
                    <a href="/ap/aging" class="sidebar-link text-xs {{ $currentRoute === '/ap/aging' ? 'sidebar-link--active' : '' }}">AP Aging</a>
                </div>
            </div>

            {{-- ACCOUNTS RECEIVABLE --}}
            <p class="sidebar-section-title mt-4">Accounts Receivable</p>

            {{-- Billing & Collections (collapsible) --}}
            <div x-data="{ open: {{ str_starts_with($currentRoute, '/ar/invoices') || str_starts_with($currentRoute, '/ar/collections') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full justify-between">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                        <span>Billing & Collections</span>
                    </span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-white/10 pl-3">
                    <a href="/ar/invoices" class="sidebar-link text-xs {{ $currentRoute === '/ar/invoices' ? 'sidebar-link--active' : '' }}">Invoices / Charges</a>
                    <a href="/ar/collections" class="sidebar-link text-xs {{ $currentRoute === '/ar/collections' ? 'sidebar-link--active' : '' }}">Collections / Receipts</a>
                </div>
            </div>

            <a href="/ar/customers" class="sidebar-link {{ $currentRoute === '/ar/customers' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                <span>Customers / Students</span>
            </a>
            <a href="/ar/aging" class="sidebar-link {{ $currentRoute === '/ar/aging' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                <span>AR Aging</span>
            </a>
            <a href="/ar/soa" class="sidebar-link {{ $currentRoute === '/ar/soa' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                <span>Statement of Account</span>
            </a>

            {{-- GENERAL LEDGER --}}
            <p class="sidebar-section-title mt-4">General Ledger</p>
            <a href="/gl/accounts" class="sidebar-link {{ $currentRoute === '/gl/accounts' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
                <span>Chart of Accounts</span>
            </a>
            <a href="/gl/journal-entries" class="sidebar-link {{ $currentRoute === '/gl/journal-entries' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                <span>Journal Entries</span>
            </a>
            <a href="/gl/recurring" class="sidebar-link {{ $currentRoute === '/gl/recurring' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
                <span>Recurring Journals</span>
            </a>
            <a href="/gl/ledger-inquiry" class="sidebar-link {{ $currentRoute === '/gl/ledger-inquiry' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                <span>Ledger Inquiry</span>
            </a>
            <a href="/gl/bank-reconciliation" class="sidebar-link {{ $currentRoute === '/gl/bank-reconciliation' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21" /></svg>
                <span>Bank Reconciliation</span>
            </a>
            <a href="/gl/period-closing" class="sidebar-link {{ $currentRoute === '/gl/period-closing' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                <span>Period Closing</span>
            </a>

            {{-- REPORTS --}}
            <p class="sidebar-section-title mt-4">Reports</p>

            {{-- Financial Reports (collapsible) --}}
            <div x-data="{ open: {{ str_starts_with($currentRoute, '/reports/trial-balance') || str_starts_with($currentRoute, '/reports/balance-sheet') || str_starts_with($currentRoute, '/reports/income-statement') || str_starts_with($currentRoute, '/reports/cash-flow') || str_starts_with($currentRoute, '/reports/general-ledger') || str_starts_with($currentRoute, '/reports/expense-schedule') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full justify-between">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        <span>Financial Reports</span>
                    </span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-white/10 pl-3">
                    <p class="text-xs text-white/40 font-semibold uppercase mt-1 mb-1 pl-1">Financial Statements</p>
                    <a href="/reports/trial-balance" class="sidebar-link text-xs {{ $currentRoute === '/reports/trial-balance' ? 'sidebar-link--active' : '' }}">Trial Balance</a>
                    <a href="/reports/balance-sheet" class="sidebar-link text-xs {{ $currentRoute === '/reports/balance-sheet' ? 'sidebar-link--active' : '' }}">Balance Sheet</a>
                    <a href="/reports/income-statement" class="sidebar-link text-xs {{ $currentRoute === '/reports/income-statement' ? 'sidebar-link--active' : '' }}">Income Statement</a>
                    <a href="/reports/cash-flow" class="sidebar-link text-xs {{ $currentRoute === '/reports/cash-flow' ? 'sidebar-link--active' : '' }}">Cash Flow</a>
                    <p class="text-xs text-white/40 font-semibold uppercase mt-2 mb-1 pl-1">Books of Accounts</p>
                    <a href="/reports/general-ledger" class="sidebar-link text-xs {{ $currentRoute === '/reports/general-ledger' ? 'sidebar-link--active' : '' }}">General Ledger</a>
                    <a href="/reports/general-journal" class="sidebar-link text-xs {{ $currentRoute === '/reports/general-journal' ? 'sidebar-link--active' : '' }}">General Journal</a>
                    <a href="/reports/cash-receipts-book" class="sidebar-link text-xs {{ $currentRoute === '/reports/cash-receipts-book' ? 'sidebar-link--active' : '' }}">Cash Receipts Book</a>
                    <a href="/reports/cash-disbursements-book" class="sidebar-link text-xs {{ $currentRoute === '/reports/cash-disbursements-book' ? 'sidebar-link--active' : '' }}">Cash Disbursements Book</a>
                    <a href="/tax/special-journals" class="sidebar-link text-xs {{ $currentRoute === '/tax/special-journals' ? 'sidebar-link--active' : '' }}">Special Journals</a>
                    <p class="text-xs text-white/40 font-semibold uppercase mt-2 mb-1 pl-1">Other Reports</p>
                    <a href="/reports/expense-schedule" class="sidebar-link text-xs {{ $currentRoute === '/reports/expense-schedule' ? 'sidebar-link--active' : '' }}">Expense Schedule</a>
                </div>
            </div>

            {{-- Budget Reports (collapsible) --}}
            <div x-data="{ open: false }">
                <button @click="open = !open" class="sidebar-link w-full justify-between">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                        <span>Budget Reports</span>
                    </span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-white/10 pl-3">
                    <a href="/reports/budget-vs-actual" class="sidebar-link text-xs">Budget vs Actual</a>
                    <a href="/reports/monthly-variance" class="sidebar-link text-xs">Monthly Variance</a>
                </div>
            </div>

            {{-- Tax & BIR (collapsible) --}}
            <div x-data="{ open: {{ str_starts_with($currentRoute, '/tax/') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link w-full justify-between">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>
                        <span>Tax & BIR</span>
                    </span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-white/10 pl-3">
                    <p class="text-xs text-white/40 font-semibold uppercase mt-2 mb-1 pl-1">Monthly</p>
                    <a href="/tax/bir-0619e" class="sidebar-link text-xs {{ $currentRoute === '/tax/bir-0619e' ? 'sidebar-link--active' : '' }}">0619-E (EWT)</a>
                    <a href="/tax/bir-0619f" class="sidebar-link text-xs {{ $currentRoute === '/tax/bir-0619f' ? 'sidebar-link--active' : '' }}">0619-F (Final WT)</a>
                    <a href="/tax/bir-1601c" class="sidebar-link text-xs {{ $currentRoute === '/tax/bir-1601c' ? 'sidebar-link--active' : '' }}">1601-C (Compensation)</a>
                    <a href="/tax/vat-2550m" class="sidebar-link text-xs {{ $currentRoute === '/tax/vat-2550m' ? 'sidebar-link--active' : '' }}">VAT 2550M</a>
                    <p class="text-xs text-white/40 font-semibold uppercase mt-2 mb-1 pl-1">Quarterly</p>
                    <a href="/tax/bir-1601eq" class="sidebar-link text-xs {{ $currentRoute === '/tax/bir-1601eq' ? 'sidebar-link--active' : '' }}">1601-EQ (Expanded)</a>
                    <a href="/tax/bir-1601e" class="sidebar-link text-xs {{ $currentRoute === '/tax/bir-1601e' ? 'sidebar-link--active' : '' }}">1601-E Return</a>
                    <a href="/tax/alphalist-quarterly" class="sidebar-link text-xs {{ $currentRoute === '/tax/alphalist-quarterly' ? 'sidebar-link--active' : '' }}">Alphalist (QAP)</a>
                    <p class="text-xs text-white/40 font-semibold uppercase mt-2 mb-1 pl-1">Annual</p>
                    <a href="/tax/bir-1604e" class="sidebar-link text-xs {{ $currentRoute === '/tax/bir-1604e' ? 'sidebar-link--active' : '' }}">1604-E (Expanded)</a>
                    <a href="/tax/bir-1604cf" class="sidebar-link text-xs {{ $currentRoute === '/tax/bir-1604cf' ? 'sidebar-link--active' : '' }}">1604-CF (Comp/Final)</a>
                    <a href="/tax/alphalist-annual" class="sidebar-link text-xs {{ $currentRoute === '/tax/alphalist-annual' ? 'sidebar-link--active' : '' }}">Alphalist (Annual)</a>
                    <p class="text-xs text-white/40 font-semibold uppercase mt-2 mb-1 pl-1">Other</p>
                    <a href="/tax/bir-2307" class="sidebar-link text-xs {{ $currentRoute === '/tax/bir-2307' ? 'sidebar-link--active' : '' }}">BIR 2307</a>
                    <a href="/tax/alphalist" class="sidebar-link text-xs {{ $currentRoute === '/tax/alphalist' ? 'sidebar-link--active' : '' }}">QAP / SAWT</a>
                </div>
            </div>

            {{-- SYSTEM --}}
            <p class="sidebar-section-title mt-4">System</p>
            <a href="/audit-trail" class="sidebar-link {{ $currentRoute === '/audit-trail' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                <span>Audit Trail</span>
            </a>
            <a href="/settings" class="sidebar-link {{ $currentRoute === '/settings' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                <span>Settings</span>
            </a>
            <a href="/api-docs" class="sidebar-link {{ $currentRoute === '/api-docs' ? 'sidebar-link--active' : '' }}">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
                <span>API Docs</span>
            </a>
        </nav>

        {{-- User Profile at bottom --}}
        <div class="border-t border-white/10 px-4 py-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-9 h-9 bg-primary-600 rounded-full flex items-center justify-center">
                    <span class="text-sm font-semibold text-white">RT</span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-white truncate">Roberto Tan</p>
                    <p class="text-xs text-slate-400 truncate">Finance Manager</p>
                </div>
                <button class="ml-auto text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                </button>
            </div>
        </div>
    </aside>

    {{-- ================================================================
         MAIN CONTENT
         ================================================================ --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">

        {{-- Top Header Bar --}}
        <header class="sticky top-0 z-10 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between px-4 sm:px-6 h-16">

                {{-- Left: hamburger + search --}}
                <div class="flex items-center gap-4">
                    {{-- Mobile hamburger --}}
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden btn-icon">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>

                    {{-- Search --}}
                    <div x-data="globalSearch()" class="hidden sm:block relative w-72" @click.away="open = false" @keydown.escape.window="open = false">
                        <div class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            <input x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) open = true"
                                   type="text" placeholder="Search transactions, accounts..." class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-400 focus:outline-none w-full">
                            <svg x-show="loading" class="w-4 h-4 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </div>
                        {{-- Results dropdown --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="absolute top-full left-0 mt-1 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-80 overflow-y-auto" style="display: none;">
                            <template x-if="results.length === 0 && !loading && query.length >= 2">
                                <div class="px-4 py-3 text-sm text-gray-500">No results found.</div>
                            </template>
                            <template x-for="item in results" :key="item.title + item.type">
                                <a :href="item.url" class="flex items-start gap-3 px-4 py-2.5 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                    <span class="mt-0.5 flex-shrink-0 w-6 h-6 rounded bg-primary-50 text-primary-600 flex items-center justify-center">
                                        <svg x-show="item.icon === 'book'" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                                        <svg x-show="item.icon === 'truck'" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0H21M3.375 14.25h3.75M21 12.75H8.25m0 0L6.75 7.5h12.75l-1.5 5.25" /></svg>
                                        <svg x-show="item.icon === 'users'" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                        <svg x-show="['file-text','file'].includes(item.icon)" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        <svg x-show="item.icon === 'layers'" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L12 12.75 6.429 9.75m11.142 0 4.179 2.25-9.75 5.25-9.75-5.25 4.179-2.25" /></svg>
                                        <svg x-show="['banknotes','credit-card','receipt','calculator','building'].includes(item.icon)" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate" x-text="item.title"></div>
                                        <div class="text-xs text-gray-500 truncate"><span class="font-medium text-primary-600" x-text="item.type"></span> &middot; <span x-text="item.subtitle"></span></div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Right: SY badge, notifications, profile --}}
                <div class="flex items-center gap-3">
                    {{-- School Year badge --}}
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 bg-primary-50 text-primary-700 text-xs font-semibold rounded-full">
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        SY {{ date('Y') }}&ndash;{{ date('Y') + 1 }}
                    </span>

                    {{-- Notifications --}}
                    <div x-data="notificationBell()" x-init="fetchNotifications()" class="relative" @click.away="open = false">
                        <button @click="toggle()" class="relative btn-icon">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount"
                                  class="absolute -top-1 -right-1 flex items-center justify-center min-w-[16px] h-4 px-0.5 text-[10px] font-bold text-white bg-danger-500 rounded-full"></span>
                        </button>
                        {{-- Notifications dropdown --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50" style="display: none;">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                <button x-show="unreadCount > 0" @click="markAllRead()" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Mark all read</button>
                            </div>
                            <div class="max-h-72 overflow-y-auto">
                                <template x-if="notifications.length === 0">
                                    <div class="px-4 py-6 text-center text-sm text-gray-500">No notifications yet.</div>
                                </template>
                                <template x-for="n in notifications" :key="n.id">
                                    <div @click="markRead(n)" class="flex gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0"
                                         :class="{ 'bg-primary-50/50': !n.read_at }">
                                        <div class="flex-shrink-0 mt-0.5 w-8 h-8 rounded-full flex items-center justify-center"
                                             :class="{
                                                 'bg-primary-100 text-primary-600': n.type === 'info',
                                                 'bg-green-100 text-green-600': n.type === 'success',
                                                 'bg-amber-100 text-amber-600': n.type === 'warning',
                                                 'bg-red-100 text-red-600': n.type === 'danger',
                                                 'bg-gray-100 text-gray-600': !['info','success','warning','danger'].includes(n.type)
                                             }">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900" x-text="n.title"></div>
                                            <div class="text-xs text-gray-500 truncate" x-text="n.message"></div>
                                            <div class="text-[11px] text-gray-400 mt-0.5" x-text="n.time_ago"></div>
                                        </div>
                                        <span x-show="!n.read_at" class="mt-2 flex-shrink-0 w-2 h-2 rounded-full bg-primary-500"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- User dropdown --}}
                    <div x-data="{ userMenu: false }" class="relative">
                        <button @click="userMenu = !userMenu" class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
                                <span class="text-xs font-semibold text-white">RT</span>
                            </div>
                            <span class="hidden md:block text-sm font-medium text-secondary-700">Roberto Tan</span>
                            <svg class="hidden md:block w-4 h-4 text-secondary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="userMenu" @click.away="userMenu = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                             style="display: none;">
                            <a href="/settings" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-gray-50">Settings</a>
                            <a href="/audit-trail" class="block px-4 py-2 text-sm text-secondary-700 hover:bg-gray-50">Audit Trail</a>
                            <hr class="my-1 border-gray-100">
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-danger-500 hover:bg-gray-50">Sign Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 x-transition class="mx-4 sm:mx-6 mt-4">
                <x-alert type="success" :message="session('success')" />
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                 x-transition class="mx-4 sm:mx-6 mt-4">
                <x-alert type="danger" :message="session('error')" />
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-6" id="app">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-gray-100 px-4 sm:px-6 py-3">
            <p class="text-xs text-secondary-400 text-center">OrangeApps School Finance ERP &copy; {{ date('Y') }}. All rights reserved.</p>
        </footer>
    </div>

    {{-- Global Search & Notification Alpine components --}}
    <script>
        function globalSearch() {
            return {
                query: '',
                results: [],
                open: false,
                loading: false,
                async search() {
                    if (this.query.length < 2) { this.results = []; this.open = false; return; }
                    this.loading = true;
                    try {
                        const res = await fetch(`/search?q=${encodeURIComponent(this.query)}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();
                        this.results = data.results;
                        this.open = true;
                    } catch (e) {
                        console.error('Search error:', e);
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }

        function notificationBell() {
            return {
                open: false,
                notifications: [],
                unreadCount: 0,
                toggle() {
                    this.open = !this.open;
                    if (this.open) this.fetchNotifications();
                },
                async fetchNotifications() {
                    try {
                        const res = await fetch('/notifications', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();
                        this.notifications = data.notifications;
                        this.unreadCount = data.unread_count;
                    } catch (e) {
                        console.error('Notification fetch error:', e);
                    }
                },
                async markRead(n) {
                    if (!n.read_at) {
                        const token = document.querySelector('meta[name="csrf-token"]').content;
                        try {
                            await fetch(`/notifications/${n.id}/read`, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            n.read_at = new Date().toISOString();
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        } catch (e) {
                            console.error('Mark read error:', e);
                        }
                    }
                    if (n.url) { this.open = false; window.location.href = n.url; }
                },
                async markAllRead() {
                    const token = document.querySelector('meta[name="csrf-token"]').content;
                    try {
                        await fetch('/notifications/mark-all-read', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        this.notifications.forEach(n => n.read_at = new Date().toISOString());
                        this.unreadCount = 0;
                    } catch (e) {
                        console.error('Mark all read error:', e);
                    }
                }
            };
        }
    </script>

    {{-- Turbo loading bar + Alpine compatibility --}}
    <style>
        .turbo-progress-bar { background: #4f46e5; height: 3px; position: fixed; top: 0; left: 0; z-index: 9999; transition: width 300ms ease; }
    </style>
    <script>
        // Show progress bar during Turbo navigation
        (function() {
            let bar = null;
            document.addEventListener('turbo:before-fetch-request', () => {
                if (!bar) { bar = document.createElement('div'); bar.className = 'turbo-progress-bar'; document.body.prepend(bar); }
                bar.style.width = '0%'; bar.style.display = 'block';
                requestAnimationFrame(() => bar.style.width = '70%');
            });
            document.addEventListener('turbo:before-render', () => { if (bar) bar.style.width = '100%'; });
            document.addEventListener('turbo:load', () => { if (bar) { bar.style.display = 'none'; bar.style.width = '0%'; } });
        })();

        // Disable Turbo on forms with file uploads and logout forms
        document.addEventListener('turbo:before-fetch-request', (e) => {
            const form = e.target.closest?.('form');
            if (form?.enctype === 'multipart/form-data') { e.preventDefault(); form.submit(); }
        });
    </script>

    @stack('scripts')
</body>
</html>
