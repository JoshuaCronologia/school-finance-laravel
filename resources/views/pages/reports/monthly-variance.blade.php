@extends('layouts.app')
@section('title', 'Monthly Variance')

@section('content')
<x-page-header title="Monthly Variance Analysis" subtitle="Budget vs actual expenses per category">
    <x-slot name="actions">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
    </x-slot>
</x-page-header>

{{-- Filters --}}
<x-filter-bar>
    <div>
        <label class="form-label">Month</label>
        <select name="month" class="form-input w-36">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
            @endfor
        </select>
    </div>
    <div>
        <label class="form-label">Department</label>
        <select name="department_id" class="form-input w-48">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-4">
        <p class="text-xs font-medium text-secondary-500 uppercase">Budget for {{ $selectedMonthName }}</p>
        <p class="text-xl font-bold text-blue-700 mt-1">₱{{ number_format($totals['monthly_budget'], 2) }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-medium text-secondary-500 uppercase">Actual Expenses</p>
        <p class="text-xl font-bold text-green-700 mt-1">₱{{ number_format($totals['monthly_actual'], 2) }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-medium text-secondary-500 uppercase">Variance</p>
        <p class="text-xl font-bold {{ $totals['variance'] >= 0 ? 'text-success-700' : 'text-danger-600' }} mt-1">
            {{ $totals['variance'] < 0 ? '(' : '' }}₱{{ number_format(abs($totals['variance']), 2) }}{{ $totals['variance'] < 0 ? ')' : '' }}
        </p>
    </div>
</div>

{{-- Trend Chart --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Monthly Trend</h3>
    </div>
    <div class="card-body">
        <canvas id="trendChart" height="100"></canvas>
    </div>
</div>

{{-- Itemized Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">{{ $selectedMonthName }} — Budget vs Actual</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category / Budget Item</th>
                    <th class="text-right">Budget for {{ $selectedMonthName }}</th>
                    <th class="text-right">Actual Expenses</th>
                    <th class="text-right">Variance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedData as $deptName => $items)
                    {{-- Department Header --}}
                    <tr class="bg-gray-100">
                        <td colspan="4" class="font-bold text-secondary-800 text-sm py-2">{{ $deptName }}</td>
                    </tr>
                    @foreach($items as $item)
                    @php $isOver = $item->variance < 0; @endphp
                    <tr>
                        <td class="pl-6">{{ $item->category }} — {{ $item->budget_name }}</td>
                        <td class="text-right font-mono">₱{{ number_format($item->monthly_budget, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($item->monthly_actual, 2) }}</td>
                        <td class="text-right font-mono font-semibold {{ $isOver ? 'text-danger-600' : 'text-success-600' }}">
                            {{ $isOver ? '(' : '' }}₱{{ number_format(abs($item->variance), 2) }}{{ $isOver ? ')' : '' }}
                        </td>
                    </tr>
                    @endforeach
                    {{-- Subtotal --}}
                    @php
                        $deptBudget = $items->sum('monthly_budget');
                        $deptActual = $items->sum('monthly_actual');
                        $deptVar = $deptBudget - $deptActual;
                    @endphp
                    <tr class="bg-gray-50 font-semibold">
                        <td class="text-right text-secondary-600">{{ $deptName }} Subtotal</td>
                        <td class="text-right font-mono">₱{{ number_format($deptBudget, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($deptActual, 2) }}</td>
                        <td class="text-right font-mono {{ $deptVar < 0 ? 'text-danger-600' : 'text-success-600' }}">
                            {{ $deptVar < 0 ? '(' : '' }}₱{{ number_format(abs($deptVar), 2) }}{{ $deptVar < 0 ? ')' : '' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-secondary-400 py-8">No budget data found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($itemizedData->isNotEmpty())
            <tfoot class="bg-gray-100 font-bold">
                <tr>
                    <td class="text-right">TOTAL</td>
                    <td class="text-right font-mono">₱{{ number_format($totals['monthly_budget'], 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totals['monthly_actual'], 2) }}</td>
                    <td class="text-right font-mono {{ $totals['variance'] < 0 ? 'text-danger-600' : 'text-success-600' }}">
                        {{ $totals['variance'] < 0 ? '(' : '' }}₱{{ number_format(abs($totals['variance']), 2) }}{{ $totals['variance'] < 0 ? ')' : '' }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var chartLabels = @json($monthLabels);
    var chartBudget = @json($monthlyChartData->pluck('budget')->values()->toArray());
    var chartActual = @json($monthlyChartData->pluck('actual')->values()->toArray());

    function loadChartJs(callback) {
        if (typeof Chart !== 'undefined') { callback(); return; }
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        s.onload = callback;
        document.head.appendChild(s);
    }

    function renderChart() {
        var canvas = document.getElementById('trendChart');
        if (!canvas) return;

        loadChartJs(function() {
            var existing = Chart.getChart(canvas);
            if (existing) existing.destroy();

            new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Budget',
                            data: chartBudget,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true, tension: 0.3
                        },
                        {
                            label: 'Actual',
                            data: chartActual,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true, tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': \u20B1' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: function(v) { return '\u20B1' + (v / 1000).toFixed(0) + 'K'; } }
                        }
                    }
                }
            });
        });
    }

    // Run immediately (Turbo already loaded the page at this point)
    renderChart();
})();
</script>
@endpush
