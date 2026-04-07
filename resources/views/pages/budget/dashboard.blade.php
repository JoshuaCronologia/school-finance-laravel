@extends('layouts.app')
@section('title', 'Budget Dashboard')

@section('content')
<x-page-header title="Budget Dashboard" subtitle="Budget monitoring and analysis" />

{{-- Department Filter & PDF Export --}}
<div class="card mb-6">
    <div class="card-body">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-input w-64" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request('department_id'))
                    <a href="{{ request()->url() }}" class="btn-secondary">Clear Filter</a>
                @endif
            </form>
            <a href="{{ route('budget.budget-vs-actual.pdf', ['department_id' => request('department_id')]) }}" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                Budget vs Actual PDF
            </a>
        </div>
    </div>
</div>

@php
    $incomeVariance = ($incomeBudget ?? 0) > 0 ? $totalIncome - $incomeBudget : $totalIncome;
    $incomeVariancePct = ($incomeBudget ?? 0) > 0 ? round(($incomeVariance / $incomeBudget) * 100, 2) : 0;
    $netVariance = $netIncome - ($incomeBudget ?? 0);
    $netVariancePct = ($incomeBudget ?? 0) > 0 ? round(($netVariance / $incomeBudget) * 100, 2) : 0;
@endphp

{{-- Budget & Variance Year-To-Date + Expenses side by side --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Budget and Variance YTD --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Budget and Variance Year-To-Date</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-secondary-600">As of {{ now()->format('F j, Y') }}</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Actual</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Budget</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Variance</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">%</th>
                        <th class="px-3 py-2 w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="px-4 py-2.5">+ Income</td>
                        <td class="text-right px-3 py-2.5 font-mono">{{ number_format($totalIncome ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono">{{ number_format($incomeBudget ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono {{ $incomeVariance < 0 ? 'text-danger-600' : 'text-success-600' }}">{{ number_format($incomeVariance, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono {{ $incomeVariancePct < 0 ? 'text-danger-600' : '' }}">{{ $incomeVariancePct }}</td>
                        <td class="px-3 py-2.5">@include('partials.variance-bar', ['pct' => $incomeVariancePct])</td>
                    </tr>
                    <tr class="border-b">
                        <td class="px-4 py-2.5">- Expenses</td>
                        <td class="text-right px-3 py-2.5 font-mono">{{ number_format($totalExpenses ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono">0.00</td>
                        <td class="text-right px-3 py-2.5 font-mono text-success-600">{{ number_format($totalExpenses ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5">-</td>
                        <td class="px-3 py-2.5"></td>
                    </tr>
                    <tr class="border-b bg-gray-50 font-semibold">
                        <td class="px-4 py-2.5">Gross Profit</td>
                        <td class="text-right px-3 py-2.5 font-mono">{{ number_format($grossProfit ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono">{{ number_format($incomeBudget ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono {{ $incomeVariance < 0 ? 'text-danger-600' : 'text-success-600' }}">{{ number_format($incomeVariance, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono {{ $incomeVariancePct < 0 ? 'text-danger-600' : '' }}">{{ $incomeVariancePct }}</td>
                        <td class="px-3 py-2.5">@include('partials.variance-bar', ['pct' => $incomeVariancePct])</td>
                    </tr>
                    <tr class="border-b font-bold text-lg bg-white">
                        <td class="px-4 py-3">Net Income</td>
                        <td class="text-right px-3 py-3 font-mono">{{ number_format($netIncome ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-3 font-mono">{{ number_format($incomeBudget ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-3 font-mono {{ $netVariance < 0 ? 'text-danger-600' : 'text-success-600' }}">{{ number_format($netVariance, 2) }}</td>
                        <td class="text-right px-3 py-3 font-mono {{ $netVariancePct < 0 ? 'text-danger-600' : '' }}">{{ $netVariancePct }}</td>
                        <td class="px-3 py-3">@include('partials.variance-bar', ['pct' => $netVariancePct])</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Expenses YTD --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Expenses Year-To-Date</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-secondary-600">As of {{ now()->format('F j, Y') }}</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Actual</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Budget</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Variance</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">%</th>
                        <th class="px-3 py-2 w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenseAccounts ?? [] as $exp)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $exp->account_name }}</td>
                        <td class="text-right px-3 py-2 font-mono">{{ number_format($exp->actual, 2) }}</td>
                        <td class="text-right px-3 py-2 font-mono text-secondary-400">0.00</td>
                        <td class="text-right px-3 py-2 font-mono {{ $exp->actual > 0 ? 'text-success-600' : 'text-danger-600' }}">{{ number_format($exp->actual, 2) }}</td>
                        <td class="text-right px-3 py-2 font-mono">-</td>
                        <td class="px-3 py-2">
                            @php $expPct = ($totalExpenses ?? 0) > 0 ? round(($exp->actual / $totalExpenses) * 100, 1) : 0; @endphp
                            @include('partials.variance-bar', ['pct' => $exp->actual > 0 ? $expPct : -$expPct])
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-secondary-400">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-semibold border-t">
                    <tr>
                        <td class="px-4 py-2.5">Total Expenses</td>
                        <td class="text-right px-3 py-2.5 font-mono">{{ number_format($totalExpenses ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono">0.00</td>
                        <td class="text-right px-3 py-2.5 font-mono text-success-600">{{ number_format($totalExpenses ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono">-</td>
                        <td class="px-3 py-2.5"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Income YTD --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Income Year-To-Date</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-secondary-600">As of {{ now()->format('F j, Y') }}</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Actual</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Budget</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">Variance</th>
                        <th class="text-right px-3 py-2 font-medium text-secondary-600">%</th>
                        <th class="px-3 py-2 w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incomeAccounts ?? [] as $inc)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $inc->account_name }}</td>
                        <td class="text-right px-3 py-2 font-mono">{{ number_format($inc->actual, 2) }}</td>
                        <td class="text-right px-3 py-2 font-mono text-secondary-400">0.00</td>
                        <td class="text-right px-3 py-2 font-mono {{ $inc->actual > 0 ? 'text-success-600' : 'text-danger-600' }}">{{ number_format($inc->actual, 2) }}</td>
                        <td class="text-right px-3 py-2 font-mono">-</td>
                        <td class="px-3 py-2">
                            @php $incPct = ($totalIncome ?? 0) > 0 ? round(($inc->actual / $totalIncome) * 100, 1) : 0; @endphp
                            @include('partials.variance-bar', ['pct' => $inc->actual >= 0 ? $incPct : -abs($incPct)])
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-secondary-400">No income recorded.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-semibold border-t">
                    <tr>
                        <td class="px-4 py-2.5">Total Income</td>
                        <td class="text-right px-3 py-2.5 font-mono">{{ number_format($totalIncome ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono">{{ number_format($incomeBudget ?? 0, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono {{ $incomeVariance < 0 ? 'text-danger-600' : 'text-success-600' }}">{{ number_format($incomeVariance, 2) }}</td>
                        <td class="text-right px-3 py-2.5 font-mono {{ $incomeVariancePct < 0 ? 'text-danger-600' : '' }}">{{ $incomeVariancePct }}</td>
                        <td class="px-3 py-2.5">@include('partials.variance-bar', ['pct' => $incomeVariancePct])</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="card p-4">
                <p class="text-xs font-medium text-secondary-500 uppercase">Total Budget</p>
                <p class="text-xl font-bold text-secondary-900 mt-1">@currency($totalBudget)</p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-medium text-secondary-500 uppercase">Committed</p>
                <p class="text-xl font-bold text-warning-600 mt-1">@currency($totalCommitted)</p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-medium text-secondary-500 uppercase">Actual Spent</p>
                <p class="text-xl font-bold text-danger-600 mt-1">@currency($totalActual)</p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-medium text-secondary-500 uppercase">Remaining</p>
                <p class="text-xl font-bold text-success-600 mt-1">@currency($totalRemaining)</p>
                <p class="text-xs text-secondary-400 mt-0.5">Utilization: {{ number_format($utilizationRate, 1) }}%</p>
            </div>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-secondary-500 uppercase">Net Income</p>
            <p class="text-2xl font-bold {{ ($netIncome ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }} mt-1">@currency($netIncome ?? 0)</p>
            <p class="text-xs text-secondary-400 mt-0.5">Income: @currency($totalIncome ?? 0) — Expenses: @currency($totalExpenses ?? 0)</p>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Budget by Department</h3></div>
        <div class="card-body">
            <canvas id="budgetDeptChart" height="320"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Budget Utilization</h3></div>
        <div class="card-body flex flex-col items-center justify-center">
            <div style="max-width: 320px; width: 100%;">
                <canvas id="budgetUtilChart" height="300"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-6 text-center text-sm">
                <div><div class="font-semibold text-primary-600">@currency($totalActual)</div><div class="text-secondary-400">Actual</div></div>
                <div><div class="font-semibold text-warning-600">@currency($totalCommitted)</div><div class="text-secondary-400">Committed</div></div>
                <div><div class="font-semibold text-success-600">@currency($totalRemaining)</div><div class="text-secondary-400">Remaining</div></div>
            </div>
        </div>
    </div>
</div>

{{-- All Budgets Table --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">All Budgets</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Budget Name</th>
                    <th>Department</th>
                    <th>Category</th>
                    <th class="text-right">Annual Budget</th>
                    <th class="text-right">Committed</th>
                    <th class="text-right">Actual</th>
                    <th class="text-right">Remaining</th>
                    <th class="text-right">Variance</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets ?? [] as $budget)
                @php
                    $pct = $budget->annual_budget > 0 ? round(($budget->actual / $budget->annual_budget) * 100, 1) : 0;
                    $variance = $budget->annual_budget - $budget->actual;
                @endphp
                <tr>
                    <td class="font-medium">{{ $budget->budget_name }}</td>
                    <td>{{ $budget->department->name ?? '-' }}</td>
                    <td>{{ $budget->category->name ?? '-' }}</td>
                    <td class="text-right font-mono">@currency($budget->annual_budget)</td>
                    <td class="text-right font-mono">@currency($budget->committed ?? 0)</td>
                    <td class="text-right font-mono">@currency($budget->actual)</td>
                    <td class="text-right font-mono {{ ($budget->remaining ?? 0) < 0 ? 'text-danger-600' : '' }}">@currency($budget->remaining ?? 0)</td>
                    <td class="text-right font-mono {{ $variance < 0 ? 'text-danger-600' : 'text-success-600' }}">@currency($variance)</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="{{ $pct > 90 ? 'bg-danger-500' : ($pct > 70 ? 'bg-warning-500' : 'bg-primary-500') }} h-2 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            <span class="text-xs font-medium {{ $pct > 100 ? 'text-danger-600' : '' }}">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-secondary-400 py-8">No budgets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var deptLabels = @json($deptLabels);
    var deptDatasets = @json($deptDatasets);
    var utilLabels = @json($utilizationLabels);
    var utilValues = @json($utilizationValues);
    var barColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
    var doughnutColors = ['#3b82f6', '#f59e0b', '#10b981'];

    function loadChartJs(cb) {
        if (typeof Chart !== 'undefined') { cb(); return; }
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        s.onload = cb;
        document.head.appendChild(s);
    }

    function currencyTick(v) {
        return '\u20B1' + (v >= 1000000 ? (v/1000000).toFixed(1)+'M' : (v/1000).toFixed(0)+'K');
    }

    function initChart(id, cfg) {
        var canvas = document.getElementById(id);
        if (!canvas) return;
        var existing = Chart.getChart(canvas);
        if (existing) existing.destroy();
        new Chart(canvas.getContext('2d'), cfg);
    }

    loadChartJs(function() {
        // Budget by Department (Bar)
        initChart('budgetDeptChart', {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: deptDatasets.map(function(ds, i) {
                    return { label: ds.label, data: ds.data, backgroundColor: barColors[i % barColors.length], borderRadius: 4 };
                })
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': \u20B1' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2}); } } } },
                scales: { y: { beginAtZero: true, ticks: { callback: currencyTick } }, x: { grid: { display: false } } }
            }
        });

        // Budget Utilization (Doughnut)
        initChart('budgetUtilChart', {
            type: 'doughnut',
            data: {
                labels: utilLabels,
                datasets: [{ data: utilValues, backgroundColor: doughnutColors.slice(0, utilLabels.length) }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: function(ctx) { return ctx.label + ': \u20B1' + ctx.parsed.toLocaleString('en-PH', {minimumFractionDigits:2}); } } }
                }
            }
        });
    });
})();
</script>
@endpush
