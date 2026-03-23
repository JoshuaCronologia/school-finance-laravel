@extends('layouts.app')
@section('title', 'API Documentation')

@section('content')
@php
    $modules = [
        'dashboard' => ['label' => 'Dashboard', 'count' => 2],
        'budgets' => ['label' => 'Budgets', 'count' => 5],
        'disbursements' => ['label' => 'Disbursements', 'count' => 4],
        'payments' => ['label' => 'Payments', 'count' => 3],
        'vendors' => ['label' => 'Vendors', 'count' => 4],
        'invoices' => ['label' => 'Invoices', 'count' => 4],
        'collections' => ['label' => 'Collections', 'count' => 3],
        'customers' => ['label' => 'Customers', 'count' => 4],
        'coa' => ['label' => 'Chart of Accounts', 'count' => 4],
        'journal-entries' => ['label' => 'Journal Entries', 'count' => 4],
        'reports' => ['label' => 'Reports', 'count' => 3],
        'tax' => ['label' => 'Tax', 'count' => 1],
    ];
@endphp

{{-- Dark Header --}}
<div class="bg-secondary-900 -mx-6 -mt-6 px-6 py-8 mb-6 rounded-t-lg">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-white">OrangeApps Finance API</h1>
            <p class="text-sm text-secondary-400">RESTful API for School Finance ERP</p>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-secondary-800 rounded-lg p-3">
            <p class="text-xs text-secondary-400 uppercase tracking-wider">Base URL</p>
            <p class="text-sm font-mono text-white mt-1">{{ config('app.url') }}/api/v1</p>
        </div>
        <div class="bg-secondary-800 rounded-lg p-3">
            <p class="text-xs text-secondary-400 uppercase tracking-wider">Format</p>
            <p class="text-sm font-semibold text-white mt-1">JSON</p>
        </div>
        <div class="bg-secondary-800 rounded-lg p-3">
            <p class="text-xs text-secondary-400 uppercase tracking-wider">Endpoints</p>
            <p class="text-sm font-semibold text-white mt-1">41</p>
        </div>
        <div class="bg-secondary-800 rounded-lg p-3">
            <p class="text-xs text-secondary-400 uppercase tracking-wider">Auth</p>
            <p class="text-sm font-semibold text-white mt-1">API Key</p>
        </div>
    </div>
</div>

{{-- API Key Section --}}
<div class="card mb-6 border-success-200 bg-success-50">
    <div class="card-body">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-secondary-900">Your API Key</h3>
                <p class="text-xs text-secondary-500 mt-1">Use this key to authenticate your API requests</p>
            </div>
            <div class="flex items-center gap-2" x-data="{ copied: false }">
                <code class="bg-success-100 text-success-800 px-4 py-2 rounded-lg font-mono text-sm">{{ $apiKey ?? 'sk-xxxx-xxxx-xxxx-xxxx' }}</code>
                <button @click="navigator.clipboard.writeText('{{ $apiKey ?? 'sk-xxxx-xxxx-xxxx-xxxx' }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="btn-secondary text-sm" :class="copied ? 'text-success-600' : ''">
                    <template x-if="!copied">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                    </template>
                    <template x-if="copied">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </template>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    {{-- Left Sidebar --}}
    <div class="col-span-12 md:col-span-3">
        <div class="card sticky top-20">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Modules</h3>
            </div>
            <nav class="p-2">
                @foreach($modules as $key => $mod)
                <a href="#{{ $key }}" class="flex items-center justify-between px-3 py-2 text-sm text-secondary-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                    <span>{{ $mod['label'] }}</span>
                    <span class="badge badge-neutral text-xs">{{ $mod['count'] }}</span>
                </a>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="col-span-12 md:col-span-9 space-y-6">
        {{-- Authentication --}}
        <div class="card" id="authentication">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Authentication</h3>
            </div>
            <div class="card-body space-y-4">
                <p class="text-sm text-secondary-600">All API requests require authentication using an API key. Include the key in the request header.</p>

                <div class="bg-secondary-900 rounded-lg p-4 font-mono text-sm text-green-400 overflow-x-auto">
                    <p class="text-secondary-400"># Using X-API-Key header</p>
                    <p>curl -H "X-API-Key: {{ $apiKey ?? 'your-api-key' }}" \</p>
                    <p class="pl-4">{{ config('app.url') }}/api/v1/dashboard/summary</p>
                    <br>
                    <p class="text-secondary-400"># Using Bearer token</p>
                    <p>curl -H "Authorization: Bearer {{ $apiKey ?? 'your-api-key' }}" \</p>
                    <p class="pl-4">{{ config('app.url') }}/api/v1/dashboard/summary</p>
                </div>
            </div>
        </div>

        {{-- Dashboard Endpoints --}}
        <div class="card" id="dashboard">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Dashboard</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/dashboard/summary', 'desc' => 'Get financial dashboard summary with KPIs, budget utilization, and recent activity.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/dashboard/accounting', 'desc' => 'Get accounting dashboard with GL balances, receivables, and payables overview.'])
            </div>
        </div>

        {{-- Budgets --}}
        <div class="card" id="budgets">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Budgets</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/budgets', 'desc' => 'List all budgets with optional filters for school year, department, and status.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/budgets', 'desc' => 'Create a new budget entry.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/budgets/{id}', 'desc' => 'Get a specific budget with allocations and utilization details.'])
                @include('pages.system._api-endpoint', ['method' => 'PUT', 'url' => '/api/v1/budgets/{id}', 'desc' => 'Update an existing budget.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/budgets/{id}/utilization', 'desc' => 'Get budget utilization report with line-item breakdown.'])
            </div>
        </div>

        {{-- Disbursements --}}
        <div class="card" id="disbursements">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Disbursements</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/disbursements', 'desc' => 'List all disbursement requests with filters.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/disbursements', 'desc' => 'Create a new disbursement request.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/disbursements/{id}', 'desc' => 'Get disbursement details including line items and approval history.'])
                @include('pages.system._api-endpoint', ['method' => 'PUT', 'url' => '/api/v1/disbursements/{id}/approve', 'desc' => 'Approve or reject a disbursement request.'])
            </div>
        </div>

        {{-- Payments --}}
        <div class="card" id="payments">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Payments</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/payments', 'desc' => 'List all payment vouchers.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/payments', 'desc' => 'Create a payment voucher for approved disbursements.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/payments/{id}', 'desc' => 'Get payment voucher details with check/bank transfer info.'])
            </div>
        </div>

        {{-- Vendors --}}
        <div class="card" id="vendors">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Vendors</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/vendors', 'desc' => 'List all vendors/payees.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/vendors', 'desc' => 'Create a new vendor.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/vendors/{id}', 'desc' => 'Get vendor details with transaction history.'])
                @include('pages.system._api-endpoint', ['method' => 'PUT', 'url' => '/api/v1/vendors/{id}', 'desc' => 'Update vendor information.'])
            </div>
        </div>

        {{-- Invoices --}}
        <div class="card" id="invoices">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Invoices</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/invoices', 'desc' => 'List AR invoices/charges.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/invoices', 'desc' => 'Create a new invoice with line items.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/invoices/{id}', 'desc' => 'Get invoice details with line items and payment history.'])
                @include('pages.system._api-endpoint', ['method' => 'PUT', 'url' => '/api/v1/invoices/{id}', 'desc' => 'Update an existing invoice.'])
            </div>
        </div>

        {{-- Collections --}}
        <div class="card" id="collections">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Collections</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/collections', 'desc' => 'List all collections/official receipts.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/collections', 'desc' => 'Record a new collection with invoice applications.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/collections/{id}', 'desc' => 'Get collection details with applied invoices.'])
            </div>
        </div>

        {{-- Customers --}}
        <div class="card" id="customers">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Customers</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/customers', 'desc' => 'List all customers/students.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/customers', 'desc' => 'Create a new customer.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/customers/{id}', 'desc' => 'Get customer details with SOA summary.'])
                @include('pages.system._api-endpoint', ['method' => 'PUT', 'url' => '/api/v1/customers/{id}', 'desc' => 'Update customer information.'])
            </div>
        </div>

        {{-- Chart of Accounts --}}
        <div class="card" id="coa">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Chart of Accounts</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/accounts', 'desc' => 'List all GL accounts with optional type filter.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/accounts', 'desc' => 'Create a new GL account.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/accounts/{id}', 'desc' => 'Get account details with current balance.'])
                @include('pages.system._api-endpoint', ['method' => 'PUT', 'url' => '/api/v1/accounts/{id}', 'desc' => 'Update account information.'])
            </div>
        </div>

        {{-- Journal Entries --}}
        <div class="card" id="journal-entries">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Journal Entries</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/journal-entries', 'desc' => 'List all journal entries with optional status and date filters.'])
                @include('pages.system._api-endpoint', ['method' => 'POST', 'url' => '/api/v1/journal-entries', 'desc' => 'Create a new journal entry with debit/credit lines.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/journal-entries/{id}', 'desc' => 'Get journal entry details with all lines.'])
                @include('pages.system._api-endpoint', ['method' => 'PUT', 'url' => '/api/v1/journal-entries/{id}/post', 'desc' => 'Post a draft journal entry to the general ledger.'])
            </div>
        </div>

        {{-- Reports --}}
        <div class="card" id="reports">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Reports</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/reports/trial-balance', 'desc' => 'Generate trial balance report for a given period.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/reports/general-ledger', 'desc' => 'Generate general ledger report with account transactions.'])
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/reports/financial-statements', 'desc' => 'Generate balance sheet, income statement, and cash flow.'])
            </div>
        </div>

        {{-- Tax --}}
        <div class="card" id="tax">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-secondary-900">Tax</h3>
            </div>
            <div class="card-body space-y-4">
                @include('pages.system._api-endpoint', ['method' => 'GET', 'url' => '/api/v1/tax/withholding-summary', 'desc' => 'Get withholding tax summary by period with BIR form data.'])
            </div>
        </div>
    </div>
</div>
@endsection
