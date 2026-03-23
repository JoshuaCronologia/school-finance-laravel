@extends('layouts.app')
@section('title', 'Income Statement')

@section('content')
@php
    $totalRevenue = $totalRevenue ?? 0;
    $totalExpenses = $totalExpenses ?? 0;
    $grossProfit = $grossProfit ?? 0;
    $netIncome = $netIncome ?? ($totalRevenue - $totalExpenses);
    $revenueAccounts = $revenueAccounts ?? collect();
    $expenseAccounts = $expenseAccounts ?? collect();
    $costOfServices = $costOfServices ?? 0;
    $operatingExpenses = $operatingExpenses ?? $totalExpenses;
    $profitMargin = $totalRevenue > 0 ? ($netIncome / $totalRevenue * 100) : 0;
    $expenseRatio = $totalRevenue > 0 ? ($totalExpenses / $totalRevenue * 100) : 0;
@endphp

<x-page-header title="Income Statement" subtitle="Profit & Loss">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot:actions>
</x-page-header>

{{-- Filters --}}
<x-filter-bar />

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card label="Total Revenue" :value="'₱' . number_format($totalRevenue, 2)" color="green"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941\' /></svg>'" />
    <x-stat-card label="Total Expenses" :value="'₱' . number_format($totalExpenses, 2)" color="red"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181\' /></svg>'" />
    <x-stat-card label="Gross Profit" :value="'₱' . number_format($grossProfit, 2)" color="blue"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\' /></svg>'" />
    <x-stat-card label="Net Income" :value="'₱' . number_format($netIncome, 2)" :color="$netIncome >= 0 ? 'green' : 'red'"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z\' /></svg>'" />
</div>

{{-- Two-column layout --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Statement Details --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Revenue Section --}}
        <div class="card">
            <div class="card-header bg-green-50">
                <h3 class="text-sm font-semibold text-green-800">Revenue</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">% of Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenueAccounts as $account)
                            <tr>
                                <td>{{ $account->account_name }}</td>
                                <td class="text-right font-mono">₱{{ number_format($account->balance ?? 0, 2) }}</td>
                                <td class="text-right font-mono text-secondary-500">
                                    {{ $totalRevenue > 0 ? number_format(($account->balance ?? 0) / $totalRevenue * 100, 1) : '0.0' }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-secondary-400 py-4">No revenue data</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-green-50 font-semibold">
                        <tr>
                            <td>Total Revenue</td>
                            <td class="text-right font-mono">₱{{ number_format($totalRevenue, 2) }}</td>
                            <td class="text-right font-mono">100.0%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Cost of Services --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between py-2 border-b border-gray-200">
                    <span class="text-sm font-medium text-secondary-700">Less: Cost of Services</span>
                    <span class="font-mono font-semibold text-danger-600">₱{{ number_format($costOfServices, 2) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 bg-blue-50 px-3 rounded mt-2">
                    <span class="text-sm font-semibold text-blue-800">Gross Profit</span>
                    <span class="font-mono font-bold text-blue-800">₱{{ number_format($grossProfit, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Operating Expenses --}}
        <div class="card">
            <div class="card-header bg-red-50">
                <h3 class="text-sm font-semibold text-red-800">Operating Expenses</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">% of Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseAccounts as $account)
                            <tr>
                                <td>{{ $account->account_name }}</td>
                                <td class="text-right font-mono">₱{{ number_format($account->balance ?? 0, 2) }}</td>
                                <td class="text-right font-mono text-secondary-500">
                                    {{ $totalRevenue > 0 ? number_format(($account->balance ?? 0) / $totalRevenue * 100, 1) : '0.0' }}%
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-secondary-400 py-4">No expense data</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-red-50 font-semibold">
                        <tr>
                            <td>Total Operating Expenses</td>
                            <td class="text-right font-mono">₱{{ number_format($operatingExpenses, 2) }}</td>
                            <td class="text-right font-mono">{{ $totalRevenue > 0 ? number_format($operatingExpenses / $totalRevenue * 100, 1) : '0.0' }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Net Income --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between p-4 rounded-lg {{ $netIncome >= 0 ? 'bg-success-50' : 'bg-danger-50' }}">
                    <span class="text-lg font-bold {{ $netIncome >= 0 ? 'text-success-800' : 'text-danger-800' }}">Net Income</span>
                    <span class="text-xl font-mono font-bold {{ $netIncome >= 0 ? 'text-success-700' : 'text-danger-700' }}">₱{{ number_format($netIncome, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Summary --}}
    <div class="space-y-6">
        {{-- Summary Chart Placeholder --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Revenue vs Expenses</h3>
            </div>
            <div class="card-body" x-data="{}" x-init="
                const ctx = $refs.summaryChart.getContext('2d');
                if (typeof Chart !== 'undefined') {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Revenue', 'Expenses', 'Net Income'],
                            datasets: [{
                                data: [{{ $totalRevenue }}, {{ $totalExpenses }}, {{ $netIncome }}],
                                backgroundColor: ['#10b981', '#ef4444', '{{ $netIncome >= 0 ? '#3b82f6' : '#f59e0b' }}'],
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } } }
                        }
                    });
                }
            ">
                <canvas x-ref="summaryChart" height="200"></canvas>
            </div>
        </div>

        {{-- Profit Margin --}}
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Key Ratios</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-secondary-600">Profit Margin</span>
                        <span class="text-sm font-semibold {{ $profitMargin >= 0 ? 'text-success-600' : 'text-danger-600' }}">{{ number_format($profitMargin, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="{{ $profitMargin >= 0 ? 'bg-success-500' : 'bg-danger-500' }} h-2.5 rounded-full" style="width: {{ min(abs($profitMargin), 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-secondary-600">Expense Ratio</span>
                        <span class="text-sm font-semibold {{ $expenseRatio <= 80 ? 'text-success-600' : 'text-warning-600' }}">{{ number_format($expenseRatio, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="{{ $expenseRatio <= 80 ? 'bg-blue-500' : 'bg-warning-500' }} h-2.5 rounded-full" style="width: {{ min($expenseRatio, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
